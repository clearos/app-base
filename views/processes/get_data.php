<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2003-2010 ClearFoundation
//
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
//////////////////////////////////////////////////////////////////////////////
// FIXME: translate 

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
	'Process ID',
	'Owner',
	'Running',
	'CPU',
	'Memory',
	'Size',
	'Commmand'
);

///////////////////////////////////////////////////////////////////////////////
// Anchors 
///////////////////////////////////////////////////////////////////////////////

$anchors = array();

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

array_shift($processes);

foreach ($processes as $raw_data) {

	$data = preg_split('/\s+/', trim($raw_data));

	$buttons = array(anchor_delete('/app/dhcp/subnets/delete/' . $interface));

	$item['title'] = "$interface / " .  $subnetinfo['network'];
	$item['action'] = $action;
	$item['anchors'] = button_set($buttons);
	$item['details'] = array(
		$data[0],
		$data[1],
		$data[2],
		$data[3],
		$data[4],
		$data[5],
		$data[6],
		$data[7],
	);

	$items[] = $item;
}

sort($items);

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
	lang('dhcp_subnets'),
	$anchors,
	$headers,
	$items
);

// vim: ts=4 syntax=php
