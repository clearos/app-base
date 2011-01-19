<?php

/**
 * Process manager controller.
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
 * Process manager controller.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Processes extends ClearOS_Controller
{
    /**
     * Default controller.
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->load->library('base/Process_Manager');

        // Load view data
        //---------------

        try {
            $data['processes'] = $this->process_manager->get_raw_data();
        } catch (Engine_Exception $e) {
            $this->page->view_exception($e->get_message());
            return;
        }

        // Load views
        //-----------

        $this->page->set_title("Process Manager");  // FIXME: translate

        $this->load->view('theme/header');
        $this->load->view('processes/summary', $data);
        $this->load->view('theme/footer');
    }

    /**
     * Kills the given process.
     *
     * @param integer $pid process ID
     *
     * @return view
     */

    function destroy($pid)
    {
        // Load libraries
        //---------------

        $this->load->library('base/Process_Manager');

        // Handle form submit
        //-------------------

        try {
            // $this->process_manager->kill($pid);
            $this->page->set_success('Process killed'); // FIXME: translate
        } catch (Engine_Exception $e) {
            $this->page->view_exception($e->get_message());
            return;
        }

        // Redirect
        //---------

        redirect('base/processes');
    }

    /**
     * Kill a process view.
     *
     * @param int $pid process ID
     *
     * @return view
     */

    function kill($pid)
    {
        // Load views
        //-----------

        // FIXME: translate
        $this->page->set_title('Processes');

        $data['message'] = sprintf(lang('dhcp_confirm_delete'), $pid);
        $data['ok_anchor'] = '/app/base/processes/destroy/' . $pid;
        $data['cancel_anchor'] = '/app/base/processes';

        $this->load->view('theme/header'); 
        $this->load->view('theme/confirm', $data);
        $this->load->view('theme/footer');

    }

    /**
     * Raw process data used in Ajax.
     *
     * @return view
     */

    function get_data()
    {
        // Load libraries
        //---------------

        $this->load->library('base/Process_Manager');

        // Load view data
        //---------------

        try {
            $data['processes'] = $this->process_manager->get_raw_data();
        } catch (Engine_Exception $e) {
            $this->page->view_exception($e->get_message());
            return;
        }

        // Load views
        //-----------

        $this->load->view('processes/get_data', $data);
    }
}
