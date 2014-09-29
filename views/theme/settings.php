<?php

/**
 * Summary view.
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

///////////////////////////////////////////////////////////////////////////////
// Form handler
///////////////////////////////////////////////////////////////////////////////

$buttons = array(
    form_submit_update('submit'),
    anchor_cancel('/app/base')
);

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('base/theme/edit/' . $name);
echo form_header($metadata['title']);

echo fieldset_header(lang('base_information'));
echo field_info('name', lang('base_theme') , $metadata['title']);
echo field_info('vendor', lang('base_vendor') , $metadata['vendor']);
echo field_info('license', lang('base_license') , $metadata['license']);
if (!empty($metadata['credits'])) {
    foreach ($metadata['credits'] as $credit)
        $credits .= "<div>" . $credit['contact'] . " (<a href='" . $credit['url'] . "' target='_blank'>Website</a>)</div>";
    echo field_info('credits', lang('base_credits'), $credits);
}

if (!empty($metadata['settings'])) {
    echo fieldset_header(lang('base_settings'));
    foreach ($metadata['settings'] as $field_name => $setting) {
        if ($setting['type'] == 'dropdown')
            echo field_dropdown('options[' . $field_name . ']', $setting['options'], $theme_settings[$field_name], lang($setting['lang_tag']), FALSE);
        if ($setting['type'] == 'color')
            echo field_color('options[' . $field_name . ']', $theme_settings[$field_name], lang($setting['lang_tag']), FALSE);
    }

    echo field_button_set($buttons);
} else {
    echo field_button_set(array(anchor_cancel('/app/base')));
}

echo form_footer();
echo form_close();
