<?php

/**
 * Shell execution class.
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

clearos_load_library('base/Engine');

// Exceptions
//-----------

use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Shell execution class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Shell extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $output = array();

    const COMMAND_SUDO = "/usr/bin/sudo";

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Shell constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Executes the command.
     *
     * Excecute options are:
     * - escape: scrub command line arguments for naught characters (default TRUE)
     * - log: specify a log file (default /dev/null)
     * - env: environment variables (default NULL)
     * - background: run command in background (default FALSE)
     * - stdin: write arguments to stdin (default FALSE)
     * - validate_output: throw exception on empty output (default FALSE)
     * - validate_exit_code: check exit code, throw exception if not 0 (default TRUE)
     * 
     * @param string  $command   command to excecute
     * @param string  $arguments command arguments
     * @param boolean $superuser super user flag
     * @param array   $options   extra execute options specified above
     *
     * @return int $retval command return code
     * @throws Validation_Exception
     */

    public function execute($command, $arguments, $superuser = FALSE, $options = NULL)
    {
        clearos_profile(__METHOD__, __LINE__, "$command $arguments");

        $this->output = array();

        if (! is_bool($superuser))
            throw new Validation_Exception(sprintf(lang('base_errmsg_invalid_parameter'), 'superuser'));

        if (isset($options['escape']) && (!is_bool($options['escape'])))
            throw new Validation_Exception(sprintf(lang('base_errmsg_invalid_parameter'), 'options[escape]'));

        if (isset($options['log']) && (preg_match('/\//', $options['log']) || preg_match('/\.\./', $options['log'])))
            throw new Validation_Exception(sprintf(lang('base_errmsg_invalid_parameter'), 'options[log]'));

        if (isset($options['validate_output']) && (!is_bool($options['validate_output'])))
            throw new Validation_Exception(sprintf(lang('base_errmsg_invalid_parameter'), 'options[validate_output]'));
        else if (!isset($options['validate_output']))
            $options['validate_output'] = FALSE;

        if (isset($options['validate_exit_code']) && (!is_bool($options['validate_exit_code'])))
            throw new Validation_Exception(sprintf(lang('base_errmsg_invalid_parameter'), 'options[validate_exit_code]'));
        else if (!isset($options['validate_exit_code']))
            $options['validate_exit_code'] = TRUE;

        // Validate executable for non-superuser access.
        // If the file does not exist in superuser mode, it will get caught below
        // but with a less "pretty" error message.

        if (!$superuser && (!file_exists($command)))
            throw new Validation_Exception(sprintf(lang('base_errmsg_command_execution_failed'), $command));

        if (isset($options['escape']) && $options['escape']) {
            $command = escapeshellcmd($command);
            $arguments = escapeshellcmd($arguments);
        }

        if (strlen($arguments))
            $exe = "$command $arguments";
        else
            $exe = $command;

        if ($superuser)
            $exe = self::COMMAND_SUDO . ' ' . $exe;

        if (isset($options['env']))
            $exe = $options['env'] . " $exe";

        // If set to background, output *must* be redirected to 
        // either a log or /dev/null

        // FIXME: COMMON_TEMP_DIR is no longer defined

        if (isset($options['log']))
            $exe .= ' >>' . COMMON_TEMP_DIR . '/' . $options['log'];
        else if (isset($options['background']) && $options['background'])
            $exe .= ' >/dev/null';

        $exe .= ' 2>&1';

        if (isset($options['background']) && $options['background'])
            $exe .= ' &';

        $retval = NULL;

        if (isset($options['stdin'])) {
            $ph = popen($exe, 'w');

            if (strlen($options['stdin']))
                fwrite($ph, $options['stdin']);

            $retval = pclose($ph);
        } else {
            exec($exe, $this->output, $retval);
        }

        if (isset($options['validate_exit_code']) && $options['validate_exit_code']
            && $retval != 0) {
            $message = sprintf(lang('base_errmsg_command_execution_failed'), $command);
            if (isset($this->output[0]))
                $message = $this->output[0];
            throw new Validation_Exception($message);
        }

        if (isset($options['validate_output']) && $options['validate_output']
            && !isset($this->output[0])) {
            throw new Validation_Exception(sprintf(lang('base_errmsg_command_null_output'), $command));
        }

        return $retval;
    }

    /**
     * Returns output from executed command.
     *
     * @return array command output as an array of strings
     */

    public function get_output()
    {
        return $this->output;
    }

    /**
     * Returns first output line.
     *
     * This method is useful for capturing simple command output (including errors).
     *
     * @return string first output line
     */

    public function get_first_output_line()
    {
        reset($this->output);
        $retval = current($this->output);
        return $retval;
    }

    /**
     * Returns last output line.
     *
     * This method is useful for capturing the last line of output (including errors).
     *
     * @return string last output line
     */

    public function get_last_output_line()
    {
        reset($this->output);
        $retval = end($this->output);
        reset($this->output);
        return $retval;
    }
}
