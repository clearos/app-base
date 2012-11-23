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
        $data['redirect'] = '/app/' . $this->session->userdata('default_app');

        $page['type'] = MY_Page::TYPE_SPLASH;

        $this->page->view_form('session/access', $data, lang('base_access_denied'), $page);
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

        $this->form_validation->set_policy('clearos_username', '', '', TRUE);
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

                    //` If first boot, set the default language and start the wizard, 
                    // otherwise, go to redirect page
                    if (clearos_console()) {
                        redirect('/network');
                    } else if ($this->login_session->is_install_wizard_mode()) {
                        if (clearos_app_installed('language') && ($code))
                            $this->locale->set_language_code($code);

                        redirect('/base/wizard');
                    } else {
                        redirect($post_redirect);
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
            $this->load->library('user_agent');

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

        // Load views
        //-----------

        $page['type'] = MY_Page::TYPE_LOGIN;

        $this->page->view_form('session/login', $data, lang('base_login'), $page);
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
}
