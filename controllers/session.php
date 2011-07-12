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
        $page['type'] = MY_Page::TYPE_SPLASH;

        $this->page->view_form('session/access', array(), lang('base_access_denied'), $page);
    }

    /**
     * Login handler.
     */

    function login()
    {
        // FIXME: Redirect if already logged in(?)
        //----------------------------------------

        if ($this->authorization->is_authenticated()) {
            $this->page->set_message(lang('base_you_are_already_logged_in'), 'highlight');
            redirect('/base/index');
        }

        // Set validation rules for language first
        //----------------------------------------

        if (is_library_installed('language/Locale')) {
            $this->load->library('language/Locale');

            $this->form_validation->set_policy('code', 'language/Locale', 'validate_language_code', TRUE);
            $form_ok = $this->form_validation->run();

            if ($this->input->post('submit') && ($form_ok)) {
                $this->login_session->set_locale($this->input->post('code'));
                $this->login_session->reload_language('base');
            }
        }

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
                $login_ok = $this->authorization->authenticate($this->input->post('username'), $this->input->post('password'));
                $this->session->set_userdata('lang_code', $this->input->post('code'));

                if ($login_ok) {
                    // Redirect to dashboard page
                    redirect('/base/index');
                } else {
                    $data['login_failed'] = lang('base_login_failed');
                }
            } catch (Engine_Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        // If session cookie holds last language, use it as the default.
        // Otherwise, check the accept_language user agent variable
        // Otherwise, use the default system language

        if (is_library_installed('language/Locale')) {
            $system_code = $this->locale->get_language_code();
            $data['languages'] = $this->locale->get_languages();
        }

        if ($this->session->userdata('lang_code')) {
            $data['code'] = $this->session->userdata('lang_code');
	} else {
            $this->load->library('user_agent');

            foreach ($this->agent->languages() as $browser_lang) {
                $matches = array();
                if (preg_match('/(.*)-(.*)/', $browser_lang, $matches))
                    $browser_lang = $matches[1] . '_' . strtoupper($matches[2]);
                else
                    $browser_lang = $browser_lang . '_' . strtoupper($browser_lang);

                if (array_key_exists($browser_lang, $data['languages'])) {
                   $data['code'] = $browser_lang;
                   $this->login_session->set_locale($browser_lang);
                   $this->login_session->reload_language('base');
                   break;
                }
            }
	}

        if (empty($data['code'])) {
            $data['code'] = $system_code;
            $this->login_session->set_locale($system_code);
            $this->login_session->reload_language('base');
        }

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
        // Logout via authorization handler
        //---------------------------------

        $this->authorization->logout();

        // Load views
        //-----------

        $page['type'] = MY_Page::TYPE_SPLASH;

        $this->page->view_form('session/logout', $data, lang('base_logout'), $page);
    }
}
