<?php

/**
 * Theme configuration controller.
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
 * Theme configuration controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Theme extends ClearOS_Controller
{
    /**
     * Theme overview.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('base/Webconfig');
        $this->lang->load('webconfig');

        // Load views
        //-----------

        $this->page->view_form('webconfig/summary', $data, lang('webconfig_app_name'));
    }

    /**
     * Sets given them.
     *
     * @param string $theme theme name
     *
     * @return view
     */

    function set($theme)
    {
        // TODO -- just a temporary hack for testing

        if ($theme === 'default') {
            $this->session->set_userdata('theme', 'default');
            $this->session->set_userdata('theme_mode', 'normal');
        } else if ($theme === 'mobile_default') {
            $this->session->set_userdata('theme', 'mobile_default');
            $this->session->set_userdata('theme_mode', 'control_panel');
        } else if ($theme === 'smartadmin') {
            $this->session->set_userdata('theme', 'smartadmin');
            $this->session->set_userdata('theme_mode', 'normal');
        } else if ($theme === 'clipone') {
            $this->session->set_userdata('theme', 'clipone');
            $this->session->set_userdata('theme_mode', 'normal');
        } else if ($theme === 'clearos7') {
            $this->session->set_userdata('theme', 'clearos7');
            $this->session->set_userdata('theme_mode', 'normal');
        } else if ($theme === 'AdminLTE') {
            $this->session->set_userdata('theme', 'AdminLTE');
            $this->session->set_userdata('theme_mode', 'normal');
        }

        $this->load->library('user_agent');

        if ($this->agent->is_referral()) {
            $baseapp = preg_replace('/.*\/app\//', '', $this->agent->referrer());
            redirect('/' . $baseapp);
        } else {
            redirect('/base/index');
        }
    }

    /**
     * Theme edit controller
     *
     * @param string $name theme name
     *
     * @return view
     */

    function edit($name)
    {
        // Load dependencies
        //------------------

        $this->load->library('base/Webconfig');
        $this->lang->load('theme');

        $theme = $this->webconfig->get_theme($name);

        try {
            $metadata = $this->webconfig->get_theme_metadata();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Set validation rules
        //---------------------
         
        foreach ($metadata['settings'] as $field_name => $setting)
            $this->form_validation->set_policy('options[' . $field_name . ']', 'base/Webconfig', 'validate_theme_option', $setting['required']);

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && $form_ok) {
            try {
                $this->webconfig->set_theme_options($this->input->post('options'));

                $this->page->set_status_updated();

//                redirect('/base/theme/edit/' . $name);
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        $data['metadata'] = $metadata;
        $data['theme_settings'] = $this->webconfig->get_theme_settings();

        // Load views
        //-----------

        $this->page->view_form('base/theme/settings', $data, lang('base_theme'));
    }

}
