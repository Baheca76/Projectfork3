<?php
/**
* $Id: filemanager.init.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Filemanager
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

// Load core objects
$core = PFcore::GetInstance();

// New controller
$controller = new PFfileController();

// Get user input
$dir = (int) JRequest::getVar('dir');
$id  = (int) JRequest::getVar('id');
$cid = (int) JRequest::getVar('cid');
$ob  = JRequest::getVar('ob', 'id');
$od  = JRequest::getVar('od', 'ASC');
$files    = JRequest::getVar( 'file', array(), 'files');
$file     = JRequest::getVar( 'file', '', 'files');
$keyword  = JRequest::getVar('keyword');
$mfolders = JRequest::getVar('folder', array());
$mfiles   = JRequest::getVar('file', array());
$mnotes   = JRequest::getVar('note', array());

switch( $core->GetTask() )
{
	default:
	case 'list_directory':	
		$controller->DisplayList($dir, $ob, $od, $keyword);
		break;
		
	case 'list_move':
		$controller->DisplayMove($dir, $mfolders, $mfiles, $mnotes);
		break;	
		
	case 'form_new_folder':
		$controller->DisplayNewFolder($dir);
		break;
		
	case 'form_new_file':
        $controller->DisplayNewFile($dir);
		break;
		
	case 'form_new_note':
        $controller->DisplayNewNote($dir);
		break;
		
	case 'form_edit_folder':
        $controller->DisplayEditFolder($id, $dir);
		break;
		
	case 'form_edit_note':
        $controller->DisplayEditNote($id, $dir);
		break;
		
	case 'form_edit_file':
        $controller->DisplayEditFile($id, $dir);
		break;
		
	case 'task_delete':
        $controller->Delete($dir, $mfolders, $mnotes, $mfiles);
		break;
		
	case 'task_save_folder':
        $controller->SaveFolder($dir);
		break;
		
	case 'task_save_note':
        $controller->SaveNote($dir);
		break;
		
	case 'task_save_file':
        $controller->UploadFiles($files, $dir);
		break;
		
	case 'task_update_folder':
        $controller->UpdateFolder($id, $dir);
		break;
		
	case 'task_update_note':
        $controller->UpdateNote($id, $dir);
		break;
		
	case 'task_update_file':
        $controller->UpdateFile($id, $file, $dir);
		break;
		
	case 'task_download':
        $controller->DownloadFile($id, $dir);
		break;
		
	case 'task_move':
        $controller->Move($dir, $mfolders, $mfiles, $mnotes);
		break;	
		
	case 'display_note':
        $controller->DisplayNote($id, $dir);
		break;
		
	case 'task_save_comment':
		$controller->SaveComment($id, $dir);
		break;
		
	case 'form_edit_comment':
		$controller->DisplayEditComment($id, $dir);
		break;
		
	case 'task_update_comment':
		$controller->UpdateComment($id, $cid, $dir);
		break;

	case 'task_delete_comment':
		$controller->DeleteComment();
		break;
}
?>