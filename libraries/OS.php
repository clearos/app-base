<?php

/**
 * Operating system class.
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
use \clearos\apps\base\Product as Product;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Product');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/Engine_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Operating system class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class OS extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $os = NULL;
    protected $version = NULL;

    const FILE_RELEASE = '/etc/clearos-release';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Os constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns the base version of the operating system/distribution.
     *
     * @return string OS version
     * @throws Engine_Exception
     */

    public function get_base_version()
    {
        clearos_profile(__METHOD__, __LINE__);

        $version = $this->get_version();

        return preg_replace('/\..*/', '', $version);
    }

    /**
     * Returns the name of the operating system/distribution.
     *
     * @return string OS name
     * @throws Engine_Exception
     */

    public function get_name()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Try /etc/product first
        $product = new Product();
        $name = $product->get_name();

        // Fall back to /etc/release
        if (empty($name)) {
            if (is_null($this->os))
                $this->_load_config();

            $name = $this->os;
        }

        return $name;
    }

    /**
     * Returns the version of the operating system/distribution.
     *
     * @return string OS version
     * @throws Engine_Exception
     */

    public function get_version()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->version))
            $this->_load_config();

        return $this->version;
    }

    /**
     * Returns array of system information for convenience.
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_system_info()
    {
        clearos_profile(__METHOD__, __LINE__);

        $data = array(
            'name' => $this->get_name(),
            'version' => $this->get_version()
        );
        if (clearos_library_installed('registration/Registration')) {
            clearos_load_library('registration/Registration');
            $registration = new \clearos\apps\registration\Registration();   
            if (file_exists($registration::FILE_REGISTERED_FLAG))
               $data['registered'] = lang('base_yes'); 
            else
               $data['registered'] = lang('base_no'); 
        }

        return $data;
    }

    /**
     * Populates version and name fields.
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_RELEASE);
        $contents = $file->get_contents();

        $osinfo = explode(" release ", $contents);

        if (count($osinfo) == 0)
            throw new Engine_Exception(lang('base_unknown'));

        $this->os = $osinfo[0];
        if (count($osinfo) == 2)
            $this->version = $osinfo[1];
        else
            $this->version = "";
    }
}
