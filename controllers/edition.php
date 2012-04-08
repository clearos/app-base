<?php

/**
 * OS edition controller.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
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
 * OS edition controller.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Edition extends ClearOS_Controller
{
    /**
     * Wizard default controller
     *
     * @return string
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->load->library('base/OS');

        // Load view data
        //---------------

        try {
            $os_name = $this->os->get_name();

            if (preg_match('/ClearOS Professional/', $os_name))
                $data['professional_already_installed'] = TRUE;
            else
                $data['professional_already_installed'] = FALSE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Handle form submit
        //-------------------

        if ($this->input->post('edition')) {
            if (($this->input->post('edition') === 'community') || ($this->input->post('edition') === 'professional'))
                redirect($this->session->userdata['wizard_redirect'] . '/index/' . $this->input->post('edition'));
        }

        // Load views
        //-----------

        $this->page->view_form('base/which_edition', $data, lang('base_select_edition'));
    }
}
