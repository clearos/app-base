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

    const FILE_I18N = '/etc/sysconfig/i18n';
    const FILE_KEYBOARD = '/etc/sysconfig/keyboard';
    const DEFAULT_ENCODING = 'UTF-8';
    const DEFAULT_KEYBOARD = 'us';
    const DEFAULT_LANGUAGE_BASE_CODE = 'en';
    const DEFAULT_LANGUAGE_CODE = 'en_US';
    const DEFAULT_TEXT_DIRECTION = 'LTR';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $code = self::DEFAULT_LANGUAGE_CODE;
    protected $locales = array();
    protected $is_loaded = FALSE;

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
     * Returns the character encoding for the current locale.
     *
     * @return string character encoding
     * @throws Engine_Exception
     */

    public function get_encoding()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_locales();

        $code = $this->get_language_code();

        if (isset($this->locales[$code]) && isset($this->locales[$code]['encoding']))
            $encoding = $this->locales[$code]['encoding'];
        else
            $encoding = self::DEFAULT_ENCODING;

        return $encoding;
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

        if (! $this->is_loaded)
            $this->_load_locales();

        $keyboards = array();

        foreach ($this->locales as $locale => $details)
            $keyboards[] = $details['default_keyboard'];

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
                $code = Locale::DEFAULT_LANGUAGE_CODE;
            } catch (File_No_Match_Exception $e) {
                $code = Locale::DEFAULT_LANGUAGE_CODE;
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

    public function get_language_base_code()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_locales();

        $code = $this->get_language_code();

        if (isset($this->locales[$code]) && isset($this->locales[$code]['base_code']))
            $base_code = $this->locales[$code]['base_code'];
        else
            $base_code = self::DEFAULT_LANGUAGE_BASE_CODE;

        return $base_code;
    }

    /**
     * Returns the list of installed locales used in the framework.
     *
     * The information is an array keyed on the language code (e.g. en_US)
     * - base_code, e.g. en
     * - description
     * - native_description e.g. Français instead of French
     * - default_keyboard
     * - default_time_zone
     * - text_direction
     * - encoding
     * - enabled
     *
     * @return array hash array of locales
     * @throws Engine_Exception
     */

    public function get_locales()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_locales();

        return $this->locales;
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

        if (! $this->is_loaded)
            $this->_load_locales();

        $code = $this->get_language_code();

        if (isset($this->locales[$code]) && isset($this->locales[$code]['text_direction']))
            $text_direction = $this->locales[$code]['text_direction'];
        else
            $text_direction = self::DEFAULT_TEXT_DIRECTION;

        return $text_direction;
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

        Validation_Exception::is_valid($this->validate_keyboard($keyboard));

        $file = new File(self::FILE_KEYBOARD);

        if ($file->exists()) {
            $file->replace_lines('/^KEYTABLE=/', "KEYTABLE=\"$keyboard\"\n");
        } else {
            $file->create('root', 'root', '0644');
            $file->add_lines("KEYTABLE=\"$keyboard\"\n");
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

        Validation_Exception::is_valid($this->validate_language_code($code));

        $file = new File(self::FILE_I18N);

        if ($file->exists()) {
            $file->replace_lines('/^LANG=/', "LANG=\"$code.UTF-8\"\n");
        } else {
            $file->create('root', 'root', '0644');
            $file->add_lines("LANG=\"$code.UTF-8\"\n");
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

        Validation_Exception::is_valid($this->validate_language_code($code));

        if (! $this->is_loaded)
            $this->_load_locales();

        $this->set_language_code($code);

        if (isset($this->locales[$code]) && isset($this->locales[$code]['default_keyboard']))
            $this->set_keyboard($this->locales[$code]['default_keyboard']);
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

        if (! $this->is_loaded)
            $this->_load_locales();

        foreach ($this->locales as $code => $details) {
            if ($details['default_keyboard'] === $keyboard)
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

        if (! $this->is_loaded)
            $this->_load_locales();

        foreach ($this->locales as $supported_code => $details) {
            if ($supported_code === $code)
                return;
        }

        return lang('base_validate_language_code_invalid');
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Loads locale information.
     *
     * @return array locale information
     */

    protected function _load_locales()
    {
        clearos_profile(__METHOD__, __LINE__);

        include_once clearos_app_base('base') . '/config/locales.php';

        $this->locales = $locales;
        $this->is_loaded = TRUE;
    }
}
