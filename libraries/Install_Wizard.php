<?php

/**
 * Install wizard class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
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

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\OS as OS;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/OS');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Install wizard class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
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

    const FILE_STATE = '/var/clearos/base/wizard';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Install wizard constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
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
            'nav' => '/app/base/wizard',
            'title' => lang('base_getting_started'),
            'category' => lang('base_install_wizard'),
            'subcategory' => lang('base_network'),
            'type' => 'intro'
        );

        // Network
        //--------

        if (clearos_app_installed('network')) {
            clearos_load_language('network');

            $steps[] = array(
                'nav' => '/app/network/mode',
                'title' => lang('network_network_mode'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_network'),
                'type' => 'normal'
            );

            $steps[] = array(
                'nav' => '/app/network/iface',
                'title' => lang('network_network_interfaces'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_network'),
                'type' => 'normal'
            );

            if (clearos_app_installed('upstream_proxy')) {
                $steps[] = array(
                    'nav' => '/app/upstream_proxy/edit',
                    'title' => lang('network_upstream_proxy'),
                    'category' => lang('base_install_wizard'),
                    'subcategory' => lang('base_network'),
                    'type' => 'normal'
                );
            }

            $steps[] = array(
                'nav' => '/app/network/dns',
                'title' => lang('network_dns_servers'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_network'),
                'type' => 'normal'
            );
        }

        // Which Edition
        //--------------

        if (clearos_app_installed('edition')) {
            clearos_load_language('edition');

            $os = new OS();
            $os_name = $os->get_name();

            if (preg_match('/ClearOS Community/', $os_name)) {
                $steps[] = array(
                    'nav' => '/app/edition',
                    'title' => lang('edition_select_edition'),
                    'category' => lang('base_install_wizard'),
                    'subcategory' => lang('base_registration'),
                    'type' => 'intro'
                );
            }
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

        // Default hostname and domain
        //----------------------------

        if (clearos_app_installed('network')) {
            clearos_load_language('network');

            $steps[] = array(
                'nav' => '/app/network/domain',
                'title' => lang('network_internet_domain'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'normal'
            );

            $steps[] = array(
                'nav' => '/app/network/hostname',
                'title' => lang('network_hostname'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'normal'
            );
        }

        // Date
        //-----

        if (clearos_app_installed('date')) {
            clearos_load_language('date');

            $steps[] = array(
                'nav' => '/app/date/edit',
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
                'nav' => '/app/storage',
                'title' => lang('storage_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'normal'
            );
        }

        // Account Synchronization
        //------------------------

        if (clearos_app_installed('account_synchronization')) {
            clearos_load_language('account_synchronization');

            $steps[] = array(
                'nav' => '/app/account_synchronization',
                'title' => lang('account_synchronization_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'normal'
            );
        }

        // Marketplace
        //------------

        if (clearos_app_installed('marketplace')) {
            clearos_load_language('marketplace');

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/intro',
                'title' => lang('marketplace_getting_started'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('marketplace_marketplace'),
                'type' => 'intro'
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/index/server',
                'title' => lang('marketplace_server_apps'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('marketplace_marketplace'),
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/index/gateway',
                'title' => lang('marketplace_gateway_apps'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('marketplace_marketplace'),
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/index/network',
                'title' => lang('marketplace_network_apps'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('marketplace_marketplace'),
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/index/system',
                'title' => lang('marketplace_system_apps'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('marketplace_marketplace'),
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

        if ($state) {
            if (! $file->exists())
                $file->create('root', 'root', '0644');
        } else {
            if ($file->exists())
                $file->delete();
        }
    }
}
