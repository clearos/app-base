<?php

/**
 * Install wizard class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
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

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\OS as OS;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/OS');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Install wizard class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Install_Wizard extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    // TODO: merge state into configuration file
    const FILE_STATE = '/var/clearos/base/wizard';
    const FILE_CONFIG = '/etc/clearos/base.d/wizard.conf';
    const FILE_PRE_REG_UPDATE = '/var/clearos/base/wizard_pre_update';
    const CMD_PRE_REG_UPDATES = '/usr/clearos/apps/base/deploy/wizard_update';
    const CMD_KILLALL = '/usr/bin/killall';
    const SCRIPT_UPGRADE = 'wizard_update';

    ///////////////////////////////////////////////////////////////////////////////
    // M E M B E R S
    ///////////////////////////////////////////////////////////////////////////////

    protected $CI = NULL;
    protected $config = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Install wizard constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
        $this->CI =& get_instance();
    }

    /**
     * Returns link to given step.
     *
     * @param integer $number install step
     *
     * @return string link to given step
     */

    public function get_step($number)
    {
        clearos_profile(__METHOD__, __LINE__);

        $steps = $this->get_steps();
        $number--;

        return preg_replace('/^\/app/', '', $steps[$number]['nav']);
    }

    /**
     * Returns steps in install wizard.
     *
     * @return array wizard steps
     */

    public function get_steps()
    {
        clearos_profile(__METHOD__, __LINE__);

        clearos_load_language('base');

        $steps = array();

        // Intro
        //------

        $steps[] = array(
            'nav' => '/app/base/wizard/index/start',
            'title' => lang('base_getting_started'),
            'category' => lang('base_install_wizard'),
            'subcategory' => lang('base_network_settings'),
            'type' => 'intro'
        );

        // Change Password
        //----------------

        if ($this->get_force_change_password_state()) {
            $steps[] = array(
                'nav' => '/app/base/session/change_password',
                'title' => lang('base_change_password'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_network_settings'),
                'type' => 'normal'
            );
        }

        // Network
        //--------

        if (clearos_app_installed('network')) {
            clearos_load_language('network');

            $steps[] = array(
                'nav' => '/app/network/mode',
                'title' => lang('network_network_mode'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_network_settings'),
                'inline_form' => TRUE,
                'type' => 'normal'
            );

            $steps[] = array(
                'nav' => '/app/network/iface',
                'title' => lang('network_network_interfaces'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_network_settings'),
                'type' => 'normal'
            );

            $steps[] = array(
                'nav' => '/app/network/dns',
                'title' => lang('network_dns_servers'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_network_settings'),
                'type' => 'normal'
            );
        }

        // Which Edition
        //--------------

        if (clearos_app_installed('edition')) {
            clearos_load_language('edition');

            $os = new OS();
            $os_name = $os->get_name();
            $os_version = $os->get_version();

            $steps[] = array(
                'nav' => '/app/edition',
                'title' => lang('edition_select_edition'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_registration'),
                'type' => 'intro'
            );
        }

        // Registration
        //-------------

        if (clearos_app_installed('registration')) {
            clearos_load_language('registration');
            $steps[] = array(
                'nav' => '/app/registration',
                'title' => lang('registration_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_registration'),
                'type' => 'normal'
            );
        }

        // Software Updates
        //-----------------

        if (clearos_app_installed('software_updates')) {
            clearos_load_language('software_updates');

            $steps[] = array(
                'nav' => '/app/software_updates/first_boot',
                'title' => lang('software_updates_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_registration'),
                'type' => 'normal'
            );
        }

        // Default hostname and domain
        //----------------------------

        if (clearos_app_installed('network')) {
            clearos_load_language('network');

            $steps[] = array(
                'nav' => '/app/network/domain',
                'title' => lang('network_internet_domain'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'inline_form' => TRUE,
                'type' => 'normal',
            );

            $steps[] = array(
                'nav' => '/app/network/hostname',
                'title' => lang('network_hostname'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'inline_form' => TRUE,
                'type' => 'normal'
            );
        }

        // Date
        //-----

        if (clearos_app_installed('date')) {
            clearos_load_language('date');

            $steps[] = array(
                'nav' => '/app/date',
                'title' => lang('date_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'normal'
            );
        }

        // Storage Manager
        //----------------

        if (clearos_app_installed('storage')) {
            clearos_load_language('storage');

            $steps[] = array(
                'nav' => '/app/storage/devices',
                'title' => lang('storage_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'normal'
            );
        }

        // Master / Slave Synchronization
        //-------------------------------

        /*
        if (clearos_app_installed('master_slave')) {
            clearos_load_language('master_slave');

            $steps[] = array(
                'nav' => '/app/master_slave/settings',
                'title' => lang('master_slave_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'normal'
            );
        }
        */

        // Marketplace
        //------------

        if (clearos_app_installed('marketplace')) {
            clearos_load_language('marketplace');

            $steps[] = array(
                'nav' => '/app/marketplace/wizard',
                'title' => lang('marketplace_getting_started'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_marketplace'),
                'type' => 'intro'
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/selection',
                'title' => lang('marketplace_app_selection'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_marketplace'),
            );

            $steps[] = array(
                'nav' => '/app/marketplace/install',
                'title' => lang('marketplace_app_review'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_finish'),
            );

            $steps[] = array(
                'nav' => '/app/marketplace/progress',
                'title' => lang('marketplace_download_and_install'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_finish'),
            );
        } else {
            $steps[] = array(
                'nav' => '/app/base/wizard/stop',
                'title' => lang('base_finish'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'intro'
            );
        }

        return $steps;
    }

    /**
     * Returns password changed state.
     *
     * @return boolean TRUE if default password has been changed
     * @throws Engine_Exception
     */

    public function get_password_changed_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_load_config();

        if (preg_match('/yes/i', $this->config['password_changed']))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns change default password state.
     *
     * @return boolean TRUE if change password is required
     * @throws Engine_Exception
     */

    public function get_force_change_password_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_load_config();

        if (preg_match('/yes/i', $this->config['force_change_password']))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns saved step.
     *
     * When a user logs out mid-wizard, the step is saved. 
     *
     * @return integer saved step
     */

    public function get_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_STATE);

        if ($file->exists())
            $step = trim($file->get_contents());

        if (($step < 0) || ($step > count($this->get_steps())))
            $step = 0;

        return $step;
    }

    /**
     * Sets password changed state.
     *
     * @param boolean $state state
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_password_changed_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $state_value = ($state) ? 'yes' : 'no';

        $file = new File(self::FILE_CONFIG);
        $file->replace_lines('/^password_changed\s*=/', "password_changed = $state_value\n");
    }

    /**
     * Starts the install wizard mode.
     *
     * @param boolean $state state of install wizard
     *
     * @return void
     */

    public function set_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_STATE);

        if ($file->exists())
            $file->delete();

        if ($state != -1) {
            $file->create('root', 'root', '0644');
            $file->add_lines("$state\n");
        }
    }

    /**
     * Run update script.
     *
     * @return void
     */

    public function run_update_script()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            // If the flag exists, don't run again
            $file = new File(self::FILE_PRE_REG_UPDATE);
            if ($file->exists())
                return;
            $file->create('webconfig', 'webconfig', '0644');

            $shell = new Shell();
            $options = array('background' => TRUE);
            $shell->execute(self::CMD_PRE_REG_UPDATES, NULL, FALSE, $options);
        } catch (\Exception $e) {
            // Don't do anything
        }
    }

    /**
     * Abort update script.
     *
     * @return void
     */

    public function abort_update_script()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();
            $shell->execute(self::CMD_KILLALL, self::SCRIPT_UPGRADE, TRUE);

            $file = new File(self::FILE_PRE_REG_UPDATE);
            if ($file->exists())
                $file->delete();
        } catch (\Exception $e) {
            // Don't do anything
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

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

        if (!empty($this->config))
            return $this->config;

        $file = new Configuration_File(self::FILE_CONFIG);

        $this->config = $file->load();
    }
}
