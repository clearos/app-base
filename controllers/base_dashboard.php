<?php

/**
 * Dashboard Widgets controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
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
 * Dashboard Widgets controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Base_Dashboard extends ClearOS_Controller
{
    /**
     * Default controller
     *
     * @return string
     */

    function index()
    {
        echo "Invalid dashboard widget... not sure how you got here.";
    }

    /**
     * Shutdown/Restart widget
     *
     * @return view
     */

    function shutdown()
    {
        // Load libraries
        //---------------

        $this->load->library('base/System');
        $this->lang->load('base');

        if ($this->input->post('confirm_id')) {
            if ($this->session->userdata('form_post_verify') == $this->input->post('confirm_id')) {
                if ($this->input->post('action') === 'shutdown') {
                    $this->page->set_message(lang('base_system_is_shutting_down'), 'warning');
                    $this->system->shutdown();
                    redirect('/base/shutdown/status');
                    return;
                } else if ($this->input->post('action') === 'restart') {
                    $this->page->set_message(lang('base_system_is_restarting'), 'warning');
                    $this->system->restart();
                    redirect('/base/shutdown/status');
                    return;
                }
            }
        }

        // Load views
        //-----------

        $data = array(
            'confirm_id' => $this->session->userdata('form_post_verify'),
            'actions' => array(
                'shutdown' => lang('base_shutdown'),
                'restart' => lang('base_restart')
            )
        );

        $this->page->view_form('base/dashboard/shutdown', $data, lang('base_app_name'));
    }
}
