<?php

/**
 * Daemon controller.
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
 * Daemon controller.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Daemon extends ClearOS_Controller
{
    /**
     * Default controller.
     */

    function index()
    {
    }

    /**
     * Daemon status.
     */

    function status($daemon = NULL)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        $this->load->library('base/Daemon', $daemon);

        $status['status'] = $this->daemon->get_status();

        echo json_encode($status);
    }

    /**
     * Daemon start.
     */

    function start($daemon = NULL)
    {
        $this->load->library('base/Daemon', $daemon);

        try {
            $this->daemon->set_running_state(TRUE);
            $this->daemon->set_boot_state(TRUE);
        } catch (Exception $e) {
            //
        }
    }

    /**
     * Daemon stop.
     */

    function stop($daemon = NULL)
    {
        $this->load->library('base/Daemon', $daemon);

        try {
            $this->daemon->set_running_state(FALSE);
            $this->daemon->set_boot_state(FALSE);
        } catch (Exception $e) {
            //
        }
    }
}
