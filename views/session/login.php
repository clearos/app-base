<?php

/**
 * Session login view.
 *
 * The login/logout pages are a bit special, so the HTML IDs here have
 * been standardized for theme developers.
 *
 * @category   apps
 * @package    base
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/base/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');

// TODO: Aaron can improve the look and feel of the IP address ...
$ip_extras = ($connect_ip) ? ' @ ' . $connect_ip : '';

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('base/session/login/' . $redirect);
echo form_header(lang('base_login'), array('id' => 'theme-login-form-header'));

//////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo field_input('clearos_username', '', lang('base_username'));
echo field_password('clearos_password', '', lang('base_password'));

if (count($languages) > 1)
    echo field_dropdown('code', $languages, $code, lang('base_language'));

if ($ip_extras)
    echo field_view('', "<span style='color: #666666'>" . $ip_extras . "</span>");

if ($login_failed)
    echo field_view('', $login_failed);

echo theme_field_button_set(
    array(form_submit_custom('submit', lang('base_login'), 'high'))
);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer(array('id' => 'theme-login-form-footer'));
echo form_close();
