<?php

/**
 * File system browser class.
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
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\File as File;

clearos_load_library('base/Engine');
clearos_load_library('base/Folder');

// Exceptions
//-----------

use \clearos\apps\base\Validation_Exception as Validation_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Folder_Not_Found_Exception as Folder_Not_Found_Exception;

clearos_load_library('base/Validation_Exception');
clearos_load_library('base/Folder_Not_Found_Exception');
clearos_load_library('base/File_Not_Found_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * File system browser class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class File_System_Browser extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_SELECT = 'file_system_browser';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $CI = NULL;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * File_System_Browser constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->CI =& get_instance();
        if ($this->CI->session->userdata('file_system_browser') == NULL)
            $this->CI->session->set_userdata(array('file_system_browser' => rand(10000, 1000000)));
    }

    /**
     * Returns directory contents.
     *
     * @param string $path          starting path
     * @param boolen $include_files include files in array
     * @param string $ref           reference ID
     *
     * @return array directory/file listing
     *
     * throws Folder_Not_Found_Exception
     */

    public function get_listing($path, $include_files = FALSE, $ref = '.selected')
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_dir($path))
            throw new Folder_Not_Found_Exception();

        $folder = new Folder($path, TRUE);

        $list = $folder->get_listing(TRUE, $include_files);

        $selections = array();
        $file = new File(CLEAROS_TEMP_DIR . '/' . self::FILE_SELECT . '_' . $this->CI->session->userdata['file_system_browser'] . $ref, TRUE);
        if ($file->exists())
            $selections = unserialize($file->get_contents());

        clearos_profile(__METHOD__, __LINE__, "TODO " .  $path);
        $list_with_selections = array();

        foreach ($list as $entry) {
            if (preg_match($entry['name'], "/.*ben.*/"))
                clearos_profile(__METHOD__, __LINE__, "TODO " . $entry['name']);
            $entry['selected'] = in_array(($path == '/' ? $path : $path . '/') . $entry['name'], $selections);
            // Convenience key added...full path with base64 encoding (for easy URI handling)
            $entry['base64'] = base64_encode(($path == '/' ? $path : $path . '/') . $entry['name']);
            $list_with_selections[] = $entry;
        }

        return $list_with_selections;
    }
 
    /**
     * Select or (deselect) a file/folder.
     *
     * @param string $file    starting path
     * @param boolen $include include file/folder in array
     * @param string $ref     reference ID
     *
     * @return void
     *
     * throws Folder_Not_Found_Exception
     */

    public function select_file($path, $include = FALSE, $ref = '.selected')
    {
        clearos_profile(__METHOD__, __LINE__);

        // Is it a directory?
        if (!is_dir($path)) {
            if (!is_file($path))
                throw new Folder_Not_Found_Exception();
        }

        $selections = array();

        $file = new File(CLEAROS_TEMP_DIR . '/' . self::FILE_SELECT . '_' . $this->CI->session->userdata['file_system_browser'] . $ref, TRUE);
        if ($file->exists()) {
            $selections = unserialize($file->get_contents());
            $file->delete();
        }

        if ($include) {
            $selections[] = $path;
        } else {
            foreach (array_keys($selections, $path) as $key)
                unset($selections[$key]);
        }

        $file->create('root', 'root', '0640');
        $selections = $file->add_lines(serialize($selections));
    }
}
