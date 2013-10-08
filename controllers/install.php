<?php

/**
 * Software install controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2013 ClearFoundation
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
 * Software install controller.
 *
 * @category   apps
 * @package    base
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Install extends ClearOS_Controller
{
    /**
     * Default daemon controller.
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

        $options['javascript'] = array(clearos_app_htdocs('base') . '/install.js.php');

        $this->page->view_form('base/install', $data, lang('base_software_install'), $options);
    }

    /**
     * Progress controller
     *
     * @return JSON
     */

    function progress()
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->load->library('base/Yum');

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $logs = $this->yum->get_logs();
            $logs = array_reverse($logs);

            foreach ($logs as $log) {
                $last = json_decode($log);
                // Make sure we're getting valid JSON
                if (!is_object($last))
                    continue;

                echo json_encode(
                    array(
                        'code' => $last->code, 'details' => $last->details,
                        'progress' => $last->progress, 'overall' => $last->overall,
                        'errmsg' => $last->errmsg, 'busy' => $this->yum->is_busy(),
                        'wc_busy' => $this->yum->is_wc_busy() 
                    )
                );
                return;
            }
            echo json_encode(
                array(
                    'code' => -999,
                    'wc_busy' => $this->yum->is_wc_busy(),
                    'busy' => $this->yum->is_busy(),
                    'errmsg' => lang('base_no_data')
                )
            );
        } catch (Exception $e) {
            echo json_encode(array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }
}
