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
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $steps = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    function __construct()
    {
        // FIXME: translations
        $this->lang->load('date');
        $this->lang->load('language');
        $this->lang->load('network');

        $this->steps = array(
            0 => array(
                'nav' => 'wizard/language',
                'module' => 'language',
                'method' => 'edit',
                'params' => '',
                'title' => lang('language_app_name'),
                'category' => 'Install Wizard',
                'subcategory' => 'Setup',
            ),
            1 => array(
                'nav' => 'wizard/date',
                'module' => 'date',
                'method' => 'edit',
                'title' => lang('date_app_name'),
                'category' => 'Install Wizard',
                'subcategory' => 'Setup',
            ),
            2 => array(
                'nav' => 'wizard/network',
                'module' => 'network/iface',
                'method' => 'index',
                'title' => lang('network_app_name'),
                'category' => 'Install Wizard',
                'subcategory' => 'Setup',
            ),
            3 => array(
                'nav' => 'wizard/register',
                'module' => 'registration',
                'method' => 'index',
                'title' => 'Registration',
                'category' => 'Install Wizard',
                'subcategory' => 'Setup',
            ),
            4 => array(
                'nav' => 'wizard/marketplace1',
                'module' => 'marketplace/search',
                'method' => 'index',
                'params' => '0/gateway',
                'title' => 'Gateway Apps',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace',
            ),
            5 => array(
                'nav' => 'wizard/$marketplace2',
                'module' => 'marketplace/search',
                'method' => 'index',
                'params' => '0/server',
                'title' => 'Server Apps',
                'category' => 'Install Wizard',
                'subcategory' => 'Marketplace',
            ),
        );
    }

    /**
     * Wizard server summary view.
     *
     * @return view
     */

    function index($step = 0)
    {
        $this->page->view_wizard($step, $this->steps, lang('install_wizard_app_name'));
    }

    function date()
    {
        $this->page->view_wizard(1, $this->steps, lang('install_wizard_app_name'));
    }

    function language()
    {
        $this->page->view_wizard(0, $this->steps, lang('install_wizard_app_name'));
    }

    function network()
    {
        $this->page->view_wizard(2, $this->steps, lang('install_wizard_app_name'));
    }
}
