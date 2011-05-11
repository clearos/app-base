<?php

/**
 * Product class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2010-2011 ClearFoundation
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

clearos_load_library('base/Engine');
clearos_load_library('base/Configuration_File');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Product class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2010-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Product extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/product';

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
     * Returns free trial state.
     *
     * @return boolean state of free trials
     * @throws Engine_Exception
     */

    public function get_free_trial_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        if (isset($this->config['free_trial']) && ($this->config['free_trial'] === "0"))
            return FALSE;
        else
            return TRUE;
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

        return $this->config['name'];
    }

    /**
     * Returns portal URL.
     *
     * @return string portal URL
     * @throws Engine_Exception
     */

    public function get_portal_url()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['portal_url'];
    }

    /**
     * Returns redirect URL
     *
     * @return string redirect URL
     * @throws Engine_Exception
     */

    function get_redirect_url()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['redirect_url'];
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

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['vendor'];
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

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['version'];
    }

    /**
     * Returns the vendor code.
     *
     * @return string vendor code
     * @throws Engine_Exception
     */

    public function get_vendor()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['vendor'];
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
     * Sets the partner region ID.
     *
     * @param integer partner_region_id
     * @throws Engine_Exception
     */

    public function set_partner_region_id()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new Configuration_File(self::FILE_CONFIG);
            $updated = $file->replace_lines("/^partner_region_id\s*=.*/", ($id < 0 ? "" : "partner_region_id = " . $id), 1);
            if ($updated == 0 && $id > 0)
                $file->add_lines("partner_region_id = " . $id . "\n");
            $this->_load_config();
        } catch (Exception $e) {
            throw new EngineException(clearos_exception_message($e), COMMON_WARNING);
        }
    }
    
    /**
     * Loads configuration file.
     *
     * @access private
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new Configuration_File(self::FILE_CONFIG);
        $config = $file->load();

        foreach ($config as $key => $value)
            $this->config[$key] = preg_replace('/"/', '', $value);

        $this->is_loaded = TRUE;
    }
}
