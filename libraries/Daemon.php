<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2002-2010 ClearFoundation
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
 * Daemon class.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2002-2010 ClearFoundation
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = isset($_ENV['CLEAROS_BOOTSTRAP']) ? $_ENV['CLEAROS_BOOTSTRAP'] : '/usr/clearos/framework/shared';
require_once($bootstrap . '/bootstrap.php');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Software');
clearos_load_library('base/ShellExec');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Daemon manager class.
 *
 * A meta file is used to organize and manage the daemons on the system.
 * In an ideal world, we would be able to scan the list of init scripts in
 * /etc/rc.d and generate the service list on the fly.  Unfortunately
 * there are some inconsistencies that make this impossible.  The meta file
 * holds the following information:
 *
 *  - the RPM where the daemon lives
 *  - the daemon/process name (what you see with ps)
 *  - whether or not the daemon supports a "/etc/rc.d/init.d/<xyz> reload"
 *  - a short title (eg Apache Web Server)
 *
 * Note: a few daemons are not really "running" per se, but are part of
 * the kernel e.g. the firewall and bandwidth limiter.
 *
 * Ideally, the constructor would require the same parameter as the
 * Software class -- the name of the package.  However, some packages
 * can have more than one daemon.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2002-2010 ClearFoundation
 */

class Daemon extends Software
{
	///////////////////////////////////////////////////////////////////////////////
	// V A R I A B L E S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * @var string init script filename
	 */

	protected $initscript;

	/**
	 * @var string the process name
	 */

	protected $processname;

	/**
	 * @var string short title
	 */

	protected $title;

	/**
	 * @var string software package name
	 */

	protected $package;

	/**
	/**
	 * @var boolean true if daemon supports configuration reload
	 */

	protected $reloadable;

	const CMD_LS = '/bin/ls';
	const CMD_CHKCONFIG = '/sbin/chkconfig';
	const CMD_SERVICE = '/sbin/service';
	const CMD_PIDOF = '/sbin/pidof';
	const PATH_INITD = '/etc/rc.d/rc3.d';

	///////////////////////////////////////////////////////////////////////////////
	// M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * Daemon constructor.
	 *
	 * @param string $initscript filename of init script in /etc/rc.d.
	 */

	public function __construct($initscript)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// require_once(GlobalGetLanguageTemplate(__FILE__));

		global $DAEMONS;
		require_once("Daemon.inc.php");

		if (file_exists("Daemon.custom.php"))
			require_once("Daemon.custom.php");

		if (isset($DAEMONS[$initscript][0])) {
			$this->initscript = $initscript;
			$this->package = $DAEMONS[$initscript][0];
			$this->processname = $DAEMONS[$initscript][1];
			$this->title = $DAEMONS[$initscript][3];

			if ($DAEMONS[$initscript][2] == "yes")
				$this->reloadable = true;
			else
				$this->reloadable = false;
		} else {
			$this->initscript = $initscript;
			$this->package = $initscript;
			$this->processname = $initscript;
			$this->title = $initscript;
			$this->reloadable = false;
		}

