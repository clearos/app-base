<?php

/**
 * File extension and MIME type class.
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

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;

clearos_load_library('base/Engine');
clearos_load_library('base/File');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * File extension and MIME type class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class File_Types extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_EXTENSIONS = '/etc/system/fileextensions';
    const FILE_MIME_TYPES = '/etc/system/mimetypes';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $categories = array();
    protected $file_extensions = NULL;
    protected $file_mime_types = NULL;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * File type constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->file_extensions = clearos_app_base('base') . '/config/file_extensions.php';
        $this->file_mime_types = clearos_app_base('base') . '/config/mime_types.php';

        $this->categories = array(
            'archive'       => lang('base_file_category_archive'),
            'document'      => lang('base_file_category_document'),
            'media'         => lang('base_file_category_media'),
            'application'   => lang('base_file_category_application'),
            'miscellaneous' => lang('base_file_category_miscellaneous'),
        );
    }

    /**
     * Returns the list of known file extensions.
     *
     * @return array list of file extensions
     * @throws Engine_Exception
     */

    public function get_file_extensions()
    {
        clearos_profile(__METHOD__, __LINE__);

        $extensions = array();

        include_once($this->file_extensions);

/*
        $file = new File($this->file_extensions);
        $lines = $file->get_contents_as_array();

        foreach ($lines as $line) {
            if (preg_match('/^#/', $line))
                continue;

            if (preg_match('/^\s*$/', $line))
                continue;

            $items = explode(' ', $line, 3);

            $extensions[$items[0]]['type'] = $items[1];
            $extensions[$items[0]]['description'] = $items[2];

            if (isset($this->categories[$items[1]]))
                $extensions[$items[0]]['typetext'] = $this->categories[$items[1]];
            else
                $extensions[$items[0]]['typetext'] = $items[1];
        }
*/

        return $file_extensions;
    }

    /**
     * Returns the list of known mime types.
     *
     * @return array list of mime types
     * @throws Engine_Exception
     */

    function get_mime_types()
    {
        clearos_profile(__METHOD__, __LINE__);

        $mime_types = array();

        include_once($this->file_mime_types);

/*
        $file = new File($this->file_mime_types);
        $lines = $file->get_contents_as_array();

        foreach ($lines as $line) {
            $items = explode(' ', $line, 2);
            $types[$items[0]] = $items[1];
        }
*/

        return $mime_types;
    }
}
