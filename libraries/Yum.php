<?php

/**
 * Yum package management class.
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

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\marketplace\Marketplace as Marketplace;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Shell');
clearos_load_library('marketplace/Marketplace');


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
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
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
    const COMMAND_PID = "/sbin/pidof";
    const FILE_CACHE_REPO_LIST = "yum_repo.list";
    const FILE_LOG = "yum.log";
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
     * Install a list of packages using YUM.
     *
     * @param array $list list of package names to install
     *
     * @return void
     * @throws Engine_Exception, Yum_Busy_Exception
     */

    public function install($list)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->is_busy())
            throw new Yum_Busy_Exception();

        try {
            // Delete old yum log output file
            $log = new File(CLEAROS_TEMP_DIR . "/" . self::FILE_LOG);
            if ($log->exists())
                $log->delete();
            
            $shell = new Shell();

            $options = array(
                'background' => TRUE,
                'log' =>self::FILE_LOG
            );

            $exitcode = $shell->execute(self::COMMAND_WC_YUM, "-i " . implode(" ", $list), TRUE, $options);

            if ($exitcode != 0)
                throw new Engine_Exception(lang('base_yum_something_went_wrong'), CLEAROS_ERROR);

            $output = $shell->get_output();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Returns TRUE if the yum is already running.
     *
     * @return boolean TRUE if yum is running
     * @throws Engine_Exception
     */

    public function is_busy()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();
            $options['env'] = 'LANG=en_US';
            $options['validate_exit_code'] = FALSE;
            $exitcode = $shell->Execute(self::COMMAND_PID, "-s -x " . self::COMMAND_YUM, FALSE, $options);

            if ($exitcode == 0)
                return TRUE;

            $exitcode = $shell->Execute(self::COMMAND_PID, "-s -x " . self::COMMAND_WC_YUM, FALSE, $options);

            if ($exitcode == 0)
                return TRUE;
            else
                return FALSE;
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
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

        try {
            $shell = new Shell();
            $options['env'] = 'LANG=en_US';
            $options['validate_exit_code'] = FALSE;

            $exitcode = $shell->Execute(self::COMMAND_PID, "-s -x " . self::COMMAND_WC_YUM, FALSE, $options);

            if ($exitcode == 0)
                return TRUE;
            else
                return FALSE;
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
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
            $log = new File(CLEAROS_TEMP_DIR . "/" . self::FILE_LOG);
            $lines = $log->get_contents_as_array();
        } catch (File_Not_Found_Exception $e) {
            $lines = array();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
        
        return $lines;
    }

    /**
     * Returns boolean indicating whether import is currently running.
     *
     * @return boolean
     * @throws Engine_Exception
     */

    public function get_repo_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();
            $repo_list = array();

            // Check cache
            if ($this->_check_cache_repo_list())
                return $this->cache_repo_list;

            // Get repos
            //----------
            $options['env'] = 'LANG=en_US';
            $exitcode = $shell->execute(self::COMMAND_YUM, 'repolist all', TRUE, $options);
            if ($exitcode != 0)
                throw new Engine_Exception(lang('software_repository_unable_to_get_list'), CLEAROS_WARNING);
            $rows = $shell->get_output();
            foreach ($rows as $row) {
                if (preg_match("/([\w-]+)\s+([\w\\. _\(\)-]+)\s+enabled\\:\s*([\d\\,]+)$/", $row, $match)) 
                    $repo_list[] = array('id' => $match[1], 'name' => trim($match[2]), 'packages' => trim($match[3]), 'enabled' => 1);
                else if (preg_match("/([\w-]+)\s+([\w\\. _\(\)-]+)\s+disabled$/", $row, $match)) 
                    $repo_list[] = array('id' => $match[1], 'name' => trim($match[2]), 'packages' => 0, 'enabled' => 0);
            }
            
            $this->_cache_repo_list($repo_list);

            return $repo_list;
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e));
        }
    }

    /**
     * Returns boolean indicating whether import is currently running.
     *
     * @param String  $name    repo name
     * @param boolean $enabled boolean
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_enabled($name, $enabled)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            // Enabled repos
            //--------------
            $shell = new Shell();
            $retval = $shell->execute(self::COMMAND_YUM_CONFIG_MANAGER, ($enabled ? '--enable ' : '--disable ') . $name, TRUE);

            if ($retval != 0) {
                $errstr = $shell->get_last_output_line();
                throw new Engine_Exception($errstr, CLEAROS_WARNING);
            }
            $this->_delete_cache_repo_list();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e));
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Save to cache
     *
     * @access private
     *
     * @array $repo_list repository list
     *
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
     * @param string $sig signature
     *
     * @access private
     *
     * @return boolean true if cached data available
     */

    protected function _check_cache_repo_list()
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
                $marketplace = new Marketplace();   
                $marketplace->delete_cache();
            }
            $file = new File(CLEAROS_CACHE_DIR . "/" . self::FILE_CACHE_REPO_LIST);
            if ($file->exists())
                $file->delete();
        } catch (Exception $e) {
            clearos_profile('Cache Error occurred ' . clearos_exception_message($e), __LINE__);
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }
}
