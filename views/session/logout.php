<?php

/**
 * Session logout view.
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

// FIXME: special wrappers for login page - keep them?
echo "<div class='login'>";
echo "<div class='logo-login'></div>";

// FIXME: translate

$login_button = anchor_custom('/app/base/session/login', lang('base_login'), 'high');

echo infobox_highlight(
    lang('base_logout'), 
    lang('base_logout_complete') .
    anchor_custom('/app/base/session/login', lang('base_login'), 'high')
);

echo "</div>";
