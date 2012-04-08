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
$this->lang->load('marketplace');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////
// FIXME: translate
// TODO: hard-coded anchor... fix later.  It should match order in base/libraries/Install_Wizard.php.
// TODO: standardize style for simple <a href..>" links.

echo infobox_highlight(
    'Welcome to Marketplace!',
    "
    
            <div style='background: url(" . clearos_app_htdocs('base') . "/pro-marketplace.png) no-repeat; height:370px; width:674px; margin-left: 15px; margin-top: 15px;'></div>
    
    
    <!--
    <table cellpadding='7'>
		<tr>
			<td width='50%'>
				<h3>Introducing Marketplace (Professional)</h3>
				<p>With the basic configuration under your belt, you are now ready to install apps and integrated cloud services through the ClearCenter Marketplace.  You will find a large selection of both free a paid apps all of which are available to install in a few short steps.</p>

				<h3>Getting Started</h3>
				<p>If this is the first
				time using ClearOS, the number of apps and services in the Marketplace can be overwhelming.  The
				Marketplace wizard guides you through the process of selecting the right features.  If you don't 
				like wizards, you can skip this step and jump right into the <a style='background: transparent; border: none; float: none; padding: 0; margin: 0; color: #e1852e;' href='/app/base/wizard/finish'>Marketplace</a>.</p>
			</td>
			<td valign='top'>
				<p>Hi beta users.  Some graphic will go here to help folks understand Marketplace.</p>
				<p align='center'><img src='" . clearos_app_htdocs('graphical_console') . "/browsers.png' alt=''></p>
				<p align='center'>" .  anchor_custom('/app/base/wizard/test/marketplace_pro', 'Fake Next Button') . "</p>
			</td>
		</tr>
	</table>
	
	-->
	"
);

// Fake nav buttons... normally not here
echo "
<p align='center'>" .
    anchor_custom('/app/base/wizard/test/marketplace_community', 'Fake Previous Button') . " " .
    anchor_custom('/app/base/wizard/test/which_edition', 'Fake Next Button') . "
</p>
";
