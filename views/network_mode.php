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
    'Network Mode',
	"

<div style='background: url(" . clearos_app_htdocs('base') . "/network-mode.png) no-repeat; height:410px; width:457px; margin-left: 15px; margin-top: 0px; position: relative;'>
	            
 <form style='margin:0;'>
  <input style='position: absolute; top: 9px; left: 0px; margin: 0; display: block;' type='radio' name='mode' value='gateway' /> 
  <input style='position: absolute; top: 150px; left: 0px; margin: 0; display: block;' type='radio' name='mode' value='no-firewall' /> 
  <input style='position: absolute; top: 290px; left: 0px; margin: 0; display: block;' type='radio' name='mode' value='standalone' />
 </form> 
</div>
	
	
	
	
	"
);

// Fake nav buttons... normally not here
echo "
<p align='center'>" . 
	anchor_custom('/app/base/wizard/test/which_edition', 'Fake Previous Button') . " " .
	anchor_custom('/app/network/iface', 'Fake Next Button') . "
</p>
";
