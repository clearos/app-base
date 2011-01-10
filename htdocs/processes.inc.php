<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2008 Point Clark Networks.
//
///////////////////////////////////////////////////////////////////////////////
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
///////////////////////////////////////////////////////////////////////////////

require_once("../../gui/Webconfig.inc.php");
require_once("../../api/ProcessManager.class.php");
require_once(GlobalGetLanguageTemplate(__FILE__));

///////////////////////////////////////////////////////////////////////////////
//
// Header
//
///////////////////////////////////////////////////////////////////////////////

WebAuthenticate();

///////////////////////////////////////////////////////////////////////////////
//
// Main
//
///////////////////////////////////////////////////////////////////////////////

DisplayProcessList();

///////////////////////////////////////////////////////////////////////////////
// F U N C T I O N S
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
//
// DisplayProcessList()
//
///////////////////////////////////////////////////////////////////////////////

function DisplayProcessList()
{
	try {
		$processes = new ProcessManager();
		$top = $processes->GetRawData();
	} catch (Exception $e) {
		return;
	}
	
	$g_title = isset($_POST['title']) ? $_POST['title'] : '';
	$g_idle = isset($_POST['idle']) ? $_POST['idle'] : '';
	$g_sort = isset($_POST['sort']) ? $_POST['sort'] : '';
	$g_fcmd = isset($_POST['fcmd']) ? $_POST['fcmd'] : '';
	
	$pid = 0;
	$user = 1;
	$time = 2;
	$cpu_percent = 3;
	$memory_percent = 4;
	$size = 5;
	$tt = 6;
	$command = 7;
	$show_idle = false;
	
	if ($g_idle == 1)
		$show_idle = true;
	
	$sort_key = $g_sort;
	
	$first = true;
	$rows = array();

	while (list($key, $val) = each($top))
	{
		$val = trim($val);
		$ar = preg_split("/[\s,]+/", $val);
		$k = 0;
		
		if ($first) 
			$output_line = "<tr class='mytableheader' id='pcnheader'>";
		else
			$output_line = "<tr class='%class%'>";			
		
		$full_command = "";
		$key = "";
		$cpupct = "";
		$cmd = "";
	
		while ($k < count($ar)) {
			$v = $ar[$k];
			if ($k == $cpu_percent)  $cpupct = $v;
			else if ($k == $command) $cmd = $v;
	
			$max_cnt = 8;
			if ($g_fcmd == 1)
				$max_cnt = 7;
			
			if ($first) {
				if ($k < 8) {
					$v_id = str_replace("%", "", $v);
					
					switch ($k) {
						case 0:
							$v = str_replace("PID", PROCESS_LANG_ID, $v);
							$output_line .= "<td valign='top' align='left' class='mov'>&nbsp;</td>";
							$output_line .= "<td valign='top' align='left' class='mov' id='$v_id' onclick='sortBy($k);'>$v</td>";
							break;
						case 1:
							$v = str_replace("USER", PROCESS_LANG_OWNER, $v);
							$output_line .= "<td valign='top' align='left' class='mov' id='$v_id' onclick='sortBy($k);'>$v</td>";
							break;
						case 2:
							$v = str_replace("TIME", PROCESS_LANG_RUNNING, $v);
							$output_line .= "<td valign='top' align='left' class='mov' id='$v_id' onclick='sortBy($k);'>$v</td>";
							break;
						case 3:
							$v = str_replace("%CPU", PROCESS_LANG_CPU, $v);
							$output_line .= "<td valign='top' align='right' class='mov' id='$v_id' onclick='sortBy($k);'>$v</td>";
							break;
						case 4:
							$v = str_replace("%MEM", PROCESS_LANG_MEMORY, $v);
							$output_line .= "<td valign='top' align='right' class='mov' id='$v_id' onclick='sortBy($k);'>$v</td>";
							break;
						case 5:
							$v = str_replace("SZ", PROCESS_LANG_SIZE, $v);
							$output_line .= "<td valign='top' align='right' class='mov' id='$v_id' onclick='sortBy($k);'>$v</td>";
							break;
						case 6:
							break;
						case 7:
							$v = str_replace("COMMAND", PROCESS_LANG_COMMAND, $v);
							$output_line .= "<td valign='top' align='left' class='mov' id='$v_id' onclick='sortBy($k);'>$v</td>";
							break;
						default:
							break;
					}				
				}
			} else {
				if ($k < $max_cnt) {
					switch ($k) {
						case 0:
							$output_line .= "<td valign='top'><input type='checkbox' name='id[]' value='$v'></td>";
							$output_line .= "<td valign='top'>$v</td>";
							break;
						case 3:
							$output_line .= "<td valign='top' align='right' nowrap>$v %</td>";
							break;
						case 4:
							$output_line .= "<td valign='top' align='right' nowrap>$v %</td>";
							break;
						case 5:
							$output_line .= "<td valign='top' align='right' nowrap>".CalculateFileSize($v)."</td>";
							break;
						case 6:
							break;
						default:
							$output_line .= "<td valign='top'>$v</td>";
							break;
					}				
				}
			}
			
			if ($k > 7)
				$full_command .= "$v ";
			
			if ($k == $sort_key)
				$key = $v;

			$k++;
		}
	
		if (!$first) {
			if ($g_fcmd == 1)
				$output_line .= "<td>$full_command</td></tr>\n";
			else
				$output_line .= "</tr>\n";
		}
		
		if ($first) {
			WebTableOpen("<span id='sortby'>$g_title</span>", "100%");
			echo "$output_line";
		} else {
			if (($show_idle == true) || ($cpupct != "0" && $cpupct != "0.0" && $cpupct != "0.00")) {
				if ($cmd != "ps.sh" && $full_command != "ps -eo pid,user,time,%cpu,%mem,sz,tty,ucomm,command") {
					$oldval = isset($rows[$key]) ? $rows[$key] : "";
					$rows[$key] = $output_line . $oldval;
				}
			}
		}
		
		$first = false;
	}
	
	if (count($rows) > 0) {
		krsort($rows);
		reset($rows);

		while (list($key,$val) = each($rows))
			echo $val;
	}

	WebTableClose("100%");
}

///////////////////////////////////////////////////////////////////////////////
//
// CalculateFileSize
//
///////////////////////////////////////////////////////////////////////////////

function CalculateFileSize($bytes)
{
	$decimals = 1;
	  
	$units = array('1152921504606846976' => 'EB', /* Exa Byte  10^18 */
				   '1125899906842624'	=> 'PB', /* Peta Byte 10^15 */
				   '1099511627776'	   => 'TB',
				   '1073741824'		  => 'GB',
				   '1048576'			 => 'MB',
				   '1024'				=> 'KB'
				   );
	  
	if ($bytes <= 1024)
		return $bytes . " Bytes";
		  
	foreach ($units as $base => $title) {
		if (floor($bytes / $base) != 0)
			return number_format($bytes / $base, $decimals, ".", "'") . ' ' . $title;
	}
}

// vim: syntax=php ts=4
?>
