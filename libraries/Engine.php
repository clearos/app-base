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
 * Core engine class for the API.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2002-2010 ClearFoundation
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = isset($_ENV['CLEAROS_BOOTSTRAP']) ? $_ENV['CLEAROS_BOOTSTRAP'] : '/usr/clearos/framework/shared';
require_once($bootstrap . '/bootstrap.php');

///////////////////////////////////////////////////////////////////////////////
// E X C E P T I O N  C L A S S E S
///////////////////////////////////////////////////////////////////////////////

/**
 * Base exception for all exceptions in the API.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2002-2010 ClearFoundation
 */

class EngineException extends Exception {

	/**
	 * EngineException constructor.
	 *
	 * Unlike a core PHP exception, the message and code parameters are required.
	 * See ClearOsError class for details on the error codes.
	 *
	 * @param string $message error message
	 * @param integer $code error code
	 * @return void
	 */

	public function __construct($message, $code)
	{
		parent::__construct((string)$message, (int)$code);

		if ($code >= ClearOsError::CODE_WARNING)
			ClearOsLogger::LogException($this, true);
	}
}

/**
 * Validation exception for API.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2002-2010 ClearFoundation
 */

class ValidationException extends EngineException {

	/**
	 * ValidationException constructor.
	 *
	 * @param string $message error message
	 * @return void
	 */

	public function __construct($message)
	{
		parent::__construct($message, ClearOsError::CODE_INFO);
	}
}

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Core engine class for the API.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.clearfoundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2002-2010 ClearFoundation
 */

class Engine {

	///////////////////////////////////////////////////////////////////////////////
	// F I E L D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * @var array validation error queue
	 */

	protected $errors = array();

	const COMMAND_API = "/usr/bin/api";

	///////////////////////////////////////////////////////////////////////////////
	// M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * Engine constructor.
	 *
	 * @return void
	 */

	public function __construct()
	{}

	///////////////////////////////////////////////////////////////////////////////
	// E R R O R  H A N D L I N G
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * Add a validation error to the queue.
	 *
	 * @param string $message error message
	 * @param string $tag tag (usually the method call)
	 * @param integer $line line number
	 * @return void
	 */

	protected function AddValidationError($message, $tag, $line)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$error = new ClearOsError(ClearOsError::CODE_INFO, $message, $tag, $line, null, ClearOsError::TYPE_ERROR, true);
		$this->errors[] = $error;

		ClearOsLogger::Log($error);
	}

	/**
	 * Returns an array of validation error messages.
	 *
	 * @param boolean $purge  (optional) if true, the error queue will be purged.
	 * @return array validation errors
	 */

	public function GetValidationErrors($purge = false)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$error_messages = array();

		foreach ($this->errors as $error)
			$error_messages[] = $error->GetMessage();

		if ($purge)
			$this->errors = array();

		return $error_messages;
	}

	/**
	 * Returns an array of validation error objects.
	 *
	 * @param boolean $purge  (optional) if true, the error queue will be purged.
	 * @return array validation errors
	 */

	public function CopyValidationErrors($purge = false)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		$errors_copy = $this->errors;

		if ($purge)
			$this->errors = array();

		return $errors_copy;
	}

	/**
	 * Returns true if validation errors exist.
	 *
	 * @return boolean true if validation errors exist
	 */

	public function CheckValidationErrors()
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if (empty($this->errors))
			return false;
		else
			return true;
	}

	/**
	 * @access private
	 */

	public function __destruct()
	{
		// A bit noisy
		// ClearOsLogger::Profile(__METHOD__, __LINE__);
	}
}

// vim: syntax=php ts=4
?>
