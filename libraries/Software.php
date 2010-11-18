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
 * Software package management tools.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = isset($_ENV['CLEAROS_BOOTSTRAP']) ? $_ENV['CLEAROS_BOOTSTRAP'] : '/usr/clearos/framework/shared';
require_once($bootstrap . '/bootstrap.php');

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

clearos_load_library('base/ShellExec');

///////////////////////////////////////////////////////////////////////////////
// E X C E P T I O N  C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Software not installed exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

class SoftwareNotInstalledException extends EngineException {
	/**
	 * SoftwareNotInstalledException constructor.
	 *
	 * @param string $pkgname software package name
	 * @param int $code error code
	 */

	public function __construct($pkgname, $code)
	{
		parent::__construct(SOFTWARE_LANG_ERRMSG_NOT_INSTALLED . " - $pkgname", $code);
	}
}

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Software package management tools.
 *
 * The software classes contains information about a given RPM package.
 * The software constructor requires the pkgname - release and version are
 * optional.  Why do you need the release and version?  Some packages
 * can have multiple version installed, notably the kernel.
 *
 * If you do not specify the release and version name (which is the typical
 * way to call this constructor), then this class will assume that you mean
 * the most recent version.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2003-2010 ClearFoundation
 */

class Software extends Engine {

	///////////////////////////////////////////////////////////////////////////////
	// F I E L D S
	///////////////////////////////////////////////////////////////////////////////

	protected $pkgname = null;
	protected $copyright = null;
	protected $description = null;
	protected $installsize = null;
	protected $installtime = null;
	protected $packager = null;
	protected $release = null;
	protected $summary = null;
	protected $version = null;

	const COMMAND_RPM = '/bin/rpm';

	///////////////////////////////////////////////////////////////////////////////
	// M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * Software constructor.
	 *
	 * @param string $pkgname software package name
	 * @param string $release release number (optional)
	 * @param string $version version number (optional)
	 */

	public function __construct($pkgname, $version = "", $release = "")
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		parent::__construct();
//		require_once(GlobalGetLanguageTemplate(__FILE__));

