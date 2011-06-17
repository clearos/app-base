<?php

/**
 * Daemon class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\base;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\base\Software as Software;

clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('base/Software');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Daemon class.
 *
 * A meta file is used to organize and manage the daemons on the system.
 * In an ideal world, we would be able to scan the list of init scripts in
 * /etc/rc.d and generate the service list on the fly.  Unfortunately
 * there are some inconsistencies that make this impossible.  The meta file
 * holds the following information:
 *
 *  - the RPM where the daemon lives
 *  - the daemon/process name (what you see with ps)
 *  - whether or not the daemon supports a "/etc/rc.d/init.d/<xyz> reload"
 *  - a short title (eg Apache Web Server)
 *
 * Note: a few daemons are not really "running" per se, but are part of
 * the kernel e.g. the firewall and bandwidth limiter.
 *
 * Ideally, the constructor would require the same parameter as the
 * Software class -- the name of the package.  However, some packages
 * can have more than one daemon.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Daemon extends Software
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_LS = '/bin/ls';
    const COMMAND_CHKCONFIG = '/sbin/chkconfig';
    const COMMAND_SERVICE = '/sbin/service';
    const COMMAND_PIDOF = '/sbin/pidof';
    const PATH_INITD = '/etc/rc.d/rc3.d';

    const STATUS_BUSY = 'busy';
    const STATUS_RUNNING = 'running';
    const STATUS_STARTING = 'starting';
    const STATUS_STOPPED = 'stopped';
    const STATUS_STOPPING = 'stopping';
    const STATUS_RESTARTING = 'restarting';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * @var string init script filename
     */

    protected $initscript;

    /**
     * @var string the process name
     */

    protected $processname;

    /**
     * @var string short title
     */

    protected $title;

    /**
     * @var string software package name
     */

    protected $package;

    /**
     * @var boolean TRUE if daemon supports configuration reload
     */

    protected $reloadable;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Daemon constructor.
     *
     * @param string $initscript filename of init script in /etc/rc.d.
     */

    public function __construct($initscript)
    {
        clearos_profile(__METHOD__, __LINE__);

        global $DAEMONS;

        include_once "Daemon.inc.php";

        if (file_exists("Daemon.custom.php"))
            include_once "Daemon.custom.php";

        if (isset($DAEMONS[$initscript][0])) {
            $this->initscript = $initscript;
            $this->package = $DAEMONS[$initscript][0];
            $this->processname = $DAEMONS[$initscript][1];
            $this->title = $DAEMONS[$initscript][3];

            if ($DAEMONS[$initscript][2] == "yes")
                $this->reloadable = TRUE;
            else
                $this->reloadable = FALSE;
        } else {
            $this->initscript = $initscript;
            $this->package = $initscript;
            $this->processname = $initscript;
            $this->title = $initscript;
            $this->reloadable = FALSE;
        }

        parent::__construct($this->package);
    }

    /**
     * Returns the boot state of the daemon.
     *
     * @return boolean TRUE if daemon is set to run at boot
     * @throws Engine_Exception
     */

    public function get_boot_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_installed())
            throw new Engine_Exception(lang('daemon_not_installed'), CLEAROS_ERROR);

        $folder = new Folder(self::PATH_INITD);
        $listing = $folder->get_listing();

        foreach ($listing as $file) {
            if (preg_match("/^S\d+$this->initscript$/", $file))
                return TRUE;
        }

        return FALSE;
    }

    /**
     * Returns the running state of the daemon.
     *
     * @return boolean TRUE if the daemon is running
     * @throws Engine_Exception
     */

    public function get_running_state()
    {
        clearos_profile(__METHOD__, __LINE__);

	// TODO: this is kludgy
	if ($this->processname === 'kernel')
		return TRUE;

        $file = new File("/var/run/" . $this->processname . ".pid");

        if ($file->exists())
            return TRUE;

        // pidof will return non-zero if process not found, so avoid triggering exception
        $options['validate_exit_code'] = FALSE;

        $shell = new Shell();
        $exit_code = $shell->execute(self::COMMAND_PIDOF, "-x -s $this->processname", FALSE, $options);

        if ($exit_code == 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns the process name of the daemon.
     *
     * @return string process name
     * @throws Engine_Exception
     */

    public function get_process_name()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->processname;
    }

    /**
     * Returns the status of the daemon.
     *
     * Status codes:
     * - stopped
     * - running
     * - stopping
     * - starting
     *
     * @return string status code
     * @throws Engine_Exception
     */

    public function get_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        // KLUDGE: this is a bit dirty and distro-specific
        $shell = new Shell();
        $shell->execute('/bin/ps', 'ax', FALSE);

        $ps_output = $shell->get_output();

        foreach ($ps_output as $line) {
            if (preg_match("/service $this->initscript stop/", $line))
                return self::STATUS_STOPPING;
            else if (preg_match("/service $this->initscript start/", $line))
                return self::STATUS_STARTING;
            else if (preg_match("/service $this->initscript restart/", $line))
                return self::STATUS_RESTARTING;
            else if (preg_match("/service $this->initscript /", $line))
                return self::STATUS_BUSY;
        }

        $retval = ($this->get_running_state()) ? self::STATUS_RUNNING : self::STATUS_STOPPED;

        return $retval;
    }

    /**
     * Returns a short title for the daemon (eg Apache Web Server).
     *
     * @return string short title for daemon
     * @throws Engine_Exception
     */

    public function get_title()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->title;
    }

    /**
     * Restarts the daemon if (and only if) it is already running.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function reset($background = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->get_running_state())
            return;

        $args = ($this->reloadable) ? 'reload' : 'restart';
        $options['stdin'] = 'use_popen';
        $options['background'] = $background;

        $shell = new Shell();
        $shell->execute(self::COMMAND_SERVICE, "$this->initscript $args", TRUE, $options);
    }

    /**
     * Restarts the daemon.
     *
     * @see Daemon::reset()
     * @return void
     * @throws Engine_Exception
     */

    public function restart($background = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['stdin'] = "use_popen";
        $options['background'] = $background;

        $shell = new Shell();
        $shell->execute(self::COMMAND_SERVICE, "$this->initscript restart", TRUE, $options);
    }

    /**
     * Sets the boot state of the daemon.
     *
     * @param boolean $state desired boot state
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_boot_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_state($state));

        if (! $this->is_installed())
            throw new Engine_Exception(lang('daemon_not_installed'), CLEAROS_ERROR);

        $args = ($state) ? 'on' : 'off';

        $shell = new Shell();
        $shell->execute(self::COMMAND_CHKCONFIG, "--level 345 $this->initscript $args", TRUE);
    }

    /**
     * Sets running state of the daemon.
     *
     * @param boolean $state desired running state
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_running_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_state($state));

        if (! $this->is_installed())
            throw new Engine_Exception(lang('daemon_not_installed'), CLEAROS_ERROR);

        $is_running = $this->get_running_state();

        if ($is_running && $state) {
            // issued start on already running daemon
            return;
        } else if (!$is_running && !$state) {
            // issued stop on already stopped daemon
            return;
        }

        $args = ($state) ? 'start' : 'stop';
        $options['stdin'] = "use_popen";

        $shell = new Shell();
        $shell->execute(self::COMMAND_SERVICE, "$this->initscript $args", TRUE, $options);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validate state variable.
     *
     * @param boolean $state state
     *
     * @return string error message if state is invalid.
     */
    
    public function validate_state($state)
    {
        if (! is_bool($state))
            return lang('base_validate_state_invalid');
    }
}
