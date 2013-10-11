<?php
/**
* $Id: projects.init.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Projects
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

// Capture possible user input
$id    = (int) JRequest::GetVar('id');
$cid   = JRequest::getVar('cid', array());
$hash  = JRequest::getVar('iid');
$email = JRequest::getVar('email');

// Load the controller
$controller = new PFprojectsController();

// Decide what to do
switch( $core->GetTask() )
{
	default:  // Show the project list
		$controller->DisplayList();
		break;
		
	case 'form_new': // Form - Create new project
		$controller->DisplayNew();
		break;
		
	case 'form_edit': // Form - Edit a project
        $controller->DisplayEdit($id);
		break;
		
	case 'display_details': // Display project details
        $controller->DisplayDetails($id);
		break;
		
	case 'task_save': // Save a project
		$controller->Save();
		break;
		
	case 'task_delete': // Delete a project
		$controller->Delete($cid);
		break;	
		
	case 'task_update': // Update a project
        $controller->Update($id);
		break;
			
	case 'task_copy': // Copy a project
        $controller->Copy($cid);
		break;
		
	case 'task_request_join':
	    $user = PFuser::GetInstance();
		$controller->RequestJoin($id, $user->GetId());
		unset($user);
		break;
		
	case 'task_archive': // Archive projects
		$controller->Archive($cid);
		break;
		
	case 'task_activate': // Activate projects
		$controller->Activate($cid);
		break;
		
	case 'task_approve': // Approve projects
        $controller->Approve($cid);
		break;	
		
	case 'accept_invite': // Accept invitation
		$controller->AcceptInvitation($hash, $email);
		break;
		
	case 'decline_invite': // Decline invitation
		$controller->DeclineInvitation($hash, $email);
		break;   	
}
unset($core,$controller);
?>