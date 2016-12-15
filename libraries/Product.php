<?php

/**
 * Product class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2010-2014 ClearFoundation
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
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\OS as OS;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('base/OS');

// Exceptions
//-----------

use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;

clearos_load_library('base/File_Not_Found_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Product class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2010-2014 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Product extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/product';
    const OS_COMMUNITY = 20000;
    const OS_BUSINESS = 110000;
    const OS_HOME = 120000;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $is_loaded = FALSE;
    protected $config = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Product constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns the product software ID.
     *
     * @return string product name
     * @throws Engine_Exception
     */

    public function get_software_id()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['software_id'];
    }

    /**
     * Returns the product name.
     *
     * @return string product name
     * @throws Engine_Exception
     */

    public function get_name()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        $name = empty($this->config['name']) ? '' : $this->config['name'];

        return $name;
    }

    /**
     * Returns redirect URL
     *
     * @return string redirect URL
     * @throws Engine_Exception
     */

    public function get_redirect_url()
    {
        clearos_profile(__METHOD__, __LINE__);

        $os = new OS();
        $os_name = preg_replace('/ /', '_', $os->get_name());
        $os_version = preg_replace('/ /', '_', $os->get_version());

        // TODO: not yet used, hard-code for now
        $full_url = 'http://www.clearos.com/' . $os_name . '/' . $os_version;

        return $full_url;
    }

    /**
     * Returns the product vendor.
     *
     * @return string product version
     * @throws Engine_Exception
     */

    public function get_vendor()
    {
        clearos_profile(__METHOD__, __LINE__);

        // FIXME: use constant or configuration
        return 'clear';
    }

    /**
     * Returns the product version.
     *
     * @return string product version
     * @throws Engine_Exception
     */

    public function get_version()
    {
        clearos_profile(__METHOD__, __LINE__);

        $os = new OS();

        return preg_replace('/ \(.*/', '', $os->get_version());
    }

    /**
     * Returns Java Web Services node count.
     *
     * @return integer nodes
     * @throws Engine_Exception
     */

    public function get_jws_nodes()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return (int)$this->config['jws_nodes'];
    }

    /**
     * Returns Java Web Services domain.
     *
     * @return String domain
     * @throws Engine_Exception
     */

    public function get_jws_domain()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['jws_domain'];
    }

    /**
     * Returns Java Web Services realm.
     *
     * @return String realm
     * @throws Engine_Exception
     */

    public function get_jws_realm()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['jws_realm'];
    }

    /**
     * Returns Java Web Services version.
     *
     * @return String version
     * @throws Engine_Exception
     */

    public function get_jws_version()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['jws_version'];
    }

    /**
     * Returns Java Web Services prefix.
     *
     * @return String prefix
     * @throws Engine_Exception
     */

    public function get_jws_prefix()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['jws_prefix'];
    }

    /**
     * Returns the partner region ID.
     *
     * @return int partner region ID
     * @throws Engine_Exception
     */

    public function get_partner_region_id()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        if (isset($this->config['partner_region_id']))
            return $this->config['partner_region_id'];
        else
            return 0;
    }

    /**
     * Set name.
     *
     * @param string $name name
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_name($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_name($name));

        $this->_set_parameter('name', $name);
    }

    /**
     * Set software ID.
     *
     * @param int $software_id software ID representing a version
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_software_id($software_id)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_software_id($software_id));

        $this->_set_parameter('software_id', $software_id);
    }

    /**
     * Sets the partner region ID.
     *
     * @param int $id region ID
     *
     * @return void
     * @throws Validation_Exception
     */

    public function set_partner_region_id($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter('partner_region_id', $id);
    }
    
    /**
     * Returns boolean value if platform is Home Edition.
     *
     * @return void
     */

    public function is_home()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (in_array($this->get_software_id(), range(self::OS_HOME, self::OS_HOME + 9999)))
            return TRUE;
        return FALSE;
    }
    
    /**
     * Returns boolean value if platform is Community Edition.
     *
     * @return void
     */

    public function is_community()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (in_array($this->get_software_id(), range(self::OS_COMMUNITY, self::OS_COMMUNITY + 9999)))
            return TRUE;
        return FALSE;
    }
    
    /**
     * Returns boolean value if platform is Business Edition.
     *
     * @return void
     */

    public function is_business()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (in_array($this->get_software_id(), range(self::OS_BUSINESS, self::OS_BUSINESS + 9999)))
            return TRUE;
        return FALSE;
    }
    
    /**
     * Loads configuration file.
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new Configuration_File(self::FILE_CONFIG);
            $config = $file->load();

            foreach ($config as $key => $value)
                $this->config[$key] = preg_replace('/"/', '', $value);
        } catch (File_Not_Found_Exception $e) {
            // Not fatal
        }

        $this->is_loaded = TRUE;
    }

    /**
     * Generic set routine.
     *
     * @param string $key   key name
     * @param string $value value for the key
     *
     * @return void
     * @throws Engine_Exception
     */

    function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG, TRUE);

            $match = $file->replace_lines("/^$key\s*=\s*/", "$key = $value\n");

            if (!$match)
                $file->add_lines("$key=$value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = FALSE;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   R O U T I N E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for name
     *
     * @param string $name name
     *
     * @return boolean TRUE if name is valid
     */

    public function validate_name($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^[A-Za-z0-9\.\- ]+$/", $name))
            return lang('base_product_name_invalid');
    }

    /**
     * Validation routine for software ID
     *
     * @param string $software_id Software ID software_id
     *
     * @return boolean TRUE if software_id is valid
     */

    public function validate_software_id($software_id)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_numeric($software_id))
            return lang('base_software_id_invalid') . $software_id;
    }

}
