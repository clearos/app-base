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

echo infobox_highlight(
    'Which Edition?',
	"
<div style='background: url(" . clearos_app_htdocs('base') . "/thanks.png) no-repeat; height:366px; width:668px; margin-left: 15px; margin-top: 15px; position: relative;'>
		
	  <div style='position: absolute; bottom: 2px; left: 0px; margin: 0; display: block; font-size: 13px;'><input style='height: 15px; margin: 0; margin-right: 16px;' type='radio' name='edition' value='no-firewall' /><span style='top: -1px; position: relative;'> Install ClearOS Community</span></div>
  <div style='position: absolute; bottom: 2px; left: 248px; margin: 0; display: block; font-size: 13px;'><input style='height: 15px; margin-right: 16px;' type='radio' name='edition' value='standalone' /><span style='top: -1px; position: relative;'>Upgrade to <a style='color: #e1852e;' href='http://www.clearcenter.com/Software/clearos-overview.html' target='_blank'>ClearOS Professional</a> and recieve a 30 Day Trial</span></div>
 </form>
	
	</div>
	
"
);

// Fake nav buttons... normally not here
echo "
<p align='center'>" . 
	anchor_custom('/app/base/wizard/test/marketplace_pro', 'Fake Previous Button') . " " .
	anchor_custom('/app/base/wizard/test/network_mode', 'Fake Next Button') . "
</p>
";
