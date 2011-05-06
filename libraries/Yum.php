<?php

/**
 * Yum package management class.
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
use \clearos\apps\base\Shell as Shell;

clearos_load_library('base/Engine');
clearos_load_library('base/Shell');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Yum_Busy_Exception as Yum_Busy_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Yum_Busy_Exception');


///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Yum package management class.
 *
 * The software classes contains information about a given RPM package.
 * The software constructor requires the pkgname - release and version are
 * optional.  Why do you need the release and version?  Some packages
 * can have multiple version installed, notably the kernel.
 *
 * If you do not specify the release and version name (which is the typical
 * way to call this constructor), then this class will assume that you mean
 * the most recent version.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Yum extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_WC_YUM = '/usr/sbin/wc-yum';
	const COMMAND_PID = "/sbin/pidof";
	const FILE_LOG = "yum.log";

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Yum constructor.
     *
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Install a list of packages using YUM.
     *
     * @param array $list list of package names to install
     *
     * @return void
     * @throws Engine_Exception, Yum_Busy_Exception
     */

    public function install($list)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->is_busy())
            throw new Yum_Busy_Exception();

        try {
            $shell = new Shell();

            $options = array(
			    'background' => TRUE,
                'log' =>self::FILE_LOG
            );

            $exitcode = $shell->execute(self::COMMAND_WC_YUM, "-i " . implode(" ", $list), TRUE, $options);

            if ($exitcode != 0)
                throw new Engine_Exception(lang('base_yum_something_went_wrong'), CLEAROS_ERROR);

            $output = $shell->get_output();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Returns TRUE if the yum is already running.
     *
     * @return boolean TRUE if yum is running
     * @throws Engine_Exception
     */

    public function is_busy()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();
            $options['env'] = 'LANG=en_US';
            $options['validate_exit_code'] = FALSE;
            $exitcode = $shell->Execute(self::COMMAND_PID, "-s -x " . self::COMMAND_WC_YUM, FALSE, $options);

            if ($exitcode == 0)
                return TRUE;
            else
                return FALSE;
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

    /**
     * Returns array of log lines.
     *
     * @return boolean TRUE if yum is running
     * @throws Engine_Exception
     */

    public function get_logs()
    {
        clearos_profile(__METHOD__, __LINE__);

		try {
			$log = new File(COMMON_TEMP_DIR . "/" . self::FILE_LOG);
			$lines = $log->get_contents_as_array();
		} catch (FileNotFoundException $e) {
			$lines = array();
		} catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
		}
		
		return $lines;
    }
}
