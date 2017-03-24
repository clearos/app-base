<?php

/**
 * Generaly system stats class.
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

clearos_load_library('base/Engine');
clearos_load_library('base/File');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Generaly system stats class.
 *
 * @category   apps
 * @package    base
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Stats extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_UPTIME = '/proc/uptime';
    const FILE_PROC_LOADAVG = '/proc/loadavg';
    const FILE_PROC_MEMINFO = '/proc/meminfo';
    const FILE_CLEAROS_VERSION = '/etc/redhat-release';
    const FILE_YUM_LOG = '/var/log/yum.log';
    const FILE_CPUINFO = '/proc/cpuinfo';
    const FILE_MEMINFO = '/proc/meminfo';
    const CMD_UNAME = '/bin/uname';
    const CMD_DATE = '/bin/date';
    const CMD_CAT = '/bin/cat';
    const CMD_DF = '/bin/df';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Stats constructor.
     */

    public function stats() 
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns uptime.
     *
     * @return array uptime
     * @throws Engine_Exception
     */

    public function get_uptimes()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_UPTIME);
        $contents = $file->get_contents();

        // Idle time is across all CPUs, so normalize it
        $num_cpus = $this->get_cpu_count();

        list($uptime, $idle) = explode(' ', chop($contents));

        $result = array();
        $result['uptime'] = sprintf('%d', $uptime);
        $result['idle'] = sprintf('%d', ($idle / $num_cpus));

        return $result;
    }

    /**
     * Returns load averages.
     *
     * @return array loadavg
     * @throws Engine_Exception
     */

    public function get_load_averages()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_PROC_LOADAVG);
        $contents = $file->get_contents();

        $fields = explode(' ', chop($contents));

        $result = array();
        $result['one'] = $fields[0];
        $result['five'] = $fields[1];
        $result['fifteen'] = $fields[2];

        return $result;
    }

    /**
     * Returns memory information.
     *
     * @return array memory information
     * @throws Engine_Exception
     */

    public function get_memory_stats()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_PROC_MEMINFO);

        $lines = $file->get_contents_as_array();
        $info = array();

        foreach ($lines as $line) {
            $parts = array();
            if (!preg_match('/^([A-z_]+):[[:space:]]+([0-9]+).*$/', $line, $parts))
                continue;

            if ($parts[1] == 'MemTotal')
                $info['total'] = (float)$parts[2];
            else if ($parts[1] == 'MemFree')
                $info['free'] = (float)$parts[2];
            else if ($parts[1] == 'SwapTotal')
                $info['swap_total'] = (float)$parts[2];
            else if ($parts[1] == 'SwapFree')
                $info['swap_free'] = (float)$parts[2];
            else if ($parts[1] == 'Cached')
                $info['cached'] = (float)$parts[2];
            else if ($parts[1] == 'Buffers')
                $info['buffers'] = (float)$parts[2];
        }

        // Calculate kernel_and_apps
        // total = cached + buffers + kernel_and_apps + free
        $info['used'] =  $info['total'] - $info['free'];
        $info['kernel_and_apps'] = $info['total'] - $info['free'] - $info['cached'] - $info['buffers'];
        $info['swap_used'] = $info['swap_total'] - $info['swap_free'];

        // Calculate some percentages
        $info['buffers_percent'] = round(($info['buffers'] / $info['total']) * 100);
        $info['cached_percent'] = round(($info['cached'] / $info['total']) * 100);
        $info['free_percent'] = round(($info['free'] / $info['total']) * 100);
        $info['kernel_and_apps_percent'] = 100 - $info['free_percent'] - $info['cached_percent'] - $info['buffers_percent'] ;

        ksort($info);

        return $info;
    }

    /**
     * Returns processes information.
     *
     * @return array processes information
     * @throws Engine_Exception
     */

    public function get_process_stats()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_PROC_LOADAVG);
        $contents = $file->get_contents();

        $all_fields = explode(' ', chop($contents));
        $fields = explode('/', $all_fields[3]);

        $result = array();
        $result['running'] = $fields[0];
        $result['total'] = $fields[1];

        return $result;
    }

    /**
     * Returns ClearOS version.
     *
     * @return string version information
     * @throws Engine_Exception
     */

    public function get_clearos_version()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_CLEAROS_VERSION);

        $lines = $file->get_contents_as_array();
        return $lines;
    }

    /**
     * Returns kernel version.
     *
     * @return string kernel version
     * @throws Engine_Exception
     */

    public function get_kernel_version()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $args = '-r';

        $shell->execute(self::CMD_UNAME, $args, FALSE, $options);
        $retval = $shell->get_output();

        return $retval;

    }
    /**
     * Returns system date and time.
     *
     * @return string date and time
     * @throws Engine_Exception
     */

    public function get_system_time()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $args = '';

        $shell->execute(self::CMD_DATE, $args, FALSE, $options);
        $retval = $shell->get_output();

        return $retval;
    }

    /**
     * Returns software updates
     *
     * @param int $max maximum number of entries to return
     *
     * @return array software updates
     * @throws Engine_Exception
     */

    public function get_yum_log($max = 10)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_YUM_LOG);

        $entries = $file->get_contents_as_array();

        $recententries = array_slice($entries, -1*$max, $max);

        foreach ($recententries as $line) {
            $pieces = explode(' ', $line);
            // We're looking only for lines that follow the following format:
            // MON DAY TTTT TYPE PKG
            // Ignore others
            if (count($pieces) != 5 || !preg_match("/.*\:$/", $pieces[3]))
                continue;

            $output['date'] = $pieces[0] . ' ' . $pieces[1];
            $output['time'] = $pieces[2];
            $output['action'] = preg_replace('/\:$/', '', $pieces[3]);
            $output['package'] = preg_replace('/^\d+\:/', '', $pieces[4]);
            $log[] = $output;
        }

        return $log;
    }

    /**
     * Returns CPU count.
     *
     * @return integer number of CPUs
     * @throws Engine_Exception
     */

    public function get_cpu_count()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_CPUINFO);

        $lines = $file->get_contents_as_array();
        $count = 0;

        foreach ($lines as $line) {
            if (preg_match('/^processor\s*:/', $line))
                $count++;
        }

        return $count;
    }

    /**
     * Returns CPU VT (Virtualization Technology) state.
     *
     * @return boolean TRUE if CPU supports VT
     * @throws Engine_Exception
     */

    public function get_cpu_vt_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_CPUINFO);

        $lines = $file->get_contents_as_array();
        $state = FALSE;

        foreach ($lines as $line) {
            if (preg_match('/^flags\s*:.*\s+(vmx|svm)\s*/', $line))
                $state = TRUE;
        }

        return $state;
    }

    /**
     * Returns cpu model name.
     *
     * @return array cpu model
     * @throws Engine_Exception
     */

    public function get_cpu_model()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_CPUINFO);

        $lines = $file->get_contents_as_array();
        //multiple cores will appear as individual entries, but only the first is displayed

        foreach ($lines as $line) {
            if (preg_match('/model name/', $line)) {
                 $pieces = explode(":", $line);
                 $retval[] = trim($pieces[1]);
            }
        }
        return $retval;
    }

    /**
     * Returns total RAM in GB
     *
     * @return string total RAM
     * @throws Engine_Exception
     */

    public function get_mem_size()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_MEMINFO);

        $lines = $file->get_contents_as_array();

        foreach ($lines as $line) {
            if (preg_match('/MemTotal/', $line)) {
                $pieces = explode(":", $line);
                $valuekB = trim($pieces[1]);
                $valueGB = round(str_replace(' kB', '', $valuekB)/(1024*1024), 2);
            }
        }
        return $valueGB;

    }
    /**
     * Returns filesystem usage
     *
     * @return array file system usage
     * @throws Engine_Exception
     */

    public function get_filesystem_usage()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $args = '-hP';

        // df can send a bad exit code for specific mount points - sett tracker #1363
        $options['validate_exit_code'] = FALSE;

        $shell->execute(self::CMD_DF, $args, TRUE, $options);
        $retval = $shell->get_output();

        foreach ($retval as $line) {
            $line = preg_replace('/\s+/', '|', $line);
            $pieces = explode('|', $line);
            if (preg_match('/Filesystem/', $line) || preg_match('/tmpfs/', $line))
                continue;
            
            $result['filesystem'] = $pieces[0];
            $result['size'] = $pieces[1];
            $result['used'] = $pieces[2];
            $result['avail'] = $pieces[3];
            $result['use'] = $pieces[4];
            $result['mounted'] = $pieces[5];
            $results[] = $result;
        }

        return $results;
    }
}
