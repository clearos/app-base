<?php

/**
 * Dashboard Widgets controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
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
 * Dashboard Widgets controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Dashboard_Widgets extends ClearOS_Controller
{
    /**
     * Default controller
     *
     * @return string
     */

    function index()
    {
        echo "Invalid dashboard widget...not sure how you got here.";
    }

    /**
     * Version widget
     *
     * @return view
     */

    function version()
    {
        // Load libraries
        //---------------

        $this->load->library('base/Webconfig');
        $this->lang->load('base');

        // Load views
        //-----------
        $data = array();

        $this->page->view_form('base/dashboard/version', $data, lang('base_app_name'));
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

        $this->load->library('base/Webconfig');
        $this->lang->load('base');

//        $confirm_id = $this->session->userdata('confirm_id');
        $this->session->set_userdata('item', 'bob');
            clearos_profile(__METHOD__, __LINE__, "FUCK YES 0..." . json_encode($this->session->userdata));

/*
        if ($this->input->post('confirm_id')) {
            if ($confirm_id == $this->input->post('confirm_id')) {
                clearos_profile(__METHOD__, __LINE__, "FUCK YES 2..." . $this->input->post('action') . " server");
                $this->session->unset_userdata('confirm_id');
                clearos_profile(__METHOD__, __LINE__, "FUCK YES..." . $this->input->post('action') . " server");
                redirect('/dashboard');
            }
        }
        if (!$this->session->userdata('confirm_id')) {
            $confirm_id = rand();
        clearos_profile(__METHOD__, __LINE__, "FUCK ERR..." . $confirm_id);
            $this->session->set_userdata('confirm_id', "$confirm_id");
            clearos_profile(__METHOD__, __LINE__, "FUCK YES 200..." . $this->session->userdata('confirm_id') . " server");
        }
*/

        // Load views
        //-----------
        $data = array(
            'confirm_id' => $confirm_id,
            'actions' => array(
                'shutdown' => lang('base_shutdown'),
                'restart' => lang('base_restart')
            )
        );

        $this->page->view_form('base/dashboard/shutdown', $data, lang('base_app_name'));
    }
}
