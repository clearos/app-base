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
// System Requirements Warning
///////////////////////////////////////////////////////////////////////////////

$os_name = preg_replace('/ /', '_', $os_name);

$contents = "
    <h2 style='font-size: 1.8em; color: #909090;'>" . lang('base_getting_started') ." </h2>
    <p style='font-size: 1.2em; line-height: 20px;'>The Install Wizard guides you through the steps to get your ClearOS system up and running.  
    After the basics are configured, you'll get a chance to go through the ClearOS Marketplace wizard to 
    install apps.<p>

    <p style='font-size: 1.2em; line-height: 20px;'>If you need assistance with installation or configuration, 
    please review the available help in the wizard.  You can also find more in-depth
    help online:</p>

    <ul>
        <li style='font-size: 1.2em; line-height: 20px;'><a href='http://www.clearcenter.com/redirect/$os_name/$os_base_version/install_guide' target='_blank'>" . lang('base_install_guide') . "</a></li>
        <li style='font-size: 1.2em; line-height: 20px;'><a href='http://www.clearcenter.com/redirect/$os_name/$os_base_version/user_guide' target='_blank'>" . lang('base_user_guide') . "</a></li>
    </ul>
";

if ($memory_warning || $vm_warning) {
    if ($memory_warning) {
        $memory_bullet = "<li style='font-size: 1.2em; line-height: 20px; color: red;'>Inadequate Memory: " . $memory_size . " GB</li>";
        $memory_blurb =  "<p style='font-size: 1.2em; line-height: 20px;'>More memory is recommended.
                <a target='_blank' href='http://www.clearcenter.com/redirect/$os_name/$os_base_version/system_requirements'>More Information</a>.</p>";
    } else {
        $memory_bullet = '';
        $memory_blurb = '';
    }

    if ($vm_warning) {
        $vm_bullet = "<li style='font-size: 1.2em; line-height: 20px; color: red;'>VM Test Image Detected</li>";
        $vm_blurb =  "<p style='font-size: 1.2em; line-height: 20px;'>The default Virtual Machine images are great for testing,
                but we <b>strongly</b> recommend doing a full install for live deployments.</p>";
    } else {
        $vm_bullet = '';
        $vm_blurb = '';
    }

    $contents .= "
        <h2 style='font-size: 1.4em; color: #909090; margin-top: 25px;'>System Check</h2>
        <p style='font-size: 1.2em; line-height: 20px;'>Uh oh.  The following system checks failed:</p>
        <ul>
            $memory_bullet
            $vm_bullet
        </ul>
        $memory_blurb
        $vm_blurb
    ";
}

$contents .= "<p style='font-size: 1.2em; line-height: 20px;'>Click on the <b>Next</b> button to continue.</p>";

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

// TODO: translate
// TODO: move HTML/CSS elements to theme

$blurb = "
    Did you know that ClearCenter offers industry specific solutions to simplify your deployment of ClearOS?  Click
    <a href='http://www.clearcenter.com/Solution/solutions.html' target='_blank'>here</a> to learn more or 
    <a href='http://www.clearcenter.com/Contact-Us/clearcenter-contact-us-1.html' target='_blank'>talk</a> to one of our solution specialists today.
";

echo form_open('base/wizard', array('id' => 'getting_started'));
echo form_header($this->session->userdata['os_name'] . ' ' . $this->session->userdata['os_base_version']);

// default CSS causee grief with paragraph tags, use tables for now.  Sigh.
echo form_banner("
    <table border='0' cellpadding='0' cellspacing='0'>
        <tr>
            <td valign='top'>$contents</td>
            <td width='330'>
                <div style='background: url(" . clearos_app_htdocs('base') . "/get-started.png) no-repeat; height:358px; width:330px; margin: 10px;'>
                <p style='line-height: 20px; font-size: 13px; position: relative; top: 242px; padding: 20px;'>$blurb</p>
                </div>
            </td>
        </tr>
    </table>
    "
);

echo form_footer();
echo form_close();
