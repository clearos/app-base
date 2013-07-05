<?php

/**
 * Authorization class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
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

use \clearos\apps\base\Access_Control as Access_Control;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\Posix_User as Posix_User;
use \clearos\apps\users\User_Factory as User_Factory;

clearos_load_library('base/Access_Control');
clearos_load_library('base/Engine');
clearos_load_library('base/Posix_User');
// clearos_load_library('users/User_Factory');
 
// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/Engine_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Authorization class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Authorization extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Authorization constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Authenticates user.
     *
     * @param string $username username
     * @param string $password password
     *
     * @return TRUE if authentication is successful
     * @throws Engine_Exception
     */

    public function authenticate($username, $password)
    {
        clearos_profile(__METHOD__, __LINE__);

        $is_valid = FALSE;

        // Check Posix first
        //------------------

        $user = new Posix_User($username);

        if ($user->check_password($password))
            $is_valid = TRUE;

        // Then check via user factory
        //----------------------------

        if (! $is_valid) {
            if (clearos_load_library('users/User_Factory')) {
                try {
                    $user = User_Factory::create($username);

                    if ($user->check_password($password))
                        $is_valid = TRUE;
                } catch (Engine_Exception $e) {
                    // Not fatal
                }
            }
        }

        return $is_valid;
    }

    /**
     * Check access control for given user and URL.
     *
     * @param string $username username
     * @param string $url      URL
     *
     * @return true if access is permitted
     */

    public function check_acl($username, $url)
    {
        clearos_profile(__METHOD__, __LINE__);

        // root - allow everything
        //------------------------

        if ($username === 'root')
            return TRUE;

        // Access control
        //---------------

        $access = new Access_Control();

        $allow_authenticted = $access->get_authenticated_access_state();
        $allow_custom = $access->get_custom_access_state();
        $valid_urls = $access->get_valid_pages_details($username);

        $valid_authenticated_urls = $valid_urls[Access_Control::TYPE_AUTHENTICATED];
        $valid_custom_urls = $valid_urls[Access_Control::TYPE_CUSTOM];
        $valid_public_urls = $valid_urls[Access_Control::TYPE_PUBLIC];

        // custom access - allow access to configured URLs
        //------------------------------------------------

        if ($allow_custom && $username) {
            foreach ($valid_custom_urls as $valid_url) {
                $valid_url_regex = preg_quote($valid_url, '/');

                if (preg_match("/$valid_url_regex/", $url)) {
                    return TRUE;
                }
            }
        }

        // normal user - allow access to user-specific URLs
        //------------------------------------------------------------

        if ($allow_authenticted && $username) {
            foreach ($valid_authenticated_urls as $valid_url) {
                $valid_url_regex = preg_quote($valid_url, '/');

                if (preg_match("/$valid_url_regex/", $url)) {
                    return TRUE;
                }
            }
        }

        // public pages
        //-------------

        foreach ($valid_public_urls as $valid_url) {
            $valid_url_regex = preg_quote($valid_url, '/');

            if (preg_match("/$valid_url_regex/", $url)) {
                return TRUE;
            }
        }

        // Otherwise, ACL denied
        //----------------------

        $user_log = ($username) ? " for $username" : '';

        return FALSE;
    }
}
