<?php

/**
 * Login session controller.
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
 * Login session controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Session extends ClearOS_Controller
{
    /**
     * Default controller.
     *
     * @return view
     */

    function index()
    {
        redirect('/base/session/login');
    }

    /**
     * Access denied helper
     *
     * @return view
     */

    function access_denied()
    {
        if ($this->session->userdata('default_app'))
            $data['redirect'] = '/app/' . $this->session->userdata('default_app');
        else
            $data['redirect'] = '';

        $page['type'] = MY_Page::TYPE_SPLASH;

        $this->page->view_form('session/access', $data, lang('base_access_denied'), $page);
    }

    /**
     * Root change password.
     *
     * @return view
     */

    function change_password()
    {
        // Load libraries
        //---------------

        $this->lang->load('base');
        $this->load->library('base/Posix_User', 'root');
        $this->load->library('base/Install_Wizard', 'root');

        // If password has changed, don't require another change (e.g. back button was pressed)
        //-------------------------------------------------------------------------------------

        try {
            $data['password_changed'] = $this->install_wizard->get_password_changed_state();
            $require_field = ($data['password_changed']) ? FALSE : TRUE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Validation
        //-----------

        $this->form_validation->set_policy('password', 'users/User_Engine', 'validate_password', $require_field);
        $this->form_validation->set_policy('verify', 'users/User_Engine', 'validate_password', $require_field);

        $form_ok = $this->form_validation->run();

        // Extra Validation
        //------------------

        $password = ($this->input->post('password')) ? $this->input->post('password') : '';
        $verify = ($this->input->post('verify')) ? $this->input->post('verify') : '';

        if ($password != $verify) {
            $this->form_validation->set_error('verify', lang('base_password_and_verify_do_not_match'));
            $form_ok = FALSE;
        } else if (!empty($password)) {
            try {
                $is_weak = $this->posix_user->is_weak_password($this->input->post('password'));
            } catch (Engine_Exception $e) {
                $this->page->view_exception($e);
                return;
            }

            if ($is_weak) {
                $this->form_validation->set_error('verify', lang('base_password_too_weak'));
                $form_ok = FALSE;
            }
        }

        // Handle form submit
        //-------------------

        if (!empty($password) && ($form_ok)) {
            try {
                $this->posix_user->set_password($this->input->post('password'));
                $this->install_wizard->set_password_changed_state(TRUE);
                redirect($this->session->userdata('wizard_redirect'));
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load the views
        //---------------

        $this->page->view_form('change_password', $data, lang('base_change_password'));
    }

    /**
     * Login handler.
     *
     * @param string $redirect redirect page after login, base64 encoded
     *
     * @return view
     */

    function login($redirect = NULL)
    {
        $this->load->library('user_agent');
        $this->load->library('base/Access_Control');

        // Handle page post login redirect
        //--------------------------------

        $data['redirect'] = $redirect;

        $post_redirect = is_null($redirect) ? '/base/index' : base64_decode(strtr($redirect, '-@_', '+/='));
        $post_redirect = preg_replace('/.*app\//', '/', $post_redirect); // trim /app prefix
        $code = ($this->input->post('code')) ? $this->input->post('code') : 'en_US';

        // Redirect if already logged in
        //------------------------------

        if ($this->login_session->is_authenticated()) {
            $this->page->set_message(lang('base_you_are_already_logged_in'), 'highlight');
            redirect($post_redirect);
        }

        // Set validation rules for language first
        //----------------------------------------

        if (clearos_app_installed('language')) {
            $this->load->library('language/Locale');

            if ($this->input->post('code')) {
                $this->form_validation->set_policy('code', 'language/Locale', 'validate_language_code', TRUE);
                $form_ok = $this->form_validation->run();
            }

            if ($this->input->post('submit') && ($form_ok)) {
                $this->login_session->set_language($code);
                $this->login_session->reload_language('base');
            }
        }

        // Set validation rules
        //---------------------

        // The login form handling is a bit different than your typical
        // web form validation.  We manually set the login_failed warning message.

        $this->form_validation->set_policy('clearos_username', 'base/Posix_User', 'validate_username', TRUE);
        $this->form_validation->set_policy('clearos_password', '', '', TRUE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        $data['login_failed'] = '';

        if ($this->input->post('submit') && ($form_ok)) {
            try {
                $login_ok = $this->login_session->authenticate($this->input->post('clearos_username'), $this->input->post('clearos_password'));
                if ($login_ok) {
                    $this->login_session->start_authenticated($this->input->post('clearos_username'));
                    $this->login_session->set_language($code);

                    // If first boot, set the default language and start the wizard, 
                    // otherwise, go to redirect page
                    if (clearos_console()) {
                        redirect('/network');
                    } else if ($this->login_session->is_install_wizard_mode()) {
                        if (clearos_app_installed('language') && ($code))
                            $this->locale->set_language_code($code);

                        redirect('/base/wizard/index/start');
                    } else {
                        // Go to the dashboard if access control allows it
                        $username = $this->input->post('clearos_username');
                        $valid_pages = $this->access_control->get_valid_pages($username);
                        if (preg_match('/^\/base\//', $post_redirect)
                            && (in_array('dashboard', $valid_pages) || ($username === 'root'))
                            && clearos_app_installed('dashboard')
                        ) {
                            redirect('/dashboard');
                        } else {
                            redirect($post_redirect);
                        }
                    }
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

        if (clearos_app_installed('language')) {
            $system_code = $this->locale->get_language_code();
            $data['languages'] = $this->locale->get_framework_languages();
        } else {
            $system_code = 'en_US';
            $data['languages'] = array();
        }

        $data['connect_ip'] = '';

        if (clearos_console() && clearos_app_installed('network')) {
            $this->load->library('network/Iface_Manager');
            $lan_ips = $this->iface_manager->get_most_trusted_ips();
            if (!empty($lan_ips[0]))
                $data['connect_ip'] = $lan_ips[0];
        }

        if ($this->session->userdata('lang_code') && array_key_exists($this->session->userdata('lang_code'), $data['languages'])) {
            $data['code'] = $this->session->userdata('lang_code');
        } else {
            foreach ($this->agent->languages() as $browser_lang) {
                $matches = array();

                if (preg_match('/(.*)-(.*)/', $browser_lang, $matches))
                    $browser_lang = $matches[1] . '_' . strtoupper($matches[2]);
                else
                    $browser_lang = $browser_lang . '_' . strtoupper($browser_lang);

                if (array_key_exists($browser_lang, $data['languages'])) {
                    $data['code'] = $browser_lang;
                    $this->login_session->set_language($browser_lang);
                    $this->login_session->reload_language('base');
                    break;
                }
            }
        }

        if (empty($data['code'])) {
            $data['code'] = $system_code;
            $this->login_session->set_language($system_code);
            $this->login_session->reload_language('base');
        }

        // IE warning - http://www.zytrax.com/tech/web/browser_ids.htm
        $is_old_ie = (preg_match('/MSIE [678]\./', $this->agent->agent_string())) ? TRUE : FALSE;
        $is_really_new_ie_in_compat = (preg_match('/Trident\/[0-9]+/', $this->agent->agent_string())) ? TRUE : FALSE;

        // Load views
        //-----------

        if ($is_old_ie && !$is_really_new_ie_in_compat) {
            $page['type'] = MY_Page::TYPE_SPLASH;
            $this->page->view_form('session/ie_warning', $data, lang('base_login'), $page);
        } else {
            $page['type'] = MY_Page::TYPE_LOGIN;
            $this->page->view_form('session/login', $data, lang('base_login'), $page);
        }
    }

    /**
     * Logout handler.
     *
     * @return view
     */

    function logout()
    {
        // Logout via login_session handler
        //---------------------------------

        $this->login_session->stop_authenticated();

        redirect('/base/session/login');
    }

    /**
     * REST handler.
     *
     * @return view
     */

    function rest()
    {
        // Invalid requests to w.x.y.x:83/app/some_app get redirected here.
        echo lang('base_access_denied');
    }
}
