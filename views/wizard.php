<?php

/**
 * Wizard intro view.
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

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////
// FIXME: translate

$right = "
<h3>Help and User Guide</h3>
<p>During the install wizard, you will find help and best practices provided as you go through
the configuration steps:</p>

<p align='left'><img src='" . clearos_app_htdocs('base') . "/inline_help.png' alt=''></p>

<p>In addition, you can also follow the links to the User Guide and 
Support found near the top of the page:
<p>
<p align='left'><img src='" . clearos_app_htdocs('base') . "/help_and_support.png' alt=''></p>
";

$left = "
<h3>Thank You</h3>
<p>You are now ready to install apps and integrated cloud services through the ClearCenter Marketplace.
You will find both free a paid apps... blah blah blah.</p>

<h3>Getting Started</h3>
<p>If this is the first
time using ClearOS, the number of apps and services in the Marketplace can be overwhelming.  The
Markeplace wizard guides you through the process of selecting the right features.  If you don't 
like wizards, you can skip this step and jump right into the <a style='background: transparent; border: none; float: none; padding: 0; margin: 0; color: #e1852e;' href='/app/base/wizard/finish'>Marketplace</a>.</p>
" .
"<p align='center'>" .  anchor_custom($first_step, 'Start the Wizard') . "</p>";

echo infobox_highlight(
    'Welcome to the Install Wizard!',
    "<table cellpadding='7'><tr><td valign='top'>$left</td><td valign='top' width='265'>$right</td></tr></table>"
);
