<?php

/**
 * Locale class.
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
use \clearos\apps\base\File as File;

clearos_load_library('base/Engine');
clearos_load_library('base/File');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/File_Not_Found_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Locale class.
 *
 * @category   Apps
 * @package    Base
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

class Locale extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/usr/clearos/apps/base/config/locale';
    const FILE_I18N = '/etc/sysconfig/i18n';
    const FILE_KEYBOARD = '/etc/sysconfig/keyboard';
    const DEFAULT_KEYBOARD = 'us';
    const DEFAULT_LANGUAGE = 'en_US';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $code = NULL;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Locale constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns the character set for the current locale.
     *
     * The language code format is: en, fr, etc.
     *
     * @return string character set 
     * @throws Engine_Exception
     */

    public function get_character_set()
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: this obviously needs to be improved
        switch (self::get_language_code()) {
            case 'zh_CN':
                return 'GB2312';
            default:
                return 'UTF-8';
        }
    }

    /**
     * Returns the system keyboard setting.
     *
     * @return string keyboard setting
     * @throws Engine_Exception
     */

    public function get_keyboard()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_KEYBOARD);
            $keyboard = $file->lookup_value('/^KEYTABLE=/');
        } catch (File_Not_Found_Exception $e) {
            $keyboard = self::DEFAULT_KEYBOARD;
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        return preg_replace('/\"/', '', $keyboard);
    }

    /**
     * Returns the list of available keyboards supported by the system.
     *
     * @return array list of keyboard layouts
     * @throws Engine_Exception
     */

    public function get_keyboards()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG);
            $long_list = $file->lookup_value('/^language_list\s*=\s/');
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $keyboards = array();
        $language_items = explode('|', $long_list);

        foreach ($language_items as $lang) {
            $details = explode(',', $lang);
            $keyboards[] = $details[2];
        }

        sort($keyboards);

        return array_unique($keyboards);
    }

    /**
     * Returns the system language code.
     *
     * The language code format is: en_US, fr_FR, etc.
     *
     * @return string language code 
     * @throws Engine_Exception
     */

    public function get_language_code()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_null($this->code)) {
            $file = new File(Locale::FILE_I18N);

            try {
                $code = $file->lookup_value('/^LANG=/');
                $code = preg_replace('/\..*/', '', $code);
                $code = preg_replace('/\"/', '', $code);
            } catch (File_Not_Found_Exception $e) {
                $code = Locale::DEFAULT_LANGUAGE;
            } catch (File_No_Match_Exception $e) {
                $code = Locale::DEFAULT_LANGUAGE;
            } catch (Exception $e) {
                throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
            }

            $this->code = $code;
        }

        return $this->code;
    }

    /**
     * Returns the configured two-letter language code.
     *
     * @return string two-letter language code
     * @throws Engine_Exception
     */

    public function get_language_code_simple()
    {
        clearos_profile(__METHOD__, __LINE__);

        $code = $this->get_language_code();

        return preg_replace('/_.*/', '', $code);
    }

    /**
     * Returns the list of installed languages used in the framework.
     *
     * The method returns a hash array keyed on the language code.  Each
     * entry in the array contains another hash array with the following fields:
     *
     *  - language code - eg en_US
     *  - language short code - eg en
     *  - language - eg English
     *  - keyboard - eg de-latin1-nodeadkeys
     *
     * @return array hash array of language information
     * @throws Engine_Exception
     */

    public function get_language_info()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG);
            $long_list = $file->lookup_value('/^language_list\s*=\s/');
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $language_info = array();
        $language_list = array();
        $language_items = explode('|', $long_list);

        foreach ($language_items as $lang) {
            $details = explode(',', $lang);
            $language_list['code'] = $details[0];
            $language_list['shortcode'] = preg_replace('/_.*/', '', $details[0]);
            $language_list['description'] = $details[1];
            $language_list['keyboard'] = $details[2];
            $language_info[$details[0]] = $language_list;
        }

        return $language_info;
    }

    /**
     * Returns the text direction for the current locale.
     *
     * @return string direction, RTL or LTR
     * @throws Engine_Exception
     */

    public function get_text_direction()
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: this obviously needs to be improved
        return 'LTR';
    }

    /**
     * Sets the keyboard.
     *
     * @param string $keyboard keyboard code
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_keyboard($keyboard)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($error_message = $this->validate_keyboard($keyboard))
            throw new Validation_Exception($error_message);

        // TODO: what about all the other parameters in /etc/sysconfig/keyboard.

        try {
            $file = new File(self::FILE_KEYBOARD);

            if ($file->exists()) {
                $file->replace_lines('/^KEYTABLE=/', "KEYTABLE=\"$keyboard\"\n");
            } else {
                $file->create('root', 'root', '0644');
                $file->add_lines("KEYTABLE=\"$keyboard\"\n");
            }
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Sets the language.
     *
     * @param string $code language code
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_language_code($code)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($error_message = $this->validate_language_code($code))
            throw new Validation_Exception($error_message);

        // TODO: what about the SYSFONT parameter?
        // TODO: fix hard-coded UTF-8?

        try {
            $file = new File(self::FILE_I18N);

            if ($file->exists()) {
                $file->replace_lines('/^LANG=/', "LANG=\"$code.UTF-8\"\n");
            } else {
                $file->create('root', 'root', '0644');
                $file->add_lines("LANG=\"$code.UTF-8\"\n");
            }
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Sets the language and keyboard based on defaults.
     *
     * @param string $code language code
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_locale($code)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($error_message = $this->validate_language_code($code))
            throw new Validation_Exception($error_message);

        $info = $this->get_language_info();

        $this->set_language_code($code);

        foreach ($info as $item) {
            if ($item['code'] === $code) {
                $this->set_keyboard($item['keyboard']);
                return;
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   R O U T I N E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for keyboard.
     *
     * @param string $keyboard keyboard
     *
     * @return string error message if keyboard is invalid
     * @throws Engine_Exception
     */

    public function validate_keyboard($keyboard)
    {
        clearos_profile(__METHOD__, __LINE__);

        $info = $this->get_language_info();

        foreach ($info as $language) {
            if ($language['keyboard'] === $keyboard)
                return;
        }

        return lang('base_validate_keyboard_invalid');
    }

    /**
     * Validation routine for language code.
     *
     * @param string $code language code
     *
     * @return string error message if language code is invalid
     * @throws Engine_Exception
     */

    public function validate_language_code($code)
    {
        clearos_profile(__METHOD__, __LINE__);

        $info = $this->get_language_info();

        foreach ($info as $language) {
            if ($language['code'] === $code)
                return;
        }

        return lang('base_validate_language_code_invalid');
    }
}

