<?php

/**
 * Session login view.
 *
 * The login/logout pages are a bit special, so the HTML IDs here have
 * been standardized for theme developers.
 *
 * @category   ClearOS
 * @package    Base
 * @subpackage Views
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

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('base/session/login');
echo form_header(lang('base_login'), array('id' => 'theme-login-form-header'));

//////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo field_input('username', '', lang('base_username'));
echo field_password('password', '', lang('base_password'));

if (! empty($languages))
    echo field_dropdown('code', $languages, $code, lang('base_language'));

if ($login_failed)
    echo field_view('', $login_failed);

echo form_submit_custom('submit', lang('base_login'), 'high');

if (is_console())
    echo anchor_custom('/app/graphical_console/shutdown', lang('base_exit_to_console'), 'low');

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer(array('id' => 'theme-login-form-footer'));
echo form_close();
