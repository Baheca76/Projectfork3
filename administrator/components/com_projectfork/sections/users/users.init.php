<?php
/**
* $Id: users.init.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Users
* @copyright  Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license    http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License License as published by
* the Free Software Foundation, either version 3 of the License,
* or any later version.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork.  If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

// Load objects
$core = PFcore::GetInstance();

// Load the controller
$controller = new PFusersController();

// Capture user input
$id  = (int) JRequest::getVar('id');
$cid = JRequest::getVar('cid', array());
$import_data = JRequest::getVar('accept_data', array());

switch( $core->GetTask() )
{
	default:
		$controller->DisplayUsers();
		break;
		
	case 'list_accesslvl':
		$controller->DisplayAccessLevels();
		break;
		
	case 'list_requests':
		$controller->DisplayJoinRequests();
		break;	
		
	case 'form_new_accesslvl':
		$controller->DisplayNewAccessLevel();
		break;	
		
	case 'form_edit':
        $controller->DisplayEditUser($id);
		break;
		
	case 'form_edit_accesslvl':
		$controller->DisplayEditAccessLevel($id);
		break;
		
	case 'form_accept_request':
		$controller->DisplayAcceptRequests($cid);
		break;
		
	case 'form_invite':
		$controller->DisplayInvite();
		break;
		
	case 'form_new':
        $controller->DisplayNewUser();
		break;	
		
	case 'task_save':
        $controller->SaveUser();
		break;
		
	case 'task_update':
        $controller->UpdateUser($id);
		break;	
		
	case 'task_save_accesslvl':
		$controller->SaveAccessLevel();
		break;
		
	case 'task_update_accesslvl':
		$controller->UpdateAccessLevel($id);
		break;
		
	case 'task_delete_accesslvl':
		$controller->DeleteAccessLevel($cid);
		break;
		
	case 'task_delete':
		$controller->DeleteUser($cid);
		break;	
		
	case 'task_accept_requests':
		$controller->SaveRequests($import_data);
		break;
		
	case 'task_deny':
		$controller->DenyRequests($cid);
		break;
		
	case 'task_invite':
        $controller->Invite();
		break;	
}
?>