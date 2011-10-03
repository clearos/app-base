<?php

/**
 * Mime class.
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

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Mime class.
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

class Mime extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $type = array("text", "multipart", "message", "application", "audio", "image", "video", "other");
    protected $encoding = array("7bit", "8bit", "binary", "base64", "quoted-printable", "other");

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Mime constructor.
     *
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns the list of message parts.
     *
     * @param obj $structure result of imap_fetchstructure()
     *
     * @return array list of message parts
     * @throws Engine_Exception
     */

    function get_parts($structure)
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO - Use recursive function
        $all = Array();
        $parts = Array();
        if (isset($structure->parts))
            $parts = $structure->parts;
        for ($index = 0; $index < sizeof($parts); $index++) {
            $obj = $parts[$index];
            $pid = ($index + 1);
            // default to text
            if (!isset($obj->type) || $obj->type == "")
                $obj->type = 0;
            // default to 7bit
            if (!isset($obj->encoding) || $obj->encoding == "")
                $obj->encoding = 0;
            $all[$pid]["encoding"] = $this->encoding[$obj->encoding];
            if (isset($obj->bytes))
                $all[$pid]["size"] = strtolower($obj->bytes);
            else
                $all[$pid]["size"] = 0;
            if (isset($obj->disposition))
                $all[$pid]["disposition"] = strtolower($obj->disposition);
            else
                $all[$pid]["disposition"] = NULL;
            if (isset($obj->subtype))   
                $all[$pid]["type"] = $this->type[$obj->type] . "/" . strtolower($obj->subtype);
            else
                $all[$pid]["type"] = NULL;
            if (isset($obj->ifid) && $obj->ifid) {
                $all[$pid]["Content-ID"] = $obj->id;
            }
            if (isset($obj->disposition) && preg_match("/^attachment$|^inline$/i", $obj->disposition)) {
                $params = $obj->dparameters;
                foreach ($params as $p) {
                    if(strtoupper($p->attribute) == "FILENAME" || strtoupper($p->attribute) == "NAME")
                        $all[$pid]["name"] = $p->value;
                    else if(strtoupper($p->attribute) == "CHARSET")
                        $all[$pid]["charset"] = $p->value;
                }
            }
            if (isset($obj->parts)) {
                $partsSub1 = $obj->parts;
                for ($indexSub1 = 0; $indexSub1 < sizeof($partsSub1); $indexSub1++) {
                    $objSub1 = $partsSub1[$indexSub1];
                    $pid = ($index + 1) . "." . ($indexSub1 + 1);
                    // default to text
                    if (!isset($objSub1->type) || $objSub1->type == "")
                        $objSub1->type = 0;
                    // default to 7bit
                    if (!isset($objSub1->encoding) || $objSub1->encoding == "")
                        $objSub1->encoding = 0;
                    $all[$pid]["encoding"] = $this->encoding[$objSub1->encoding];
                    if (isset($objSub1->bytes))
                        $all[$pid]["size"] = strtolower($objSub1->bytes);
                    else
                        $all[$pid]["size"] = 0;
                    if (isset($objSub1->disposition))
                        $all[$pid]["disposition"] = strtolower($objSub1->disposition);
                    else
                        $all[$pid]["disposition"] = NULL;
                    $all[$pid]["type"] = $this->type[$objSub1->type] . "/" . strtolower($objSub1->subtype);
                    if ($objSub1->ifid)
                        $all[$pid]["Content-ID"] = $objSub1->id;
                    if (isset($objSub1->disposition) && preg_match("/^attachment$|^inline$/s", $objSub1->disposition)) {
                        $params = $objSub1->parameters;
                        foreach ($params as $p) {
                            if(strtoupper($p->attribute) == "FILENAME" || strtoupper($p->attribute) == "NAME")
                                $all[$pid]["name"] = $p->value;
                            else if(strtoupper($p->attribute) == "CHARSET")
                                $all[$pid]["charset"] = $p->value;
                        }
                        $params = $objSub1->dparameters;
                        foreach ($params as $p) {
                            if(strtoupper($p->attribute) == "FILENAME" || strtoupper($p->attribute) == "NAME")
                                $all[$pid]["name"] = $p->value;
                            else if(strtoupper($p->attribute) == "CHARSET")
                                $all[$pid]["charset"] = $p->value;
                        }
                    } else if (isset($objSub1->ifparameters)) {
                        $params = $objSub1->parameters;
                        foreach ($params as $p) {
                            if(strtoupper($p->attribute) == "FILENAME" || strtoupper($p->attribute) == "NAME")
                                $all[$pid]["name"] = $p->value;
                            else if(strtoupper($p->attribute) == "CHARSET")
                                $all[$pid]["charset"] = $p->value;

                            if($p->attribute == "CHARSET")
                                $all[$pid]["charset"] = $p->value;
                        }
                    }
                }
            }
        }
        return $all;
    }
}
