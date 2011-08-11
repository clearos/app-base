<?php

/**
 * Access control class.
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

use \clearos\apps\base\Access_Control as Access_Control;
use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;

clearos_load_library('base/Access_Control');
clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Folder');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/File_Not_Found_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Access control class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Access_Control extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    // Files and paths
    const FILE_CONFIG = '/etc/clearos/base.d/access_control.conf';
    const FILE_CUSTOM = '/var/clearos/base/access_control/custom/access_control';
    const PATH_CUSTOM = '/var/clearos/base/access_control/custom';
    const PATH_PUBLIC = '/var/clearos/base/access_control/public';
    const PATH_AUTHENTICATED = '/var/clearos/base/access_control/authenticated';

    // Access types
    const TYPE_PUBLIC = 'public';
    const TYPE_CUSTOM = 'custom';
    const TYPE_AUTHENTICATED = 'authenticated';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $is_loaded = FALSE;
    protected $config = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Access control constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns state of admin access.
     *
     * @return boolean state of admin access
     * @throws Engine_Exception
     */

    public function get_custom_access_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['allow_custom'];
    }

    /**
     * Returns a list of valid custom users.
     *
     * @return array list of valid custom usernames
     */

    public function get_custom_users()
    {
        clearos_profile(__METHOD__, __LINE__);

        $users = array();

        $folder = new Folder(self::PATH_CUSTOM, FALSE);

        $configlets = $folder->get_listing();

        foreach ($configlets as $configlet) {
            $file = new File(self::PATH_CUSTOM . '/' . $configlet, FALSE);
            $lines = $file->get_contents_as_array();

            foreach ($lines as $line) {
                $parts = explode("=", $line);
                $users[] = trim($parts[0]);
            }
        }

        return array_unique($users);
    }

    /**
     * Returns state of user access.
     *
     * @return boolean state of user access
     * @throws Engine_Exception
     */

    public function get_authenticated_access_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['allow_authenticated'];
    }

    /**
     * Returns valid pages for a given user.
     *
     * @param string $username username
     *
     * @return array list of valid pages
     * @throws Engine_Exception
     */

    public function get_valid_pages($username)
    {
        clearos_profile(__METHOD__, __LINE__);

        $details = $this->get_valid_pages_details($username);

        $pages = array_merge(
            $details[Access_Control::TYPE_AUTHENTICATED],
            $details[Access_Control::TYPE_CUSTOM],
            $details[Access_Control::TYPE_PUBLIC]
        );

        return array_unique($pages);
    }

    /**
     * Returns valid pages for a given user.
     *
     * @param string $username username
     *
     * @return array list of valid pages
     * @throws Engine_Exception
     */

    public function get_valid_pages_details($username)
    {
        clearos_profile(__METHOD__, __LINE__);

        $valid_pages[Access_Control::TYPE_AUTHENTICATED] = array();
        $valid_pages[Access_Control::TYPE_CUSTOM] = array();
        $valid_pages[Access_Control::TYPE_PUBLIC] = array();

        // Process public pages
        //---------------------

        $folder = new Folder(self::PATH_PUBLIC, FALSE);

        $configlets = $folder->get_listing();

        foreach ($configlets as $configlet) {
            $file = new File(self::PATH_PUBLIC . '/' . $configlet, FALSE);
            $pages = $file->get_contents_as_array();
            $valid_pages[Access_Control::TYPE_PUBLIC] = array_merge($pages,  $valid_pages[Access_Control::TYPE_PUBLIC]);
        }

        // Process authenticated pages
        //----------------------------

        if ($this->get_authenticated_access_state()) {
            $folder = new Folder(self::PATH_AUTHENTICATED, FALSE);

            $configlets = $folder->get_listing();

            foreach ($configlets as $configlet) {
                $file = new File(self::PATH_AUTHENTICATED . '/' . $configlet, FALSE);
                $pages = $file->get_contents_as_array();
                $valid_pages[Access_Control::TYPE_AUTHENTICATED] = array_merge($pages,  $valid_pages[Access_Control::TYPE_AUTHENTICATED]);
            }
        }

        // Process custom pages
        //---------------------

        if ($this->get_custom_access_state()) {
            $folder = new Folder(self::PATH_CUSTOM, FALSE);

            $configlets = $folder->get_listing();

            foreach ($configlets as $configlet) {
                try {
                    $file = new File(self::PATH_CUSTOM . '/' . $configlet, FALSE);
                    $raw_pages = $file->lookup_value("/^$username\s*=\s*/");
                } catch (File_No_Match_Exception $e) {
                    // Not fatal
                } 

                if (! empty($raw_pages)) {
                    $raw_pages = preg_replace('/\s+/', '', $raw_pages);
                    $pages = explode(",", $raw_pages);
                    $valid_pages[Access_Control::TYPE_CUSTOM] = array_merge($pages,  $valid_pages[Access_Control::TYPE_CUSTOM]);
                }
            }
        }

        return $valid_pages;
    }

    /**
     * Sets state of admin access.
     *
     * @param boolean $state state of admin access
     *
     * @return boolean state of admin access
     * @throws Engine_Exception
     */

    public function set_custom_access_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $stateval = $state ? 1 : 0;

        $this->_set_parameter("allow_custom", $stateval);
    }

    /**
     * Sets the list of pages a custom may access.
     *
     * @param string $username admin username
     * @param array  $pages    string array of authorized pages
     *
     * @return void
     */

    public function set_valid_pages($username, $pages)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_CUSTOM);

        if (! $file->exists())
            $file->create("root", "root", "0644");

        if ($pages) {
            $value = implode("|", $pages);
            $match = $file->replace_lines("/^$username\s*=\s*/", "$username = $value\n");

            if (!$match)
                $file->add_lines("$username = $value\n");
        } else {
            $file->delete_lines("/^$username\s*=/");
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Loads configuration files.
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $config_file = new Configuration_File(self::FILE_CONFIG);
            $raw_data = $config_file->load();
        } catch (File_Not_Found_Exception $e) {
            // Not fatal, set defaults below
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message());
        }

        if (isset($raw_data['allow_authenticated']) && preg_match("/(false|0)/i", $raw_data['allow_authenticated']))
            $this->config['allow_authenticated'] = FALSE;
        else
            $this->config['allow_authenticated'] = TRUE;

        if (isset($raw_data['allow_custom']) && preg_match("/(false|1)/i", $raw_data['allow_custom']))
            $this->config['allow_custom'] = FALSE;
        else
            $this->config['allow_custom'] = TRUE;

        $this->is_loaded = TRUE;
    }

    /**
     * Sets a parameter in the config file.
     *
     * @param string $key   name of the key in the config file
     * @param string $value value for the key
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_CONFIG);

        if (! $file->exists())
            $file->create('root', 'root', '0644');

        $match = $file->replace_lines("/^$key\s*=\s*/", "$key = $value\n");

        if (!$match)
            $file->add_lines("$key = $value\n");

        $this->is_loaded = FALSE;
    }
}
