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
 * Configuration file handling class.
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

clearos_load_library('base/File');

///////////////////////////////////////////////////////////////////////////////
// E X C E P T I O N  C L A S S E S
///////////////////////////////////////////////////////////////////////////////

/**
 * Configuration file exception.
 *
 * @package ClearOS
 * @subpackage Exception
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class ConfigurationFileException extends EngineException
{
	/**
	 * ConfigurationFileException constructor.
	 *
	 * @param string $filename name of the file
	 * @param int $linenumber linenumber where the error occurred
	 * @param int $code error code
	 */

	public function __construct($filename, $linenumber, $code)
	{
		parent::__construct(LOCALE_LANG_ERRMSG_PARSE_ERROR.": ".basename($filename)."($linenumber)", $code);
	}
}

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Configuration file handling class.
 *
 * @package ClearOS
 * @subpackage API
 * @author {@link http://www.foundation.com/ ClearFoundation}
 * @license http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @copyright Copyright 2006-2010 ClearFoundation
 */

class ConfigurationFile extends File
{
	///////////////////////////////////////////////////////////////////////////////
	// F I E L D S
	///////////////////////////////////////////////////////////////////////////////

	protected $method = 'explode';
	protected $token = '=';
	protected $limit = 2;
	protected $flags = null;
	protected $loaded = false;
	protected $cfg = array();

	///////////////////////////////////////////////////////////////////////////////
	// M E T H O D S
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * Configuration file constructor.
	 *
	 * Valid method enumerators are:
	 * - ini: parses a samba style ini file
	 * - match:  use preg_match, requires a valid regex expression as the "token" parameter
	 * - split:  use preg_split, requires a valid regex expression as the "token" parameter
	 * - explode: (default) requires a delimiter as the "token" parameter
	 *
	 * @param string $filename target file
	 * @param string $method configuration file type (default = explode)
	 * @param string $token a valid regex or delimiter
	 * @param int $limit (optional) max number of parts, the first part is always used as the "key"
	 * @param int $flags (optional) preg_match/preg_split accept various flags (cf. @link http://www.php.net )
	 */

	public function __construct($filename, $method = 'explode', $token = '=', $limit = 2, $flags = null)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		parent::__construct($filename, true);

		switch ($method) {

		case 'ini':
			$token = array('/^\s*\[(.*?)\]\s*$/', '=');
			break;

		case 'split':

			if (strlen($token) == 1) {
				$method = 'explode';
				break;
			}

		case 'match':

			if (substr($token,0,1) != '/') {
				$token = "/".$token;
			}

			if (substr($token,-1,1) != '/') {
				$token .= "/";
			}
		}

		$this->method = $method;
		$this->token = $token;
		$this->limit = $limit;
		$this->flags = $flags;
		$this->loaded = false;
	}

	/**
	 * Loads a configuration file and returns its values as an array
	 *
	 * @param boolean $reload (optional) if true, a "fresh" copy will be retrieved rather than a cached version
	 * @return array parsed array of elements from configuration file
	 * @throws FileNotFoundException Exception (inherited from GetContentsAsArray)
	 * @throws FileIoException
	 */

	public function Load($reload=false)
	{
		ClearOsLogger::Profile(__METHOD__, __LINE__);

		if ($reload)
			$this->loaded = false;

		if (! $this->loaded) {
			try {
				$lines = $this->GetContentsAsArray();
			} catch (Exception $e) {
				throw $e;
			}

			$configfile = array();
			$n = 0;
			$match = "";

			switch ($this->method) {

			case 'ini':
				$key = null;
				foreach($lines as $line) {
					$n++;

					if (preg_match('/^\/\//', $line))
						continue;

					if (preg_match('/^#.*$/', $line)) {
						continue;
					} elseif (preg_match('/^\s*$/', $line)) {
						// a blank line
						continue;
					} elseif (preg_match($this->token[0], $line, $match)) {
						$key = $match[1];
					} elseif ((strpos($line,$this->token[1]) !== false) && (!(is_null($key)))) {
						$match = array_map('trim',explode($this->token[1],$line));
						$configfile[$key][$match[0]] = $match[1];
					} else {
						throw new ConfigurationFileException($this->filename, $n, COMMON_NOTICE);
					}
				}

				break;

			case 'match':
				foreach ($lines as $line) {
					$n++;

					if (preg_match('/^\/\/.*$/', $line))
						continue;

					if (preg_match('/^\#.*$/', $line)) {
						continue;
					} elseif (preg_match('/^\s*$/', $line)) {
						// a blank line
						continue;
					} elseif (preg_match($this->token, $line, $match, $this->flags)) {
						$configfile[$match[1]] = $match[2];
					} else {
						throw new ConfigurationFileException($this->filename, $n, COMMON_ERROR);
					}
				}

				break;

			case 'split':
				foreach ($lines as $line) {
					$n++;

					if (preg_match('/^\/\/.*$/', $line))
						continue;

					if (preg_match('/^\#.*$/', $line)) {
						continue;
					} elseif (preg_match('/^\s*$/', $line)) {
						// a blank line
						continue;
					} else {
						$match = array_map('trim',preg_split($this->token,$line,$this->limit,$this->flags));

						if (($match[0] == $line)||(empty($match[0]))) {
							throw new ConfigurationFileException($this->filename, $n, COMMON_ERROR);
						} else {
							if ($this->limit == 2) {
								$configfile[$match[0]] = $match[1];
							} else {
								$configfile[$match[0]] = array_slice($match,1);
							}
						}
					}
				}

				break;

			default:
				foreach ($lines as $line) {
					$n++;

					if (preg_match('/^\/\/.*$/', $line))
						continue;

					if (preg_match('/^\#.*$/', $line)) {
						continue;
					} elseif (preg_match('/^\s*$/', $line)) {
						// a blank line
						continue;
					} else {
						$match = array_map('trim',explode($this->token,$line,$this->limit));

						if ($match[0] == $line) {
							// FIXME -- Ignore unparsable?
							// throw new ConfigurationFileException($this->filename, $n, COMMON_ERROR);
						} else {
							if ($this->limit == 2) {
								$configfile[$match[0]] = $match[1];
							} else {
								$configfile[$match[0]] = array_slice($match,1);
							}
						}
					}
				}
			}

			$this->cfg = $configfile;
			$this->loaded = true;
		}

		return $this->cfg;
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
