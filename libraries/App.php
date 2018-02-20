<?php

/**
 * App class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 ClearFoundation
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
use \clearos\apps\base\Software as Software;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Shell');
clearos_load_library('base/Software');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/Engine_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * App class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2003-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class App extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const APP_PREFIX = 'app-';
    const COMMAND_YUM = '/usr/bin/yum';
    const FILE_INSTALLED_APPS = 'installed_apps';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * @var string basename
     */

    protected $basename = NULL;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * App constructor.
     *
     * @param string $basename app basename
     */

    public function __construct($basename)
    {
        clearos_profile(__METHOD__, __LINE__);
        $this->basename = $basename;
    }

    /**
     * Get metadata.
     *
     * @returns array
     *
     * @throws Engine_Exception
     */

    public function get_metadata()
    {
        clearos_profile(__METHOD__, __LINE__);
        $app_base = clearos_app_base($this->basename);
        $info_file = $app_base . '/deploy/info.php';

        if (!file_exists($info_file))
            return false;

        // Load metadata file
        include $info_file;
        return $app;
    }

    /**
     * Remove app.
     *
     * @throws Engine_Exception
     */

    public function remove()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $dependencies = $this->get_dependencies($this->basename);

            if ($dependencies === FALSE)
                throw new Engine_Exception(lang('base_app_cannot_be_deleted'), CLEAROS_WARNING);

            // Create list of deps that are installed
            $installed_apps = array();
            foreach ($dependencies as $app) {
                $software = new Software($app);
                if ($software->is_installed()) {
                    clearos_log('app-base', 'uninstalling the following package: ' . $app);
                    $installed_apps[] = $app;
                }
            }

            $apps = implode(' ', $installed_apps);

            $options = array('validate_exit_code' => FALSE);
            $shell = new Shell();
            $exitcode = $shell->execute(self::COMMAND_YUM, " -y remove $apps", TRUE, $options);
            if ($exitcode != 0) {
                $err = $shell->get_first_output_line();
                throw new Engine_Exception(lang('base_unable_to_delete_app') . ': ' . $err . '.', CLEAROS_WARNING);
            }
            // This cache is sometimes used by Marketplace
            $file = new File(CLEAROS_CACHE_DIR . "/" . self::FILE_INSTALLED_APPS, TRUE);
            if ($file->exists())
                $file->delete();

        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

    /**
     * Get app dependencies
     *
     * @return array
     */

    function get_dependencies()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {

            $app = $this->get_metadata();

            if ($app !== FALSE && isset($app['delete_dependency'])) {
                // Always include app...do not include core...it may have dependencies to other apps
                $list = array(self::APP_PREFIX . preg_replace("/_/", "-", $this->basename));
                return array_merge($list, $app['delete_dependency']);
            }
            return array();;

        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }
}
