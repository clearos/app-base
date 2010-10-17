<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2006-2010 ClearFoundation
//
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

/**
 * File manipulation class.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C E S
///////////////////////////////////////////////////////////////////////////////

require_once('/usr/clearos/framework/config.php');

clearos_load_library('base/Engine');
clearos_load_library('base/ShellExec');

///////////////////////////////////////////////////////////////////////////////
// E X C E P T I O N  C L A S S E S
///////////////////////////////////////////////////////////////////////////////

/**
 * File exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class FileException extends EngineException
{
	/**
	 * FileException constructor.
	 *
	 * @param string $errmsg error message
	 * @param int $code error code
	 */

	public function __construct($errmsg, $code)
	{
		parent::__construct($errmsg, $code);
	}
}

/**
 * File permissions exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class FilePermissionsException extends EngineException
{
	/**
	 * FilePermissionsException constructor.
	 *
	 * @param string $errmsg error message
	 * @param int $code error code
	 */

	public function __construct($errmsg, $code)
	{
		parent::__construct($errmsg, $code);
	}
}

/**
 * File already exists exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class FileAlreadyExistsException extends EngineException
{
	/**
	 * FileAlreadyExistsException constructor.
	 *
	 * @param string $filename filename
	 * @param int $code error code
	 */

	public function __construct($filename, $code)
	{
		parent::__construct(FILE_LANG_ERRMSG_EXISTS . " - " . $filename, $code);
	}
}

/**
 * File not found exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class FileNotFoundException extends EngineException
{
	/**
	 * FileNotFoundException constructor.
	 *
	 * @param string $filename filename
	 * @param int $code error code
	 */

	public function __construct($filename, $code)
	{
		parent::__construct(FILE_LANG_ERRMSG_NOTEXIST . " - " . $filename, $code);
	}
}

/**
 * File I/O exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class FileIoException extends EngineException
{
	/**
	 * FileIoException constructor.
	 *
	 * @param string $filename filename
	 * @param int $code error code
	 */

	public function __construct($filename, $code)
	{
		parent::__construct(FILE_LANG_ERRMSG_READ . " - " . $filename, $code);
	}
}

