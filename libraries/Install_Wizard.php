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

        if (clearos_app_installed('software_updates')) {
            clearos_load_language('software_updates');

            $os = new OS();
            $os_name = $os->get_name();

            if (preg_match('/ClearOS Community/', $os_name)) {
                $steps[] = array(
                    'nav' => '/app/base/edition',
                    'title' => lang('base_select_edition'),
                    'category' => lang('base_install_wizard'),
                    'subcategory' => lang('base_registration'),
                    'type' => 'intro'
                );
            }

            // Software Updates
            //-----------------

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
            clearos_load_language('registration');
            $steps[] = array(
                'nav' => '/app/date/edit',
                'title' => lang('date_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_configuration'),
                'type' => 'normal'
            );
        }

        // Marketplace
        //------------

        if (clearos_app_installed('marketplace')) {
            $steps[] = array(
                'nav' => '/app/marketplace/wizard/intro',
                'title' => 'Getting Started',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace',
                'type' => 'intro'
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/index/server',
                'title' => 'Server Apps',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace',
                'type' => 'wide'
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/index/gateway',
                'title' => 'Gateway Apps',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace',
                'type' => 'wide'
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/index/network',
                'title' => 'Network Apps',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace',
                'type' => 'wide'
            );

            $steps[] = array(
                'nav' => '/app/marketplace/wizard/index/system',
                'title' => 'System Apps',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace',
                'type' => 'wide'
            );

            $steps[] = array(
                'nav' => '/app/marketplace/install',
                'title' => 'Review',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace Wrap-up',
                'type' => 'wide'
            );

            $steps[] = array(
                'nav' => '/app/marketplace/progress',
                'title' => 'Install',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace Wrap-up',
                'type' => 'wide'
            );
        } else {
            // TODO
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
