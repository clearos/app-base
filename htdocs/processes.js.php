<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2008-2011 ClearFoundation
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

define('DEFAULT_REFRESH', "2500");

header('Content-Type:application/x-javascript');
?>

$(document).ready(function() {
        $.ajax({
            url: '/app/base/processes/get_data_FIXME',
            method: 'GET',
            dataType: 'html',
            success : function(html) {
                $("#result").html(html);
            },
        });

    $("#sync").click(function(){
        $("#result").html("whirylgig...");

        $.ajax({
            url: 'date/sync',
            method: 'GET',
            dataType: 'html',
            success : function(html) {
                $("#result").html(html);
            },
        });
    });
});


// TODO: migrate this to YUI

/* Simple AJAX Code-Kit (SACK) */
/* 2005 Gregory Wild-Smith */
/* www.twilightuniverse.com */
/* Software licenced under a modified X11 licence, see documentation or authors website for more details */

function sack(file){
	this.AjaxFailedAlert = "Your browser does not support the enhanced functionality of this website, and therefore you will have an experience that differs from the intended one.\n";
	this.requestFile = file;
	this.method = "POST";
	this.URLString = "";
	this.encodeURIString = true;
	this.execute = false;

	this.onLoading = function() { };
	this.onLoaded = function() { };
	this.onInteractive = function() { };
	this.onCompletion = function() { };

	this.createAJAX = function() {
		try {
			this.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (err) {
				this.xmlhttp = null;
			}
		}
		if(!this.xmlhttp && typeof XMLHttpRequest != "undefined")
			this.xmlhttp = new XMLHttpRequest();
		if (!this.xmlhttp){
			this.failed = true;
		}
	};
	
	this.setVar = function(name, value){
		if (this.URLString.length < 3){
			this.URLString = name + "=" + value;
		} else {
			this.URLString += "&" + name + "=" + value;
		}
	}
	
	this.encVar = function(name, value){
		var varString = encodeURIComponent(name) + "=" + encodeURIComponent(value);
	return varString;
	}
	
	this.encodeURLString = function(string){
		varArray = string.split('&');
		for (i = 0; i < varArray.length; i++){
			urlVars = varArray[i].split('=');
			if (urlVars[0].indexOf('amp;') != -1){
				urlVars[0] = urlVars[0].substring(4);
			}
			varArray[i] = this.encVar(urlVars[0],urlVars[1]);
		}
	return varArray.join('&');
	}
	
	this.runResponse = function(){
		eval(this.response);
	}
	
	this.runAJAX = function(urlstring){
		this.responseStatus = new Array(2);
		if(this.failed && this.AjaxFailedAlert){
			alert(this.AjaxFailedAlert);
		} else {
			if (urlstring){
				if (this.URLString.length){
					this.URLString = this.URLString + "&" + urlstring;
				} else {
					this.URLString = urlstring;
				}
			}
			if (this.encodeURIString){
				var timeval = new Date().getTime();
				this.URLString = this.encodeURLString(this.URLString);
				this.setVar("rndval", timeval);
			}
			if (this.element) { this.elementObj = document.getElementById(this.element); }
			if (this.xmlhttp) {
				var self = this;
				if (this.method == "GET") {
					var totalurlstring = this.requestFile + "?" + this.URLString;
					this.xmlhttp.open(this.method, totalurlstring, true);
				} else {
					this.xmlhttp.open(this.method, this.requestFile, true);
				}
				if (this.method == "POST"){
  					try {
						this.xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded')
					} catch (e) {}
				}

				this.xmlhttp.send(this.URLString);
				this.xmlhttp.onreadystatechange = function() {
					switch (self.xmlhttp.readyState){
						case 1:
							self.onLoading();
						break;
						case 2:
							self.onLoaded();
						break;
						case 3:
							self.onInteractive();
						break;
						case 4:
							self.response = self.xmlhttp.responseText;
							self.responseXML = self.xmlhttp.responseXML;
							self.responseStatus[0] = self.xmlhttp.status;
							self.responseStatus[1] = self.xmlhttp.statusText;
							self.onCompletion();
							if(self.execute){ self.runResponse(); }
							if (self.elementObj) {
								var elemNodeName = self.elementObj.nodeName;
								elemNodeName.toLowerCase();
								if (elemNodeName == "input" || elemNodeName == "select" || elemNodeName == "option" || elemNodeName == "textarea"){
									self.elementObj.value = self.response;
								} else {
									self.elementObj.innerHTML = self.response;
								}
							}
							self.URLString = "";
						break;
					}
				};
			}
		}
	};

this.createAJAX();
}

<?php
echo '
// TODO: Ugly lable hacks below:
// _setLabel for YUI
// .value = for non-YUI

function loopIt() {	
	var buttons = document.getElementsByName("PauseButton");

	// Ouch... javascript/YUI/non-YUI causing grief
	var killbuttons = document.getElementsByTagName("button");

	if (paused == "false") {
		paused = "true";
		clearInterval(tid);
		try { oButton3._setLabel("'. WEB_LANG_CONTINUE .'"); } catch (e) { }
		try { buttons[0].value = "'. WEB_LANG_CONTINUE .'"; } catch (e) { }
		killbuttons[3].disabled = false;
	} else {
		paused = "false";
		tid=setInterval("getData()", '. DEFAULT_REFRESH .');
		try { oButton3._setLabel("'. WEB_LANG_PAUSE .'"); } catch (e) { }
		try { buttons[0].value = "'. WEB_LANG_PAUSE .'"; } catch (e) { }
		killbuttons[3].disabled = true;
	}
}

function toggleIdle() {
	var buttons = document.getElementsByName("IdleButton");
	if (myidle == "1") {
		myidle = "0";
		try { oButton1._setLabel("'. WEB_LANG_SHOW_IDLE .'"); } catch (e) { }
		try { buttons[0].value = "'. WEB_LANG_SHOW_IDLE .'"; } catch (e) { }
		getData();
	} else {
		myidle = "1";
		try { oButton1._setLabel("'. WEB_LANG_HIDE_IDLE .'"); } catch (e) { }
		try { buttons[0].value = "'. WEB_LANG_HIDE_IDLE .'"; } catch (e) { }
		getData();
	}
}

function toggleFcmd() {
	var btn = document.getElementsByName("CommandButton");
	if (myfcmd == "1") {
		myfcmd = "0";
		try { oButton2._setLabel("'. WEB_LANG_FULL_CMD .'"); } catch (e) { }
		try { buttons[0].value = "'. WEB_LANG_FULL_CMD .'"; } catch (e) { }
		getData();
	} else {
		myfcmd = "1";
		try { oButton2._setLabel("'. WEB_LANG_ONLY_CMD .'"); } catch (e) { }
		try { buttons[0].value = "'. WEB_LANG_ONLY_CMD .'"; } catch (e) { }
		getData();
	}
}

function getData() {
	var mydata = "sort=" + mysort + "&idle=" + myidle + "&fcmd=" + myfcmd + "&title=" + mytitle;
	ajax = new sack("processes.inc.php");
	ajax.element = "topdiv";
	ajax.runAJAX(mydata);
}

function sortBy(i) {
	var btn = document.getElementById("sortby");
	mysort = i;
	getData();
}

function Initialize()
{
	getData();
	loopIt();
}

var paused = "true";
var mytitle = "' . WEB_LANG_PAGE_TITLE . '";
var mysort = "3";
var myidle = "0";
var myfcmd = "0";
 
YAHOO.util.Event.onContentReady("topdiv", Initialize);
';

// vim: ts=4 syntax=javascript
?>