/**
 * Value not found in file exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class FileNoMatchException extends EngineException
{
	/**
	 * FileNoMatchException constructor.
	 *
	 * @param string $filename filename
	 * @param int $code error code
	 * @param string $key key used to match a string
	 */

	public function __construct($filename, $code, $key)
	{
		parent::__construct(FILE_LANG_ERRMSG_NO_MATCH . " - $filename for key $key", $code);
	}
}

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
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class File extends Engine
{
	///////////////////////////////////////////////////////////////////////////////
	// F I E L D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * @var string filename
	 */

	protected $filename = null;

	/**
	 * @var superuser superuser
	 */

	protected $superuser = false;

	/**
	 * @var boolean temporary Temporary file
	 */

	protected $temporary = false;

	/**
	 * @var boolean contents loaded flag
	 */

	protected $contents = null;

	const CMD_RM = '/bin/rm';
	const CMD_CAT = '/bin/cat';
	const CMD_MOVE = '/bin/mv';
	const CMD_COPY = '/bin/cp';
	const CMD_TOUCH = '/bin/touch';
	const CMD_CHOWN = '/bin/chown';
	const CMD_CHMOD = '/bin/chmod';
	const CMD_LS = '/bin/ls';
	const CMD_MD5 = '/usr/bin/md5sum';
	const CMD_FILE = '/usr/bin/file';
	const CMD_HEAD = '/usr/bin/head';
	const CMD_REPLACE = '/usr/sbin/app-rename';

	///////////////////////////////////////////////////////////////////////////////
	// M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * File constructor.
	 *
	 * @param string filename target file
	 * @param boolean $superuser superuser access required to read the file
	 * @param boolean $temporary create a temporary file
	 */

	public function __construct($filename, $superuser = false, $temporary = false)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if ($temporary) {
			$this->temporary = $temporary;
			$this->filename = tempnam(COMMON_TEMP_DIR, basename($filename));
		} else
			$this->filename = $filename;

		$this->superuser = $superuser;

		parent::__construct();

	//	require_once(GlobalGetLanguageTemplate(__FILE__));
	}

	/**
	 * Returns the filename.
	 *
	 * @return string name of file
	 */
	function GetFilename()
	{
		return $this->filename;
	}

	/**
	 * Returns the contents of a file.
	 *
	 * Set maxbytes to -1 to disable file size limit.
	 *
	 * @param int $maxbytes maximum number of bytes
	 * @return string contents of file
	 * @throws FileNotFoundException, FileException
	 */

	function GetContents($maxbytes = -1)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (!is_int($maxbytes) || $maxbytes < -1)
			throw new ValidationException(LOCALE_LANG_ERRMSG_INVALID_TYPE, __METHOD__, __LINE__);

		$contents = $this->GetContentsAsArray($maxbytes);

		return implode("\n", $contents);
	}

	/**
	 * Returns the contents of a file in an array.
	 *
	 * Set maxbytes to -1 to disable file size limit.
	 *
	 * @param int $maxbytes maximum number of bytes
	 * @return array contents of file
	 * @throws FileNotFoundException, FileException
	 */

	function GetContentsAsArray($maxbytes = -1)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (!is_int($maxbytes) || $maxbytes < -1)
			throw new ValidationException(LOCALE_LANG_ERRMSG_INVALID_TYPE, __METHOD__, __LINE__);

		if (! $this->Exists() )
			throw new FileNotFoundException($this->filename, COMMON_INFO);

		// TODO: use some other semaphore -- this breaks with maxbytes set
		//if (is_null($this->contents)) {

		// If readable by webconfig, then use file_get_contents
		// If file_get_contents fails, try shellexec

		if (is_readable("$this->filename")) {
			$maxlen = ($maxbytes >= 0) ? $maxbytes : null;
			$contents = file_get_contents("$this->filename", false, NULL, 0, $maxlen);

			if ($contents) {
				$this->contents = explode("\n", rtrim($contents));
				return $this->contents;
			}
		}

		try {
			$shell = new ShellExec();
			if ($maxbytes >= 0)
				$exitcode = $shell->Execute(File::CMD_HEAD, "-c $maxbytes $this->filename", true);
			else
				$exitcode = $shell->Execute(File::CMD_CAT, escapeshellarg($this->filename), true);
		} catch (Exception $e) {
			throw new FileException($e->getMessage(), COMMON_WARNING);
		}

		if ($exitcode != 0)
			throw new FileException($shell->GetFirstOutputLine(), COMMON_WARNING);

		$this->contents = $shell->GetOutput();

		return $this->contents;
	}

	/**
	 * Returns the contents of a file that match the given regular expression.
	 *
	 * Set maxbytes to -1 to disable file size limit.
	 *
	 * @param string $regex search string
	 * @param int $maxbytes maximum number of bytes
	 * @return array contents of file
	 * @throws FileNotFoundException, FileException
	 */

	function GetSearchResults($regex, $maxbytes = -1)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (!is_int($maxbytes) || $maxbytes < -1)
			throw new ValidationException(LOCALE_LANG_ERRMSG_INVALID_TYPE, __METHOD__, __LINE__);

		$contents = $this->GetContentsAsArray();

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
	 * method will return a FileNoMatchException error if no match was made.
	 *
	 * @param string $key search string
	 * @return string value for the give key
	 * @throws ValidationException, FileNoMatchException, FileNotFoundException, FileException
	 */

	function LookupValue($key)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// TODO: input validation (if any?)

		$contents = $this->GetContentsAsArray();

		foreach ($contents as $line) {
			if (preg_match($key, $line)) {
				$result = preg_replace($key, "", $line);
				return trim($result);
			}
		}

		throw new FileNoMatchException($this->filename, COMMON_INFO, $key);
	}

	/**
	 * Checks the existence of the file.
	 *
	 * @return boolean true if file exists
	 * @throws FileException
	 */

	function Exists()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if ($this->superuser) {
			try {
				$shell = new ShellExec();
				$exitcode = $shell->Execute(File::CMD_LS, escapeshellarg($this->filename), true);
			} catch (Exception $e) {
				throw new FileException($e->GetMessage(), COMMON_WARNING);
			}

			if ($exitcode == 0)
				return true;
			else
				return false;
		} else {
			clearstatcache();
			if (file_exists("$this->filename"))
				return true;
			else
				return false;
		}
	}

	/**
	 * Returns the file size.
	 *
	 * @return int the file size
	 * @throws FileException
	 */

	function GetSize()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (! $this->Exists())
			throw new FileNotFoundException($this->filename, COMMON_INFO);

		try {
			$shell = new ShellExec();
			$args = "-loL " . escapeshellarg($this->filename);
			$exitcode = $shell->Execute(self::CMD_LS, $args, true);
		} catch (Exception $e) {
			throw new FileException($e->GetMessage(), COMMON_WARNING);
		}

		if ($exitcode == 0) {
			$shell->GetLastOutputLine();
			$parts = preg_split("/\s+/", $shell->GetLastOutputLine());
			return (int)$parts[3];
		} else {
			throw new EngineException(LOCALE_LANG_ERRMSG_WEIRD, COMMON_WARNING);
		}
	}

	/**
	 * Returns the MD5 hash of the file.
	 *
	 * @return string the MD5 hash
	 * @throws FileException
	 */

	function GetMd5()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (! $this->Exists())
			throw new FileNotFoundException($this->filename, COMMON_INFO);

		if ($this->superuser) {
			$md5 = md5_file("$this->filename");
			if ($md5)
				return $md5;
			try {
				$shell = new ShellExec();
				$exitcode = $shell->Execute(self::CMD_MD5, escapeshellarg($this->filename), true);
			} catch (Exception $e) {
				throw new FileException($e->GetMessage(), COMMON_WARNING);
			}

			if ($exitcode == 0) {
				$md5 = trim(ereg_replace("$this->filename", "", $shell->GetLastOutputLine()));
				return $md5;
			} else {
				throw new EngineException(LOCALE_LANG_ERRMSG_WEIRD, COMMON_WARNING);
			}
		} else {
			return md5_file($this->GetFilename());
		}
	}

	/**
	 * Changes file mode.
	 *
	 * @param string $mode mode of the file
	 * @return void
	 * @throws ValidationException, FileNotFoundException, FilePermissionsException, FileException
	 */

	function Chmod($mode)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// TODO: validate $mode

		if (! $this->Exists())
			throw new FileNotFoundException($this->filename, COMMON_NOTICE);

		try {
			$shell = new ShellExec();
			$exitcode = $shell->Execute(File::CMD_CHMOD, " $mode " . escapeshellarg($this->filename), true);
		} catch (Exception $e) {
			throw new FileException($e->GetMessage(), COMMON_WARNING);
		}

		if ($exitcode != 0)
			throw new FilePermissionsException(FILE_LANG_ERRMSG_CHMOD . " - " . $this->filename, COMMON_WARNING);
	}


	/**
	 * Changes file owner and/or group.
	 *
	 * Leave the owner or group blank if you do not want change one or the other.
	 *
	 * @param string $owner file owner
	 * @param string $group file group
	 * @return void
	 * @throws ValidationException, FileNotFoundException, FilePermissionsException, FileException
	 */

	function Chown($owner, $group)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (empty($owner) && empty($group))
			throw new ValidationException(LOCALE_LANG_ERRMSG_NO_ENTRIES, __METHOD__, __LINE__);

		// TODO: more input validation

		if (! $this->Exists())
			throw new FileNotFoundException($this->filename, COMMON_NOTICE);

		$shell = new ShellExec();

		if (! empty($owner)) {
			try {
				$exitcode = $shell->Execute(File::CMD_CHOWN, "$owner " . escapeshellarg($this->filename), true);
			} catch (Exception $e) {
				throw new FileException($e->getMessage(), COMMON_WARNING);
			}

			if ($exitcode != 0)
				throw new FilePermissionsException(FILE_LANG_ERRMSG_CHOWN . " - " . $this->filename, COMMON_WARNING);
		}

		if (! empty($group)) {
			try {
				$exitcode = $shell->Execute(File::CMD_CHOWN, " :$group " . escapeshellarg($this->filename), true);
			} catch (Exception $e) {
				throw new FileException($e->getMessage(), COMMON_WARNING);
			}

			if ($exitcode != 0)
				throw new FilePermissionsException(FILE_LANG_ERRMSG_CHOWN . " - " . $this->filename, COMMON_WARNING);
		}
	}

	/**
	 * Returns the octal permissions of the current file.
	 *
	 * @return string file permissions
	 * @throws FileNotFoundException, FileException
	 */

	function GetPermissions()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		clearstatcache();

		if (! $this->Exists())
			throw new FileNotFoundException($this->filename, COMMON_INFO);

		// TODO: this will fail on files that user webconfig cannot read (protected directories).
		// Added FileException to docs to futureproof API.

		return substr(sprintf('%o', fileperms("$this->filename")), -4);
	}

	/**
	 * Returns the last modified date of the file.
	 *
	 * @return long representing time file was last modified
	 * @throws FileNotFoundException, FileException
	 */

	function LastModified()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (! $this->Exists())
			throw new FileNotFoundException($this->filename, COMMON_INFO);

		if ($this->superuser) {
			try {
				$shell = new ShellExec();
				$args = "-l --time-style=full-iso " . escapeshellarg($this->filename);
				$exitcode = $shell->Execute(self::CMD_LS, $args, true);
			} catch (Exception $e) {
				throw new FileException($e->GetMessage(), COMMON_WARNING);
			}

			if ($exitcode == 0) {
				$shell->GetLastOutputLine();
				$parts = preg_split("/\s+/", $shell->GetLastOutputLine());
				return strtotime($parts[5] . " " . substr($parts[6], 0, 8) . " " . $parts[7]);

			} else {
				throw new EngineException(LOCALE_LANG_ERRMSG_WEIRD, COMMON_WARNING);
			}
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
	 * @param string $mode mode of the file
	 * @return void
	 * @throws FileAlreadyExistsException, FilePermissionsException, FileException
	 */

	function Create($owner, $group, $mode)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		clearstatcache();

		if ($this->Exists())
			throw new FileAlreadyExistsException($this->filename, COMMON_NOTICE);

		try {
			$shell = new ShellExec();
			$shell->Execute(File::CMD_TOUCH, escapeshellarg($this->filename), true);

			if ($owner || $group)
				$this->Chown($owner, $group);

			if ($mode)
				$this->Chmod($mode);

		} catch (FilePermissionsException $e) {
			// Delete file if permissions barf, rethrow
			$this->Delete();
			throw new FilePermissionsException($e->GetMessage(), COMMON_WARNING);
		} catch (Exception $e) {
			throw new FileException($e->getMessage(), COMMON_WARNING);
		}

		$this->contents = null;
	}


	/**
	 * Deletes the file.
	 *
	 * @return void
	 * @throws FileNotFoundException, FileException
	 */

	function Delete()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		clearstatcache();

		if (! $this->Exists())
			throw new FileNotFoundException($this->filename, COMMON_NOTICE);

		try {
			$shell = new ShellExec();
			$shell->Execute(File::CMD_RM, escapeshellarg($this->filename), true);
		} catch (Exception $e) {
			throw new FileException($e->getMessage(), COMMON_WARNING);
		}

		$this->contents = null;
	}

	/**
	 * Checks to see if specified file is a directory.
	 *
	 * @return boolean true if file is a directory
	 * @throws FileException
	 */

	function IsDirectory()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$isdir = false;

		if ($this->superuser) {

			try {
				$shell = new ShellExec();
				$shell->Execute(File::CMD_FILE, escapeshellarg($this->filename), true);

				// TODO -- a hack
				if (preg_match("/directory/", $shell->GetOutput(0))) {
					$isdir = true;
				}

			} catch (Exception $e) {
				throw new FileException($e->getMessage(),COMMON_WARNING);
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
	 * @throws FileException
	 */

	function IsSymLink()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$issym = 0;

		if ($this->superuser) {

			try {
				$shell = new ShellExec();
				$shell->Execute(File::CMD_FILE, escapeshellarg($this->filename), true);

				// TODO -- a hack
				if (preg_match("/symbolic link/", $shell->GetFirstOutputLine())) {
					if (preg_match("/broken/", $shell->GetFirstOutputLine()))
						$issym = -1;
					else
						$issym = 1;
				} else {
					$issym = 0;
				}

			} catch (Exception $e) {
				throw new FileException($e->getMessage(),COMMON_WARNING);
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
	 * @return void
	 * @throws FileNotFoundException FileException
	 */

	function Replace($tempfile)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (! file_exists($tempfile))
			throw FileNotFoundException($tempfile, COMMON_NOTICE);

		if (! $this->Exists())
			throw FileNotFoundException($this->filename, COMMON_NOTICE);

		$tempfile = escapeshellarg($tempfile);
		$thisfile = escapeshellarg($this->filename);

		try {
			$shell = new ShellExec();
			$exitcode = $shell->Execute(self::CMD_REPLACE, "$tempfile $thisfile", true);
		} catch (Exception $e) {
			throw new FileException($e->getMessage(), COMMON_WARNING);
		}

		if ($exitcode != 0) {
			$errmsg = $shell->GetFirstOutputLine();
			throw new FileException($errmsg, COMMON_WARNING);
		}

		$this->contents = null;
	}


	/**
	 * Writes array data to a file.
	 *
	 * The method does not automatically add a newline - that is up to you!
	 * This method will return an error if the file does not exist.
	 *
	 * @param  array  $contents  an array containing output lines
	 * @return void
	 */

	function DumpContentsFromArray($contents)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		if (!($fh_t = @fopen($tempfile, "w"))) {
			// TODO: AddValidationError replacement
			$this->AddValidationError(FILE_LANG_ERRMSG_OPEN . $tempfile,__METHOD__,__LINE__);
		} else {
			if ($contents)
				fputs($fh_t, implode("\n", $contents) . "\n");

			fclose($fh_t);

			$this->Replace($tempfile);
		}
	}

	/**
	 * Appends data to a file.
	 *
	 * The method does not automatically add a newline - that is up to you!
	 *
	 * @param  string $data line (or lines) to append to the file
	 * @return void
	 * @throws FileNotFoundException FileException
	 */

	function AddLines($data)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		try {
			$contents = $this->GetContents();
		} catch (Exception $e) {
			throw $e;
		}

		if (!($fh_t = @fopen($tempfile, "w")))
			throw new FileException(FILE_LANG_ERRMSG_OPEN . " - " . $tempfile, COMMON_NOTICE);

		// Remove and then re-insert newline on files...
		// this catches invalid files with no newline at the end
		trim($contents);

		if ($contents)
			fputs($fh_t, $contents . "\n");

		fputs($fh_t, $data);
		fclose($fh_t);

		$this->Replace($tempfile);

		$this->contents = null;
	}

	/**
	 * Appends a line (or lines) to a file at a particular location in the file.
	 *
	 * @param string $data line(s) to insert into file
	 * @param string $after regular expression defining the file location
	 * @return void
	 * @throws FileNoMatchException, FileNotFoundException, FileException
	 */

	function AddLinesAfter($data, $after)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		$lines = $this->GetContentsAsArray();

		if (!($fh_t = @fopen($tempfile, "w")))
			throw new FileException(FILE_LANG_ERRMSG_OPEN . " - " . $tempfile, COMMON_NOTICE);

		$match = false;

		foreach ($lines as $line) {
			fputs($fh_t, $line . "\n");

			if (preg_match($after, $line) && (!$match)) {
				$match = true;
				fputs($fh_t, $data);
			}
		}

		fclose($fh_t);

		if (! $match) {
			throw new FileNoMatchException($tempfile, COMMON_NOTICE, $after);
			unlink($tempfile);
		}

		$this->Replace($tempfile);

		$this->contents = null;
	}

	/**
	 * Prepends a line (or lines) to a file at a particular location in the file.
	 *
	 * @param string $data line(s) to insert into file
	 * @param string $before regular expression defining the file location
	 * @return void
	 * @throws FileNoMatchException, FileNotFoundException, FileException
	 */

	function AddLinesBefore($data, $before)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		$lines = $this->GetContentsAsArray();

		if (!($fh_t = @fopen($tempfile, "w")))
			throw new FileException(FILE_LANG_ERRMSG_OPEN . " - " . $tempfile, COMMON_NOTICE);

		$match = false;

		foreach ($lines as $line) {
			if (preg_match($before, $line) && (!$match)) {
				$match = true;
				fputs($fh_t, $data);
			}

			fputs($fh_t, $line . "\n");
		}

		fclose($fh_t);

		if (! $match) {
			throw new FileNoMatchException($tempfile, COMMON_NOTICE, $before);
			unlink($tempfile);
		}

		$this->Replace($tempfile);

		$this->contents = null;
	}

	/**
	 * Removes lines from a file that match the regular expression.
	 *
	 * @param string $search regular expression used to match removed lines
	 * @return integer number of lines deleted
	 * @throws FileNotFoundException Exception (inherited from GetContents)
	 */

	function DeleteLines($search)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$deleted = $this->ReplaceLines($search, '');

		$this->contents = null;

		return $deleted;
	}

	/**
	 * Prepends lines with a string (usually a comment character).
	 *
	 * Any line matching the search string will be changed.
	 *
	 * @param  string  $prepend  prepend string
	 * @param  string  $search  regular expression used to match removed lines
	 * @return  boolean  true if any matches were made
	 * @throws  FileNotFoundException Exception (inherited from GetContentsAsArray)
	 */

	function PrependLines($search, $prepend)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$prependlines = false;

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		$lines = $this->GetContentsAsArray();

		if (!($fh_t = @fopen($tempfile, "w"))) {
			// TODO: AddValidationError replacement
			$this->AddValidationError(FILE_LANG_ERRMSG_OPEN . $tempfile,__METHOD__,__LINE__);
		} else {
			$match = false;
			foreach ($lines as $line) {
				if (preg_match($search, $line)) {
					fputs($fh_t, $prepend . $line . "\n");
					$match = true;
				} else {
					fputs($fh_t, $line . "\n");
				}
			}

			fclose($fh_t);

			if (! $match) {
				// TODO: AddValidationError replacement
				$this->AddValidationError(LOCALE_LANG_ERRMSG_NO_MATCH,__METHOD__,__LINE__);
				unlink($tempfile);
			} else {
				$prependlines = $this->Replace($tempfile);
			}
		}

		return $prependlines;
	}

	/**
	 * Searches the file with the given regular expression and returns the first match.
	 *
	 * @param string $search regular expression
	 * @return string matching line
	 * @throws ValidationException, FileNoMatchException, FileNotFoundException, FileException
	 */

	function LookupLine($search)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// TODO: validation (e.g. search must have two slashes)

		$lines = $this->GetContentsAsArray();

		foreach ($lines as $line) {
			if (preg_match($search, $line)) {
				return $line;
			}
		}

		throw new FileNoMatchException($this->filename, COMMON_INFO, $search);
	}

	/**
	 * Similar to LookupValue, except you can specify a subsection of the target file.
	 *
	 * The start and end are regular expressions.  This can be handy in Apache-style configuration
	 * files (e.g. configuring a particular Virtual Host).
	 *
	 * @param  string  $key  search string
	 * @param  string  $start  regular expression specifying the start line
	 * @param  string  $end  regular expression specifying the end line
	 * @return  string  value for the give key
	 * @throws  FileNotFoundException Exception (inherited from GetContentsAsArray)
	 * @throws  FileNoMatchException
	 */

	function LookupValueBetween($key, $start, $end)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		try {
			$lines = $this->GetContentsAsArray();
		} catch (Exception $e) {
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
					return true;

				return $result;
			}
		}

		throw new FileNoMatchException($this->filename, COMMON_INFO, $key);
	}

	/**
	 * Copies the file to new location.
	 *
	 * @param string $destination destination location
	 * @return void
	 * @throws FileException, ValidationException
	 */

	function CopyTo($destination)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// TODO: validate destination

		try {
			$shell = new ShellExec();
			$exitcode = $shell->Execute(File::CMD_COPY, "-a " . escapeshellarg($this->filename) . " " . escapeshellarg($destination), true);
		} catch (Exception $e) {
			throw new FileException($e->getMessage(), COMMON_WARNING);
		}

		if ($exitcode != 0) {
			$errmsg = $shell->GetOutput();
			throw new FileException($errmsg[0], COMMON_WARNING);
		}
	}

	/**
	 * Moves the file to new location.
	 *
	 * @see Replace
	 * @param string $destination destination location
	 * @return void
	 * @throws FileException, ValidationException
	 */

	function MoveTo($destination)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// TODO: validate destination

		try {
			$shell = new ShellExec();
			$exitcode = $shell->Execute(File::CMD_MOVE, escapeshellarg($this->filename) . " " . escapeshellarg($destination), true);
		} catch (Exception $e) {
			throw new FileException($e->getMessage(),COMMON_WARNING);
		}

		if ($exitcode != 0) {
			$errmsg = $shell->GetOutput();
			throw new FileException($errmsg[0], COMMON_WARNING);
		}

		$this->filename = $destination;
	}

	/**
	 * Replaces lines in a section of a file for Apache-style configuration files.
	 *
	 * Specify the (non-unique) start and end tags along with a search value that uniquely defines the section.
	 *
	 * @param string $start regular expression specifying the start line
	 * @param string $end regular expression specifying the end line
	 * @param string $search regular expression for the search string
	 * @param string $replacement replacement line
	 * @return void
	 * @throws FileNotFoundException Exception (inherited from GetContentsAsArray)
	 */

	function ReplaceLinesBetween($search, $replacement, $start, $end)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$replaced = false;

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		$lines = $this->GetContentsAsArray();

		if (!($fh_t = @fopen($tempfile, "w"))) {
			// TODO: AddValidationError replacement
			$this->AddValidationError(FILE_LANG_ERRMSG_OPEN . $tempfile,__METHOD__,__LINE__);
		} else {

			// Find start tag
			$match = false;
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
					$match = true;

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
				// TODO: AddValidationError replacement
				$this->AddValidationError(LOCALE_LANG_ERRMSG_NO_MATCH,__METHOD__,__LINE__);
				unlink($tempfile);
			} else {
				$replaced = $this->Replace($tempfile);
			}
		}

		return $match;
	}

	/**
	 * Replaces a line (defined by a regular expression) with a replacement.
	 *
	 * @param string $search search string
	 * @param string $replacement replacement line (or lines)
	 * @param integer $maxreplaced maximum number of matches to make
	 * @return integer number of replacements made
	 * @throws FileException, ValidationException
	 */

	function ReplaceLines($search, $replacement, $maxreplaced = -1)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// TODO: add validation

		$replaced = 0;

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		$lines = $this->GetContentsAsArray();

		if (!($fh_t = @fopen($tempfile, "w")))
			throw new FileException(FILE_LANG_ERRMSG_OPEN . " - " . $tempfile, COMMON_NOTICE);

		// Find start tag
		foreach ($lines as $line) {
			if (preg_match($search, $line) && (($replaced < $maxreplaced) || $maxreplaced == -1)) {
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
			$this->Replace($tempfile);
		}

		$this->contents = null;

		return $replaced;
	}

	/**
	 * Replaces a line defined by a regular expression.
	 *
	 * @param string $search search string
	 * @param string $replacement replacement line (or lines)
	 * @return integer number of replacements made
	 * @throws FileNotFoundException, FileException
	 */

	// TODO: deprecate
	function ReplaceOneLine($search, $replacement)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		return $this->ReplaceLines($search, $replacement, 1);
	}

	/**
	 * Replaces a line defined by a regular expression.
	 *
	 * This version differs from ReplaceOneLine
	 * in that it uses preg_replace to do the substitution.  Thus you can
	 * use parts of a pattern match in the replacement (ie: $1, $2, etc).
	 *
	 * @param  string  $search  search expression
	 * @param  string  $replacement  replacement expression
	 * @return  boolean  true if any replacements were made
	 * @throws  FileNotFoundException Exception (inherited from GetContentsAsArray)
	 */

	function ReplaceOneLineByPattern($search, $replacement)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$replaced = false;

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		$lines = $this->GetContentsAsArray();

		if (!($fh_t = @fopen($tempfile, "w"))) {
			// TODO: AddValidationError replacement
			$this->AddValidationError(FILE_LANG_ERRMSG_OPEN . $tempfile,__METHOD__,__LINE__);
		} else {
			$match = false;
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
				// TODO: AddValidationError replacement
				$this->AddValidationError(LOCALE_LANG_ERRMSG_NO_MATCH,__METHOD__,__LINE__);
				unlink($tempfile);
			} else {
				$replaced = $this->Replace($tempfile);
			}
		}

		return $replaced;
	}

	/**
	 * Replaces all matching lines defined by a regular expression.
	 *
	 * @param  string  $search  search expression
	 * @param  string  $replacement  replacement expression
	 * @return  boolean  true if any replacements were made
	 * @throws  FileNotFoundException Exception (inherited from GetContentsAsArray)
	 */

	function ReplaceLinesByPattern($search, $replacement)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$replaced = false;

		$tempfile = tempnam(COMMON_TEMP_DIR, basename("$this->filename"));

		$lines = $this->GetContentsAsArray();

		if (!($fh_t = @fopen($tempfile, "w"))) {
			// TODO: AddValidationError replacement
			$this->AddValidationError(FILE_LANG_ERRMSG_OPEN . $tempfile,__METHOD__,__LINE__);
		} else {
			$match = false;
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
				// TODO: AddValidationError replacement
				$this->AddValidationError(LOCALE_LANG_ERRMSG_NO_MATCH,__METHOD__,__LINE__);
				unlink($tempfile);
			} else {
				$replaced = $this->Replace($tempfile);
			}
		}

		return $replaced;
	}

	/**
	 * @access private
	 */

	function __destruct()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		parent::__destruct();
	}
}

// vim: syntax=php ts=4
?>
