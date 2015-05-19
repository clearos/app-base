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

        $data['themes'] = $this->webconfig->get_themes();
        $data['current_theme'] = $this->session->userdata['theme'];

        // Load views
        //-----------

        $this->page->view_form('base/theme/summary', $data, lang('base_theme'));
    }

    /**
     * Sets given them.
     *
     * @param string $name theme name
     *
     * @return view
     */

    function set($name)
    {
        // Load dependencies
        //------------------

        $this->load->library('base/Webconfig');

        try {
            $themes = $this->webconfig->get_themes();

            if (!array_key_exists($name, $themes))
                return;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Set theme
        //----------

        try {
            $this->webconfig->set_theme($name);
            $this->session->set_userdata('theme_' . $name, $this->webconfig->get_theme_settings());
            $this->session->set_userdata('theme', $name);
            $this->session->set_userdata('theme_mode', 'normal'); // normal is only used right now
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        redirect('/base/theme');
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

        try {
            $metadata = $this->webconfig->get_theme_metadata($name);
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
                $this->webconfig->set_theme_options($name, $this->input->post('options'));

                // Update session
                $this->session->set_userdata('theme_' . $name, $this->webconfig->get_theme_settings());

                $this->page->set_status_updated();

                redirect('/base/theme');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        $data['name'] = $name;
        $data['metadata'] = $metadata;
        $data['theme_settings'] = $this->webconfig->get_theme_settings();

        // Load views
        //-----------

        $this->page->view_form('base/theme/settings', $data, lang('base_theme'));
    }
}
