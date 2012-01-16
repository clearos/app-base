<?php

/**
 * Install wizard controller.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Install wizard controller.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Wizard extends ClearOS_Controller
{
    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Wizard server summary view.
     *
     * @return view
     */

    function index($step = 0)
    {
        // FIXME: translations
        if ($step != 0) {
            $this->lang->load('date');
            $this->lang->load('language');
            $this->lang->load('network');
        }

        $steps = array();

/*
        $steps[] = array(
            'nav' => 'wizard/language',
            'module' => 'language',
            'method' => 'edit',
            'params' => '',
            'title' => lang('language_app_name'),
            'category' => 'Install Wizard',
            'subcategory' => 'Setup',
        );
*/

        $steps[] = array(
            'nav' => 'wizard/date',
            'module' => 'date',
            'method' => 'edit',
            'title' => lang('date_app_name'),
            'category' => 'Install Wizard',
            'subcategory' => 'Setup',
        );

/*
        $steps[] = array(
            'nav' => 'wizard/network',
            'module' => 'network/iface',
            'method' => 'index',
            'title' => lang('network_app_name'),
            'category' => 'Install Wizard',
            'subcategory' => 'Setup',
        );
*/

        $steps[] = array(
            'nav' => 'wizard/register',
            'module' => 'registration',
            'method' => 'register',
            'title' => 'Registration',
            'category' => 'Install Wizard',
            'subcategory' => 'Setup',
        );

        $steps[] = array(
            'nav' => 'wizard/$marketplace2',
            'module' => 'marketplace/wizard_helper',
            'method' => 'index',
            'param' => 'server',
            'title' => 'Server Apps',
            'category' => 'Install Wizard',
            'subcategory' => 'Marketplace',
        );

        $steps[] = array(
            'nav' => 'wizard/marketplace1',
            'module' => 'marketplace/wizard_helper',
            'method' => 'index',
            'param' => 'gateway',
            'title' => 'Gateway Apps',
            'category' => 'Install Wizard',
            'subcategory' => 'Marketplace',
        );

        $steps[] = array(
            'nav' => 'wizard/$marketplace2',
            'module' => 'marketplace/wizard_helper',
            'method' => 'index',
            'param' => 'network',
            'title' => 'Network Apps',
            'category' => 'Install Wizard',
            'subcategory' => 'Marketplace',
        );

        $steps[] = array(
            'nav' => 'wizard/$marketplace2',
            'module' => 'marketplace/wizard_helper',
            'method' => 'index',
            'param' => 'system',
            'title' => 'System Apps',
            'category' => 'Install Wizard',
            'subcategory' => 'Marketplace',
        );

        $steps[] = array(
                'nav' => 'wizard/$marketplace3',
                'module' => 'marketplace',
                'method' => 'install',
                'title' => 'Confirm',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace',
        );

        $this->page->view_wizard($step, $steps, lang('install_wizard_app_name'));
    }
}
