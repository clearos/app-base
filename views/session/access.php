<?php

///////////////////////////////////////////////////////////////////////////////
//
// Copyright 2010 ClearFoundation
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

echo infobox_highlight("
	<h2>Access Denied</h2> 
	<p>This page will be shown when a user attempts to access an unauthorized page.  This typically
happens when sub-administrator access has been disabled.  A list of permitted pages can be shown
to guide the user to authorized pages (e.g. the user profile page). It can also happen on an
uprade where the administrator has not yet gone through the setup/upgrade wizard.</p>
");

// vim: ts=4 syntax=php
