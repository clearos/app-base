<?php

/**
 * Access control class.
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
 * @category   apps
 * @package    base
 * @subpackage libraries
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
    const PATH_REST = '/var/clearos/base/access_control/rest';
    const PATH_CUSTOM = '/var/clearos/base/access_control/custom';
    const PATH_PUBLIC = '/var/clearos/base/access_control/public';
    const PATH_AUTHENTICATED = '/var/clearos/base/access_control/authenticated';

    // Access types
    const TYPE_REST = 'rest';
    const TYPE_PUBLIC = 'public';
    const TYPE_CUSTOM = 'custom';
    const TYPE_ADMINISTRATORS = 'administrators';
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

        if ($_SERVER['SERVER_PORT'] == 82) {
            // Proxy and other splash pages are available with HTTPS (i.e. ugly SSL warning)
            $pages = $details[Access_Control::TYPE_PUBLIC];

        } else if ($_SERVER['SERVER_PORT'] == 83) {
            // Rest pages
            $pages = $details[Access_Control::TYPE_REST];

        } else if ($_SERVER['SERVER_PORT'] == 1501) {
            // Development gets everything
            $pages = array_merge(
                $details[Access_Control::TYPE_ADMINISTRATORS],
                $details[Access_Control::TYPE_AUTHENTICATED],
                $details[Access_Control::TYPE_CUSTOM],
                $details[Access_Control::TYPE_PUBLIC],
                $details[Access_Control::TYPE_REST]
            );
        } else {
            // Standard webconfig access control
            $pages = array_merge(
                $details[Access_Control::TYPE_ADMINISTRATORS],
                $details[Access_Control::TYPE_AUTHENTICATED],
                $details[Access_Control::TYPE_CUSTOM],
                $details[Access_Control::TYPE_PUBLIC]
            );
        }

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

        $valid_pages[Access_Control::TYPE_ADMINISTRATORS] = array();
        $valid_pages[Access_Control::TYPE_AUTHENTICATED] = array();
        $valid_pages[Access_Control::TYPE_CUSTOM] = array();
        $valid_pages[Access_Control::TYPE_PUBLIC] = array();
        $valid_pages[Access_Control::TYPE_REST] = array();

        // Process public pages
        //---------------------

        $folder = new Folder(self::PATH_PUBLIC, FALSE);

        $configlets = $folder->get_listing();

        foreach ($configlets as $configlet) {
            $options['skip_size_check'] = TRUE;

            $file = new File(self::PATH_PUBLIC . '/' . $configlet, FALSE, FALSE, $options);
            $pages = $file->get_contents_as_array();
            $valid_pages[Access_Control::TYPE_PUBLIC] = array_merge($pages, $valid_pages[Access_Control::TYPE_PUBLIC]);
        }

        // Process authenticated pages
        //----------------------------

        if ($this->get_authenticated_access_state()) {
            $folder = new Folder(self::PATH_AUTHENTICATED, FALSE);

            $configlets = $folder->get_listing();

            foreach ($configlets as $configlet) {
                $options['skip_size_check'] = TRUE;

                $file = new File(self::PATH_AUTHENTICATED . '/' . $configlet, FALSE, FALSE, $options);
                $pages = $file->get_contents_as_array();
                $valid_pages[Access_Control::TYPE_AUTHENTICATED] = array_merge($pages, $valid_pages[Access_Control::TYPE_AUTHENTICATED]);
            }
        }

        // Process administrators pages
        //-----------------------------

        if (clearos_app_installed('administrators') && !empty($username)) {
            clearos_load_library('administrators/Administrators');

            $administrators = new \clearos\apps\administrators\Administrators;
            $apps = $administrators->get_user_apps($username);

            $pages = array();

            foreach ($apps as $app)
                $pages[] = '/app/' . $app;

            $valid_pages[Access_Control::TYPE_ADMINISTRATORS] = array_merge($pages, $valid_pages[Access_Control::TYPE_ADMINISTRATORS]);
        }

        // Process custom pages
        // TODO: deprecate
        //---------------------

        if ($this->get_custom_access_state()) {
            $folder = new Folder(self::PATH_CUSTOM, FALSE);

            $configlets = $folder->get_listing();

            foreach ($configlets as $configlet) {
                try {
                    $options['skip_size_check'] = TRUE;

                    $file = new File(self::PATH_CUSTOM . '/' . $configlet, FALSE, FALSE, $options);
                    $raw_pages = $file->lookup_value("/^$username\s*=\s*/");
                } catch (File_No_Match_Exception $e) {
                    // Not fatal
                } 

                if (! empty($raw_pages)) {
                    $raw_pages = preg_replace('/\s+/', '', $raw_pages);
                    $pages = explode(",", $raw_pages);
                    $valid_pages[Access_Control::TYPE_CUSTOM] = array_merge($pages, $valid_pages[Access_Control::TYPE_CUSTOM]);
                }
            }
        }

        // Process REST pages
        //-------------------

        $folder = new Folder(self::PATH_REST, FALSE);

        $configlets = $folder->get_listing();

        foreach ($configlets as $configlet) {
            $options['skip_size_check'] = TRUE;

            $file = new File(self::PATH_REST . '/' . $configlet, FALSE, FALSE, $options);
            $pages = $file->get_contents_as_array();
            $valid_pages[Access_Control::TYPE_REST] = array_merge($pages, $valid_pages[Access_Control::TYPE_REST]);
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
