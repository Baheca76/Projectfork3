<?php
/**
* $Id: board.init.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Board
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
$load = PFload::GetInstance();
$core = PFcore::GetInstance();

// Get user input
$id  = (int) JRequest::getVar('id');
$rid = (int) JRequest::getVar('rid');
$cid = JRequest::getVar('cid', array(), 'array');

// Load the controller
require_once($load->Section('board.controller.php', 'board'));
$controller = new PFboardController();

switch($core->GetTask())
{
	default:
	case 'list_topics':	
		$controller->DisplayList();
		break;
		
	case 'display_details':
		$controller->DisplayDetails($id);
		break;	
		
	case 'form_new_topic':
		$controller->DisplayNewTopic();
		break;
		
	case 'form_edit_topic':
		$controller->DisplayEditTopic($id);
		break;
		
	case 'form_edit_reply':
		$controller->DisplayEditReply($id, $rid);
		break;	
		
	case 'task_save_topic':
		$controller->SaveTopic();
		break;
		
	case 'task_save_reply':
		$controller->SaveReply($id);
		break;	
			
	case 'task_update_topic':
		$controller->UpdateTopic($id);
		break;
		
	case 'task_update_reply':
		$controller->UpdateReply($id, $rid);
		break;	
		
	case 'task_delete_topic':
        $controller->DeleteTopic($id);
		break;
		
	case 'task_delete_reply':
		$controller->DeleteReply($id, $rid);
		break;
		
	case 'task_subscribe':
		$controller->Subscribe($cid);
		break;	
		
	case 'task_unsubscribe':
		$controller->Unsubscribe($cid);
		break;
}
unset($core,$load,$controller);
?>