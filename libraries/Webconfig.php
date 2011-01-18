<?php

/**
 * Webconfig class.
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

$bootstrap = isset($_ENV['CLEAROS_BOOTSTRAP']) ? $_ENV['CLEAROS_BOOTSTRAP'] : '/usr/clearos/framework/shared';
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

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Posix_User as Posix_User;
// use \clearos\ as User;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Posix_User');
// clearos_load_library('user/User');

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
 * Webconfig class.
 *
 * Only application-level methods are in this class.  In other words, no
 * GUI components are found here.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Webconfig extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/system/webconfig';
    const FILE_ACCESS_DATA = '/etc/system/webconfig-access';
    const FILE_SETUP_FLAG = '/etc/system/initialized/setup';
    const FILE_INSTALL_SETTINGS = '/usr/share/system/settings/install';
    const PATH_CACHE = '/htdocs/tmp';
    const ACCESS_TYPE_PUBLIC = 'public';
    const ACCESS_TYPE_USER = 'regular';
    const ACCESS_TYPE_SUBADMIN = 'subadmin';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $is_loaded = FALSE;
    protected $config = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Webconfig constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct("webconfig-httpd");
    }

    /**
     * Authenticates user.
     *
     * @param string $username username
     * @param string $password password
     *
     * @return TRUE if authentication is successful
     * @throws Engine_Exception
     */

    public function authenticate($username, $password)
    {
        clearos_profile(__METHOD__, __LINE__);

        $is_valid = FALSE;

        if ($username == "root") {
            clearos_load_library('base/Posix_User');

            try {
                $user = new Posix_User($username);
                $is_valid = $user->check_password($password);
            } catch (Engine_Exception $e) {
                throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
            }
        } else {
            /*
            FIXME
            if (! file_exists(CLEAROS_CORE_DIR . '/api/User.class.php'))
                exit();

                require_once(CLEAROS_CORE_DIR . '/api/User.class.php');

                try {
                    $user = new User($username);
                    $passwordok = $user->check_password($password, 'pcnWebconfigPassword');
            } catch (Engine_Exception $e) {
                throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
            }
            */
        }

        return $is_valid;
    }

    /**
     * Clears cache files.
     *
     * @return void
     */

    public function clear_cache()
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder = new Folder(CLEAROS_CORE_DIR . self::PATH_CACHE, TRUE);

        try {
            if ($folder->exists())
                $folder->delete(TRUE);

            $folder->create("webconfig", "webconfig", "0755");
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }
    }

    /**
     * Returns state of admin access.
     *
     * @return boolean state of admin access
     * @throws Engine_Exception
     */

    public function get_admin_access_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['allow_subadmins'];
    }

    /**
     * Returns a list of valid subadmins.
     *
     * @return array list of valid usernames
     */

    public function get_admin_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $admins = array();

        try {
            $file = new File(self::FILE_ACCESS_DATA);

            if (!$file->exists())
                return $admins;

            $lines = $file->get_contents_as_array();

        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }

        foreach ($lines as $line) {
            $parts = explode("=", $line);
            $admins[] = trim($parts[0]);
        }

        return $admins;
    }

    /**
     * Returns redirect URL.
     *
     * @return string redirect URL
     * @throws Engine_Exception
     */

    public function get_redirect_url()
    {
        clearos_profile(__METHOD__, __LINE__);

        // This should probably move to a "vendor" class one day

        try {
            $configfile = new Configuration_File(self::FILE_INSTALL_SETTINGS);
            $configdata = $configfile->load();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }

        $url = isset($configdata['redirect_url']) ? $configdata['redirect_url'] : '';

        return $url;
    }

    /**
     * Returns state of shell access for users.
     *
     * @return boolean state of shell access
     * @throws Engine_Exception
     */

    public function get_shell_access_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['allow_shell'];
    }

    /**
     * Returns configured template.
     *
     * @return string online help URL
     * @throws Engine_Exception
     */

    public function get_template()
    {
        clearos_profile(__METHOD__, __LINE__);

        // For developers -- allow environment variable to override configuration
        if (isset($_ENV['WEBCONFIG_TEMPLATE']))
            return $_ENV['WEBCONFIG_TEMPLATE'];

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['template'];
    }

    /**
     * Returns the list of available templates for webconfig.
     *
     * @return array list of template names
     * @throws Engine_Exception
     */

    public function get_template_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder = new Folder(CLEAROS_CORE_DIR  . "/htdocs/templates");

        $templatelist = array();

        try {
            $folderlist = $folder->GetListing();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }

        foreach ($folderlist as $template) {
            if (preg_match("/(base)|(default)/", $template))
                continue;

            $templateinfo = array();

            try {
                $file = new Configuration_File(CLEAROS_CORE_DIR . "/htdocs/templates/" . $template . "/info");
                if ($file->exists())
                    $templateinfo = $file->load();
            } catch (Engine_Exception $e) {
                throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
            }

            $templatename = isset($templateinfo['name']) ? $templateinfo['name'] : $template;

            $templatelist[$templatename] = $template;
        }

        // Sort by name, but key by template directory

        $list = array();
        ksort($templatelist);

        foreach ($templatelist as $name => $folder)
        $list[$folder] = $name;

        return $list;
    }

    /**
     * Returns state of user access.
     *
     * @return boolean state of user access
     * @throws Engine_Exception
     */

    public function get_user_access_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['allow_user'];
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

        $valid_pages[Webconfig::ACCESS_TYPE_PUBLIC] = array();
        $valid_pages[Webconfig::ACCESS_TYPE_USER] = array();
        $valid_pages[Webconfig::ACCESS_TYPE_SUBADMIN] = array();

        // TODO:
        // - move these lists to a configuration file
        // - handle the servicestatus page a better way

        // FIXME: remove theme set from this list
        $valid_pages[Webconfig::ACCESS_TYPE_PUBLIC] = array(
            '/app/base/session/login',
            '/app/base/theme/set'
        );

        if ($this->get_user_access_state())
            $valid_pages[Webconfig::ACCESS_TYPE_USER] = array(
                '/app/base/session/logout',
                '/app/base/session/access_denied',
                '/app/user/profile',
                '/admin/security.php',
                '/admin/clearcenter-status.php'
        );

        if ($this->get_admin_access_state()) {
            try {
                $file = new File(self::FILE_ACCESS_DATA);
                $rawlist = $file->LookupValue("/^$username\s*=\s*/");
                $valid_pages[Webconfig::ACCESS_TYPE_SUBADMIN] = explode("|", $rawlist);
            } catch (File_Not_Found_Exception $e) {
                // Not fatal
            } catch (File_No_Match_Exception $e) {
                // Not fatal
            } catch (Engine_Exception $e) {
                throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
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

    public function set_admin_access_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $stateval = $state ? 1 : 0;

        $this->_set_parameter("allow_subadmins", $stateval);
    }

    /**
     * Sets the template for webconfig.
     *
     * @param string $template template for webconfig
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_template($template)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter("template", $template);
    }

    /**
     * Sets the list of pages a subadmin may access.
     *
     * @param string $username admin username
     * @param array  $pages    string array of authorized pages
     *
     * @return void
     */

    public function set_valid_pages($username, $pages)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_ACCESS_DATA);

        try {
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

        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }
    }

    /**
     * Sets the state of the setup/upgrade wizard.
     *
     * @param boolean $state state of setup/upgrade wizard
     *
     * @return void
     */

    public function set_setup_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_SETUP_FLAG);

            if ($state && !$file->exists())
                $file->create("root", "root", "0644");
            else if (!$state && $file->exists())
                $file->delete();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
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

        $configfile = new Configuration_File(self::FILE_CONFIG);

        try {
            $rawdata = $configfile->load();

            if (isset($rawdata['allow_user']) && preg_match("/(true|1)/i", $rawdata['allow_user']))
                $this->config['allow_user'] = TRUE;
            else
                $this->config['allow_user'] = FALSE;

            if (isset($rawdata['allow_subadmins']) && preg_match("/(true|1)/i", $rawdata['allow_subadmins']))
                $this->config['allow_subadmins'] = TRUE;
            else
                $this->config['allow_subadmins'] = FALSE;

            if (isset($rawdata['allow_shell']) && preg_match("/(true|1)/i", $rawdata['allow_shell']))
                $this->config['allow_shell'] = TRUE;
            else
                $this->config['allow_shell'] = FALSE;

            $this->config['template'] = $rawdata['template'];

        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }

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

        try {
            $file = new File(self::FILE_CONFIG);
            $match = $file->replace_lines("/^$key\s*=\s*/", "$key = $value\n");

            if (!$match)
                $file->add_lines("$key = $value\n");
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }

        $this->is_loaded = FALSE;
    }
}
