<?php

/**
 * Script helper class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2013 ClearFoundation
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

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Shell as Shell;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Shell');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/Engine_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Script class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Script extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $pid;
    protected $script_name;
    protected $lock_file;

    const DIR_LOCK = "/var/run/webconfig";
    const LOCK_SUFFIX = ".pid";
    const CMD_PS = '/bin/ps';
    const CMD_CAT = '/bin/cat';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Script constructor.
     *
     * @param string $script_name name of script
     */

    public function __construct($script_name = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);
        
        if ($script_name == NULL)
            $this->script_name = basename($_SERVER['SCRIPT_NAME']);
        else
            $this->script_name = $script_name;

        $this->lock_file = self::DIR_LOCK . '/' . $this->script_name . self::LOCK_SUFFIX;
    }

    /**
     * Create a lock file.
     *
     * @param int $interval interval (in seconds) to retry if busy
     * @param int $retries  number of retries
     *
     * @return boolean TRUE if lock file was created
     * @throws Engine_Exception
     */

    public function lock($interval = 0, $retries = 0)
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $file = new File($this->lock_file, TRUE);
            if ($file->exists()) {
                $this->pid = $file->get_contents();
                $running = $this->is_running();
                if ($running) {
                    for ($counter = 0; $counter < $retries; $counter++) {
                        sleep($interval);
                        $running = $this->is_running();
                    }
                    if ($running) {
                        clearos_log($this->script_name, 'Unable to start script - currently running.');
                        return FALSE;
                    }
                } else {
                    clearos_log($this->script_name, 'Dead PID found - removing lock file.');
                    $file->delete();
                }
            }
            $file->create('webconfig', 'webconfig', '0644');
            $this->pid = getmypid();
            $file->add_lines($this->pid);
            return TRUE;
        } catch (Exception $e) {
            clearos_log($this->script_name, sprintf('Unable to create lock file: %s.', clearos_exception_message($e)));
            throw new Engine_Exception(lang('base_unable_to_create_lock_file'), CLEAROS_WARNING);
        }
    }

    /**
     * Remove lock file.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function unlock()
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $file = new File($this->lock_file, TRUE);
            if ($file->exists())
                $file->delete();
        } catch (Exception $e) {
            throw new Engine_Exception(lang('base_unable_to_remove_lock_file'), CLEAROS_WARNING);
        }
    }

    /**
     * Returns boolean indicating whether script is running.
     *
     * @return boolean
     * @throws Engine_Exception
     */

    public function is_running()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {

            $shell = new Shell();
            $exitcode = $shell->execute(self::CMD_PS, " -e | awk '{print $1}'");
            if ($exitcode != 0)
                throw new Engine_Exception($shell->get_first_output_line(), CLEAROS_WARNING);
            $pids = $shell->get_output();
            if (in_array($this->pid, $pids)) {
                // Check remote case where PID has been re-used by another process
                $shell->execute(self::CMD_CAT, "/proc/" . $this->pid . '/cmdline');
                $cmdline = trim($shell->get_last_output_line());
                if (preg_match('/' . $this->script_name . "/", $cmdline))
                    return TRUE;
            }
            return FALSE;
        } catch (Exception $e) {
            clearos_log($this->script_name, sprintf('Unknown status: %s.', clearos_exception_message($e)));
            throw new Engine_Exception(
                lang('base_unknown_script_state') . ' (' . $this->script_name . ') - ' .
                clearos_exception_message($e), CLEAROS_WARNING
            );
        }
    }
}
