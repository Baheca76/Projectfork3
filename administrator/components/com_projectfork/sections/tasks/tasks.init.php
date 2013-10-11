<?php
/**
* $Id: tasks.init.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Tasks
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

// Setup controller
$controller = new PFtasksController();

// Get user input
$id       = (int) JRequest::getVar('id');
$apply    = (int) JRequest::getVar('apply');
$ids      = JRequest::getVar('ordering', array(), 'default', 'array');
$mids     = JRequest::getVar('mordering', array(), 'default', 'array');
$progress = JRequest::getVar('progress', array(), 'default', 'array');
$cid      = JRequest::getVar('cid', array(), 'default', 'array');
$cid2     = JRequest::getVar('cid', 0);
$mid      = JRequest::getVar('mid', array(), 'default', 'array');

switch($core->GetTask())
{
	case 'list_tasks':
	default:
		$controller->DisplayList();
		break;
		
	case 'form_new_task':
        $controller->DisplayNewTask();
		break;
		
	case 'form_new_milestone':
		$controller->DisplayNewMilestone();
		break;	
		
	case 'form_edit_task':
        $controller->DisplayEditTask($id);
		break;
		
	case 'form_edit_milestone':
		$controller->DisplayEditMilestone($id);
		break;	
		
	case 'display_details':
        $controller->DisplayDetails($id);
		break;
		
	case 'task_save_task':
		$controller->SaveTask();
		break;
		
	case 'task_save_milestone':
		$controller->SaveMilestone();
		break;	
		
	case 'task_update_task':
        $controller->UpdateTask($id, $apply);
		break;
		
	case 'task_update_milestone':
		$controller->UpdateMilestone($id);
		break;
		
	case 'task_update_progress':
		$controller->UpdateProgress($id, $progress);
		break;	
			
	case 'task_delete':
        $controller->Delete($cid, $mid);
		break;
		
	case 'task_copy':
		$controller->Copy($cid, $mid);
		break;
		
	case 'task_reorder':
		$controller->ReOrder($ids, $mids);
		break;
		
	case 'task_save_comment':
		$controller->SaveComment($id);
		break;
		
	case 'form_edit_comment':
		$controller->DisplayEditComment($id);
		break;
		
	case 'task_update_comment':
		$controller->UpdateComment($id, $cid2);
		break;

	case 'task_delete_comment':
		$controller->DeleteComment($id, $cid2);
		break;		
}
unset($core,$controller);
?>