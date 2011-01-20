<?php

/**
 * Software package management class.
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
use \clearos\apps\base\Software_Not_Installed_Exception as Software_Not_Installed_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Software_Not_Installed_Exception');


///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Software package management class.
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

class Software extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $pkgname = NULL;
    protected $license = NULL;
    protected $description = NULL;
    protected $install_size = NULL;
    protected $install_time = NULL;
    protected $packager = NULL;
    protected $release = NULL;
    protected $summary = NULL;
    protected $version = NULL;

    const COMMAND_RPM = '/bin/rpm';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Software constructor.
     *
     * @param string $pkgname software package name
     * @param string $version version number
     * @param string $release release number
     */

    public function __construct($pkgname, $version = "", $release = "")
    {
        clearos_profile(__METHOD__, __LINE__);

        if (($version) && ($release))
            $this->pkgname = "$pkgname-$version-$release";
        else
            $this->pkgname = $pkgname;
    }

    /**
     * Returns the license of the software - eg GPL.
     *
     * @return string license
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_license()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->license))
            $this->_load_info();

        return $this->license;
    }

    /**
     * Returns a long description in text format.
     *
     * Descriptions can be anywhere from one-sentence long to several paragraphs.
     *
     * @return string description
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_description()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->description))
            $this->_load_info();

        return $this->description;
    }

    /**
     * Returns the installed size (not the download size).
     *
     * @return integer install size in bytes
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_install_size()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->install_size))
            $this->_load_info();

        return $this->install_size;
    }

    /**
     * Returns install time in seconds since Jan 1, 1970.
     *
     * @return integer install time
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_install_time()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->install_time))
            $this->_load_info();

        return $this->install_time;
    }

    /**
     * Returns the package name.
     *
     * @return string package name
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_package_name()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->pkgname;
    }

    /**
     * Returns the packager.
     *
     * @return string packager
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_packager()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->packager))
            $this->_load_info();

        return $this->packager;
    }

    /**
     * Returns the release.
     *
     * The release is not necessarily numeric!
     *
     * @return string release
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_release()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->release))
            $this->_load_info();

        return $this->release;
    }

    /**
     * Returns the version.
     *
     * The version is not necessarily numeric!
     *
     * @return string version
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_version()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->version))
            $this->_load_info();

        return $this->version;
    }

    /**
     * Returns a one-line description.
     *
     * @return string description
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_summary()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->summary))
            $this->_load_info();

        return $this->summary;
    }

    /**
     * Generic method to grab information from the RPM database.
     *
     * There are dozens of bits of information in an RPM file accessible via the
     * "rpm -q --queryformat" command.  See list of tags at
     * http://www.rpm.org/max-rpm-snapshot/ch-queryformat-tags.html
     *
     * @param string $tag queryformat tag in RPM
     *
     * @return string value from queryformat command
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function get_rpm_info($tag)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_installed())
            throw new Software_Not_Installed_Exception($this->pkgname, CLEAROS_INFO);

        $rpm = escapeshellarg($this->pkgname);

        // For some reason, the output formatting with "rpm --last" is fubar.
        // We have to implement it here instead.

        try {
            $shell = new Shell();
            $exitcode = $shell->execute(self::COMMAND_RPM, "-q --queryformat \"%{VERSION}\\n\" $rpm", FALSE);
            if ($exitcode != 0)
                throw new Engine_Exception(SOFTWARE_LANG_ERRMSG_LOOKUP_ERROR, CLEAROS_WARNING);
            $rawoutput = $shell->get_output();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_exception(), CLEAROS_WARNING);
        }

        // More than 1 version?  Sort and grab the latest.
        if (count($rawoutput) > 1) {
            rsort($rawoutput);
            $version = $rawoutput[0];
            $rpm = escapeshellarg($this->pkgname . "-" . $version);
            unset($rawoutput);

            try {
                $exitcode = $shell->execute(self::COMMAND_RPM, "-q --queryformat \"%{RELEASE}\\n\" $rpm", FALSE);
                if ($exitcode != 0)
                    throw new Engine_Exception(SOFTWARE_LANG_ERRMSG_LOOKUP_ERROR, CLEAROS_WARNING);
                $rawoutput = $shell->get_output();
            } catch (Engine_Exception $e) {
                throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
            }

            // More than 1 release?  Sort and grab the latest.
            if (count($rawoutput) > 1) {
                rsort($rawoutput);
                $release = $rawoutput[0];
                $rpm = escapeshellarg($this->pkgname . "-" . $version . "-" . $release);
            }
        }

        // Add formatting for bare tags (e.g. LICENSE -> %{LICENSE})
        if (!preg_match("/%/", $tag))
            $tag = "%{" . $tag . "}";

        unset($rawoutput);

        try {
            $exitcode = $shell->execute(self::COMMAND_RPM, "-q --queryformat \"" . $tag . "\" $rpm", FALSE);

            if ($exitcode != 0)
                throw new Engine_Exception(SOFTWARE_LANG_ERRMSG_LOOKUP_ERROR, CLEAROS_WARNING);

            $rawoutput = $shell->get_output();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }

        return implode(" ", $rawoutput);
    }

    /**
     * Returns TRUE if the package is installed.
     *
     * @return boolean TRUE if package is installed
     * @throws Engine_Exception, Software_Not_Installed_Exception
     */

    public function is_installed()
    {
        clearos_profile(__METHOD__, __LINE__);

        $rpm = escapeshellarg($this->pkgname);
        $exitcode = 1;

        try {
            // KLUDGE: rpm does not seem to have a nice way to get around
            // running multiple rpm commands simultaneously.  You can get a
            // temporary "cannot get shared lock" error in this case.

            $shell = new Shell();
            $options['env'] = "LANG=en_US";

            for ($i = 0; $i < 5; $i++) {
                $exitcode = $shell->execute(self::COMMAND_RPM, "-q $rpm 2>&1", FALSE, $options);
                $lines = implode($shell->get_output());

                if (($exitcode === 1) && (preg_match("/shared lock/", $lines)))
                    sleep(1);
                else
                    break;
            }
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message(), CLEAROS_WARNING);
        }

        if ($exitcode == 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Loads all the fields in this class.
     *
     * @access private
     * @return void
     */

    protected function _load_info()
    {
        clearos_profile(__METHOD__, __LINE__);

        $rawoutput = explode("|", 
            $this->get_rpm_info("%{LICENSE}|%{DESCRIPTION}|%{SIZE}|%{INSTALLTIME}|%{PACKAGER}|%{RELEASE}|%{SUMMARY}|%{VERSION}")
        );

        $this->license = $rawoutput[0];
        $this->description = $rawoutput[1];
        $this->install_size = $rawoutput[2];
        $this->install_time = $rawoutput[3];
        $this->packager = $rawoutput[4];
        $this->release = $rawoutput[5];
        $this->summary = $rawoutput[6];
        $this->version = $rawoutput[7];
    }
}
