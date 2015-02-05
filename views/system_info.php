<?php

/**
 * System Info view.
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
$this->lang->load('registration');

echo row_open();
echo column_open(7);

echo form_open('base/system_info');
echo form_header(lang('base_system_information'));
echo field_input('name', $name, lang('base_operating_system'), TRUE);
echo field_input('version', $version, lang('base_version'), TRUE);
echo field_input('registered', $registered, lang('registration_registered'), TRUE);

echo form_footer();
echo form_close();

echo column_close();
echo column_open(5);
// TOD - FIXME
echo image('get-started.png');
echo column_close();
echo row_close();
