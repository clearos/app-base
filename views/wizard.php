<?php

/**
 * Wizard intro view.
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
// Content
///////////////////////////////////////////////////////////////////////////////
// TODO: translate
// TODO: move HTML/CSS elements to theme

if (preg_match('/Community/', $this->session->userdata['os_name'])) {
    $sidebar_image = 'community-get-started.png';
    $blurb = "
        Did you know that ClearCenter offers ClearOS Professional which includes professional support, 
        optional hardware appliances with software pre-installed and professional apps &amp; services?
        <a href='http://www.clearcenter.com/Contact-Us/clearcenter-contact-us-1.html' target='_blank'>Contact</a> a solution specialists today.
    ";
} else {
    $sidebar_image = 'pro-get-started.png';
    $blurb = "
        Did you know that ClearCenter offers industry specific solutions to simplify your deployment of ClearOS?  Click
        <a href='http://www.clearcenter.com/Solution/solutions.html' target='_blank'>here</a> to learn more or 
        <a href='http://www.clearcenter.com/Contact-Us/clearcenter-contact-us-1.html' target='_blank'>talk</a> to one of our solution specialists today.
    ";
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('base/wizard', array('id' => 'getting_started'));
echo form_header($this->session->userdata['os_name'] . ' ' . $this->session->userdata['os_base_version']);

echo form_banner(
    "<div style='background: url(" . clearos_app_htdocs('base') . "/$sidebar_image) no-repeat; height:374px; width:682px; margin-left: 15px; margin-top: 15px;'>
        <p style='line-height: 20px; width: 285px; font-size: 13px; position: relative; top: 262px; left: 368px;'>$blurb</p>
    </div>
    "
);

echo form_footer();
echo form_close();
