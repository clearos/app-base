<?php

/**
 * System process manager class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\base;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = isset($_ENV['CLEAROS_BOOTSTRAP']) ? $_ENV['CLEAROS_BOOTSTRAP'] : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\ShellExec as ShellExec;

clearos_load_library('base/Engine');
clearos_load_library('base/ShellExec');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/Engine_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * System process manager class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Process_Manager extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_PS = "/bin/ps";
    const COMMAND_KILL = "/bin/kill";

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Proccess_Manager constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns raw output from ps command.
     *
     * @return string raw output
     * @throws Engine_Exception
     */

    public function get_raw_data()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new ShellExec();
            $shell->execute(self::COMMAND_PS, "-eo pid,user,time,%cpu,%mem,sz,tty,ucomm,command");
            $output = $shell->get_output();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_ERROR);
        }

        return $output;
    }

    /**
     * Kills processes in given list.
     *
     * @param array $pids list of process IDs
     *
     * @return void
     * @throws Engine_Exception
     */

    public function kill($pids)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new ShellExec();

            foreach ($pids as $pid)
                $shell->execute(self::COMMAND_KILL, $pid, TRUE);
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_ERROR);
        }
    }
}
