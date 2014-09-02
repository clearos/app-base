<?php

/**
 * Lock helper class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 ClearFoundation
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

use \Exception as Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Lock helper class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Lock extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $lock_handle;
    protected $lock_file;

    const DIR_LOCK = '/var/clearos/base/lock';
    const LOCK_SUFFIX = '.lock';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Lock constructor.
     *
     * @param string $lock_name name of lock
     */

    public function __construct($lock_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        
        $this->lock_handle = NULL;
        $this->lock_file = self::DIR_LOCK . '/' . $lock_name . self::LOCK_SUFFIX;
    }

    /**
     * Create a lock file.
     *
     * @return boolean TRUE if lock file was created
     * @throws Engine_Exception
     */

    public function get_lock()
    {
        clearos_profile(__METHOD__, __LINE__);

        $old = umask(002);
        $this->lock_handle = fopen($this->lock_file, 'w');
        umask($old);

        if (!flock($this->lock_handle, LOCK_EX | LOCK_NB))
            return FALSE;
        else
            return TRUE;
    }

    /**
     * Remove lock file.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function unlock()
    {
        clearos_profile(__METHOD__, __LINE__);

        flock($this->lock_handle, LOCK_UN);
        fclose($this->lock_handle);

        try {
            $file = new File($this->lock_file, TRUE);
            if ($file->exists())
                $file->delete();
        } catch (Exception $e) {
            // Not fatal, just tidying up
        }
    }

    /**
     * Returns boolean indicating whether file is locked.
     *
     * @return boolean
     * @throws Engine_Exception
     */

    public function is_locked()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File($this->lock_file, FALSE);

        if ($file->exists()) {
            $locked = FALSE;
            $this->lock_handle = fopen($this->lock_file, 'r');
            flock($this->lock_handle, LOCK_SH | LOCK_NB, $locked);

            if ($locked)
                return TRUE;
        }

        return FALSE;
    }
}
