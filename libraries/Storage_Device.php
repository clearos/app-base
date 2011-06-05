<?php

/**
 * Stroage device class.
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

use \clearos\apps\\Exception as Exception;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\framework\Logger as Logger;

clearos_load_library('/Exception');
clearos_load_library('base/Engine');
clearos_load_library('base/File');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Stroage device class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Storage_Device extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    const PROC_IDE = '/proc/ide';
    const PROC_MDSTAT = '/proc/mdstat';
    const ETC_MTAB = '/etc/mtab';
    const BIN_SWAPON = '/sbin/swapon -s %s';
    const USB_DEVICES = '/sys/bus/usb/devices';
    const IDE_DEVICES = '/sys/bus/ide/devices';
    const SCSI_DEVICES = '/sys/bus/scsi/devices';

    protected $devices = Array();
    protected $is_scanned = FALSE;
    protected $mount_point = NULL;


    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Storage device constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /** Retrieve a list of all storage devices.
     *
     * @param boolean $mounted flag to show/hide mounted devices
     * @param boolean $swap    flag to show/hide swap devices
     *
     * @return array
     * @throws Engine_Exception
     */

    final public function get_devices($mounted = TRUE, $swap = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_scanned) $this->Scan($mounted, $swap);

        return $this->devices;
    }

    /** Retrieve mount point location set by last is_mounted() call.
     *
     * @return string
     * @throws Engine_Exception
     */

    final public function get_mount_point()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->mount_point;
    }

    /** Is the device mounted?
     *
     * @param string $device device
     *
     * @return boolean
     * @throws Engine_Exception
     */

    final public function is_mounted($device)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!($fh = fopen(self::ETC_MTAB, 'r')))
            return FALSE;

        while (!feof($fh)) {
            $buffer = chop(fgets($fh, 4096));
            if (!strlen($buffer)) break;
            list($name, $this->mount_point) = explode(' ', $buffer);
            if ($name == $device) {
                fclose($fh);
                return TRUE;
            }
        }

        $this->mount_point = NULL;

        fclose($fh);
        return FALSE;
    }

    /** Is this a swap device?
     *
     * @param string $device device
     *
     * @return boolean
     * @throws Engine_Exception
     */

    final public function is_swap($device)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!($ph = popen(sprintf(self::BIN_SWAPON, $device), 'r')))
            return FALSE;

        while (!feof($ph)) {
            list($name) = explode(' ', fgets($ph, 4096));
            if ($name == $device) {
                pclose($ph);
                return TRUE;
            }
        }

        pclose($ph);
        return FALSE;
    }

    /** Get software RAID devices
     *
     * @return array
     * @throws Engine_Exception
     */
    final public function get_software_raid_devices()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!($fh = fopen(self::PROC_MDSTAT, 'r')))
            return FALSE;

        $devices = array();
        while (!feof($fh)) {
            if (!preg_match('/^(md[0-9]+)\s+:\s+(\w+)\s+(\w+)\s+(.*$)/', chop(fgets($fh, 8192)), $matches))
                continue;
            $device = array();
            $device['status'] = $matches[2];
            $device['type'] = strtoupper($matches[3]);
            $nodes = explode(' ', $matches[4]);
            foreach ($nodes as $node) {
                $device['node'][] = '/dev/' . preg_replace('/\[[0-9]+\]/', '', $node);
            }
            $devices['/dev/' . $matches[1]] = $device;
        }

        fclose($fh);
        return $devices;
    }

    /** Is this a software RAID device?
     *
     * @param string $device device
     *
     * @return boolean
     * @throws Engine_Exception
     */

    final public function is_software_raid_device($device)
    {
        clearos_profile(__METHOD__, __LINE__);

        $raid = $this->get_software_raid_devices();
        if (array_key_exists($device, $raid)) return TRUE;
        return FALSE;
    }

    /** Is this a software RAID node?
     *
     * @param string $device device
     *
     * @return boolean
     * @throws Engine_Exception
     */

    final public function is_software_raid_node($device)
    {
        clearos_profile(__METHOD__, __LINE__);

        $raid = $this->get_software_raid_devices();
        foreach ($raid as $dev) {
            if (in_array($device, $dev['node']))
                return TRUE;
        }
        return FALSE;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Scan devices.
     *
     * @param boolean $mounted flag TRUE to show mounted devices
     * @param boolean $swap    flag TRUE to show swap devices
     *
     * @return array
     */


    final private function _scan($mounted, $swap)
    {
        clearos_profile(__METHOD__, __LINE__);

        $atapi = $this->_scan_atapi();

        foreach ($atapi as $parent => $device) {
            if (!isset($device['partition'])) continue;
            foreach ($device['partition'] as $partition) {
                $atapi[$partition]['vendor'] = $device['vendor'];
                $atapi[$partition]['model'] = $device['model'];
                $atapi[$partition]['type'] = $device['type'];
                $atapi[$partition]['parent'] = $parent;
            }
            unset($atapi[$parent]);
        }

        $devices = $this->_scan_scsi();
        $scsi = array();
        foreach ($devices as $device) {
            if (!isset($device['partition'])) {
                $scsi[$device['device']]['vendor'] = $device['vendor'];
                $scsi[$device['device']]['model'] = $device['model'];
                if ($device['bus'] == 'usb')
                    $scsi[$device['device']]['type'] = 'USB';
                else
                    $scsi[$device['device']]['type'] = 'SCSI/SATA';
                continue;
            }

            foreach ($device['partition'] as $partition) {
                $scsi[$partition]['vendor'] = $device['vendor'];
                $scsi[$partition]['model'] = $device['model'];
                $scsi[$device['device']]['parent'] = $device['device'];
                if ($device['bus'] == 'usb')
                    $scsi[$partition]['type'] = 'USB';
                else
                    $scsi[$partition]['type'] = 'SCSI/SATA';
            }
            unset($scsi[$device['device']]);
        }

        $this->devices = array_merge($atapi, $scsi);

        $raid_devices = $this->get_software_raid_devices();
        $purge = array();
        foreach ($this->devices as $device => $details) {
            foreach ($raid_devices as $raid) {
                if (!in_array($device, $raid['node']))
                    continue;
                $purge[] = $device;
            }
        }
        foreach ($purge as $device) unset($this->devices[$device]);
        $purge = array();

        foreach ($raid_devices as $device => $details) {
            $this->devices[$device]['vendor'] = 'Software';
            $this->devices[$device]['model'] = 'RAID';
            $this->devices[$device]['type'] = $details['type'];
        }

        foreach ($this->devices as $device => $details) {
            $this->devices[$device]['mounted'] = $this->is_mounted($device);
            if ($this->devices[$device]['mounted'])
                $this->devices[$device]['mount_point'] = $this->mount_point;
        }

        $purge = array();
        if (!$mounted) {
            foreach ($this->devices as $device => $details) {
                if (!$details['mounted']) continue;
                $purge[] = $device;
            }
        }
        if (!$swap) {
            foreach ($this->devices as $device => $details) {
                if (!$this->is_swap($device)) continue;
                $purge[] = $device;
            }
        }

        foreach ($purge as $device) unset($this->devices[$device]);

        ksort($this->devices);
        $this->is_scanned = TRUE;
    }

    /**
     * Scan ATAPI sub system.
     *
     * @return array
     */

    final private function _scan_atapi()
    {
        clearos_profile(__METHOD__, __LINE__);

        $scan = Array();
        // Find IDE devices that match: %d.%d
        $entries = $this->_scan_dir(self::IDE_DEVICES, '/^\d.\d$/');

        // Scan all ATAPI/IDE devices.
        foreach ($entries as $entry) {
            $path = self::IDE_DEVICES . "/$entry";
            if (($block_devices = $this->_scan_dir("$path/block", '/^dev$/')) === FALSE) {
                if (($block_devices = $this->_scan_dir($path, '/^block:.*$/')) === FALSE) continue;
                if (!count($block_devices)) continue;
                $path .= '/' . $block_devices[0];
            } else $path .= '/block';
            if (($block = basename(readlink("$path"))) === FALSE) continue;

            $info = array();
            $info['type'] = 'IDE/ATAPI';

            try {
                $file = new File(self::PROC_IDE . "/$block/model", TRUE);
                if ($file->Exists())
                    list($info['vendor'], $info['model']) = preg_split('/ /', $file->get_contents(), 2);
            } catch (Exception $e) {
                Logger::log_exception($e);
            }

            // Here we are looking for detected partitions
            if (($partitions = $this->_scan_dir($path, "/^$block\d$/")) !== FALSE && count($partitions) > 0) {
                foreach($partitions as $partition)
                    $info['partition'][] = "/dev/$partition";
            }

            $scan["/dev/$block"] = $info;
        }
        return $scan;
    }
 
    /**
     * Scan SCSI sub system.
     *
     * @return array
     */

    final private function _scan_scsi()
    {
        clearos_profile(__METHOD__, __LINE__);

        $devices = Array();

        try {
            // Find USB devices that match: %d-%d
            $entries = $this->_scan_dir(self::USB_DEVICES, '/^\d-\d$/');

            // Walk through the expected USB -> SCSI /sys paths.
            foreach ($entries as $entry) {
                $path = self::USB_DEVICES . "/$entry";
                if (($devid = $this->_scan_dir($path, "/^$entry:\d\.\d$/")) === FALSE) continue;
                if (count($devid) != 1) continue;

                // Might need this product
                //if (!($fh = fopen("$path/product", 'r'))) continue;
                //$device['product'] = chop(fgets($fh, 4096));
                //fclose($fh);

                $path .= '/' . $devid[0];
                if (($host = $this->_scan_dir($path, '/^host\d+$/')) === FALSE) continue;
                if (count($host) != 1) continue;
                $path .= '/' . $host[0];
                if (($target = $this->_scan_dir($path, '/^target\d+:\d:\d$/')) === FALSE) continue;
                if (count($target) != 1) continue;
                $path .= '/' . $target[0];
                if (($lun = $this->_scan_dir($path, '/^\d+:\d:\d:\d$/')) === FALSE) continue;
                if (count($lun) != 1) continue;
                $path .= '/' . $lun[0];
                if (($dev = $this->_scan_dir("$path/block", '/^dev$/')) === FALSE) continue;
                if (count($dev) != 1) continue;

                // Validate USB mass-storage device
                if (!($fh = fopen("$path/vendor", 'r'))) continue;
                $device['vendor'] = chop(fgets($fh, 4096));
                fclose($fh);
                if (!($fh = fopen("$path/model", 'r'))) continue;
                $device['model'] = chop(fgets($fh, 4096));
                fclose($fh);
                if (!($fh = fopen("$path/block/dev", 'r'))) continue;
                $device['nodes'] = chop(fgets($fh, 4096));
                fclose($fh);
                $device['path'] = $path;
                $device['bus'] = 'usb';

                // Valid device found (almost, continues below)...
                $devices[] = $device;
            }

            // Find SCSI devices that match: %d:%d:%d:%d
            $entries = $this->_scan_dir(self::SCSI_DEVICES, '/^\d:\d:\d:\d$/');

            // Scan all SCSI devices.
            if ($entries !== FALSE) {
                foreach ($entries as $entry) {
                    $block = 'block';
                    $path = self::SCSI_DEVICES . "/$entry";
                    if (($dev = $this->_scan_dir("$path/block", '/^dev$/')) === FALSE) {
                        if (($block_devices = $this->_scan_dir("$path", '/^block:.*$/')) === FALSE) continue;
                        $block = $block_devices[0];
                        if (($dev = $this->_scan_dir("$path/$block", '/^dev$/')) === FALSE) continue;
                    }
                    if (count($dev) != 1) continue;

                    // Validate SCSI storage device
                    if (!($fh = fopen("$path/vendor", 'r'))) continue;
                    $device['vendor'] = chop(fgets($fh, 4096));
                    fclose($fh);
                    if (!($fh = fopen("$path/model", 'r'))) continue;
                    $device['model'] = chop(fgets($fh, 4096));
                    //$device['product'] = $device['model'];
                    fclose($fh);
                    if (!($fh = fopen("$path/$block/dev", 'r'))) continue;
                    $device['nodes'] = chop(fgets($fh, 4096));
                    fclose($fh);
                    $device['path'] = "$path/$block";
                    $device['bus'] = 'scsi';

                    // Valid device found (almost, continues below)...
                    $unique = TRUE;
                    foreach ($devices as $usb) {
                        if ($usb['nodes'] != $device['nodes']) continue;
                        $unique = FALSE;
                        break;
                    }

                    if ($unique) $devices[] = $device;
                }
            }

            if (count($devices)) {
                // Create a hashed array of all device nodes that match: /dev/s*
                // XXX: This can be fairly expensive, takes a few seconds to run.
                if (!($ph = popen('stat -c 0x%t:0x%T:%n /dev/s*', 'r')))
                    throw new Exception("Error running stat command", CLEAROS_WARNING);

                $nodes = array();
                $major = '';
                $minor = '';
                
                while (!feof($ph)) {
                    $buffer = chop(fgets($ph, 4096));
                    if (sscanf($buffer, '%x:%x:', $major, $minor) != 2) continue;
                    if ($major == 0) continue;
                    $nodes["$major:$minor"] = substr($buffer, strrpos($buffer, ':') + 1);
                }

                // Clean exit?
                if (pclose($ph) != 0)
                    throw new Exception("Error running stat command", CLEAROS_WARNING);

                // Hopefully we can now find the TRUE device name for each
                // storage device found above.  Validation continues...
                foreach ($devices as $key => $device) {
                    if (!isset($nodes[$device['nodes']])) {
                        unset($devices[$key]);
                        continue;
                    }

                    // Set the block device
                    $devices[$key]['device'] = $nodes[$device['nodes']];

                    // Here we are looking for detected partitions
                    if (($partitions = $this->_scan_dir($device['path'], '/^' . basename($nodes[$device['nodes']]) . '\d$/')) !== FALSE && count($partitions) > 0) {
                        foreach($partitions as $partition)
                            $devices[$key]['partition'][] = dirname($nodes[$device['nodes']]) . '/' . $partition;
                    }

                    unset($devices[$key]['path']);
                    unset($devices[$key]['nodes']);
                }
            }
        } catch (Exception $e) {
            Logger::log_exception($e);
        }

        return $devices;
    }

    /**
     * Scan a directory returning files that match the pattern.
     *
     * @param string $dir     directory
     * @param string $pattern pattern
     *
     * @return array
     */

    final private function _scan_dir($dir, $pattern)
    {
        if (!($dh = opendir($dir))) return FALSE;

        $matches = array();
        while (($file = readdir($dh)) !== FALSE) {
            if (!preg_match($pattern, $file)) continue;
            $matches[] = $file;
        }

        closedir($dh);
        sort($matches);

        return $matches;
    }
}
