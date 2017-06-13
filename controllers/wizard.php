<?php

/**
 * Wizard controller.
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\base\Install_Wizard as Install_Wizard;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Wizard controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
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
        $this->load->library('base/OS');
        $this->load->library('base/Stats');
        $this->load->library('base/System');

        // Load view data
        //---------------

        try {
            $this->session->set_userdata('wizard', TRUE);

            $state = $this->install_wizard->get_state();

            $data['memory_size'] = $this->stats->get_mem_size();
            $data['memory_warning'] = ($data['memory_size'] < 0.9) ? TRUE : FALSE;
            $data['os_name'] = $this->os->get_name();
            $data['os_base_version'] = $this->os->get_base_version();
            $data['vm_warning'] = $this->system->is_default_vm_image();

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

        if ($this->session->userdata('os_name') == 'ClearOS') {
            // Don't let them out of the wizard until Edition has been set.
            $this->page->set_message('Please select your edition before quitting the post-install wizard.', 'warning');
            redirect('/edition');
            return;
        }

        // Start wizard mode
        //------------------

        $this->install_wizard->set_state(-1);
        $this->session->unset_userdata('wizard');
        $this->session->unset_userdata('wizard_redirect');
        $this->session->unset_userdata('wizard_marketplace_mode');

        if (clearos_app_installed('dashboard'))
            redirect('/dashboard');
        else
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
        $this->session->unset_userdata('wizard');

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

    /**
     * Ajax update running check
     *
     * @return JSON
     */

    function is_wizard_upgrade_running()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->load->library('base/Install_Wizard');

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $this->load->library('base/Script', Install_Wizard::SCRIPT_UPGRADE);
            if ($this->script->is_running()) {
                echo json_encode(array('state' => 1));
                return;
            }
            echo json_encode(array('state' => 0));
        } catch (Exception $e) {
            echo json_encode(array('state' => 0));
        }
    }

}
