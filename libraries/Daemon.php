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
// FIXME use \clearos\ as Syswatch;

clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('base/Software');
// FIXME clearos_load_library('syswatch/Syswatch');

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

    const CMD_LS = '/bin/ls';
    const CMD_CHKCONFIG = '/sbin/chkconfig';
    const CMD_SERVICE = '/sbin/service';
    const CMD_PIDOF = '/sbin/pidof';
    const PATH_INITD = '/etc/rc.d/rc3.d';

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

        try {
            $folder = new Folder(self::PATH_INITD);
            $listing = $folder->get_listing();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_ERROR);
        }

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

        $file = new File("/var/run/" . $this->processname . ".pid");

        if ($file->exists())
            return TRUE;

        try {
            $shell = new Shell();
            $exitcode = $shell->execute(self::CMD_PIDOF, "-x -s $this->processname");
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_ERROR);
        }

        if ($exitcode == 0)
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
     * Returns a short title for the daemon (eg Apache Web Server).
     *
     * @return string short tile for daemon
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

    public function reset()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $isrunning = $this->get_running_state();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_ERROR);
        }

        if (! $isrunning)
            return;

        if ($this->reloadable)
            $args = "reload";
        else
            $args = "restart";

        try {
            $options['stdin'] = "use_popen";

            $shell = new Shell();
            $shell->execute(self::CMD_SERVICE, "$this->initscript $args", TRUE, $options);
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_ERROR);
        }
    }

    /**
     * Restarts the daemon.
     *
     * @see Daemon::reset()
     * @return void
     * @throws Engine_Exception
     */

    public function restart()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $options['stdin'] = "use_popen";

            $shell = new Shell();
            $shell->execute(self::CMD_SERVICE, "$this->initscript restart", TRUE, $options);
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_ERROR);
        }
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

        if (! is_bool($state))
            throw new Validation_Exception(lang('base_errmsg_invalid') . lang('daemon_boot'));

        if (! $this->is_installed())
            throw new Engine_Exception(lang('daemon_not_installed'), CLEAROS_ERROR);

        if ($state)
            $args = "on";
        else
            $args = "off";

        try {
            $shell = new Shell();
            $shell->execute(self::CMD_CHKCONFIG, "--level 345 $this->initscript $args", TRUE);
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), COMMON_FATAL);
        }
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

        if (! is_bool($state))
            throw new Validation_Exception(lang('base_errmsg_invalid') . lang('daemon_running'));

        if (! $this->is_installed())
            throw new Engine_Exception(lang('daemon_not_installed'), CLEAROS_ERROR);

        $isrunning = $this->get_running_state();

        if ($isrunning && $state) {
            // issued start on already running daemon
            return;
        } else if (!$isrunning && !$state) {
            // issued stop on already stopped daemon
            return;
        }

        if ($state)
            $args = "start";
        else
            $args = "stop";

        try {
            $options['stdin'] = "use_popen";

            $shell = new Shell();

            // TODO: there is some strange behavior with the Cups daemon that causes
            // PHP to hang.  A temporary workaround
            if (($this->package == "cups") && $state) {
                $file = new File("/etc/system/initialized/cups");
                if (! $file->exists()) {
                    include_once 'Syswatch.php';
                    $syswatch = new Syswatch();
                    $syswatch->SendSignal("61");

                    $file->create("root", "root", "0644");
                    sleep(10);
                } else {
                    $exitcode = $shell->execute(self::CMD_SERVICE, "$this->initscript $args", TRUE, $options);
                }
            } else {
                $exitcode = $shell->execute(self::CMD_SERVICE, "$this->initscript $args", TRUE, $options);
            }
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_ERROR);
        }
    }
}
