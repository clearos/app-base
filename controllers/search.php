<?php

/**
 * Search controller.
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
 * Search controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Search extends ClearOS_Controller
{
    /**
     * Default controller
     *
     * @return string
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->lang->load('base');
        $this->load->library('base/Search');

        // Load controllers
        //-----------------

        $query = NULL;
        if ($this->input->post('g_search'))
            $query = $this->input->post('g_search');
        
        $data = array('query' => $query);
        if ($this->session->userdata('username') != 'root')
            $data['filesystem_path'] = '/home/' . $this->session->userdata('username');

        /* TODO - One day, add search settings (eg. what you want to see, where you want to search etc.)
        $breadcrumb_links = array(
            'settings' => array('url' => '/app/base/search/settings', 'tag' => lang('base_settings')),
        );
        */

        $this->page->view_form('base/search', $data, lang('base_search'), array('type' => MY_Page::TYPE_DASHBOARD));
    }

    /**
     * Get Installed Apps
     *
     * @param string $search search string
     *
     * @return JSON
     */

    function get_installed_apps()
    {
        // Load libraries
        //---------------

        $this->lang->load('base');
        $this->load->library('base/Search');

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $search = $this->input->post('search');
            $data['code'] = 0;
            $data['list'] = $this->search->get_installed_apps($search);

            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Get search result for filesystem files
     *
     * @param string $search search string
     *
     * @return JSON
     */

    function get_files($search)
    {
        // Load libraries
        //---------------

        $this->lang->load('base');
        $this->load->library('base/Search');

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $search = $this->input->post('search');
            $data['code'] = 0;
            $data['list'] = $this->search->get_files($search, $this->session->userdata('username'));

            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }
}
