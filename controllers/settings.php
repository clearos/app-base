<?php

/**
 * Settings controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/language/
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
 * Settings controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/language/
 */

class Settings extends ClearOS_Controller
{
    /**
     * Translations widget default controller.
     *
     * @return view
     */

    function index()
    {
        $this->view();
    }

    /**
     * Settings edit view.
     *
     * @return view
     */

    function edit()
    {
        $this->_view_edit('edit');
    }

    /**
     * Settings view view.
     *
     * @return view
     */

    function view()
    {
        $this->_view_edit('view');
    }

    /**
     * Settings view/edit common controller
     *
     * @param string $form_type form type
     *
     * @return view
     */

    function _view_edit($form_type)
    {
        // Load dependencies
        //------------------

        $this->load->library('base/Webconfig');

        if (clearos_library_installed('language/Locale')) {
            $this->lang->load('language');
            $this->load->library('language/Locale');
        }

        // Set validation rules
        //---------------------
         
        if (clearos_library_installed('language/Locale'))
            $this->form_validation->set_policy('code', 'language/Locale', 'validate_language_code', TRUE);

        $this->form_validation->set_policy('ssl_certificate', 'base/Webconfig', 'validate_ssl_certificate', TRUE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                if (clearos_library_installed('language/Locale'))
                    $this->locale->set_locale($this->input->post('code'));

                $reload = $this->webconfig->set_ssl_certificate($this->input->post('ssl_certificate'));

                if ($this->input->post('update_session'))
                    $this->login_session->set_language($this->input->post('code'));

                $this->page->set_status_updated();
                redirect('/base' . ($reload ? '/?reloading' : ''));
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        try {
            $data['form_type'] = $form_type;

            $data['update_session'] = TRUE;

            if (clearos_library_installed('language/Locale')) {
                $data['code'] = $this->locale->get_language_code();
                $data['languages'] = $this->locale->get_languages();
            }

            $data['ssl_certificate'] = $this->webconfig->get_ssl_certificate();
            $data['ssl_certificate_options'] = $this->webconfig->get_ssl_certificate_options();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('settings', $data, lang('base_settings'));
    }
}
