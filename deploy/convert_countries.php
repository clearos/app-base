#!/usr/clearos/sandbox/usr/bin/php
<?php

/**
 * Country list converter.
 *
 * This tool converts the lists from https://github.com/umpirsky/country-list/
 * and converts them to the CodeIgniter format.
 *
 * @category   apps
 * @package    base
 * @subpackage scripts
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
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
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

$cldr_dir = '/tmp/cldr';
$input_codes = array(
    'es_CA',
    'de_DE',
    'en_US',
    'es_ES',
    'fa_IR',
    'fr_FR',
    'id_ID',
    'it_IT',
    'my_MM',
    'nl_NL',
    'no',
    'pl_PL',
    'pt_BR',
    'ru_RU',
    'sk_SK',
    'sv_SE',
    'tl_PH',
    'tr_TR',
    'zh_CN',
);


// Load our gold default country list, reverse key
//------------------------------------------------

$gold_list = explode("\n", file_get_contents('countries.php'));
$gold_country_codes = array();

foreach ($gold_list as $line) {
    $matches = array();

    if (preg_match("/^\s+'([A-Z][A-Z])'\s*=>\s*lang\('(.*)'\),/", $line, $matches)) {
        $gold_country_tags[$matches[1]] = $matches[2];
        $gold_country_codes[] = $matches[1];
    }
}

// Load external lists 
//--------------------

foreach ($input_codes as $input_code) {
    $input_file = $cldr_dir . '/' . $input_code . '/country.php';

    if (! file_exists($input_file)) {
        echo "No translations for $input_code?\n";
        continue;
    }

    $import_list = include $input_file;

    $output_array = array();

    foreach ($gold_country_codes as $code) {
        if (isset($import_list[$code]))
            $output_array[] = "\$lang['" . $gold_country_tags[$code] . "'] = '" . preg_replace("/'/", '\\\'', $import_list[$code]) . "';\n";
    }

    sort($output_array);
    $output = implode("", $output_array);
    $output = "<?php\n\n" . $output;

    file_put_contents($cldr_dir . '/' . $input_code . '/base_countries_lang.php', $output);
}
