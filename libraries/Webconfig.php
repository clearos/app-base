<?php

/**
 * Webconfig class.
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

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Posix_User as Posix_User;
use \clearos\apps\base\Webconfig as Webconfig;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Posix_User');
clearos_load_library('base/Webconfig');

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
 * @category   apps
 * @package    base
 * @subpackage libraries
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

    const FILE_CONFIG = '/etc/clearos/webconfig';
    const FILE_RESTART = '/var/clearos/base/webconfig_restart';
    const PATH_THEMES = '/usr/clearos/themes';

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
     * Returns configured theme.
     *
     * @return string theme
     * @throws Engine_Exception
     */

    public function get_theme()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        $theme = (empty($this->config['theme'])) ? 'default' : $this->config['theme'];

        return $theme;
    }

    /**
     * Returns the list of available themes for webconfig.
     *
     * @return array list of theme names
     * @throws Engine_Exception
     */

    public function get_themes()
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder = new Folder(self::PATH_THEMES);

        $theme_list = array();
        $folder_list = $folder->get_listing();

        foreach ($folder_list as $theme) {
            $file = new File(self::PATH_THEMES . '/' . $theme . '/info/info');

            if (!$file->exists())
                continue;

            // FIXME info/info -> deploy/info.php
            include self::PATH_THEMES . '/' . $theme . '/info/info';
            $theme_list[$theme] = $package;
        }

        // TODO: Sort by name, but key by theme directory
        return $theme_list;
    }

    /**
     * Returns configured theme mode.
     *
     * @return string theme mode
     * @throws Engine_Exception
     */

    public function get_theme_mode()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        return $this->config['theme_mode'];
    }

    /**
     * Restarts webconfig in a gentle way.
     *
     * A webconfig restart request through the GUI needs special handling.  
     * To avoid killing itself, a restart is handled by the external
     * clearsync system.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function reset_gently()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_RESTART);

        if ($file->exists())
            $file->delete();

        $file->create('root', 'root', '0644');
        $file->add_lines("restart requested\n");
    }

    /**
     * Sets the theme for webconfig.
     *
     * @param string $theme theme for webconfig
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_theme($theme)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter('theme', $theme);
    }

    /**
     * Sets the theme mode for webconfig.
     *
     * @param string $mode theme mode for webconfig
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_theme_mode($mode)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter('theme_mode', $mode);
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
            $rawdata = $config_file->load();
        } catch (File_Not_Found_Exception $e) {
            // Not fatal, set defaults below
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }

        if (isset($rawdata['allow_shell']) && preg_match("/(true|1)/i", $rawdata['allow_shell']))
            $this->config['allow_shell'] = TRUE;
        else
            $this->config['allow_shell'] = FALSE;

        if (isset($rawdata['theme_mode']) && !empty($rawdata['theme_mode']))
            $this->config['theme_mode'] = $rawdata['theme_mode'];
        else
            $this->config['theme_mode'] = 'normal';

        if (isset($rawdata['theme']) && !empty($rawdata['theme']))
            $this->config['theme'] = $rawdata['theme'];
        else
            $this->config['theme'] = 'default';

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