		if (($version) && ($release)) {
			$this->pkgname = "$pkgname-$version-$release";
		} else {
			$this->pkgname = $pkgname;
		}
	}

	/**
	 * Returns the copyright of the software - eg GPL.
	 *
	 * @return string copyright
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetCopyright()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (is_null($this->copyright))
			$this->_LoadInfo();

		return $this->copyright;
	}

	/**
	 * Returns a long description in text format.
	 *
	 * Descriptions can be anywhere from one-sentence long to several paragraphs.
	 *
	 * @return string description
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetDescription()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (is_null($this->description))
			$this->_LoadInfo();

		return $this->description;
	}

	/**
	 * Returns the installed size (not the download size).
	 *
	 * @return integer install size in bytes
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetInstallSize()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (is_null($this->installsize))
			$this->_LoadInfo();

		return $this->installsize;
	}

	/**
	 * Returns install time in seconds since Jan 1, 1970.
	 *
	 * @return integer install time
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetInstallTime()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (is_null($this->installtime))
			$this->_LoadInfo();

		return $this->installtime;
	}

	/**
	 * Returns the package name.
	 *
	 * @return string package name
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetPackageName()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		return $this->pkgname;
	}

	/**
	 * Returns the packager.
	 *
	 * @return string packager
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetPackager()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (is_null($this->packager))
			$this->_LoadInfo();

		return $this->packager;
	}

	/**
	 * Returns the release.
	 *
	 * The release is not necessarily numeric!
	 *
	 * @return string release
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetRelease()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (is_null($this->release))
			$this->_LoadInfo();

		return $this->release;
	}

	/**
	 * Returns the version.
	 *
	 * The version is not necessarily numeric!
	 *
	 * @return string version
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetVersion()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (is_null($this->version))
			$this->_LoadInfo();

		return $this->version;
	}

	/**
	 * Returns a one-line description.
	 *
	 * @return string description
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetSummary()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (is_null($this->summary))
			$this->_LoadInfo();

		return $this->summary;
	}

	/**
	 * Generic method to grab information from the RPM database.
	 *
	 * There are dozens of bits of information in an RPM file accessible via the
	 * "rpm -q --queryformat" command.  See list of tags at
	 * http://www.rpm.org/max-rpm-snapshot/ch-queryformat-tags.html
	 *
	 * @param string $tag queryformat tag in RPM
	 * @return string value from queryformat command
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function GetRpmInfo($tag)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (! $this->IsInstalled())
			throw new SoftwareNotInstalledException($this->pkgname, COMMON_NOTICE);

		$rpm = escapeshellarg($this->pkgname);

		// For some reason, the output formatting with "rpm --last" is fubar.
		// We have to implement it here instead.

		try {
			$shell = new ShellExec();
			$exitcode = $shell->Execute(self::COMMAND_RPM, "-q --queryformat \"%{VERSION}\\n\" $rpm", false);
			if ($exitcode != 0)
				throw new EngineException(SOFTWARE_LANG_ERRMSG_LOOKUP_ERROR, COMMON_WARNING);
			$rawoutput = $shell->GetOutput();
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_WARNING);
		}

		// More than 1 version?  Sort and grab the latest.
		if (count($rawoutput) > 1) {
			rsort($rawoutput);
			$version = $rawoutput[0];
			$rpm = escapeshellarg($this->pkgname . "-" . $version);
			unset($rawoutput);

			try {
				$exitcode = $shell->Execute(self::COMMAND_RPM, "-q --queryformat \"%{RELEASE}\\n\" $rpm", false);
				if ($exitcode != 0)
					throw new EngineException(SOFTWARE_LANG_ERRMSG_LOOKUP_ERROR, COMMON_WARNING);
				$rawoutput = $shell->GetOutput();
			} catch (Exception $e) {
				throw new EngineException($e->GetMessage(), COMMON_WARNING);
			}

			// More than 1 release?  Sort and grab the latest.
			if (count($rawoutput) > 1) {
				rsort($rawoutput);
				$release = $rawoutput[0];
				$rpm = escapeshellarg($this->pkgname . "-" . $version . "-" . $release);
			}
		}

		// Add formatting for bare tags (e.g. COPYRIGHT -> %{COPYRIGHT})
		if (!preg_match("/%/", $tag))
			$tag = "%{" . $tag . "}";

		unset($rawoutput);
		try {
			$exitcode = $shell->Execute(self::COMMAND_RPM, "-q --queryformat \"" . $tag . "\" $rpm", false);
			if ($exitcode != 0)
				throw new EngineException(SOFTWARE_LANG_ERRMSG_LOOKUP_ERROR, COMMON_WARNING);
			$rawoutput = $shell->GetOutput();
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_WARNING);
		}

		return implode(" ", $rawoutput);
	}

	/**
	 * Returns true if the package is installed.
	 *
	 * @return boolean true if package is installed
	 * @throws EngineException, SoftwareNotInstalledException
	 */

	public function IsInstalled()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$rpm = escapeshellarg($this->pkgname);
		$exitcode = 1;

		try {
			// KLUDGE: rpm does not seem to have a nice way to get around
			// running multiple rpm commands simultaneously.  You can get a
			// temporary "cannot get shared lock" error in this case.

			$shell = new ShellExec();
			$options['env'] = "LANG=en_US";

			for ($i = 0; $i < 5; $i++) {
				$exitcode = $shell->Execute(self::COMMAND_RPM, "-q $rpm 2>&1", false, $options);
				$lines = implode($shell->GetOutput());

				if (($exitcode === 1) && (preg_match("/shared lock/", $lines)))
					sleep(1);
				else
					break;
			}
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_WARNING);
		}

		if ($exitcode == 0)
			return true;
		else
			return false;
	}

	/**
	 * Loads all the fields in this class.
	 *
	 * @access private
	 */

	protected function _LoadInfo()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$rawoutput = explode("|", $this->GetRpmInfo(
		                         "%{COPYRIGHT}|%{DESCRIPTION}|%{SIZE}|%{INSTALLTIME}|%{PACKAGER}|%{RELEASE}|%{SUMMARY}|%{VERSION}"));

		$this->copyright = $rawoutput[0];
		$this->description = $rawoutput[1];
		$this->installsize = $rawoutput[2];
		$this->installtime = $rawoutput[3];
		$this->packager = $rawoutput[4];
		$this->release = $rawoutput[5];
		$this->summary = $rawoutput[6];
		$this->version = $rawoutput[7];
	}

	/**
	 * @access private
	 */

	public function __destruct()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		parent::__destruct();
	}
}

// vim: syntax=php ts=4
?>
