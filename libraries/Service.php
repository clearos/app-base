<?php

/**
 * Service class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2012 ClearFoundation
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

use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\framework\Logger as Logger;

clearos_load_library('base/Daemon');
clearos_load_library('base/File');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');
clearos_load_library('base/File_Not_Found_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Service class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Service extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Service constructor.
     *
     * @param int   $argc argument count.
     * @param array $argv parameter array.
     */

    public function __construct($argc = 0, $argv = array())
    {
        clearos_profile(__METHOD__, __LINE__);

        $init = '/etc/init.d/TODO';
        parent::__construct($init);
    }

    /**
     * Create service factory.
     *
     * @param int   $argc argument count.
     * @param array $argv parameter array.
     *
     * @return integer exit code
     */

    public final static function create($argc, $argv)
    {
        // Parse command-line options
        $options = getopt('a:s:p:h');
        if ($options === FALSE || array_key_exists('h', $options)) {
            printf("%s: ClearOS Webconfig Service Usage\n", basename($argv[0]));
            printf("Copyright (C) 2012 ClearFoundation\n");
            printf("  -a {app}\n\tSpecify application base name.\n");
            printf("  -s {name}\n\tSpecify service to start.\n");
            printf("  -p {PID file}\n\tSpecify PID file location.\n");
            exit(0);
        }

        if (!array_key_exists('a', $options)) {
            echo "Required argument missing: -a {app}\n";
            exit(1);
        }

        if (!array_key_exists('s', $options)) {
            echo "Required argument missing: -s {name}\n";
            exit(1);
        }

        if (!array_key_exists('p', $options)) {
            echo "Required argument missing: -p {PID file}\n";
            exit(1);
        }

        $service = NULL;
        $app_name = $options['a'];
        $service_class = $options['s'];
        $pid_file = $options['p'];

        $loader = "
            clearos_load_library(\"$app_name/$service_class\");
            \$service = new \\clearos\\apps\\$app_name\\$service_class(\$argc, \$argv);
        ";

        eval($loader);
        if (!is_object($service)) exit(1);

        try {
            switch (($pid = pcntl_fork())) {
                case -1:
                    exit(1);
                case 0:
                    set_time_limit(0);
                    exit($service->entry());
            }

            $file = new File($pid_file);
            try {
                if ($file->exists()) $file->delete();

                $file->create('webconfig', 'webconfig', '0644');
                $file->add_lines(sprintf("%d\n", $pid));
            }
            catch (Exception $e) {
                echo "Error saving PID to file: $pid_file\n";
            }
        }
        catch (Exception $e) {
                echo "Error: {$e->getMessage()}\n";
                return 1;
        }

        return 0;
    }

    /**
     * Service entry point.
     *
     * Entry method (main).  Pure virtual override.
     *
     * @return void
     */

    public function entry()
    {
        // TODO: translate
        throw new Engine_Exception('Call to pure virtual method');
    }
}
