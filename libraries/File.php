<?php

/**
 * File manipulation class.
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
use \clearos\apps\base\File_Already_Exists_Exception as File_Already_Exists_Exception;
use \clearos\apps\base\File_Exception as File_Exception;
use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\File_Permissions_Exception as File_Permissions_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/File_Already_Exists_Exception');
clearos_load_library('base/File_Exception');
clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/File_Not_Found_Exception');
clearos_load_library('base/File_Permissions_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * File manipulation class.
 *
 * The File class can be use for creating, reading and manipulating the
 * contents of a file.  If you need to change a configuration file, this may
 * be the class for you.  However, configuration files come in many different
 * forms, so this might not have what you need.  Feel free to do your own file
 * parsing.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class File extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * @var string filename
     */

    protected $filename = NULL;

    /**
     * @var superuser superuser
     */

    protected $superuser = FALSE;

    /**
     * @var boolean temporary Temporary file
     */

    protected $temporary = FALSE;

    /**
     * @var boolean contents loaded flag
     */

    protected $contents = NULL;

    const COMMAND_RM = '/bin/rm';
    const COMMAND_CAT = '/bin/cat';
    const COMMAND_MOVE = '/bin/mv';
    const COMMAND_COPY = '/bin/cp';
    const COMMAND_TOUCH = '/bin/touch';
    const COMMAND_CHOWN = '/bin/chown';
    const COMMAND_CHMOD = '/bin/chmod';
    const COMMAND_LS = '/bin/ls';
    const COMMAND_MD5 = '/usr/bin/md5sum';
    const COMMAND_FILE = '/usr/bin/file';
    const COMMAND_HEAD = '/usr/bin/head';
    const COMMAND_REPLACE = '/usr/sbin/app-rename';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * File constructor.
     *
     * @param string  $filename  target file
     * @param boolean $superuser superuser access required to read the file
     * @param boolean $temporary create a temporary file
     */

    public function __construct($filename, $superuser = FALSE, $temporary = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($temporary) {
            $this->temporary = $temporary;
            $this->filename = tempnam(CLEAROS_TEMP_DIR, basename($filename));
        } else
            $this->filename = $filename;

        $this->superuser = $superuser;
    }

    /**
     * Returns the filename.
     *
     * @return string name of file
     */

    public function get_filename()
    {
        return $this->filename;
    }

    /**
     * Returns the contents of a file.
     *
     * Set maxbytes to -1 to disable file size limit.
     *
     * @param int $maxbytes maximum number of bytes
     *
     * @return string contents of file
     * @throws File_Not_Found_Exception
     */

    public function get_contents($maxbytes = -1)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_int($maxbytes) || $maxbytes < -1)
            throw new Validation_Exception(LOCALE_LANG_ERRMSG_INVALID_TYPE, __METHOD__, __LINE__);

        $contents = $this->get_contents_as_array($maxbytes);

        return implode("\n", $contents);
    }

    /**
     * Returns the contents of a file in an array.
     *
     * Set maxbytes to -1 to disable file size limit.
     *
     * @param int $maxbytes maximum number of bytes
     *
     * @return array contents of file
     * @throws File_Not_Found_Exception, File_Exception
     */

    public function get_contents_as_array($max_bytes = -1)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_int($max_bytes) || $max_bytes < -1)
            throw new Validation_Exception(LOCALE_LANG_ERRMSG_INVALID_TYPE, __METHOD__, __LINE__);

        clearstatcache();

        if (! $this->exists() )
            throw new File_Not_Found_Exception();

        // If readable by webconfig, then use file_get_contents instead of shell

        if (is_readable($this->filename)) {
            if ($max_bytes > 0)
                $contents = file_get_contents($this->filename, FALSE, NULL, 0, $max_bytes);
            else
                $contents = file_get_contents($this->filename, FALSE, NULL, 0);

            if ($contents === FALSE)
                throw new Engine_Exception(LOCALE_LANG_ERRMSG_WEIRD, CLEAROS_WARNING);

            $this->contents = explode("\n", rtrim($contents));
        } else {
            $shell = new Shell();

            if ($max_bytes >= 0)
                $shell->execute(File::COMMAND_HEAD, "-c $max_bytes $this->filename", TRUE);
            else
                $shell->execute(File::COMMAND_CAT, escapeshellarg($this->filename), TRUE);

            $this->contents = $shell->get_output();
        }

        return $this->contents;
    }

    /**
     * Returns the contents of a file that match the given regular expression.
     *
     * Set maxbytes to -1 to disable file size limit.
     *
     * @param string $regex    search string
     * @param int    $maxbytes maximum number of bytes
     *
     * @return array contents of file
     * @throws File_Not_Found_Exception, EngineException
     */

    public function get_search_results($regex, $maxbytes = -1)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_int($maxbytes) || $maxbytes < -1)
            throw new Validation_Exception(LOCALE_LANG_ERRMSG_INVALID_TYPE, __METHOD__, __LINE__);

        $contents = $this->get_contents_as_array();

        $result = array();
        $count = 0;

        foreach ($contents as $line) {
            if (preg_match("/$regex/", $line)) {
                $result[] = $line;
                if ($maxbytes != -1) {
                    $count += strlen($line);
                    if ($count > $maxbytes)
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns a value for a given unique regular expression.
     *
     * This method is handy for simple configuration files with key/value pairs.  The
     * method will return a File_No_Match_Exception error if no match was made.
     *
     * @param string $key search string
     *
     * @return string value for the give key
     * @throws Validation_Exception, File_No_Match_Exception, File_Not_Found_Exception
     */

    public function lookup_value($key)
    {
        clearos_profile(__METHOD__, __LINE__);

        $contents = $this->get_contents_as_array();

        foreach ($contents as $line) {
            if (preg_match($key, $line)) {
                $result = preg_replace($key, "", $line);
                return trim($result);
            }
        }

        throw new File_No_Match_Exception($this->filename, $key);
    }

    /**
     * Checks the existence of the file.
     *
     * @return boolean TRUE if file exists
     * @throws File_Exception
     */

    public function exists()
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->superuser) {

            $options['validate_exit_code'] = FALSE;

            $shell = new Shell();
            $exit_code = $shell->execute(File::COMMAND_LS, escapeshellarg($this->filename), TRUE, $options);

            if ($exit_code == 0)
                return TRUE;
            else
                return FALSE;
        } else {
            clearstatcache();
            if (file_exists($this->filename))
                return TRUE;
            else
                return FALSE;
        }
    }

    /**
     * Returns the file size.
     *
     * @return int the file size
     * @throws File_Exception
     */

    public function get_size()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->exists())
            throw new File_Not_Found_Exception();

        try {
            $shell = new Shell();
            $args = "-loL " . escapeshellarg($this->filename);
            $exitcode = $shell->execute(self::COMMAND_LS, $args, TRUE);
        } catch (Engine_Exception $e) {
            throw new File_Exception($e->GetMessage(), CLEAROS_WARNING);
        }

        if ($exitcode == 0) {
            $shell->get_last_output_line();
            $parts = preg_split("/\s+/", $shell->get_last_output_line());
            return (int)$parts[3];
        } else {
            throw new Engine_Exception(LOCALE_LANG_ERRMSG_WEIRD, CLEAROS_WARNING);
        }
    }

    /**
     * Returns the MD5 hash of the file.
     *
     * @return string the MD5 hash
     * @throws File_Exception
     */

    public function get_md5()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->exists())
            throw new File_Not_Found_Exception();

        if ($this->superuser) {
            $md5 = md5_file("$this->filename");

            if ($md5)
                return $md5;

            try {
                $shell = new Shell();
                $exitcode = $shell->execute(self::COMMAND_MD5, escapeshellarg($this->filename), TRUE);
            } catch (Engine_Exception $e) {
                throw new File_Exception($e->GetMessage(), CLEAROS_WARNING);
            }

            if ($exitcode == 0) {
                $md5 = trim(ereg_replace("$this->filename", "", $shell->get_last_output_line()));
                return $md5;
            } else {
                throw new Engine_Exception(LOCALE_LANG_ERRMSG_WEIRD, CLEAROS_WARNING);
            }
        } else {
            return md5_file($this->get_filename());
        }
    }

    /**
     * Changes file mode.
     *
     * @param string $mode mode of the file
     *
     * @return void
     * @throws Validation_Exception, File_Not_Found_Exception, File_Permissions_Exception, File_Exception
     */

    public function chmod($mode)
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: validate $mode

        if (! $this->exists())
            throw new File_Not_Found_Exception();

        try {
            $shell = new Shell();
            $exitcode = $shell->execute(File::COMMAND_CHMOD, " $mode " . escapeshellarg($this->filename), TRUE);
        } catch (Engine_Exception $e) {
            throw new File_Exception($e->GetMessage(), CLEAROS_WARNING);
        }

        if ($exitcode != 0)
            throw new File_Permissions_Exception(FILE_LANG_ERRMSG_CHMOD . " - " . $this->filename, CLEAROS_WARNING);
    }


    /**
     * Changes file owner and/or group.
     *
     * Leave the owner or group blank if you do not want change one or the other.
     *
     * @param string $owner file owner
     * @param string $group file group
     *
     * @return void
     * @throws Validation_Exception, File_Not_Found_Exception, File_Permissions_Exception, File_Exception
     */

    public function chown($owner, $group)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (empty($owner) && empty($group))
            throw new Validation_Exception(LOCALE_LANG_ERRMSG_NO_ENTRIES, __METHOD__, __LINE__);

        // TODO: more input validation

        if (! $this->exists())
            throw new File_Not_Found_Exception();

        $shell = new Shell();

        if (! empty($owner)) {
            try {
                $exitcode = $shell->execute(File::COMMAND_CHOWN, "$owner " . escapeshellarg($this->filename), TRUE);
            } catch (Engine_Exception $e) {
                throw new File_Exception($e->get_message(), CLEAROS_WARNING);
            }

            if ($exitcode != 0)
                throw new File_Permissions_Exception(FILE_LANG_ERRMSG_CHOWN . " - " . $this->filename, CLEAROS_WARNING);
        }

        if (! empty($group)) {
            try {
                $exitcode = $shell->execute(File::COMMAND_CHOWN, " :$group " . escapeshellarg($this->filename), TRUE);
            } catch (Engine_Exception $e) {
                throw new File_Exception($e->get_message(), CLEAROS_WARNING);
            }

            if ($exitcode != 0)
                throw new File_Permissions_Exception(FILE_LANG_ERRMSG_CHOWN . " - " . $this->filename, CLEAROS_WARNING);
        }
    }

    /**
     * Returns the octal permissions of the current file.
     *
     * @return string file permissions
     * @throws File_Not_Found_Exception, File_Exception
     */

    public function get_permissions()
    {
        clearos_profile(__METHOD__, __LINE__);

        clearstatcache();

        if (! $this->exists())
            throw new File_Not_Found_Exception();

        // TODO: this will fail on files that user webconfig cannot read (protected directories).
        // Added File_Exception to docs to futureproof API.

        return substr(sprintf('%o', fileperms("$this->filename")), -4);
    }

    /**
     * Returns the last modified date of the file.
     *
     * @return long representing time file was last modified
     * @throws File_Not_Found_Exception, File_Exception
     */

    public function last_modified()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->exists())
            throw new File_Not_Found_Exception();

        if ($this->superuser) {
            $args = "-l --time-style=full-iso " . escapeshellarg($this->filename);

            $shell = new Shell();
            $shell->execute(self::COMMAND_LS, $args, TRUE);
            $shell->get_last_output_line();

            $parts = preg_split("/\s+/", $shell->get_last_output_line());
            return strtotime($parts[5] . " " . substr($parts[6], 0, 8) . " " . $parts[7]);

        } else {
            clearstatcache();
            return filemtime("$this->filename");
        }
    }


    /**
     * Creates a file on the system.
     *
     * @param string $owner file owner
     * @param string $group file group
     * @param string $mode  mode of the file
     *
     * @return void
     * @throws File_Already_Exists_Exception, File_Permissions_Exception, File_Exception
     */

    public function create($owner, $group, $mode)
    {
        clearos_profile(__METHOD__, __LINE__);

        clearstatcache();

        if ($this->exists())
            throw new File_Already_Exists_Exception();

        try {
            $shell = new Shell();
            $shell->execute(File::COMMAND_TOUCH, escapeshellarg($this->filename), TRUE);

            if ($owner || $group)
                $this->chown($owner, $group);

            if ($mode)
                $this->chmod($mode);

        } catch (File_Permissions_Exception $e) {
            // Delete file if permissions barf, rethrow
            $this->delete();
            throw new File_Permissions_Exception($e->GetMessage(), CLEAROS_WARNING);
        } catch (Engine_Exception $e) {
            throw new File_Exception($e->get_message(), CLEAROS_WARNING);
        }

        $this->contents = NULL;
    }


    /**
     * Deletes the file.
     *
     * @return void
     * @throws File_Not_Found_Exception, File_Exception
     */

    public function delete()
    {
        clearos_profile(__METHOD__, __LINE__);

        clearstatcache();

        if (! $this->exists())
            throw new File_Not_Found_Exception();

        try {
            $shell = new Shell();
            $shell->execute(File::COMMAND_RM, escapeshellarg($this->filename), TRUE);
        } catch (Engine_Exception $e) {
            throw new File_Exception($e->get_message(), CLEAROS_WARNING);
        }

        $this->contents = NULL;
    }

    /**
     * Checks to see if specified file is a directory.
     *
     * @return boolean TRUE if file is a directory
     * @throws File_Exception
     */

    public function is_directory()
    {
        clearos_profile(__METHOD__, __LINE__);

        $isdir = FALSE;

        if ($this->superuser) {

            try {
                $shell = new Shell();
                $shell->execute(File::COMMAND_FILE, escapeshellarg($this->filename), TRUE);

                // TODO -- a hack
                if (preg_match("/directory/", $shell->get_output(0))) {
                    $isdir = TRUE;
                }

            } catch (Engine_Exception $e) {
                throw new File_Exception($e->get_message(), CLEAROS_WARNING);
            }
        } else {
            $isdir = is_dir("$this->filename");
        }

        return $isdir;
    }

    /**
     * Checks to see if specified file is a symbolic link.
     *
     * @return integer  0 if not, 1 if active sym link, -1 if broken sym link
     * @throws File_Exception
     */

    public function is_sym_link()
    {
        clearos_profile(__METHOD__, __LINE__);

        $issym = 0;

        if ($this->superuser) {

            try {
                $shell = new Shell();
                $shell->execute(File::COMMAND_FILE, escapeshellarg($this->filename), TRUE);

                // TODO -- a hack
                if (preg_match("/symbolic link/", $shell->get_first_output_line())) {
                    if (preg_match("/broken/", $shell->get_first_output_line()))
                        $issym = -1;
                    else
                        $issym = 1;
                } else {
                    $issym = 0;
                }

            } catch (Engine_Exception $e) {
                throw new File_Exception($e->get_message(), CLEAROS_WARNING);
            }
        } else {
            if (is_link("$this->filename")) {
                if (! file_exists(readlink("$this->filename")))
                    $issym = -1;
                else
                    $issym = 1;
            } else {
                $issym = 0;
            }
        }

        return $issym;
    }

    /**
     * Replaces the contents of the given tempfile to this file.
     *
     * This is basically a "mv" with the following behavior:
     *  - This file (the one passed to the constructor) must exist.
     *  - The tempfile is deleted if successful.
     *  - The tempfile will take on the same file permissions and ownership as the target file.
     *
     * @param string $tempfile temp file
     *
     * @return void
     * @throws File_Not_Found_Exception File_Exception
     */

    public function replace($tempfile)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! file_exists($tempfile))
            throw new File_Not_Found_Exception();

        if (! $this->exists())
            throw new File_Not_Found_Exception();

        $tempfile = escapeshellarg($tempfile);
        $thisfile = escapeshellarg($this->filename);

        try {
            $shell = new Shell();
            $exitcode = $shell->execute(self::COMMAND_REPLACE, "$tempfile $thisfile", TRUE);
        } catch (Engine_Exception $e) {
            throw new File_Exception($e->get_message(), CLEAROS_WARNING);
        }

        if ($exitcode != 0) {
            $errmsg = $shell->get_first_output_line();
            throw new File_Exception($errmsg, CLEAROS_WARNING);
        }

        $this->contents = NULL;
    }


    /**
     * Writes array data to a file.
     *
     * The method does not automatically add a newline - that is up to you!
     * This method will return an error if the file does not exist.
     *
     * @param array $contents an array containing output lines
     *
     * @return void
     */

    public function dump_contents_from_array($contents)
    {
        clearos_profile(__METHOD__, __LINE__);

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        if (!($fh_t = @fopen($tempfile, "w")))
            throw new Engine_Exception(lang('base_file_open_error'));

        if ($contents)
            fputs($fh_t, implode("\n", $contents) . "\n");

        fclose($fh_t);

        $this->replace($tempfile);
    }

    /**
     * Appends data to a file.
     *
     * The method does not automatically add a newline - that is up to you!
     *
     * @param string $data line (or lines) to append to the file
     *
     * @return void
     * @throws File_Not_Found_Exception File_Exception
     */

    public function add_lines($data)
    {
        clearos_profile(__METHOD__, __LINE__);

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        try {
            $contents = $this->get_contents();
        } catch (Engine_Exception $e) {
            throw $e;
        }

        if (!($fh_t = @fopen($tempfile, "w")))
            throw new File_Exception(FILE_LANG_ERRMSG_OPEN . " - " . $tempfile, CLEAROS_INFO);

        // Remove and then re-insert newline on files...
        // this catches invalid files with no newline at the end
        trim($contents);

        if ($contents)
            fputs($fh_t, $contents . "\n");

        fputs($fh_t, $data);
        fclose($fh_t);

        $this->replace($tempfile);

        $this->contents = NULL;
    }

    /**
     * Appends a line (or lines) to a file at a particular location in the file.
     *
     * @param string $data  line(s) to insert into file
     * @param string $after regular expression defining the file location
     *
     * @return void
     * @throws File_No_Match_Exception, File_Not_Found_Exception, File_Exception
     */

    public function add_lines_after($data, $after)
    {
        clearos_profile(__METHOD__, __LINE__);

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        $lines = $this->get_contents_as_array();

        if (!($fh_t = @fopen($tempfile, "w")))
            throw new File_Exception(FILE_LANG_ERRMSG_OPEN . " - " . $tempfile, CLEAROS_INFO);

        $match = FALSE;

        foreach ($lines as $line) {
            fputs($fh_t, $line . "\n");

            if (preg_match($after, $line) && (!$match)) {
                $match = TRUE;
                fputs($fh_t, $data);
            }
        }

        fclose($fh_t);

        if (! $match) {
            unlink($tempfile);
            throw new File_No_Match_Exception($tempfile, $after);
        }

        $this->replace($tempfile);

        $this->contents = NULL;
    }

    /**
     * Prepends a line (or lines) to a file at a particular location in the file.
     *
     * @param string $data   line(s) to insert into file
     * @param string $before regular expression defining the file location
     *
     * @return void
     * @throws File_No_Match_Exception, File_Not_Found_Exception, File_Exception
     */

    public function add_lines_before($data, $before)
    {
        clearos_profile(__METHOD__, __LINE__);

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        $lines = $this->get_contents_as_array();

        if (!($fh_t = @fopen($tempfile, "w")))
            throw new File_Exception(FILE_LANG_ERRMSG_OPEN . " - " . $tempfile, CLEAROS_INFO);

        $match = FALSE;

        foreach ($lines as $line) {
            if (preg_match($before, $line) && (!$match)) {
                $match = TRUE;
                fputs($fh_t, $data);
            }

            fputs($fh_t, $line . "\n");
        }

        fclose($fh_t);

        if (! $match) {
            unlink($tempfile);
            throw new File_No_Match_Exception($tempfile, $before);
        }

        $this->replace($tempfile);

        $this->contents = NULL;
    }

    /**
     * Removes lines from a file that match the regular expression.
     *
     * @param string $search regular expression used to match removed lines
     *
     * @return integer number of lines deleted
     * @throws File_Not_Found_Exception
     */

    public function delete_lines($search)
    {
        clearos_profile(__METHOD__, __LINE__);

        $deleted = $this->replace_lines($search, '');

        $this->contents = NULL;

        return $deleted;
    }

    /**
     * Prepends lines with a string (usually a comment character).
     *
     * Any line matching the search string will be changed.
     *
     * @param string $search  regular expression used to match removed lines
     * @param string $prepend prepend string
     *
     * @return boolean TRUE if any matches were made
     * @throws File_Not_Found_Exception
     */

    public function prepend_lines($search, $prepend)
    {
        clearos_profile(__METHOD__, __LINE__);

        $prependlines = FALSE;

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        $lines = $this->get_contents_as_array();

        if (!($fh_t = @fopen($tempfile, "w")))
            throw new Engine_Exception(lang('base_file_open_error'));

        $match = FALSE;

        foreach ($lines as $line) {
            if (preg_match($search, $line)) {
                fputs($fh_t, $prepend . $line . "\n");
                $match = TRUE;
            } else {
                fputs($fh_t, $line . "\n");
            }
        }

        fclose($fh_t);

        if (! $match) {
            unlink($tempfile);
            throw new File_No_Match_Exception($tempfile, $search);
        } else {
            $prependlines = $this->replace($tempfile);
        }

        return $prependlines;
    }

    /**
     * Searches the file with the given regular expression and returns the first match.
     *
     * @param string $search regular expression
     *
     * @return string matching line
     * @throws Validation_Exception, File_No_Match_Exception, File_Not_Found_Exception, File_Exception
     */

    public function lookup_line($search)
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: validation (e.g. search must have two slashes)

        $lines = $this->get_contents_as_array();

        foreach ($lines as $line) {
            if (preg_match($search, $line)) {
                return $line;
            }
        }

        throw new File_No_Match_Exception($this->filename, $search);
    }

    /**
     * Similar to lookup_value, except you can specify a subsection of the target file.
     *
     * The start and end are regular expressions.  This can be handy in Apache-style configuration
     * files (e.g. configuring a particular Virtual Host).
     *
     * @param string $key   search string
     * @param string $start regular expression specifying the start line
     * @param string $end   regular expression specifying the end line
     *
     * @return string value for the given key
     * @throws File_Not_Found_Exception
     * @throws File_No_Match_Exception
     */

    public function lookup_value_between($key, $start, $end)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $lines = $this->get_contents_as_array();
        } catch (Engine_Exception $e) {
            throw $e;
        }

        // Find start tag
        foreach ($lines as $line) {
            if (preg_match($start, $line))
                break;

            array_shift($lines);
        }

        foreach ($lines as $line) {
            // Bail if see the end tag

            if (preg_match($end, $line))
                break;

            if (preg_match($key, $line)) {
                $result = trim(preg_replace($key, "", $line));

                if (!strlen($result))
                    return TRUE;

                return $result;
            }
        }

        throw new File_No_Match_Exception($this->filename, $key);
    }

    /**
     * Copies the file to new location.
     *
     * @param string $destination destination location
     *
     * @return void
     * @throws File_Exception, Validation_Exception
     */

    public function copy_to($destination)
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: validate destination

        try {
            $shell = new Shell();
            $arguments = "-a " . escapeshellarg($this->filename) . " " . escapeshellarg($destination);
            $exitcode = $shell->execute(File::COMMAND_COPY, $arguments, TRUE);
        } catch (Engine_Exception $e) {
            throw new File_Exception($e->get_message(), CLEAROS_WARNING);
        }

        if ($exitcode != 0) {
            $errmsg = $shell->get_output();
            throw new File_Exception($errmsg[0], CLEAROS_WARNING);
        }
    }

    /**
     * Moves the file to new location.
     *
     * @param string $destination destination location
     *
     * @see replace
     * @return void
     * @throws File_Exception, Validation_Exception
     */

    public function move_to($destination)
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: validate destination

        try {
            $shell = new Shell();
            $arguments =  escapeshellarg($this->filename) . " " . escapeshellarg($destination);
            $exitcode = $shell->execute(File::COMMAND_MOVE, $arguments, TRUE);
        } catch (Engine_Exception $e) {
            throw new File_Exception($e->get_message(), CLEAROS_WARNING);
        }

        if ($exitcode != 0) {
            $errmsg = $shell->get_output();
            throw new File_Exception($errmsg[0], CLEAROS_WARNING);
        }

        $this->filename = $destination;
    }

    /**
     * Replaces lines in a section of a file for Apache-style configuration files.
     *
     * Specify the (non-unique) start and end tags along with a search value that uniquely defines the section.
     *
     * @param string $search      regular expression for the search string
     * @param string $replacement replacement line
     * @param string $start       regular expression specifying the start line
     * @param string $end         regular expression specifying the end line
     * 
     * @return void
     * @throws File_Not_Found_Exception Exception (inherited from get_contents_as_array)
     */

    public function replace_lines_between($search, $replacement, $start, $end)
    {
        clearos_profile(__METHOD__, __LINE__);

        $replaced = FALSE;

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        $lines = $this->get_contents_as_array();

        if (!($fh_t = @fopen($tempfile, "w"))) {
            throw new Engine_Exception(lang('base_file_open_error'));
        } else {

            // Find start tag
            $match = FALSE;
            foreach ($lines as $line) {
                if (preg_match($start, $line))
                    break;

                fputs($fh_t, $line . "\n");

                array_shift($lines);
            }

            foreach ($lines as $line) {
                // Bail if see the end tag

                if (preg_match($end, $line))
                    break;

                if (preg_match($search, $line)) {
                    $match = TRUE;

                    if (strlen($replacement))
                        fputs($fh_t, $replacement);
                } else {
                    fputs($fh_t, $line . "\n");
                }

                array_shift($lines);
            }

            foreach ($lines as $line)
            fputs($fh_t, $line . "\n");

            fclose($fh_t);

            if (! $match) {
                unlink($tempfile);
                throw new File_No_Match_Exception($tempfile, $search);
            } else {
                $replaced = $this->replace($tempfile);
            }
        }

        return $match;
    }

    /**
     * Replaces a line (defined by a regular expression) with a replacement.
     *
     * @param string  $search       search string
     * @param string  $replacement  replacement line (or lines)
     * @param integer $max_replaced maximum number of matches to make
     *
     * @return integer number of replacements made
     * @throws File_Exception, Validation_Exception
     */

    public function replace_lines($search, $replacement, $max_replaced = -1)
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: add validation

        $replaced = 0;

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        $lines = $this->get_contents_as_array();

        if (!($fh_t = @fopen($tempfile, "w")))
            throw new File_Exception(FILE_LANG_ERRMSG_OPEN . " - " . $tempfile, CLEAROS_INFO);

        // Find start tag
        foreach ($lines as $line) {
            if (preg_match($search, $line) && (($replaced < $max_replaced) || $max_replaced == -1)) {
                fputs($fh_t, $replacement);
                $replaced++;
            } else {
                fputs($fh_t, $line . "\n");
            }
        }

        fclose($fh_t);

        if ($replaced == 0) {
            unlink($tempfile);
            return 0;
        } else {
            $this->replace($tempfile);
        }

        $this->contents = NULL;

        return $replaced;
    }

    /**
     * Replaces a line defined by a regular expression.
     *
     * @param string $search      search string
     * @param string $replacement replacement line (or lines)
     *
     * @return integer number of replacements made
     * @throws File_Not_Found_Exception, File_Exception
     */

    public function replace_one_line($search, $replacement)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->replace_lines($search, $replacement, 1);
    }

    /**
     * Replaces a line defined by a regular expression.
     *
     * This version differs from replace_one_line
     * in that it uses preg_replace to do the substitution.  Thus you can
     * use parts of a pattern match in the replacement (ie: $1, $2, etc).
     *
     * @param string $search      search expression
     * @param string $replacement replacement expression
     *
     * @return boolean TRUE if any replacements were made
     * @throws File_Not_Found_Exception Exception, File_No_Match_Exception
     */

    public function replace_one_line_by_pattern($search, $replacement)
    {
        clearos_profile(__METHOD__, __LINE__);

        $replaced = FALSE;

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        $lines = $this->get_contents_as_array();

        if (!($fh_t = @fopen($tempfile, "w"))) {
            throw new Engine_Exception(lang('base_file_open_error'));
        } else {
            $match = FALSE;
            foreach ($lines as $line) {
                if ((preg_match($search, $line)) && !$match) {
                    $match = preg_replace($search, $replacement, $line);

                    if ($match)
                        fputs($fh_t, $match . "\n");
                } else
                    fputs($fh_t, $line . "\n");
            }

            fclose($fh_t);

            if (! $match) {
                unlink($tempfile);
                throw new File_No_Match_Exception($this->filename, $search);
            } else {
                $replaced = $this->replace($tempfile);
            }
        }

        return $replaced;
    }

    /**
     * Replaces all matching lines defined by a regular expression.
     *
     * @param string $search      search expression
     * @param string $replacement replacement expression
     *
     * @return boolean TRUE if any replacements were made
     * @throws File_Not_Found_Exception, Engine_Exception
     */

    public function replace_lines_by_pattern($search, $replacement)
    {
        clearos_profile(__METHOD__, __LINE__);

        $replaced = FALSE;

        $tempfile = tempnam(CLEAROS_TEMP_DIR, basename("$this->filename"));

        $lines = $this->get_contents_as_array();

        if (!($fh_t = @fopen($tempfile, "w"))) {
            throw new Engine_Exception(lang('base_file_open_error'));
        } else {
            $match = FALSE;
            foreach ($lines as $line) {
                if ((preg_match($search, $line))) {
                    $match = preg_replace($search, $replacement, $line);

                    if ($match)
                        fputs($fh_t, $match . "\n");
                } else
                    fputs($fh_t, $line . "\n");
            }

            fclose($fh_t);

            if (! $match) {
                unlink($tempfile);
                throw new File_No_Match_Exception($tempfile, $search);
            } else {
                $replaced = $this->replace($tempfile);
            }
        }

        return $replaced;
    }
}
