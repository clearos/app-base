<?php

/**
 * Settings view.
 *
 * @category   apps
 * @package    devel
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
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
$this->lang->load('language');

echo "<div id='reload_bar' class='theme-hidden'>";
echo "<h4>" . lang('base_reloading_webconfig') . "</h4>\n";
echo progress_bar('webconfig_reload', array('input' => 'webconfig_reload'));
echo "</div>";

///////////////////////////////////////////////////////////////////////////////
// Form handler
///////////////////////////////////////////////////////////////////////////////

// Sort by value instead of language code.
asort($languages);

if ($form_type === 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/base')
    );
} else {
    $read_only = TRUE;
    $buttons = array(
        anchor_edit('/app/base/settings/edit')
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('base/settings/edit');
echo form_header(lang('base_settings'));

echo field_dropdown('ssl_certificate', $ssl_certificate_options, $ssl_certificate, lang('base_ssl_certificate'), $read_only);
echo field_dropdown('code', $languages, $code, lang('language_default_system_language'), $read_only);
if ($form_type === 'edit')
    echo field_checkbox('update_session', $update_session, lang('base_update_your_current_session'), $read_only);
echo field_button_set($buttons);

echo form_footer();
echo form_close();
