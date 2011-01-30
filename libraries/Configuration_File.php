<?php

/**
 * Configuration file handling class.
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
use \clearos\apps\base\File as File;

clearos_load_library('base/Engine');
clearos_load_library('base/File');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/Engine_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Configuration file handling class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Configuration_File extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $method = 'explode';
    protected $token = '=';
    protected $limit = 2;
    protected $loaded = FALSE;
    protected $filename = NULL;
    protected $config = array();

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
     * @param string  $filename target file
     * @param string  $method   configuration file type (default = explode)
     * @param string  $token    a valid regex or delimiter
     * @param integer $limit    max number of parts, the first part is always used as the "key"
     */

    public function __construct($filename, $method = 'explode', $token = '=', $limit = 2)
    {
        clearos_profile(__METHOD__, __LINE__);

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
                if (substr($token, 0, 1) != '/')
                    $token = "/".$token;

                if (substr($token, -1, 1) != '/')
                    $token .= "/";
        }

        $this->filename = $filename;
        $this->method = $method;
        $this->token = $token;
        $this->limit = $limit;
        $this->loaded = FALSE;
    }

    /**
     * Loads a configuration file and returns its values as an array.
     *
     * @param boolean $reload loads a fresh copy instead of a potentially cached copy
     *
     * @return array parsed array of elements from configuration file
     * @throws Engine_Exception
     */

    public function load($reload = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($reload)
            $this->loaded = FALSE;

        if (! $this->loaded) {
            $file = new File($this->filename);
            $lines = $file->get_contents_as_array();

            $configfile = array();
            $n = 0;
            $match = "";

            switch ($this->method) {

                case 'ini':
                    $key = NULL;
                    foreach ($lines as $line) {
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
                        } elseif ((strpos($line, $this->token[1]) !== FALSE) && (!(is_null($key)))) {
                            $match = array_map('trim', explode($this->token[1], $line));
                            $configfile[$key][$match[0]] = $match[1];
                        } else {
                            throw new Engine_Exception(lang('base_exception_configuration_file_parse_error'), CLEAROS_ERROR);
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
                        } elseif (preg_match($this->token, $line, $match)) {
                            $configfile[$match[1]] = $match[2];
                        } else {
                            throw new Engine_Exception(lang('base_exception_configuration_file_parse_error'), CLEAROS_ERROR);
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
                            $match = array_map('trim', preg_split($this->token, $line, $this->limit));
    
                            if (($match[0] == $line)||(empty($match[0]))) {
                                throw new Engine_Exception(lang('base_exception_configuration_file_parse_error'), CLEAROS_ERROR);
                            } else {
                                if ($this->limit == 2) {
                                    $configfile[$match[0]] = $match[1];
                                } else {
                                    $configfile[$match[0]] = array_slice($match, 1);
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
                            $match = array_map('trim', explode($this->token, $line, $this->limit));
    
                            if ($match[0] == $line) {
                                throw new Engine_Exception(lang('base_exception_configuration_file_parse_error'), CLEAROS_ERROR);
                            } else {
                                if ($this->limit == 2) {
                                    $configfile[$match[0]] = $match[1];
                                } else {
                                    $configfile[$match[0]] = array_slice($match, 1);
                                }
                            }
                        }
                    }
            }

            $this->config = $configfile;
            $this->loaded = TRUE;
        }

        return $this->config;
    }
}
