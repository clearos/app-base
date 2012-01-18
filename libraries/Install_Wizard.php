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

// Classes
//--------

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\Shell as Shell;

clearos_load_library('base/Engine');
clearos_load_library('base/Shell');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Software_Not_Installed_Exception as Software_Not_Installed_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Software_Not_Installed_Exception');


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
     * Returns link to first step.
     *
     * @return string link to first step
     */

    public function get_first_step()
    {
        clearos_profile(__METHOD__, __LINE__);

        $steps = $this->get_steps();

        return preg_replace('/^\/app/', '', $steps[0]['nav']);
    }

    /**
     * Returns steps in install wizard.
     *
     * @return array wizard steps
     */

    public function get_steps()
    {
        clearos_profile(__METHOD__, __LINE__);

        $steps = array();

        // Language
        //---------

        /*
        $steps[] = array(
            'nav' => '/app/language/edit',
            'title' => lang('language_app_name'),
            'category' => lang('base_install_wizard'),
            'subcategory' => lang('base_getting_started'),
            'type' => 'normal'
        );
        */

        // Network
        //--------

        if (clearos_app_installed('network')) {
            clearos_load_language('network');

            $steps[] = array(
                'nav' => '/app/network',
                'title' => lang('network_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_getting_started'),
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
                'subcategory' => lang('base_getting_started'),
                'type' => 'normal'
            );
        }

        // Date
        //-----

        $steps[] = array(
            'nav' => '/app/date/edit',
            'title' => lang('date_app_name'),
            'category' => lang('base_install_wizard'),
            'subcategory' => lang('base_getting_started'),
            'type' => 'normal'
        );

        // Central Management
        //-------------------

        if (clearos_app_installed('central_management')) {
            $steps[] = array(
                'nav' => '/app/central_management',
                'title' => lang('central_management_app_name'),
                'category' => lang('base_install_wizard'),
                'subcategory' => lang('base_getting_started'),
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
                'type' => 'wide'
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
