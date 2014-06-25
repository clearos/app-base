<?php

/**
 * Shutdown/restart view.
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

$buttons = array(form_submit_select('submit', 'high', array('id' => 'confirm-action')));

echo form_open('base/shutdown');
echo form_header(lang('base_shutdown_restart'));

echo field_dropdown('action', $actions, $action, lang('base_action'), FALSE);
echo field_button_set($buttons);

echo form_footer();
echo form_close();
echo modal_confirm(
    lang('base_confirmation_required'),
    lang('base_confirm_action') . ": <span id='action-selected'></span>?",
    "dashboard"
);
echo "<script type='text/javascript'>\n";
echo "  $(document).ready(function() {";
echo "    $('#modal-confirm').on('shown.bs.modal', function (e) {";
echo "      $('#action-selected').html($('#action option:selected').text());\n";
echo "    });";
echo "  });";
echo "</script>\n";
