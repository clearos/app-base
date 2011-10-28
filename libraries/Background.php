<?php

/**
 * Operating system class.
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

clearos_load_library('base/Engine');
clearos_load_library('base/Shell');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Operating system class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Background extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Background constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Runs an API call in the background.
     *
     * @return mixed return from API call
     * @throws Engine_Exception
     */

    public function run($namespace, $class_path, $method, $params = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Create object
        //--------------

        $class_name = preg_replace('/.*\//', '', $class_path);
        $object_create = $namespace . '\\' . $class_name;
        
        clearos_load_library($class_path);

        $object = new $object_create();

        // Run method - Perhaps there is a more clever way?
        //-------------------------------------------------

        $param_count = count($params);

        if ($param_count == 0) {
            $retval = $object->$method();
        } else if ($param_count == 1) {
            $retval = $object->$method($params[0]);
        } else if ($param_count == 2) {
            $retval = $object->$method($params[0], $params[1]);
        } else if ($param_count == 3) {
            $retval = $object->$method($params[0], $params[1], $params[2]);
        } else if ($param_count == 4) {
            $retval = $object->$method($params[0], $params[1], $params[2], $params[3]);
        } else if ($param_count == 5) {
            $retval = $object->$method($params[0], $params[1], $params[2], $params[3], $params[4]);
        } else if ($param_count == 6) {
            $retval = $object->$method($params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
        } else if ($param_count == 7) {
            $retval = $object->$method($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6]);
        }

        return $retval;
    }
}
