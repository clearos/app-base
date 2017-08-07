<?php

/**
 * Yum package management class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
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
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Yum_Busy_Exception as Yum_Busy_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Yum_Busy_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Yum package management class.
 *
 * The software classes contains information about a given RPM package.
 * The software constructor requires the pkgname - release and version are
 * optional.  Why do you need the release and version?  Some packages
 * can have multiple version installed, notably the kernel.
 *
 * If you do not specify the release and version name (which is the typical
 * way to call this constructor), then this class will assume that you mean
 * the most recent version.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Yum extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_WC_YUM = '/usr/sbin/wc-yum';
    const COMMAND_YUM = '/usr/bin/yum';
    const COMMAND_YUM_CONFIG_MANAGER = '/usr/bin/yum-config-manager';
    const COMMAND_PID = '/sbin/pidof';
    const COMMAND_YUM_INSTALL = '/usr/sbin/yum-install';
    const FILE_CACHE_REPO_LIST = 'yum_repo.list';
    const FILE_LOG = 'yum.log';
    const REPO_ACTIVE = 1;
    const REPO_DISABLED = 2;
    const REPO_ALL = 3;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $cache_repo_list;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Yum constructor.
     *
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Cleans yum cache.
     *
     * @param boolean $run_in_background if FALSE, do not run in background (default = FALSE)
     *
     * @return void
     * @throws Engine_Exception, Yum_Busy_Exception
     */

    public function clean($run_in_background = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options = array();
        $options['proxy'] = TRUE;

        if ($run_in_background)
            $options['background'] = TRUE;

        $shell = new Shell();
        $shell->execute(self::COMMAND_YUM, '--enablerepo=* clean all', TRUE, $options);
    }

    /**
     * Install a list of packages using wc-yum.
     *
     * @param array   $list              list of package names to install
     * @param boolean $run_in_background if FALSE, do not run in background (default = TRUE)
     *
     * @return void
     * @throws Engine_Exception, Yum_Busy_Exception
     */

    public function install($list, $run_in_background = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Bail if busy
        if ($this->is_busy())
            throw new Yum_Busy_Exception();

        // Delete old yum log output file
        $log = new File(CLEAROS_TEMP_DIR . '/' . self::FILE_LOG);
        if ($log->exists())
            $log->delete();

        // Run install
        // Yum caching is problematic.  See example in tracker #1562.
        $options['proxy'] = TRUE;

        $shell = new Shell();
        $shell->execute(self::COMMAND_YUM, '--enablerepo=* clean metadata', TRUE, $options);

        $options = array('log' => self::FILE_LOG);

        if ($run_in_background)
            $options['background'] = TRUE;

        $shell->execute(self::COMMAND_WC_YUM, "-i " . implode(" ", $list), TRUE, $options);
    }

    /**
     * Local install an RPM.
     *
     * @param array   $list              list of rpm filenames to install
     * @param boolean $run_in_background if FALSE, do not run in background (default = TRUE)
     *
     * @return void
     * @throws Engine_Exception, Yum_Busy_Exception
     */

    public function local_install($list, $run_in_background = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Bail if busy
        if ($this->is_busy())
            throw new Yum_Busy_Exception();

        // Run local install
        // Yum caching is problematic.  See example in tracker #1562.
        $options['proxy'] = TRUE;

        $shell = new Shell();
        $shell->execute(self::COMMAND_YUM, '--enablerepo=* clean metadata', TRUE, $options);

        if ($run_in_background)
            $options['background'] = TRUE;

        $shell->execute(self::COMMAND_YUM, '-y localinstall ' . implode(" ", $list), TRUE, $options);
    }

    /**
     * Install a list of packages using yum-install wrapper.
     *
     * @param array $list list of package names to install
     *
     * @return void
     * @throws Engine_Exception, Yum_Busy_Exception
     */

    public function run_upgrade($list)
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $options['background'] = TRUE;
        $options['proxy'] = TRUE;

        $shell->execute(self::COMMAND_YUM_INSTALL, implode(' ', $list), TRUE, $options);
    }

    /**
     * Returns TRUE if yum is already running.
     *
     * @return boolean TRUE if yum is running
     * @throws Engine_Exception
     */

    public function is_yum_busy()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $options['env'] = 'LANG=en_US';
        $options['proxy'] = TRUE;
        $options['validate_exit_code'] = FALSE;
        $exitcode = $shell->Execute(self::COMMAND_PID, "-s -x " . self::COMMAND_YUM, FALSE, $options);

        if ($exitcode == 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns TRUE if yum or wc-yum is already running.
     *
     * @return boolean TRUE if yum or wc-yum is running
     * @throws Engine_Exception
     */

    public function is_busy()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $options['env'] = 'LANG=en_US';
        $options['proxy'] = TRUE;
        $options['validate_exit_code'] = FALSE;
        $exitcode = $shell->Execute(self::COMMAND_PID, "-s -x " . self::COMMAND_YUM, FALSE, $options);

        if ($exitcode == 0)
            return TRUE;

        $exitcode = $shell->Execute(self::COMMAND_PID, "-s -x " . self::COMMAND_WC_YUM, FALSE, $options);

        if ($exitcode == 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns TRUE if the wc-yum is already running.
     *
     * @return boolean TRUE if wc-yum is running
     * @throws Engine_Exception
     */

    public function is_wc_busy()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $options['env'] = 'LANG=en_US';
        $options['validate_exit_code'] = FALSE;

        $exitcode = $shell->Execute(self::COMMAND_PID, "-s -x " . self::COMMAND_WC_YUM, FALSE, $options);

        if ($exitcode == 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns status of install.
     *
     * @return array status information
     */

    public function get_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        $logs = $this->get_logs();
        $logs = array_reverse($logs);

        foreach ($logs as $log) {
            $last = json_decode($log);

            // Make sure we're getting valid JSON
            if (!is_object($last))
                continue;

            $status = array(
                'code' => $last->code,
                'details' => $last->details,
                'progress' => $last->progress,
                'overall' => $last->overall,
                'errmsg' => $last->errmsg,
                'busy' => $this->is_busy(),
                'wc_busy' => $this->is_wc_busy()
            );  
            return $status;
        }
    }

    /**
     * Returns array of log lines.
     *
     * @return boolean TRUE if yum is running
     * @throws Engine_Exception
     */

    public function get_logs()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $sanitized = array();
            $log = new File(CLEAROS_TEMP_DIR . '/' . self::FILE_LOG);
            $lines = $log->get_contents_as_array();
            // Could be non-JSON in the log-file from yum plugins
            foreach ($lines as $line) { 
                if (json_decode($line) === NULL)
                    continue;
                array_push($sanitized, $line);
            }
        } catch (File_Not_Found_Exception $e) {
            // Send back empty array
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
        
        return $sanitized;
    }

    /**
     * Returns array of repositories that are available.
     * This function uses the 'yum repolist all' command and can be quite slow to update.
     * Use 'get_repo_list' function if details are not required.
     *
     * @return array a list of repositories
     * @throws Engine_Exception
     */

    public function get_live_repo_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $repo_list = array();

        // Check cache
        if ($this->_check_cache_repo_list(TRUE))
            return $this->cache_repo_list;

        // Get repos
        //----------
        $options['env'] = 'LANG=en_US';
        $options['proxy'] = TRUE;
        $exitcode = $shell->execute(self::COMMAND_YUM, '-v repolist all', TRUE, $options);

        if ($exitcode != 0) {
            // Run a 'clean all'...this can fix issues so next time this function is called it may work.
            $this->clean(TRUE);
            throw new Engine_Exception(lang('base_unable_to_get_software_list'), CLEAROS_WARNING);
        }
        $rows = $shell->get_output();
        foreach ($rows as $row) {
            if (preg_match("/(Repo-id\s+:\s)(.*)/", $row, $match)) { 
                $parts = explode('/', $match[2]);
                $id = trim($parts[0]);
                $repo_list[$id] = array ('packages' => 0);
            } else if (preg_match("/(Repo-name\s+:\s)(.*)/", $row, $match)) { 
                $repo_list[$id]['name'] = trim($match[2]);
            } else if (preg_match("/(Repo-status\s+:\s)(.*)/", $row, $match)) { 
                $repo_list[$id]['enabled'] = preg_match('/enabled/', $match[2]) ? TRUE : FALSE;
            } else if (preg_match("/(Repo-pkgs\s+:\s)(.*)/", $row, $match)) { 
                $repo_list[$id]['packages'] = preg_replace('/,/', '', $match[2]);
            }
        }
        
        $this->_cache_repo_list($repo_list);

        return $repo_list;
    }

    /**
     * Returns array of repositories that are available.
     * This function uses the 'yum-config-manager' command and is very fast.  For more detailed information, use 'get_live_repo_data'.
     *
     * @param $disable_cache disables use of API cache
     *
     * @return array a list of repositories
     * @throws Engine_Exception
     */

    public function get_repo_list($disable_cache = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $repo_list = array();

        // Check cache
        if ($this->_check_cache_repo_list() && !$disable_cache)
            return $this->cache_repo_list;

        // Get repos
        //----------
        $options['env'] = 'LANG=en_US';
        $options['proxy'] = TRUE;

        $exitcode = $shell->execute(self::COMMAND_YUM_CONFIG_MANAGER, '', TRUE, $options);
        if ($exitcode != 0) {
            // Run a 'clean all'...this can fix issues so next time this function is called it may work.
            $this->clean(TRUE);
            throw new Engine_Exception(lang('base_unable_to_get_software_list'), CLEAROS_WARNING);
        }
        $rows = $shell->get_output();
        foreach ($rows as $row) {
            if (preg_match("/^\\[(.*)\\]$/", $row, $match)) { 
                $id = trim($match[1]);
                $repo_list[$id] = array ('packages' => 0, 'enabled' => FALSE);
            } else if (preg_match("/^enabled\s+=\s+True/i", $row, $match)) { 
                $repo_list[$id]['enabled'] = TRUE;
            } else if (preg_match("/^enabled\s+=\s+1/i", $row, $match)) {
                $repo_list[$id]['enabled'] = TRUE;
            }
        }
        
        $this->_cache_repo_list($repo_list);

        return $repo_list;
    }

    /**
     * Returns boolean indicating whether import is currently running.
     *
     * @param mixed   $name    repo name
     * @param boolean $enabled boolean
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_enabled($name, $enabled)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Enabled repos
        //--------------

        if (is_array($name))
            $name = implode(' ' , $name);

        $options['proxy'] = TRUE;

        $shell = new Shell();
        $retval = $shell->execute(self::COMMAND_YUM_CONFIG_MANAGER, ($enabled ? '--enable ' : '--disable ') . $name, TRUE, $options);

        if ($retval != 0) {
            $errstr = $shell->get_last_output_line();
            throw new Engine_Exception($errstr);
        }

        $this->_delete_cache_repo_list();
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Save to cache
     *
     * @param array $repo_list repository list
     *
     * @access private
     * @return void
     */

    protected function _cache_repo_list($repo_list)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            // Save cached copy
            $file = new File(CLEAROS_CACHE_DIR . "/" . self::FILE_CACHE_REPO_LIST);
            if ($file->exists())
                $file->delete();
            $file->create('webconfig', 'webconfig', '0644');
            $file->add_lines(serialize($repo_list));
        } catch (Exception $e) {
            clearos_profile('Cache Error occurred ' . clearos_exception_message($e), __LINE__);
        }
    }

    /**
     * Check the cache availability
     *
     * @param boolean $live get repo list using live update
     *
     * @access private
     * @return boolean true if cached data available
     */

    protected function _check_cache_repo_list($live = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            // 4 hours in seconds
            $cache_time = 14400;
            $filename = CLEAROS_CACHE_DIR . "/" . self::FILE_CACHE_REPO_LIST; 

            if (file_exists($filename))
                $lastmod = filemtime($filename);
            else
                return FALSE;

            if ($lastmod && (time() - $lastmod < $cache_time)) {
                $list = unserialize(file_get_contents($filename));
                if ($live) {
                    $live_cache_ok = FALSE;
                    // Live function has number of packages data...loop through to see which function
                    // Cached data most recently.  If we have incomplete data, force update with appropriate
                    // function call
                    foreach ($list as $id => $repo) {
                        if ($repo['packages'] > 0) {
                            $live_cache_ok = TRUE;
                            break;
                        }
                    }
                    if (!$live_cache_ok)
                        return FALSE;
                }
                $this->cache_repo_list = unserialize(file_get_contents($filename));
                return TRUE;
            }
            return FALSE;
        } catch (Exception $e) {
            clearos_profile('Cache Error occurred ' . clearos_exception_message($e), __LINE__);
            return FALSE;
        }
    }

    /**
     * Deletes a cache repo list file.
     *
     * @return void
     *
     */

    protected function _delete_cache_repo_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            // If Marketplace exists, delete cached files
            if (clearos_library_installed('marketplace/Marketplace')) {
                clearos_load_library('marketplace/Marketplace');
                $marketplace = new \clearos\apps\marketplace\Marketplace();   
                $marketplace->delete_cache();
            }

            $file = new File(CLEAROS_CACHE_DIR . "/" . self::FILE_CACHE_REPO_LIST);
            if ($file->exists())
                $file->delete();
        } catch (Exception $e) {
            clearos_profile('Cache Error occurred ' . clearos_exception_message($e), __LINE__);
            throw new Engine_Exception(clearos_exception_message($e));
        }
    }
}
