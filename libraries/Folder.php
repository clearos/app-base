<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2003-2010 ClearFoundation
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
 * Folder manipulation class.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

require_once('/usr/clearos/framework/config.php');

clearos_load_library('base/Engine');
clearos_load_library('base/ShellExec');

///////////////////////////////////////////////////////////////////////////////
// E X C E P T I O N  C L A S S E S
///////////////////////////////////////////////////////////////////////////////

/**
 * Folder exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

class FolderException extends EngineException
{
	/**
	 * FolderException constructor.
	 *
	 * @param string $errmsg error message
	 * @param int $code error code
	 */

	function __construct($errmsg, $code)
	{
		parent::__construct($errmsg, $code);
	}
}

/**
 * Folder permissions exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

class FolderPermissionsException extends EngineException
{
	/**
	 * FolderPermissionsException constructor.
	 *
	 * @param string $errmsg error message
	 * @param int $code error code
	 */

	function __construct($errmsg, $code)
	{
		parent::__construct($errmsg, $code);
	}
}

/**
 * Folder already exists exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

class FolderAlreadyExistsException extends EngineException
{
	/**
	 * FolderAlreadyExistsException constructor.
	 *
	 * @param string $folder folder name
	 * @param int $code error code
	 */

	function __construct($folder, $code)
	{
		parent::__construct(FOLDER_LANG_ERRMSG_EXISTS . " - " . $folder, $code);
	}
}

/**
 * Folder not found exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

class FolderNotFoundException extends EngineException
{
	/**
	 * FolderNotFoundException constructor.
	 *
	 * @param string $folder folder name
	 */

	function __construct($folder)
	{
		parent::__construct(FOLDER_LANG_ERRMSG_NOTEXIST . " - " . $folder, COMMON_INFO);
	}
}

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Folder manipulation class.
 *
 * The Folder class can be used for creating, reading and manipulating
 * folders (directories) on the filesystem.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

class Folder extends Engine
{
	///////////////////////////////////////////////////////////////////////////////
	// C O N S T A N T S
	///////////////////////////////////////////////////////////////////////////////

	const CMD_LS = '/bin/ls';
	const CMD_DU = '/usr/bin/du';
	const CMD_MKDIR = '/bin/mkdir';
	const CMD_CHOWN = '/bin/chown';
	const CMD_CHMOD = '/bin/chmod';
	const CMD_FILE = '/usr/bin/file';
	const CMD_RMDIR = '/bin/rmdir';
	const CMD_RM = '/bin/rm';
	const CMD_FIND = '/usr/bin/find';
	const CMD_REALPATH = '/usr/sbin/app-realpath';

	///////////////////////////////////////////////////////////////////////////////
	// F I E L D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * @var string folder
	 */

	protected $folder = null;

	/**
	 * @var boolean superuser
	 */

	protected $superuser = false;

	///////////////////////////////////////////////////////////////////////////////
	// M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * Folder constructor.
	 *
	 * @param boolean $superuser superuser access required to read the file
	 * @param string folder target folder
	 */

	function __construct($folder, $superuser = false)
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		$this->folder = $folder;
		$this->superuser = $superuser;

