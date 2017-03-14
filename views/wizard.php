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
    <p style='font-size: 1.2em; line-height: 20px;'>" . lang('base_getting_started_intro') . "</p>
    <p style='font-size: 1.2em; line-height: 20px;'>" . lang('base_getting_started_intro_part2') . "</p>
    <ul>
        <li style='font-size: 1.2em; line-height: 20px;'>" . anchor_custom("https://www.clearos.com/resources/documentation/clearos/content:en_us:7_first_boot_wizard", lang('base_install_guide'), "link-only", array('target' => '_blank')) . "</li>
        <li style='font-size: 1.2em; line-height: 20px;'>" . anchor_custom("https://www.clearos.com/resources/documentation/clearos/index:userguide7", lang('base_user_guide'), "link-only", array('target' => '_blank')) . "</li>
    </ul>
";

if ($memory_warning) {
    if ($memory_warning) {
        $memory_bullet = "<li style='font-size: 1.2em; line-height: 20px; color: red;'>" . lang('base_inadequate_memory') . " - " . $memory_size . " " . lang('base_gigabytes') . "</li>";
        $memory_blurb =  "<p style='font-size: 1.2em; line-height: 20px;'>" . lang('base_more_memory_recommended') . "
                <a target='_blank' href='https://www.clearos.com/resources/documentation/clearos/content:en_us:7_b_system_requirements'>" . lang('base_details') . "</a>.</p>";
    } else {
        $memory_bullet = '';
        $memory_blurb = '';
    }

    $contents .= "
        <h2 style='font-size: 1.4em; color: #909090; margin-top: 25px;'>" . lang('base_system_check') . "</h2>
        <p style='font-size: 1.2em; line-height: 20px;'>" . lang('base_system_check_failed') . "</p>
        <ul>
            $memory_bullet
        </ul>
        $memory_blurb
    ";
}

$contents .= "<p style='font-size: 1.2em; line-height: 20px;'>" . lang('base_click_next') . "</p>";

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

$blurb = "
    <div style='padding-right: 120px;'>" . lang('base_clearos_blurb') . "</div><br>
    <ul>
        <li>" . anchor_custom("https://www.clearos.com", lang('base_details'), "link-only", array('target' => '_blank')) . "</li>
        <li>" . anchor_custom("https://www.clearcenter.com/community/contact", lang('base_contact_us'), "link-only", array('target' => '_blank')) . "</li>
    <ul>
";

echo form_open('base/wizard', array('id' => 'getting_started'));
echo form_header($this->session->userdata['os_name'] . ' ' . $this->session->userdata['os_base_version']);

$banner_contents = 
    row_open() .
        column_open(7) .
        $contents .
        column_close() .
        column_open(5) .
        box_open(lang('base_did_you_know')) .
        box_content(image('clearcenter.png', array('class' => 'pull-right')) . $blurb) .
        box_close() .
        column_close() .
    row_close()
;
  
echo form_banner($banner_contents);

echo form_footer();
echo form_close();
