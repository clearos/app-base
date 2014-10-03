<?php

/**
 * Search view.
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

echo row_open();
echo column_open(4);
$options = array(
    'id' => 'clearos_installed_apps',
    'paginate' => TRUE,
    '2_button_paginate' => TRUE,
    'empty_table_message' => loading('normal', lang('base_searching...'))
);

echo summary_table(
    lang('base_installed_apps'),
    NULL,
    array(lang('base_app_display_name')),
    NULL,
    $options
);

echo column_close();
echo column_open(8);
echo box_open(lang('base_marketplace'), array('id' => 'clearos-marketplace-apps'));
echo box_content('', array('id' => 'content-marketplace-apps', 'class' => 'theme-search-empty clearfix'));
echo box_footer('content-marketplace-apps-loading', NULL, array('loading' => TRUE));
echo box_close();
echo column_close();
echo row_close();

echo row_open();
echo column_open(12);

$options = array(
    'id' => 'clearos_files',
    'paginate' => TRUE,
    'no_action' => TRUE,
    'empty_table_message' => loading('normal', lang('base_searching...'))
);

echo summary_table(
    lang('base_filesystem') . (isset($filesystem_path) ? ' (' . $filesystem_path . ' ' . lang('base_only') . ')' : ''),
    NULL,
    array(lang('base_filename')),
    NULL,
    $options
);
echo column_close();
echo row_close();
// How else to get the query posted back to input form?
if ($query) {
    echo "<script type='text/javascript'>\n";
    echo "  $('#g_search').val('" . $query . "');\n";
    echo "</script>\n";
}
