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


$buttons = array(
    form_submit_custom('verify', lang('base_verify_token_and_continue')),
    anchor_cancel('/app/base/session/logout')
);
echo row_open();
echo column_open(2);
echo column_close();
echo column_open(8);
echo form_open('/base/session/two_factor/' . $username);
echo form_header(lang('base_2factor_auth'));

echo field_input('redirect', $redirect, "", FALSE, ['hide_field' => TRUE]);
echo field_input('username', $username, lang('base_account'), TRUE);
echo field_input('token', "", lang('base_2factor_auth_token'));

echo field_button_set($buttons);

echo form_footer();

if ($errmsg)
    echo infobox_warning(lang('base_warning'), $errmsg);

echo infobox_highlight(lang('base_information'),
    lang('base_2factor_protection_enabled') .
    "<div class='text-center' style='padding-top: 20px;'>" .
    form_submit_custom('resend', lang('base_resend_token')) .
    "</div>"
);

echo form_close();
echo column_close();
echo column_open(2);
echo column_close();
echo row_close();
