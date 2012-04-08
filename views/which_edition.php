<?php

/**
 * Marketplace banner view.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Views
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2012 ClearCenter
 * @license    http://www.clearcenter.com/Company/terms.html ClearSDN license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');

///////////////////////////////////////////////////////////////////////////////
// Infobox
///////////////////////////////////////////////////////////////////////////////

if ($professional_already_installed) {
    echo infobox_highlight('Thank You', 'blah', array('id' => 'professional_already_installed'));
    return;
}

///////////////////////////////////////////////////////////////////////////////
// Content
///////////////////////////////////////////////////////////////////////////////

// TODO: form width needs to be more flexible.  Hack in width for now.
// TODO: convert image to text
// TODO: translate
// TODO: integrate radio set into theme
// TODO: integrate text size and other hard-coded elements in theme_field_radio_set_item

$banner = "<p align='center' style='width: 680'><img src='" . clearos_app_htdocs('base') . "/thanks.png' alt=''></p>";

$community_label = "<span style='font-size: 13px;'>Install ClearOS Community</span>";
$community_options['image'] = clearos_app_htdocs('base') . '/community_logo.png';
$community_options['orientation'] = 'horizontal';

$professional_label = "<span style='font-size: 13px;'>Install and Evaluate ClearOS Professional</span>";
$professional_options['image'] = clearos_app_htdocs('base') . '/professional_logo.png';
$professional_options['orientation'] = 'horizontal';

$options['orientation'] = 'horizontal';

$read_only = FALSE;

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('base/edition', array('id' => 'edition_form'));
echo form_header(lang('base_select_edition'));

echo form_banner($banner);
echo field_radio_set(
    '',
    array(
        field_radio_set_item('community', 'edition', $community_label, TRUE, $read_only, $community_options),
        field_radio_set_item('professional', 'edition', $professional_label, FALSE, $read_only, $professional_options)
    ),
    array('orientation' => 'horizontal')
);

echo form_footer();
echo form_close();
