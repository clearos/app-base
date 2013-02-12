<?php

/**
 * Wizard controller.
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
 * Wizard controller.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Wizard extends ClearOS_Controller
{
    /**
     * Wizard default controller
     *
     * @param string $start start flag
     *
     * @return string
     */

    function index($start = NULL)
    {
        // Load dependencies
        //------------------

        $this->load->library('base/Install_Wizard');

        // Load view data
        //---------------

        try {
            $this->session->set_userdata('wizard', TRUE);
            $state = $this->install_wizard->get_state();

            // Jump to last wizard step if user is returning
            if ($start == "start") {
                $this->install_wizard->set_state(0);
            } else if ($state && ($state >= 1)) {
                $step = $this->install_wizard->get_step($state + 1);
                redirect($step);
            } else {
                $this->install_wizard->set_state(0);
            }
        } catch (Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load view
        //----------

        $this->page->view_form('base/wizard', $data, lang('base_install_wizard'));
    }

    /**
     * Starts install wizard.
     *
     * Handy link for testing the install wizard.
     *
     * @return view
     */

    function start()
    {
        // Load dependencies
        //------------------

        $this->load->library('base/Install_Wizard');

        // Start wizard mode
        //------------------

        $this->install_wizard->set_state(0);
        $this->session->set_userdata('wizard', TRUE);

        // Redirect to first page
        //-----------------------

        $first_step = $this->install_wizard->get_step(1);

        redirect($first_step);
    }

    /**
     * Stops install wizard.
     *
     * @return view
     */

    function stop()
    {
        // Load dependencies
        //------------------

        $this->load->library('base/Install_Wizard');

        // Start wizard mode
        //------------------

        $this->install_wizard->set_state(-1);
        $this->session->unset_userdata('wizard');
        $this->session->unset_userdata('wizard_redirect');

        redirect('/base');
    }

    /**
     * Stops install wizard and redirects to Marketplace (if installed)
     *
     * @return view
     */

    function finish()
    {
        // Load dependencies
        //------------------

        $this->load->library('base/Install_Wizard');

        // Start wizard mode
        //------------------

        $this->install_wizard->set_state(-1);
        $this->session->unset_userdata('wizard', FALSE);

        redirect('/marketplace');
    }

    /**
     * Redirects to next step in the wizard.
     *
     * @return redirect
     */

    function next_step()
    {
        redirect($this->session->userdata('wizard_redirect'));
    }
}
