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


// <h3>" . $this->session->userdata['os_name'] . " " . $this->session->userdata['os_base_version'] . "</h3>
$left = "
<h3>Welcome to the Install Wizard!</h3>
<p>The install wizard is going to take you through the basic steps to get your " . $this->session->userdata['os_name'] . " system up and running.  Once you have the basics all configured, you will be ready to move on to 
Marketplace to select apps and services</p>
";

$help_text = "
<h3>Getting Started</h3>
<p>During the install wizard, you will find help and best practices provided as you go through
the configuration steps.</p>

<p><i>Example Help:</i><Br>
<img src='" . clearos_app_htdocs('base') . "/inline_help.png' alt=''></p>

<p>In addition, you can also follow the links to the User Guide and 
Support found near the top of the page.  Let's get started!</p>
";

$start_wizard = "<p align='center'>" .  anchor_custom($first_step, 'Start the Wizard') . "</p>";

// FIXME: this blurb should change on ClearBOX hardware
$right = "
<h3>Looking for Hardware? Take a Look at ClearBOX...</h3>
<p>Here comes the shameless plug. ClearBOX is a high performance server from ClearCenter specifically designed to run the ClearOS platform. We designed this LInux IT server with ClearOS in mind and frankly, we're proud of our work! ClearBOX delivers leading technology and performance to leverage the strengths of ClearOS.</p>

<p><a style='background: transparent; border: none; float: none; padding: 0; margin: 0; color: #e1852e;' href='http://www.clearcenter.com/clearbox'>Read More...</a></p>


<p align='center'><img src='" . clearos_app_htdocs('base') . "/clearbox-product.jpg' alt='ClearBOX'></p>
";

// 'Welcome to the Install Wizard!',
echo infobox_highlight(
    $this->session->userdata['os_name'] . " " . $this->session->userdata['os_base_version'],
    "<table cellpadding='7' border='0'>
        <tr>
            <td valign='top' colspan='2'>$left</td>
            <td valign='top' width='360' rowspan='3'>$right</td>
        </tr>
        <tr>
            <td valign='top'>$help_text</td>
            <td valign='top'>$help_graphic</td>
        </tr>
        <tr>
            <td valign='top' colspan='2'>$start_wizard</td>
        </tr>
    </table>"
);
