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
 * Posix user administration.
 *
 * @package ClearOS
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = isset($_ENV['CLEAROS_BOOTSTRAP']) ? $_ENV['CLEAROS_BOOTSTRAP'] : '/usr/clearos/framework/shared';
require_once($bootstrap . '/bootstrap.php');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

clearos_load_library('base/ShellExec');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Posix user administration.
 *
 * @package ClearOS
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class PosixUser extends Engine
{
	///////////////////////////////////////////////////////////////////////////////
	// C O N S T A N T S
	///////////////////////////////////////////////////////////////////////////////

	const COMMAND_CHKPWD = "/usr/sbin/app-passwd";
	const COMMAND_PASSWD = "/usr/bin/passwd";
	const COMMAND_USERDEL = "/usr/sbin/userdel";

	///////////////////////////////////////////////////////////////////////////////
	// V A R I A B L E S
	///////////////////////////////////////////////////////////////////////////////

	protected $username;

	///////////////////////////////////////////////////////////////////////////////
	// M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * PosixUser constructor.
	 */

	public function __construct($username)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$this->username = $username;
	}

	/**
	 * Checks the password for the user.
	 *
	 * @param string password password for the user
	 * @return boolean TRUE if password is correct
	 * @throws EngineException, ValidationException
	 */

	public function CheckPassword($password)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// Validate
		//---------

		$error = $this->ValidatePassword($password);

		if ($error)
			throw new ValidationException($error, ClearOsError::CODE_ERROR);

		$error = $this->ValidateUsername($this->username);

		if ($error)
			throw new ValidationException($error, ClearOsError::CODE_ERROR);

		// Check password
		//---------------
		
		try {
			$options['stdin'] = "$this->username $password";

			$shell = new ShellExec();
			$retval = $shell->Execute(self::COMMAND_CHKPWD, "", TRUE, $options);

		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), ClearOsError::CODE_ERROR);
		}

		if ($retval === 0)
			return TRUE;
		else
			return FALSE;
	}

	/**
	 * Deletes a user from the Posix system.
	 *
	 * @returns void
	 * @throws EngineException, ValidationException 
	 */

	public function Delete()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// Validate
		//---------

		$error = $this->ValidateUsername($this->username);

		if ($error)
			throw new ValidationException($error, ClearOsError::CODE_ERROR);

		// Delete
		//-------

		try {
			$shell = new ShellExec();

			$username = escapeshellarg($this->username);
			$retval = $shell->Execute(self::COMMAND_USERDEL, "$username", TRUE);

			if ($retval != 0)
				throw new EngineException($shell->GetLastOutputLine(), ClearOsError::CODE_ERROR);
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), ClearOsError::CODE_ERROR);
		}
	}

	/**
	 * Sets the user's system password.
	 *
	 * @param string $password password
	 * @returns void
	 * @throws EngineException, ValidationException 
	 */

	public function SetPassword($password)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		// Validate
		//---------

		$error = $this->ValidatePassword($password);

		if ($error)
			throw new ValidationException($error, ClearOsError::CODE_ERROR);
		
		// Update
		//-------

		try {
			$shell = new ShellExec();

			$user = escapeshellarg($this->username);
			$options['stdin'] = $password;

			$retval = $shell->Execute(self::COMMAND_PASSWD, "--stdin $user", TRUE, $options);

			if ($retval != 0)
				throw new EngineException($shell->GetLastOutputLine(), ClearOsError::CODE_ERROR);
		} catch (Exception $e) {
			throw new EngineException($e->GetMessage(), ClearOsError::CODE_ERROR);
		}
	}

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

	/**
	 * Password validation routine.
	 *
	 * @param string $password password
	 * @return boolean TRUE if password is valid
	 */

	public function ValidatePassword($password)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

        if (preg_match("/[\|;\*]/", $password))
			return lang('base_errmsg_password_invalid');
		else
			return '';
	}

	/**
	 * Username validation routine.
	 *
	 * @param string $username username
	 * @return boolean TRUE if username is valid
	 */

	public function ValidateUsername($username)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

        if (preg_match("/^([a-z0-9_\-\.\$]+)$/", $username))
			return '';
		else
			return lang('base_errmsg_username_invalid');
	}
}

// vim: syntax=php ts=4
?>