		parent::__construct($this->package);
	}


	/**
	 * Returns the boot state of the daemon.
	 *
	 * @return boolean true if daemon is set to run at boot
	 * @throws EngineException
	 */

	public function GetBootState()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (! $this->IsInstalled())
			throw new EngineException(SOFTWARE_LANG_ERRMSG_NOT_INSTALLED . " - $this->package", COMMON_WARNING);

		try {
			$folder = new Folder(self::PATH_INITD);
			$listing = $folder->GetListing();
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_ERROR);
		}

		foreach ($listing as $file) {
			if (preg_match("/^S\d+$this->initscript$/", $file))
				return true;
		}

		return false;
	}

	/**
	 * Returns the running state of the daemon.
	 *
	 * @return boolean true if the daemon is running
	 * @throws EngineException
	 */

	public function GetRunningState()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$file = new File("/var/run/" . $this->processname . ".pid");

		if ($file->Exists())
			return true;

		try {
			$shell = new ShellExec();
			$exitcode = $shell->Execute(self::CMD_PIDOF, "-x -s $this->processname");
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_ERROR);
		}

		if ($exitcode == 0)
			return true;
		else
			return false;
	}

	/**
	 * Returns the process name of the daemon.
	 *
	 * @return string process name
	 * @throws EngineException
	 */

	public function GetProcessName()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		return $this->processname;
	}

	/**
	 * Returns a short title for the daemon (eg Apache Web Server).
	 *
	 * @return string short tile for daemon
	 * @throws EngineException
	 */

	public function GetTitle()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		return $this->title;
	}

	/**
	 * Restarts the daemon if (and only if) it is already running.
	 *
	 * @return void
	 * @throws EngineException
	 */

	public function Reset()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		try {
			$isrunning = $this->GetRunningState();
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_WARNING);
		}

		if (! $isrunning)
			return;

		if ($this->reloadable)
			$args = "reload";
		else
			$args = "restart";

		try {
			$options['stdin'] = "use_popen";

			$shell = new ShellExec();
			$shell->Execute(self::CMD_SERVICE, "$this->initscript $args", true, $options);
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_WARNING);
		}
	}

	/**
	 * Restarts the daemon.
	 *
	 * @see Daemon::Reset()
	 * @return void
	 * @throws EngineException
	 */

	public function Restart()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		try {
			$options['stdin'] = "use_popen";

			$shell = new ShellExec();
			$shell->Execute(self::CMD_SERVICE, "$this->initscript restart", true, $options);
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_WARNING);
		}
	}

	/**
	 * Sets the boot state of the daemon.
	 *
	 * @param boolean $state desired boot state
	 * @return void
	 * @throws EngineException, ValidationException
	 */

	public function SetBootState($state)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (! is_bool($state))
			throw new ValidationException(LOCALE_LANG_ERRMSG_INVALID_TYPE . " (state)");

		if (! $this->IsInstalled())
			throw new EngineException(SOFTWARE_LANG_ERRMSG_NOT_INSTALLED . " - $this->package", COMMON_WARNING);

		if ($state)
			$args = "on";
		else
			$args = "off";

		try {
			$shell = new ShellExec();
			$shell->Execute(self::CMD_CHKCONFIG, "--level 345 $this->initscript $args", true);
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_FATAL);
		}
	}

	/**
	 * Sets running state of the daemon.
	 *
	 * @param boolean $state desired running state
	 * @return void
	 * @throws EngineException, ValidationException
	 */

	public function SetRunningState($state)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (! is_bool($state))
			throw new ValidationException(LOCALE_LANG_ERRMSG_INVALID_TYPE . " (state)");

		if (! $this->IsInstalled())
			throw new EngineException(SOFTWARE_LANG_ERRMSG_NOT_INSTALLED . " - $this->package", COMMON_WARNING);

		$isrunning = $this->GetRunningState();

		if ($isrunning && $state) {
			// issued start on already running daemon
			return;
		} else if (!$isrunning && !$state) {
			// issued stop on already stopped daemon
			return;
		}

		if ($state)
			$args = "start";
		else
			$args = "stop";

		try {
			$options['stdin'] = "use_popen";

			$shell = new ShellExec();

			// TODO: there is some strange behavior with the Cups daemon that causes
			// PHP to hang.  A temporary workaround
			if (($this->package == "cups") && $state) {
				$file = new File("/etc/system/initialized/cups");
				if (! $file->Exists()) {
					require_once("Syswatch.php");
					$syswatch = new Syswatch();
					$syswatch->SendSignal("61");

					$file->Create("root", "root", "0644");
					sleep(10);
				} else {
					$exitcode = $shell->Execute(self::CMD_SERVICE, "$this->initscript $args", true, $options);
				}
			} else {
				$exitcode = $shell->Execute(self::CMD_SERVICE, "$this->initscript $args", true, $options);
			}
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), COMMON_WARNING);
		}
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
