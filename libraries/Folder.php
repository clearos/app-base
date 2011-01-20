<?php

/**
 * Folder manipulation class.
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
use \clearos\apps\base\Shell as Shell;

clearos_load_library('base/Engine');
clearos_load_library('base/Shell');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Folder_Already_Exists_Exception as Folder_Already_Exists_Exception;
use \clearos\apps\base\Folder_Exception as Folder_Exception;
use \clearos\apps\base\Folder_Not_Found_Exception as Folder_Not_Found_Exception;
use \clearos\apps\base\Folder_Permissions_Exception as Folder_Permissions_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Folder_Already_Exists_Exception');
clearos_load_library('base/Folder_Exception');
clearos_load_library('base/Folder_Not_Found_Exception');
clearos_load_library('base/Folder_Permissions_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Folder manipulation class.
 *
 * The Folder class can be used for creating, reading and manipulating
 * folders (directories) on the filesystem.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Folder extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_LS = '/bin/ls';
    const COMMAND_DU = '/usr/bin/du';
    const COMMAND_MKDIR = '/bin/mkdir';
    const COMMAND_CHOWN = '/bin/chown';
    const COMMAND_CHMOD = '/bin/chmod';
    const COMMAND_FILE = '/usr/bin/file';
    const COMMAND_RMDIR = '/bin/rmdir';
    const COMMAND_RM = '/bin/rm';
    const COMMAND_FIND = '/usr/bin/find';
    const COMMAND_REALPATH = '/usr/sbin/app-realpath';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * @var string folder
     */

    protected $folder = NULL;

    /**
     * @var boolean superuser
     */

    protected $superuser = FALSE;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Folder constructor.
     *
     * @param string  $folder    target folder
     * @param boolean $superuser superuser access required to read the file
     */

    public function __construct($folder, $superuser = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->folder = $folder;
        $this->superuser = $superuser;
    }

    /**
     * Changes the folder mode.
     *
     * Use the standard command-line chmod values.
     *
     * @param string $mode mode of the folder
     *
     * @return  void
     */

    public function chmod($mode)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->exists())
            throw new Folder_Not_Found_Exception($this->folder, CLEAROS_WARNING);

        try {
            $shell = new Shell();
            if ($shell->execute(self::COMMAND_CHMOD, $mode . ' ' . $this->folder, TRUE) != 0)
                throw new Folder_Permissions_Exception($shell->get_first_output_line(), COMMON_ERROR);
        } catch (Engine_Exception $e) {
            throw new Folder_Exception($e->get_message(), COMMON_ERROR);
        }
    }

    /**
     * Changes the owner and/or group.
     *
     * Leave the owner or group blank if you do not want change one or the other.
     *
     * @param string $owner     folder owner
     * @param string $group     folder group
     * @param string $recursive do chown recursively
     *
     * @return  void
     */

    public function chown($owner, $group, $recursive = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->exists())
            throw new Folder_Not_Found_Exception($this->folder, CLEAROS_WARNING);

        // Let the chown command do the validation

        if ($owner) {
            try {
                $flags = ($recursive) ? '-R' : '';

                $shell = new Shell();

                if ($shell->execute(self::COMMAND_CHOWN, $owner . " $flags " . $this->folder, TRUE) != 0)
                    throw new Folder_Permissions_Exception($shell->get_first_output_line(), COMMON_ERROR);
            } catch(Engine_Exception $e) {
                throw new Folder_Exception($e->get_message(), COMMON_ERROR);
            }
        }

        if ($group) {
            try {
                $flags = ($recursive) ? '-R' : '';

                $shell = new Shell();

                if ($shell->execute(self::COMMAND_CHOWN, ':' . $group . " $flags " . $this->folder, TRUE) != 0)
                    throw new Folder_Permissions_Exception($shell->get_first_output_line(), COMMON_ERROR);
            } catch(Engine_Exception $e) {
                throw new Folder_Exception($e->get_message(), COMMON_ERROR);
            }
        }
    }

    /**
     * Creates a folder on the system.
     *
     * The method will return an error if the file already exists.
     *
     * @param string $owner folder owner
     * @param string $group folder group
     * @param string $mode  mode of the folder
     *
     * @return void
     */

    public function create($owner, $group, $mode)
    {
        clearos_profile(__METHOD__, __LINE__);

        clearstatcache(); // PHP caches file stat information... don't let it

        if ($this->exists())
            throw new Folder_Already_Exists_Exception($this->folder, COMMON_ERROR);

        try {
            $shell = new Shell();
            if ($shell->execute(self::COMMAND_MKDIR, "-p $this->folder", TRUE) != 0)
                throw new Folder_Exception($shell->get_first_output_line());
        } catch(Engine_Exception $e) {
            throw new Folder_Exception($e->get_message(), COMMON_ERROR);
        }

        if ($owner || $group) {
            try {
                $this->chown($owner, $group);
            } catch(Engine_Exception $e) {
                throw new Folder_Exception($e->get_message(), COMMON_ERROR);
            }
        }

        if ($mode) {
            try {
                $this->chmod($mode);
            } catch(Engine_Exception $e) {
                throw new Folder_Exception($e->get_message(), COMMON_ERROR);
            }
        }
    }

    /**
     * Deletes the folder.
     *
     * @param boolean $ignore_nonempty flag to ignore if files are contained within folder
     *
     * @return  void
     */

    public function delete($ignore_nonempty = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->exists())
            throw new Folder_Not_Found_Exception($this->folder, CLEAROS_WARNING);

        $shell = new Shell();
        if ($ignore_nonempty) {
            try {
                //TODO TODO TODO - validate the hell out of an "rm -rf"
                if ($shell->execute(self::COMMAND_RM, "-rf $this->folder", TRUE) != 0)
                    throw new Folder_Exception($shell->get_first_output_line());
            } catch(Engine_Exception $e) {
                throw new Folder_Exception($e->get_message(), COMMON_ERROR);
            }
        } else {
            try {
                if ($shell->execute(self::COMMAND_RMDIR, $this->folder, TRUE) != 0)
                    throw new Folder_Permissions_Exception($shell->get_first_output_line(), COMMON_ERROR);
            } catch(Engine_Exception $e) {
                throw new Folder_Exception($e->get_message(), COMMON_ERROR);
            }
        }
    }

    /**
     * Checks to see if given folder is really a folder.
     *
     * @return  boolean  TRUE if directory
     */

    public function is_directory()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();
            if ($shell->execute(self::COMMAND_FILE, $this->folder, TRUE) != 0)
                throw new Folder_Exception($shell->get_first_output_line());
        } catch(Engine_Exception $e) {
            throw new Folder_Exception($e->get_message(), COMMON_ERROR);
        }

        if (preg_match("/directory/", $shell->get_first_output_line()))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Checks the existence of the folder.
     *
     * @return  boolean  TRUE if folder exists
     */

    public function exists()
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->superuser) {
            try {
                $shell = new Shell();
                if ($shell->execute(self::COMMAND_LS, escapeshellarg($this->folder), TRUE) != 0)
                    return FALSE;
                else
                    return TRUE;
            } catch(Engine_Exception $e) {
                throw new Folder_Exception($e->get_message(), COMMON_ERROR);
            }
        } else {
            if (is_dir($this->folder))
                return TRUE;
            else
                return FALSE;
        }
    }

    /**
     * Returns the listing of files in the directory.
     *
     * The current (.) and and parent (..) entries are not included.
     *
     * @param boolean $detailed if TRUE, array contains detailed information about directory
     *
     * @return array file listing
     */

    public function get_listing($detailed = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $listing = array();

        if (! $this->exists())
            throw new Folder_Not_Found_Exception($this->folder, CLEAROS_WARNING);

        if ($detailed) {
            try {
                $shell = new Shell();
                $options = ' -lA --time-style=full-iso ' . escapeshellarg($this->folder);
                if ($shell->execute(self::COMMAND_LS, $options, TRUE) != 0)
                    throw new Folder_Exception($shell->get_first_output_line());
            } catch(Engine_Exception $e) {
                throw new Folder_Exception($e->get_message(), COMMON_ERROR);
            }

            $lines = $shell->get_output();
            // Remove ls summary
            array_shift($lines);
            $directories = array();
            $files = array();
            foreach ($lines as $line) {
                $parts = preg_split("/\s+/", $line, 9);
                // We want to list all directories first, the files
                if (substr($parts[0], 0, 1) == 'd') {
                    $directories[] = array(
                         'name' => $parts[8],
                         'properties' => $parts[0],
                         'size' => $parts[4],
                         'modified' => strtotime($parts[5] . ' ' . substr($parts[6], 0, 8) . ' ' . $parts[7])
                    );
                } else {
                    $files[] = array(
                                   'name' => $parts[8],
                                   'properties' => $parts[0],
                                   'size' => $parts[4],
                                   'modified' => strtotime($parts[5] . ' ' . substr($parts[6], 0, 8) . ' ' . $parts[7])
                               );
                }
            }
            $listing = array_merge($directories, $files);

            return $listing;
        } else {
            if ($this->superuser) {
                try {
                    $shell = new Shell();
                    if ($shell->execute(self::COMMAND_LS, $this->folder, TRUE) != 0)
                        throw new Folder_Exception($shell->get_first_output_line());
                } catch(Engine_Exception $e) {
                    throw new Folder_Exception($e->get_message(), COMMON_ERROR);
                }

                $fulllist = $shell->get_output();
            } else {
                $fulllist = scandir($this->folder);
            }
            foreach ($fulllist as $file) {
                if ($file != '.' && $file != '..')
                    $listing[] = $file;
            }

            sort($listing);

            return $listing;
        }
    }

    /**
     * Returns the octal permissions of the current folder.
     *
     * @return string folder permissions
     * @throws Folder_Not_Found_Exception, Engine_Exception
     */

    public function get_permissions()
    {
        clearos_profile(__METHOD__, __LINE__);

        clearstatcache();

        if (! $this->exists())
            throw new Folder_Not_Found_Exception($this->filename, COMMON_INFO);

        // TODO: this will fail on folders that user webconfig cannot read (protected directories).
        // Added Engine_Exception to docs to futureproof API.

        return substr(sprintf('%o', fileperms($this->folder)), -4);
    }

    /**
     * Returns the listing of files in the directory.
     *
     * The current (.) and and parent (..) entries are not included.
     *
     * @return  array  file listing
     */

    public function get_recursive_listing()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->exists())
            throw new Folder_Not_Found_Exception($this->folder);

        $listing = array();
        $fulllist = array();

        try {
            $shell = new Shell();
            if ($shell->execute(self::COMMAND_FIND, "$this->folder -type f", TRUE) != 0)
                throw new Folder_Exception($shell->get_first_output_line());
            $fulllist = $shell->get_output();
        } catch(Engine_Exception $e) {
            throw new Folder_Exception($e->get_message(), COMMON_ERROR);
        }

        foreach ($fulllist as $file)
            $listing[] = preg_replace("/" . preg_quote($this->folder, "/") . "\//", "", $file);

        sort($listing);

        return $listing;
    }

    /**
     * Returns an estimate of the size of the contents of the folder.
     *
     * @return integer the folder size in bytes
     * @throws Engine_Exception
     */

    public function get_size()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->exists())
            throw new Folder_Not_Found_Exception($this->filename, CLEAROS_WARNING);

        try {
            $shell = new Shell();
            $options = "-bc $this->folder";
            $exitcode = $shell->execute(self::COMMAND_DU, $options, TRUE);
        } catch(Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), COMMON_WARNING);
        }

        if ($exitcode == 0) {
            $parts = explode(" ", $shell->get_last_output_line());
            $size = (int)$parts[0];
            // Account for directory iteself
            if ($size <= 4096)
                return 0;
            else
                return (int)$parts[0];
        } else {
            throw new Engine_Exception(LOCALE_LANG_ERRMSG_WEIRD, COMMON_WARNING);
        }
    }

    /**
     * Returns the foldername (using PHP 'realpath') resolving references like "../../".
     *
     * @return  string  name of present working directory
     */
    public function get_folder_name()
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->superuser) {
            try {
                $shell = new Shell();
                $exitcode = $shell->execute(self::COMMAND_REALPATH, escapeshellarg($this->folder), TRUE);
            } catch(Engine_Exception $e) {
                throw new Engine_Exception($e->get_message(), COMMON_WARNING);
            }

            if ($exitcode == 0)
                return $shell->get_last_output_line();
            else
                throw new Engine_Exception(LOCALE_LANG_ERRMSG_WEIRD, COMMON_WARNING);
        }

        return realpath($this->folder);
    }
}