		parent::__construct();

//		require_once(GlobalGetLanguageTemplate(__FILE__));
	}

	/**
	 * Changes the folder mode. 
	 *
	 * Use the standard command-line chmod values.
	 *
	 * @param  string  $mode  the mode of the folder
	 * @return  void
	 */

	function Chmod($mode)
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		if (! $this->Exists())
			throw new FolderNotFoundException($this->folder);

		// Let the chmod command do the validation

		try {
			$shell = new ShellExec();
			if ($shell->Execute(self::CMD_CHMOD, $mode . ' ' . $this->folder, true) != 0)
				throw new FolderPermissionsException($shell->GetFirstOutputLine(), COMMON_ERROR);
		} catch (Exception $e) {
			throw new FolderException($e->GetMessage(), COMMON_ERROR);
		}
	}


	/**
	 * Changes the owner and/or group.
	 *
	 * Leave the owner or group blank if you do not want change one or the other.
	 *
	 * @param  string  $owner  folder owner
	 * @param  string  $group  folder group
	 * @param  string  $recursive  do chown recursively
	 * @return  void
	 */

	function Chown($owner, $group, $recursive = false)
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		if (! $this->Exists())
			throw new FolderNotFoundException($this->folder);

		// Let the chown command do the validation

		if ($owner) {
			try {
				if ($recursive)
					$flags = '-R';
				else
					$flags = '';

				$shell = new ShellExec();
				if ($shell->Execute(self::CMD_CHOWN, $owner . " $flags " . $this->folder, true) != 0)
					throw new FolderPermissionsException($shell->GetFirstOutputLine(), COMMON_ERROR);
			} catch (Exception $e) {
				throw new FolderException($e->GetMessage(), COMMON_ERROR);
			}
		}

		if ($group) {
			try {
				if ($recursive)
					$flags = '-R';
				else
					$flags = '';

				$shell = new ShellExec();
				if ($shell->Execute(self::CMD_CHOWN, ':' . $group . " $flags " . $this->folder, true) != 0)
					throw new FolderPermissionsException($shell->GetFirstOutputLine(), COMMON_ERROR);
			} catch (Exception $e) {
				throw new FolderException($e->GetMessage(), COMMON_ERROR);
			}
		}
	}


	/**
	 * Creates a folder on the system.
	 *
	 * The method will return an error if the file already exists.
	 * 
	 * @param  string  $owner  folder owner
	 * @param  string  $group  folder group
	 * @param  string  $mode  the mode of the folder
	 * @return  void
	 */

	function Create($owner, $group, $mode)
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		clearstatcache(); // PHP caches file stat information... don't let it

		if ($this->Exists())
			throw new FolderAlreadyExistsException($this->folder, COMMON_ERROR);

		try {
			$shell = new ShellExec();
			if ($shell->Execute(self::CMD_MKDIR, "-p $this->folder", true) != 0)
				throw new FolderException($shell->GetFirstOutputLine());
		} catch (Exception $e) {
			throw new FolderException($e->GetMessage(), COMMON_ERROR);
		}

		if ($owner || $group) {
			try {
				$this->Chown($owner, $group);
			} catch (Exception $e) {
				throw new FolderException($e->GetMessage(), COMMON_ERROR);
			}
		}

		if ($mode) {
			try {
				$this->Chmod($mode);
			} catch (Exception $e) {
				throw new FolderException($e->GetMessage(), COMMON_ERROR);
			}
		}
	}


	/**
	 * Deletes the folder.
	 *
	 * @param  bool  $ignore_nonempty  flag to ignore (use rm -rf) if files are contained within folder
	 * @return  void
	 */

	function Delete($ignore_nonempty = false)
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		if (! $this->Exists())
			throw new FolderNotFoundException($this->folder);

		$shell = new ShellExec();
		if ($ignore_nonempty) {
			try {
				//TODO TODO TODO - validate the hell out of an "rm -rf"
				if ($shell->Execute(self::CMD_RM, "-rf $this->folder", true) != 0)
					throw new FolderException($shell->GetFirstOutputLine());
			} catch (Exception $e) {
				throw new FolderException($e->GetMessage(), COMMON_ERROR);
			}
		} else {
			try {
				if ($shell->Execute(self::CMD_RMDIR, $this->folder, true) != 0)
					throw new FolderPermissionsException($shell->GetFirstOutputLine(), COMMON_ERROR);
			} catch (Exception $e) {
				throw new FolderException($e->GetMessage(), COMMON_ERROR);
			}
		}
	}


	/**
	 * Checks to see if given folder is really a folder.
	 * 
	 * @return  boolean  true if directory
	 */

	function IsDirectory()
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		try {
			$shell = new ShellExec();
			if ($shell->Execute(self::CMD_FILE, $this->folder, true) != 0)
				throw new FolderException($shell->GetFirstOutputLine());
		} catch (Exception $e) {
			throw new FolderException($e->GetMessage(), COMMON_ERROR);
		}

		if (preg_match("/directory/", $shell->GetFirstOutputLine()))
			return true;
		else
			return false;
	}


	/**
	 * Checks the existence of the folder.
	 *
	 * @return  boolean  true if folder exists
	 */

	function Exists()
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		if ($this->superuser) {
			try {
				$shell = new ShellExec();
				if ($shell->Execute(self::CMD_LS, escapeshellarg($this->folder), true) != 0)
					return false;
				else
					return true;
			} catch (Exception $e) {
				throw new FolderException($e->GetMessage(), COMMON_ERROR);
			}
		} else {
			if (is_dir($this->folder))
				return true;
			else
				return false;
		}
	}

	/**
	 * Returns the listing of files in the directory.
	 *
	 * The current (.) and and parent (..) entries are not included.
	 * @param boolean $detailed if true, array contains detailed informatio about diredtory
	 *
	 * @return  array  file listing
	 */

	function GetListing($detailed = false)
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		$listing = array();

		if (! $this->Exists())
			throw new FolderNotFoundException($this->folder);

		if ($detailed) {
			try {
				$shell = new ShellExec();
				$options = ' -lA --time-style=full-iso ' . escapeshellarg($this->folder);
				if ($shell->Execute(self::CMD_LS, $options, true) != 0)
					throw new FolderException($shell->GetFirstOutputLine());
			} catch (Exception $e) {
				throw new FolderException($e->GetMessage(), COMMON_ERROR);
			}

			$lines = $shell->GetOutput();
			// Remove ls summary
			array_shift($lines);
			$directories = Array();
			$files = Array();
			foreach ($lines as $line) {
				$parts = preg_split("/\s+/", $line, 9);
				// We want to list all directories first, the files
				if (substr($parts[0], 0, 1) == 'd') {
					$directories[] = Array(
						'name' => $parts[8],
						'properties' => $parts[0],
						'size' => $parts[4],
						'modified' => strtotime($parts[5] . ' ' . substr($parts[6], 0, 8) . ' ' . $parts[7])
					); 
				} else {
					$files[] = Array(
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
					$shell = new ShellExec();
					if ($shell->Execute(self::CMD_LS, $this->folder, true) != 0)
						throw new FolderException($shell->GetFirstOutputLine());
				} catch (Exception $e) {
					throw new FolderException($e->GetMessage(), COMMON_ERROR);
				}

				$fulllist = $shell->GetOutput();
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
	 * @throws FolderNotFoundException, EngineException
	 */

	function GetPermissions()
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		clearstatcache();

		if (! $this->Exists())
			throw new FileNotFoundException($this->filename, COMMON_INFO);

		// TODO: this will fail on folders that user webconfig cannot read (protected directories).
		// Added EngineException to docs to futureproof API.

		return substr(sprintf('%o', fileperms($this->folder)), -4);
	}

	/**
	 * Returns the listing of files in the directory.
	 *
	 * The current (.) and and parent (..) entries are not included.
	 *
	 * @return  array  file listing
	 */

	function GetRecursiveListing()
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		if (! $this->Exists())
			throw new FolderNotFoundException($this->folder);

		$listing = array();	
		$fulllist = array();

		try {
			$shell = new ShellExec();
			if ($shell->Execute(self::CMD_FIND, "$this->folder -type f", true) != 0)
				throw new FolderException($shell->GetFirstOutputLine());
			$fulllist = $shell->GetOutput();
		} catch (Exception $e) {
			throw new FolderException($e->GetMessage(), COMMON_ERROR);
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
	 * @throws FileException
	 */

	function GetSize()
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		if (! $this->Exists())
			throw new FolderNotFoundException($this->filename);

		try {
			$shell = new ShellExec();
			$options = "-bc $this->folder"; 
			$exitcode = $shell->Execute(self::CMD_DU, $options, true);
		} catch (Exception $e) {
			throw new FileException($e->GetMessage(), COMMON_WARNING);
		}

		if ($exitcode == 0) {
			$parts = explode(" ", $shell->GetLastOutputLine());
			$size = (int)$parts[0];
			# Account for directory iteself
			if ($size <= 4096)
				return 0;
			else
				return (int)$parts[0];
		} else {
			throw new EngineException(LOCALE_LANG_ERRMSG_WEIRD, COMMON_WARNING);
		}
	}

	/**
	 * Returns the foldername (using PHP 'realpath') resolving references like "../../".
	 *
	 * @return  string  name of present working directory
	 */
	function GetFoldername()
	{
		if ($this->superuser) {
			try {
				$shell = new ShellExec();
				$exitcode = $shell->Execute(self::CMD_REALPATH, escapeshellarg($this->folder), true);
			} catch (Exception $e) {
				throw new FileException($e->GetMessage(), COMMON_WARNING);
			}
			if ($exitcode == 0)
				return $shell->GetLastOutputLine();
			else throw new EngineException(LOCALE_LANG_ERRMSG_WEIRD, COMMON_WARNING);
		}
		return realpath($this->folder);
	}

	///////////////////////////////////////////////////////////////////////////////
	// P R I V A T E   M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * @access private
	 */

	function __destruct()
	{
		if (COMMON_DEBUG_MODE)
			$this->Log(COMMON_DEBUG, "called", __METHOD__, __LINE__);

		parent::__destruct();
	}
}

// vim: syntax=php ts=4
?>
