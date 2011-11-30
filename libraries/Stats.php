<?php

/**
 * Generaly system stats class.
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

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
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
use \clearos\apps\base\File as File;

clearos_load_library('base/Engine');
clearos_load_library('base/File');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Generaly system stats class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Stats extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_UPTIME = '/proc/uptime';
    const FILE_PROC_LOADAVG = '/proc/loadavg';
    const FILE_PROC_MEMINFO = '/proc/meminfo';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Stats constructor.
     */

    public function stats() 
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns uptime.
     *
     * @return array uptime
     * @throws Engine_Exception
     */

    public function get_uptimes()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_UPTIME);
        $contents = $file->get_contents();

        list($uptime, $idle) = explode(' ', chop($contents));

        $result = array();
        $result['uptime'] = sprintf('%d', $uptime);
        $result['idle'] = sprintf('%d', $idle);

        return $result;
    }

    /**
     * Returns load averages and processes running/total.
     *
     * @return array loadavg
     * @throws Engine_Exception
     */

    public function get_load_averages()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_PROC_LOADAVG);
        $contents = $file->get_contents();

        $fields = explode(' ', chop($contents));

        $result = array();
        $result['one'] = $fields[0];
        $result['five'] = $fields[1];
        $result['fifteen'] = $fields[2];

        return $result;
    }

    /**
     * Returns memory information.
     *
     * @return array memory information
     * @throws Engine_Exception
     */

    public function get_memory_stats()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_PROC_MEMINFO);

        $lines = $file->get_contents_as_array();
        $info = array();

        foreach ($lines as $line) {
            $parts = array();
            if (!preg_match('/^([A-z_]+):[[:space:]]+([0-9]+).*$/', $line, $parts))
                continue;

            if ($parts[1] == "MemTotal")
                $info['memory_total'] = (float)$parts[2];
            else if ($parts[1] == "MemFree")
                $info['memory_free'] = (float)$parts[2];
            else if ($parts[1] == "SwapTotal")
                $info['swap_total'] = (float)$parts[2];
            else if ($parts[1] == "SwapFree")
                $info['swap_free'] = (float)$parts[2];
        }

        return $info;
    }
}
