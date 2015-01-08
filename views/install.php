<?php

/**
 * Software install progress view.
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

echo "<div id='software_install_busy' style='display:none;'>";
echo infobox_highlight(lang('base_software_updates_busy'), lang('base_software_update_busy_warning'));
echo "</div>";

echo "<div id='software_install_running' style='display:none;'>";

echo "<div id='info'></div>";

echo "<div id='summary-info' style='width:500px;'>\n";
    echo "<h2>" . lang('base_overall_progress') . "</h2>\n";
    echo progress_bar('overall', array('input' => 'overall'));

    echo "<h2 style='clear: both;'>" . lang('base_operation_progress') . "</h2>\n";
    echo "<div>\n";
    echo progress_bar('progress', array('input' => 'progress'));
    echo "</div>\n";

    echo "<h2 style='clear: both;'>" . lang('base_details') . "</h2>\n";
    echo "<div id='details'></div>\n";
echo "</div>\n";

echo "</div>\n";
