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

echo infobox_highlight(
    "ClearOS Community " . $this->session->userdata['os_base_version'],

    "
    
    <div style='background: url(" . clearos_app_htdocs('base') . "/community-get-started.png) no-repeat; height:374px; width:670px; margin-left: 15px; margin-top: 15px;'>
        <p style='line-height: 20px; width: 285px; font-size: 13px; position: relative; top: 262px; left: 368px;'>Did you know that ClearCenter offers ClearOS Professional which includes professional support, optional hardware appliances with software pre-installed and professional apps & services? <a style='color: #e1852e' href='http://www.clearcenter.com/Contact-Us/clearcenter-contact-us-1.html' target='_blank'>Contact</a> a solution specialists today.</p>
    
    
    </div>
    
   <!-- 
    <table cellpadding='7' border='0'>
        <tr>
            <td valign='top' colspan='2'>
				<h3>Welcome to the Install Wizard!</h3>
				<p>The install wizard is going to take you through the basic steps to get 
				your " . $this->session->userdata['os_name'] . " system up and running.  
				Once you have the basics all configured, you will be ready to move on to 
				Marketplace to select apps and services.</p>
			</td>
            <td valign='top' width='360' rowspan='3'>
				<h3>Looking for Hardware? Take a Look at ClearBOX...</h3>
				<p>Here comes the shameless plug. ClearBOX is a high performance server from 
				ClearCenter specifically designed to run the ClearOS platform. We designed 
				this Linux IT server with ClearOS in mind and frankly, we're proud of our work! 
				ClearBOX delivers leading technology and performance to leverage the strengths of ClearOS.</p>

				<p><a style='background: transparent; border: none; float: none; padding: 0; margin: 0; color: #e1852e;' target='_blank' href='http://www.clearcenter.com/clearbox'>Read More...</a></p>
				<p align='center'><img src='" . clearos_app_htdocs('base') . "/clearbox-product.jpg' alt='ClearBOX'></p>
			</td>
        </tr>
        <tr>
            <td valign='top'>
				<h3>Getting Started</h3>
				<p>During the install wizard, you will find help and best practices provided as you go through
				the configuration steps.</p>

				<p><i>Example Help:</i><Br>
				<img src='" . clearos_app_htdocs('base') . "/inline_help.png' alt=''></p>

				<p>In addition, you can also follow the links to the User Guide and 
				Support found near the top of the page.  Let's get started!</p>
			</td>
            <td valign='top'>...</td>
        </tr>
        <tr>
            <td valign='top' colspan='2'>
				<p align='center'>" .  anchor_custom('#', 'Start the Install Wizard') . "</p>
			</td>
        </tr>
    </table>
    
    -->
    "
);

// Fake nav buttons... normally not here
echo "
<p align='center'>" .
    anchor_custom('/app/base/wizard/test/getting_started_pro', 'Fake Next Button') . "
</p>
";
