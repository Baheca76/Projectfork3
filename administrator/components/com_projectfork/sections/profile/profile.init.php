<?php
/**
* $Id: profile.init.php 837 2010-11-17 12:03:35Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2009 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License,
* or any later version.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public License
* along with Projectfork.  If not, see <http://www.gnu.org/licenses/lgpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

// Load objects
$load = PFload::GetInstance();
$core = PFcore::GetInstance();

// Get possible user input
$id = (int) JRequest::GetVar('id');

// load the controller
require_once( $load->Section('profile.controller.php') );
$controller = new PFprofileController();

// Decide what to do
switch($core->GetTask())
{
	default:	
		$controller->DisplayEdit();
		break;
		
	case 'display_details':
		$controller->DisplayDetails($id);
		break;
			
	case 'task_update':
        $controller->Update();
		break;
}

// Unset objects
unset($load,$core,$controller);
?>