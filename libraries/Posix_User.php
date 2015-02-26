<?php

/**
 * Posix user manager class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
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
use \clearos\apps\base\Shell as Shell;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Shell');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Posix user manager class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Posix_User extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_CHKPWD = '/usr/sbin/app-passwd';
    const COMMAND_PASSWD = '/usr/bin/passwd';
    const COMMAND_USERDEL = '/usr/sbin/userdel';
    const COMMAND_CRACKLIB_CHECK = '/usr/sbin/cracklib-check';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $username;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Posix_User constructor.
     *
     * @param string $username username
     */

    public function __construct($username)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->username = empty($username) ? '' : $username;
    }

    /**
     * Checks the password for the user.
     *
     * @param string $password password for the user
     *
     * @return boolean TRUE if password is correct
     * @throws Engine_Exception, Validation_Exception
     */

    public function check_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validate
        //---------

        $error = $this->validate_password($password);

        if ($error)
            throw new Validation_Exception($error, CLEAROS_ERROR);

        $error = $this->validate_username($this->username);

        if ($error)
            throw new Validation_Exception($error, CLEAROS_ERROR);

        // Check password
        //---------------
        
        $options['stdin'] = $this->username . ' ' . $password;
        $options['validate_exit_code'] = FALSE;

        $shell = new Shell();
        $retval = $shell->execute(self::COMMAND_CHKPWD, '', TRUE, $options);

        if ($retval === 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Deletes a user from the Posix system.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function delete()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validate
        //---------

        $error = $this->validate_username($this->username);

        if ($error)
            throw new Validation_Exception($error, CLEAROS_ERROR);

        // Delete
        //-------

        try {
            $shell = new Shell();

            $username = escapeshellarg($this->username);
            $retval = $shell->execute(self::COMMAND_USERDEL, $username, TRUE);

            if ($retval != 0)
                throw new Engine_Exception($shell->get_last_output_line(), CLEAROS_ERROR);
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->GetMessage(), CLEAROS_ERROR);
        }
    }

    /**
     * Checks password complexity.
     *
     * @param string $password password
     *
     * @return boolean TRUE if password is too weak
     * @throws Engine_Exception, Validation_Exception 
     */

    public function is_weak_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_password($password));

        $file = new File(CLEAROS_TEMP_DIR . '/password_check');

        if ($file->exists())
            $file->delete();

        $options['stdin'] = "\"$password\"";
        $options['log'] = 'password_check';
        $options['env'] = 'LANG=en_US';

        $shell = new Shell();
        $shell->execute(self::COMMAND_CRACKLIB_CHECK, '', FALSE, $options);

        $file = new File(CLEAROS_TEMP_DIR . '/password_check');
        $output = $file->get_contents();

        if (preg_match('/: OK$/', $output))
            return FALSE;
        else
            return TRUE;
    }
        
    /**
     * Sets the user's system password.
     *
     * @param string $password password
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception 
     */

    public function set_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_password($password));
        
        $shell = new Shell();

        $user = escapeshellarg($this->username);
        $options['stdin'] = $password;

        $shell->execute(self::COMMAND_PASSWD, '--stdin ' . $user, TRUE, $options);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Password validation routine.
     *
     * @param string $password password
     *
     * @return string error message if passsword is invalid
     */

    public function validate_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);

        return;
    }

    /**
     * Username validation routine.
     *
     * @param string $username username
     *
     * @return string error message if username is invalid
     */

    public function validate_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match('/^([a-z0-9_\-\.\$]+)$/', $username))
            return lang('base_username_invalid');
    }
}
