<?php

/**
 * Search class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2003-2011 ClearFoundation
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

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\Shell as Shell;

clearos_load_library('base/Engine');
clearos_load_library('base/Shell');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Search class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2003-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Search extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const MAX_FILES = 250;
    const COMMAND_LOCATE = '/usr/bin/locate';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Search constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns array of installed apps.
     *
     * @param string $search search query
     *
     * @return array array of installed apps
     * @throws Engine_Exception
     */

    public function get_installed_apps($search)
    {
        clearos_profile(__METHOD__, __LINE__);

        $app_list = clearos_get_apps();
        $filtered_list = array();

        foreach ($app_list as $basename => $meta) {
            if (preg_match("/category=(.*)/i", $search, $match)) {
                if (preg_match("/$match[1]/i", $meta['category']))
                    $filtered_list[$basename] = $meta;
            } else if (preg_match("/$search/i", $basename)) {
                $filtered_list[$basename] = $meta;
            } else if (preg_match("/$search/i", $meta['name'])) {
                $filtered_list[$basename] = $meta;
            } else if (preg_match("/$search/i", $meta['description'])) {
                $filtered_list[$basename] = $meta;
            } else if (preg_match("/$search/i", $meta['category'])) {
                $filtered_list[$basename] = $meta;
            }
        }
        return $filtered_list;
    }

    /**
     * Returns array of files found.
     *
     * @param string $search   search query
     * @param string $username username
     *
     * @return array array of installed apps
     * @throws Engine_Exception
     */

    public function get_files($search, $username = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            if (!isset($search) || $search == NULL || $search === '')
                throw new Engine_Exception(lang('base_no_search_term_provided'), CLEAROS_WARNING);

            $shell = new Shell();
            $options = array('validate_exit_code' => FALSE);
            $params = '-i --limit ' . self::MAX_FILES . ' ' . $search;
            // If a user is searching, look only in home dir
            if ($username != 'root')
                $params = '-i -r --limit ' . self::MAX_FILES . ' "/home/' . $username . '/.*' . $search . '.*"';
            $exitcode = $shell->execute(self::COMMAND_LOCATE, $params, FALSE, $options);
            $rows = array();
            if ($exitcode == 0)
                $rows = $shell->get_output();
            return $rows;
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

}
