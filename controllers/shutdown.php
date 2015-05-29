<?php

/**
 * Shutdown and restart controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012-2015 ClearFoundation
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
 * Shutdown and restart controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012-2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Shutdown extends ClearOS_Controller
{
    /**
     * Shutdown and restart default controller
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');

        // Load views
        //-----------

        $this->page->view_form('base/shutdown', array(), lang('base_shutdown_restart'));
    }

    function dashboard()
    {
        $this->index();
    }

    /**
     * Shutdown view confirm view.
     *
     * @param string $type restart or shutdown
     *
     * @return view
     */

    function confirm($type = '')
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->load->library('base/System');

        // Handle action
        //--------------

        if ($type === 'shutdown') {
            $confirm_uri = '/app/base/shutdown/action/shutdown';
            $cancel_uri = '/app/base/shutdown';
            $items = array();

            $this->page->view_confirm(lang('base_confirm_shutdown'), $confirm_uri, $cancel_uri, $items);
        } else if ($type === 'restart') {
            $confirm_uri = '/app/base/shutdown/action/restart';
            $cancel_uri = '/app/base/shutdown';
            $items = array();

            $this->page->view_confirm(lang('base_confirm_restart'), $confirm_uri, $cancel_uri, $items);
        }
    }

    /**
     * Performs shutdown or restart.
     *
     * @param string $type restart or shutdown
     *
     * @return view
     */

    function action($type = '')
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->load->library('base/System');

        // Handle action
        //--------------

        if ($type === 'shutdown') {
            $this->page->set_message(lang('base_system_is_shutting_down'), 'warning');
            $this->system->shutdown();

            redirect('/base/shutdown/status');
        } else if ($type === 'restart') {
            $this->page->set_message(lang('base_system_is_restarting'), 'warning');
            $this->system->restart();

            redirect('/base/shutdown/status');
        }
    }

    /**
     * Show status message.
     *
     * The page handling here is a bit unique.  When someone is shutting
     * down or restarting a system, we don't really want to return them to
     * webconfig, but show them a handy status message.  However, we also
     * want to make sure a "refresh" doesn't redo the shutdown/restart after
     * a reboot.
     *
     * @return view
     */

    function status()
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->load->library('base/System');

        // Load views
        //-----------

        // On the first page load, show a status message
        // On the second page load, just go back to the home page

        if ($this->session->userdata('message_code')) {
            $options['type'] = MY_Page::TYPE_SPLASH;
            $this->page->view_form('shutdown_status', $data, lang('base_shutdown_restart'), $options);
        } else {
            redirect('base');
        }
    }
}
