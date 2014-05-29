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
// Themes
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('base_theme'),
    lang('base_vendor'),
    lang('base_version')
);

foreach ($themes as $theme_name => $info) {

    if ($theme_name != $current_theme)
        $buttons = array(
            anchor_edit('/app/base/theme/edit/' . $theme_name),
            anchor_select('/app/base/theme/select/' . $theme_name, lang('base_select'))
        );
    else
        $buttons = array(
            anchor_edit('/app/base/theme/edit/' . $theme_name),
        );

    $item['title'] = $info['title'];
    $item['action'] = '/app/theme/edit/' . $theme_name;
    $item['anchors'] = button_set($buttons);

    $item['details'] = array(
        $info['title'],
        $info['vendor'],
        $info['version']
    );

    $items[] = $item;

}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('theme_available_list'),
    NULL,
    $headers,
    $items,
    NULL 
);
