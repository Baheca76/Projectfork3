<?php
/**
* $Id: filemanager.controller.php 935 2013-05-14 12:39:24Z eaxs $
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

class PFfileController extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

	public function DisplayList($dir, $ob, $od, $keyword)
	{
	    $user   = PFuser::GetInstance();
		$class  = new PFfilemanagerClass();
		$form   = new PFform();
        $config = PFconfig::GetInstance();

        $wizard = (int) $config->Get('use_wizard');

        $ts1 = array('TITLE', 'DESC', 'DATE', 'AUTHOR', 'ID');
		$ts2 = array('title', 'description', 'cdate', 'author', 'id');
		$ts3 = array('ASC', 'DESC');
		// Sanitize table settings
		if(!in_array($ob, $ts2)) $ob = 'id';
		if(!in_array($od, $ts3)) $od = 'ASC';
		// Create the table
		$table = new PFtable($ts1, $ts2, $ob, $od);

		$workspace = $user->GetWorkspace();
		$folders   = $class->LoadFolderList($dir, $ob, $od, $keyword, $workspace);
		$notes     = $class->LoadNoteList($dir, $ob, $od, $keyword, $workspace);
		$files     = $class->LoadFileList($dir, $ob, $od, $keyword, $workspace);
		$total     = count($folders) + count($notes) + count($files);
		$ws_title  = PFformat::WorkspaceTitle();

		$form->SetBind(true, 'REQUEST');

		$can_move    = $user->Access('list_move', 'filemanager');
		$can_delete  = $user->Access('task_delete', 'filemanager');
		$can_cfolder = $user->Access('form_new_folder', 'filemanager');
		$can_cnote   = $user->Access('form_new_note', 'filemanager');
		$can_cfile   = $user->Access('form_new_file', 'filemanager');

		require_once($this->GetOutput('list_directory.php', 'filemanager'));
	}

	public function DisplayMove($dir = 0, $mfolders, $mfiles, $mnotes)
	{
		$class = new PFfilemanagerClass();
		$user  = PFuser::GetInstance();
		$db    = PFdatabase::GetInstance();
		$form  = new PFform();

        // Validate folder access
        if(count($mfolders)) {
            $ifolders = implode(', ', $mfolders);
            $tfolders = array();

            $query = "SELECT id, author FROM #__pf_folders WHERE id IN($ifolders)";
                   $db->setQuery($query);
                   $rows = $db->loadObjectList();

            if(!is_array($rows)) $rows = array();

            foreach($rows AS $row)
            {
                if(!$user->Access('list_move', 'filemanager', $row->author)) continue;
                $tfolders[] = $row->id;
                unset($row);
            }
            unset($rows);
            $mfolders = $tfolders;
        }

        // Validate note access
        if(count($mnotes)) {
            $inotes = implode(', ', $mnotes);
            $tnotes = array();

            $query = "SELECT id, author FROM #__pf_notes WHERE id IN($inotes)";
                   $db->setQuery($query);
                   $rows = $db->loadObjectList();

            if(!is_array($rows)) $rows = array();

            foreach($rows AS $row)
            {
                if(!$user->Access('list_move', 'filemanager', $row->author)) continue;
                $tnotes[] = $row->id;
                unset($row);
            }
            unset($rows);
            $mnotes = $tnotes;
        }

        // Validate file access
        if(count($mfiles)) {
            $ifiles = implode(', ', $mfiles);
            $tfiles = array();

            $query = "SELECT id, author FROM #__pf_files WHERE id IN($ifiles)";
                   $db->setQuery($query);
                   $rows = $db->loadObjectList();

            if(!is_array($rows)) $rows = array();

            foreach($rows AS $row)
            {
                if(!$user->Access('list_move', 'filemanager', $row->author)) continue;
                $tfiles[] = $row->id;
                unset($row);
            }
            unset($rows);
            $mfiles = $tfiles;
        }

        // Check if any data left
        if(!count($mfolders) && !count($mnotes) && !count($mfiles)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'NOT_AUTHORIZED');
            return false;
        }

		$ob = JRequest::getVar('ob', 'id');
		$od = JRequest::getVar('od', 'ASC');

		$workspace = $user->GetWorkspace();
		$folders   = $class->LoadFolderList($dir, $ob, $od, '', $workspace);
		$total     = count($folders);
		$ws_title  = PFformat::WorkspaceTitle();

		$form->SetBind(true, 'REQUEST');

		$table = new PFtable(array('TITLE', 'DESC', 'DATE', 'AUTHOR', 'ID'),
		                     array('title', 'description', 'cdate', 'author', 'id'),
		                     'id',
		                     'ASC');

		require_once( $this->GetOutput('list_move.php', 'filemanager') );
	}

	public function DisplayNewFolder()
	{
        $config = PFconfig::GetInstance();
		$form   = new PFform();

		$ws_title = PFformat::WorkspaceTitle();
		$attach   = $config->Get('attach_folders', 'filemanager');

		$form->SetBind(true, 'REQUEST');

		require_once($this->GetOutput('form_new_folder.php', 'filemanager'));
	}

	public function DisplayNewFile($dir)
	{
	    require_once($this->GetHelper('filemanager'));

	    $config = PFconfig::GetInstance();
		$form = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');

		$ws_title = PFformat::WorkspaceTitle();
		$attach   = (int) $config->Get('attach_files', 'filemanager');

		$form->SetBind(true, 'REQUEST');

		require_once($this->GetOutput('form_new_file.php', 'filemanager'));
	}

	public function DisplayNewNote($dir = 0)
	{
	    $config = PFconfig::GetInstance();
		$form   = new PFform();
		$editor = JFactory::getEditor();

		$ws_title   = PFformat::WorkspaceTitle();
		$attach     = (int) $config->Get('attach_notes', 'filemanager');
		$use_editor = (int) $config->Get('use_editor', 'filemanager');

		$form->SetBind(true, 'REQUEST');
		require_once($this->GetOutput('form_new_note.php', 'filemanager'));
	}

	public function DisplayEditFolder($id, $dir = 0)
	{
	    $config = PFconfig::GetInstance();
	    $user   = PFuser::GetInstance();
		$class  = new PFfilemanagerClass();
		$form   = new PFform();

		$row = $class->LoadFolder($id);

        // Check if folder exists
		if(!is_object($row)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author access
        if(!$user->Access('form_edit_folder', 'filemanager', $row->author)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'NOT_AUTHORIZED');
            return false;
        }

        $ws_title = PFformat::WorkspaceTitle();
        $attach   = $config->Get('attach_folders', 'filemanager');

		$form->SetBind(true, $row);

		require_once($this->GetOutput('form_edit_folder.php'));
	}

	public function DisplayEditFile($id, $dir = 0)
	{
		$class  = new PFfilemanagerClass();
		$config = PFconfig::GetInstance();
		$user   = PFuser::GetInstance();
		$form   = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$row    = $class->LoadFile($id);

		// Check if file exists
		if(!is_object($row)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author access
        if(!$user->Access('form_edit_file', 'filemanager', $row->author)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'NOT_AUTHORIZED');
            return false;
        }

		$ws_title = PFformat::WorkspaceTitle();
		$attach   = $config->Get('attach_files', 'filemanager');

		$form->SetBind(true, $row);

		require_once($this->GetOutput('form_edit_file.php', 'filemanager'));
	}

	public function DisplayEditNote($id, $dir = 0)
	{
		$class  = new PFfilemanagerClass();
		$form   = new PFform();
		$editor = JFactory::getEditor();
		$config = PFconfig::GetInstance();
		$user   = PFuser::GetInstance();

		$row = $class->LoadNote($id);

		// Check if note exists
		if(!is_object($row)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author access
        if(!$user->Access('form_edit_note', 'filemanager', $row->author)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'NOT_AUTHORIZED');
            return false;
        }

		$ws_title   = PFformat::WorkspaceTitle();
		$attach     = (int) $config->Get('attach_notes', 'filemanager');
		$use_editor = (int) $config->Get('use_editor', 'filemanager');

		$form->SetBind(true, $row);
		require_once($this->GetOutput('form_edit_note.php', 'filemanager'));
	}

	public function DisplayNote($id, $dir = 0)
	{
		$class  = new PFfilemanagerClass();
		$form   = new PFform();
		$editor = JFactory::getEditor();
		$user   = PFuser::GetInstance();

		$row      = $class->LoadNote($id);
		$ws_title = PFformat::WorkspaceTitle();

		// Check if note exists
		if(!is_object($row)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author access
        if(!$user->Access('display_note', 'filemanager', $row->author)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'NOT_AUTHORIZED');
            return false;
        }

		$form->SetBind(true, $row);

		require_once($this->GetOutput('display_note.php', 'filemanager'));
	}

	public function DisplayEditComment($id, $dir)
	{
		$this->DisplayNote($id, $dir);
	}

	public function SaveFolder($dir = 0)
	{
		$class = new PFfilemanagerClass();

		if( !$class->SaveFolder($dir) ) {
			$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_FOLDER_E_SAVE');
			return false;
		}

        $this->SetRedirect("section=filemanager&dir=$dir", 'MSG_FOLDER_S_SAVE');
        return true;
	}

	public function SaveNote($dir = 0)
	{
		$class = new PFfilemanagerClass();

		if( !$class->SaveNote($dir) ) {
			$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_NOTE_E_SAVE');
			return false;
		}

        $this->SetRedirect("section=filemanager&dir=$dir", 'MSG_NOTE_S_SAVE');
        return true;
	}

	public function UploadFiles($files, $dir = 0)
	{
		$class = new PFfilemanagerClass();

		if(!$class->UploadFiles($files, $dir)) {
			$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_FILE_E_UPLOAD');
			return false;
		}

		$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_FILE_S_UPLOAD');
		return true;
	}

	public function SaveComment($id, $dir = 0)
	{
        if(defined('PF_COMMENTS_PROCESS')) {
        	$comments = new PFcomments();
        	$comments->Init('notes', $id);
        	$title   = JRequest::getVar('title');
        	$content = JRequest::getVar('ctext');
        	$user    = PFuser::GetInstance();

            if(!$comments->Save($title, $content, $user->GetId())) {
			    $this->SetRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'MSG_COMMENT_E_SAVE');
			    return false;
		    }
		    else {
			    $this->SetRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'MSG_COMMENT_S_SAVE');
			    return true;
		    }
        }
        else {
        	$this->_core->setRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'MSG_COMMENT_E_SAVE');
        	return false;
        }
	}

	public function UpdateFolder($id, $dir)
	{
		$class = new PFfilemanagerClass();

		if(!$class->UpdateFolder($id)) {
			$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_E_UPDATE');
			return false;
		}

		$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_S_UPDATE');
		return true;
	}

	public function UpdateFile($id, $file, $dir = 0)
	{
		$class = new PFfilemanagerClass();

		if(!$class->UpdateFile($id, $file)) {
			$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_E_UPDATE');
			return false;
		}

        $this->SetRedirect("section=filemanager&dir=$dir", 'MSG_S_UPDATE');
		return true;
	}

	public function UpdateNote($id, $dir = 0)
	{
		$class = new PFfilemanagerClass();

		if(!$class->UpdateNote($id)) {
			$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_E_UPDATE');
			return false;
		}

		$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_S_UPDATE');
		return true;
	}

	public function UpdateComment($id, $cid, $dir = 0)
	{
        if(defined('PF_COMMENTS_PROCESS')) {
        	$comments = new PFcomments();
        	$comments->Init('notes', $id);
        	$title   = JRequest::getVar('title');
        	$content = JRequest::getVar('ctext');

            if(!$comments->Update($title, $content, $cid)) {
			    $this->SetRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'COMMENT_E_UPDATE');
			    return false;
		    }
		    else {
			    $this->SetRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'COMMENT_S_UPDATE');
			    return true;
		    }
        }
        else {
        	$this->SetRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'COMMENT_E_UPDATE');
        	return false;
        }
	}

	public function Delete($dir, $folder, $note, $file)
	{
		$class = new PFfilemanagerClass();

		$folders = $class->DeleteFolders($dir, $folder);

		if($folders === false) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'FM_E_DELFOLDERS');
            return false;
        }
		if(!$class->DeleteNotes($dir, $note, $folders)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'FM_E_DELNOTES');
            return false;
        }
		if(!$class->DeleteFiles($dir, $file, $folders)) {
            $this->SetRedirect("section=filemanager&dir=$dir", 'FM_E_DELFILES');
            return false;
        }

		$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_S_DELETE');
		return true;
	}

	public function DeleteComment()
	{
        $id  = (int) JRequest::getVar('id');
        $cid = (int) JRequest::getVar('cid');
        $dir = (int) JRequest::getVar('dir');

        if(defined('PF_COMMENTS_PROCESS')) {
        	$comments = new PFcomments();
        	$comments->Init('notes', $id);
        	$title   = JRequest::getVar('title');
        	$content = JRequest::getVar('content');

            if(!$comments->Delete($cid)) {
			    $this->SetRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'COMMENT_E_DELETE');
		    }
		    else {
			    $this->SetRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'COMMENT_S_DELETE');
		    }
        }
        else {
        	$this->_core->setRedirect("section=filemanager&dir=$dir&task=display_note&id=$id", 'COMMENT_E_DELETE');
        }
	}

	public function DownloadFile($id, $dir = 0)
	{
		jimport('joomla.filesystem.file');

		$class  = new PFfilemanagerClass();
		$config = PFconfig::GetInstance();

		$row   = $class->LoadFile($id);
		$name = JFile::makeSafe($row->name);
		$prefix1 = "project_".$row->project;
		$prefix2 = $row->prefix;
		$download_path = JPath::clean(JPATH_ROOT.DS.$config->Get('upload_path', 'filemanager').DS.$prefix1.DS.$prefix2.strtolower($name));

		if(file_exists($download_path)) {
            ob_end_clean();
            ob_end_clean();
			header("Content-Type: APPLICATION/OCTET-STREAM");
			header("Content-Length: ".filesize($download_path));
			header("Content-Disposition: attachment; filename=\"$name\";");
			header("Content-Transfer-Encoding: Binary");

			if(function_exists('readfile')) {
			    readfile($download_path);
			}
            else {
                echo file_get_contents($download_path);
            }
			die();
		}
		else {
			$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_FILE_NOT_FOUND');
		}
	}

	public function Move($dir, $mfolders, $mfiles, $mnotes)
	{
		$class = new PFfilemanagerClass();

		if(!$class->Move($dir, $mfolders, $mfiles, $mnotes)) {
			$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_E_MOVE');
			return false;
		}

		$this->SetRedirect("section=filemanager&dir=$dir", 'MSG_S_MOVE');
		return true;
	}
}
?>