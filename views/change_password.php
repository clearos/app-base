<?php

/**
 * Change password view.
 *
 * @category   apps
 * @package    base
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2013 ClearFoundation
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
// Form
///////////////////////////////////////////////////////////////////////////////

if ($password_changed) {
    echo infobox_highlight(lang('base_information'), lang('base_password_was_already_changed'));
    echo "<input type='hidden' id='password_changed' name='password_changed' value='yes'>";
    return;
}

echo form_open('/base/session/change_password', array('autocomplete' => 'off', 'id' => 'change_password_form'));
echo form_header(lang('base_settings'));

echo field_password('password', '', lang('base_new_password'));
echo field_password('verify', '', lang('base_verify'));

echo form_footer();
echo form_close();
