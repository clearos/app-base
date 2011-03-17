<?php

/**
 * Login session controller.
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
 * Login session controller.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Session extends ClearOS_Controller
{
    /**
     * Default controller.
     */

    function index()
    {
        redirect('/base/session/login');
    }

    /**
     * Access denied helper
     */

    function access_denied()
    {
        // Load libraries
        //---------------

        $data = array();

        // Load views
        //-----------

        $this->page->set_title("Access");  // FIXME: translate
        $this->page->set_layout(MY_Page::TYPE_SPLASH);

        $this->load->view('theme/header');
        $this->load->view('session/access', $data);
        $this->load->view('theme/footer');
    }

    /**
     * Login handler.
     */

    function login()
    {
        // FIXME: Redirect if already logged in(?)
        //------------------------------

        if ($this->session->userdata('logged_in')) {
            $this->page->set_success('You are already logged in.'); // FIXME: translate
            redirect('/base/dashboard/');
        }

        // Load libraries
        //---------------

        $this->load->library('base/Webconfig');

        // Set validation rules
        //---------------------

        // The login form handling is a bit different than your typical
        // web form validation.  We manually set the login_failed warning message.
         
        $this->form_validation->set_policy('username', '', '', TRUE);
        $this->form_validation->set_policy('password', '', '', TRUE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        $data['login_failed'] = '';

        if ($this->input->post('submit') && ($form_ok)) {
            try {
                $login_ok = $this->webconfig->authenticate($this->input->post('username'), $this->input->post('password'));

                if ($login_ok) {
                    // Set login session variables
                    $this->session->set_userdata('logged_in', 'TRUE');
                    $this->session->set_userdata('username', $this->input->post('username'));

                    // Redirect to dashboard page
                    redirect('/base/dashboard/');
                } else {
                    $data['login_failed'] = "Login failed"; // FIXME: translate
                }
            } catch (Engine_Exception $e) {
                $this->page->view_exception($e->get_message());
                return;
            }
        }

        // Load view data
        //---------------

        // Load views
        //-----------

        $page['type'] = MY_Page::TYPE_SPLASH;

        $this->page->view_form('session/login', $data, lang('base_login'), $page);
    }

    /**
     * Logout handler.
     */

    function logout()
    {
        // Load libraries
        //---------------

        $this->load->library('base/Webconfig');

        // Handle logout action
        //---------------------

        $this->session->unset_userdata('logged_in');
        $this->session->unset_userdata('username');

        // Load views
        //-----------

        $page['type'] = MY_Page::TYPE_SPLASH;

        $this->page->view_form('session/logout', $data, lang('base_logout'), $page);
    }
}
