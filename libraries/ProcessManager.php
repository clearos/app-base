<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2008-2010 ClearFoundation
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
 * System process manager.
 *
 * @package ClearOS
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2008-2010 ClearFoundation
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = isset($_ENV['CLEAROS_BOOTSTRAP']) ? $_ENV['CLEAROS_BOOTSTRAP'] : '/usr/clearos/framework/shared';
require_once($bootstrap . '/bootstrap.php');

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

clearos_load_library('base/Engine');
clearos_load_library('base/ShellExec');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * System process manager.
 *
 * @package ClearOS
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2008-2010 ClearFoundation
 */

class ProcessManager extends Engine
{
	///////////////////////////////////////////////////////////////////////////////
	// C O N S T A N T S
	///////////////////////////////////////////////////////////////////////////////

	const COMMAND_PS = "/bin/ps";
	const COMMAND_KILL = "/bin/kill";

	///////////////////////////////////////////////////////////////////////////////
	// M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * ProccessManager constructor.
	 */

	public function __construct()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);
	}

	/**
	 * Returns raw output from ps command.
	 *
	 * @return string raw output
	 * @throws EngineException
	 */

	public function GetRawData()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		try {
			$shell = new ShellExec();
			$shell->Execute(self::COMMAND_PS, "-eo pid,user,time,%cpu,%mem,sz,tty,ucomm,command");
			$output = $shell->GetOutput();
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), ClearOsError::CODE_ERROR);
		}

		return $output;
	}

	/**
	 * Kills processes in given list.
	 *
	 * @param array $pids list of process IDs
	 * @throws EngineException
	 */

	public function Kill($pids)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		try {
			$shell = new ShellExec();
			foreach ($pids as $pid)
				$shell->Execute(self::COMMAND_KILL, $pid, TRUE);
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), ClearOsError::CODE_ERROR);
		}
	}
}

// vim: syntax=php ts=4
?>
