<?php
/**
* $Id: filemanager.class.php 932 2013-04-06 04:00:39Z eaxs $
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

class PFfilemanagerClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

	public function LoadFolderList($dir = 0, $ob = 'id', $od = 'ASC', $keyword = '', $workspace = 0)
	{
	    $db = PFdatabase::GetInstance();

		if($keyword) {
		    $sub_dirs   = $this->LoadFolderChildren((int) $dir);
            $sub_dirs[] = (int) $dir;

            $keyword    = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

            $filter = " \n AND t.parent_id IN(" . implode(',', $sub_dirs) . ") ";
			$filter .= "\n AND (f.title LIKE ".$keyword." OR f.description LIKE ".$keyword.")";
		}
        else {
            $filter = " \n AND t.parent_id = ".$db->quote($dir);
        }

		$query = "SELECT f.*, u.name, u.id AS uid FROM #__pf_folders AS f"
		       . "\n RIGHT JOIN #__pf_folder_tree AS t ON t.folder_id = f.id"
		       . "\n LEFT JOIN #__users AS u ON u.id = f.author"
		       . $db->projectFilter('f.project', $workspace, 'WHERE')
		       . $filter
		       . "\n GROUP BY f.id"
		       . "\n ORDER BY f.$ob $od";
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

		if(!is_array($rows)) $rows = array();
        if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

        unset($db);
		return $rows;
	}


    public function LoadFolderChildren($root, $parent = 0)
    {
        static $children = array();
        static $cached   = array();
        static $db = null;

        if (in_array($root, $cached)) return $children[$root];
        if (!isset($children[$root])) $children[$root] = array();

        if (empty($db)) $db = PFdatabase::GetInstance();

        if (empty($parent)) {
            $parent  = $root;
            $is_root = true;
        }
        else {
            $is_root = false;
        }

        $query = "SELECT a.id, COUNT(b.id) AS has_children FROM #__pf_folder_tree AS a"
               . "\n LEFT JOIN #__pf_folder_tree AS b ON (b.parent_id = a.id)"
               . "\n WHERE a.parent_id = " . (int) $parent
               . "\n GROUP BY a.id"
               . "\n ORDER BY a.id ASC";

        $db->setQuery($query);
        $items = $db->loadObjectList();

        foreach ($items AS $item)
        {
            if(!in_array($item->id, $children[$root])) {
                $children[$root][] = $item->id;
            }

            if ($item->has_children) {
                $this->LoadFolderChildren($root, $item->id);
            }
        }

        if ($is_root) $cached[] = $root;

        return $children[$root];
    }


    public function LoadElementPath($leaf, $node = 0)
    {
        static $path   = array();
        static $cached = array();
        static $ncache = array();
        static $db = null;

        if (in_array($leaf, $cached)) return $path[$leaf];
        if (!isset($path[$leaf])) $path[$leaf] = array();

        if (empty($db)) $db = PFdatabase::GetInstance();

        if (empty($node)) {
            $node    = $leaf;
            $is_leaf = true;
        }
        else {
            $is_leaf = false;
        }

        if (!isset($ncache[$node])) {
            $query = "SELECT a.parent_id, f.title FROM #__pf_folder_tree AS a"
                   . "\n INNER JOIN #__pf_folders AS f ON (f.id = a.folder_id)"
                   . "\n WHERE a.folder_id = " . (int) $node;

            $db->setQuery($query);
            $ncache[$node] = $db->loadObject();
        }

        if (!empty($ncache[$node])) {
            $path[$leaf][$node] = $ncache[$node]->title;

            if ($ncache[$node]->parent_id) {
                $this->LoadElementPath($leaf, $ncache[$node]->parent_id);
            }
            else {
                $path[$leaf][0] = PFformat::Lang('FM_ROOT');
            }
        }

        if ($is_leaf) $cached[] = $leaf;

        return array_reverse($path[$leaf], true);
    }

	public function LoadFolder($id)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT * FROM #__pf_folders WHERE id = ".$db->quote($id);
		       $db->setQuery($query);
		       $row = $db->loadObject();

		if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

		if(!is_null($row)) {
			$query = "SELECT task_id FROM #__pf_task_attachments WHERE attach_id = '$id' AND attach_type = 'folder'";
			       $db->setQuery($query);
			       $row->attachments = $db->loadResultArray();

			if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
		}

        unset($db);
		return $row;
	}

	public function LoadNoteList($dir = 0, $ob = 'id', $od = 'ASC', $keyword = '', $workspace = 0)
	{
	    $db = PFdatabase::GetInstance();
		$filter = "";

		if($keyword) {
		    $sub_dirs   = $this->LoadFolderChildren((int) $dir);
            $sub_dirs[] = (int) $dir;

		    $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

			$filter .= "\n AND (n.title LIKE ".$keyword." OR n.description LIKE ".$keyword.")"
                     . "\n AND n.dir IN(" . implode(', ', $sub_dirs) . ")";
		}

		$query = "SELECT n.*, u.name, u.id AS uid FROM #__pf_notes AS n"
		       . "\n LEFT JOIN #__users AS u ON u.id = n.author"
		       . (empty($keyword) ? "\n WHERE n.dir = ".$db->quote($dir) : '')
		       . $db->projectFilter('n.project', $workspace, (empty($keyword) ? 'AND' : 'WHERE'))
		       . $filter
		       . "\n ORDER BY n.$ob $od";
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

		if(!is_array($rows)) $rows = array();
		if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

        unset($db);
		return $rows;
	}

	public function loadNote($id)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT * FROM #__pf_notes WHERE id = ".$db->quote($id);
		       $db->setQuery($query);
		       $row = $db->loadObject();

        if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

		if(!is_null($row)) {
			$query = "SELECT task_id FROM #__pf_task_attachments WHERE attach_id = '$id' AND attach_type = 'note'";
			       $db->setQuery($query);
			       $row->attachments = $db->loadResultArray();

			if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
		}

		unset($db);
		return $row;
	}

	public function LoadFileList( $dir = 0, $ob = 'id', $od = 'ASC', $keyword = '', $workspace = 0 )
	{
	    $db = PFdatabase::GetInstance();
		$filter = "";

		if($keyword) {
		    $sub_dirs   = $this->LoadFolderChildren((int) $dir);
            $sub_dirs[] = (int) $dir;

		    $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

			$filter .= "\n AND (f.name LIKE ".$keyword." OR f.description LIKE ".$keyword.")"
                     . "\n AND f.dir IN(" . implode(', ', $sub_dirs) . ")";
		}

		if($ob == 'title') $ob = "name";

		$query = "SELECT f.*, u.name AS uname, u.id AS uid FROM #__pf_files AS f"
		       . "\n LEFT JOIN #__users AS u ON u.id = f.author"
		       . (empty($keyword) ? "\n WHERE f.dir = ".$db->quote($dir) : '')
		       . $db->projectFilter('f.project', $workspace, (empty($keyword) ? 'AND' : 'WHERE'))
		       . $filter
		       . "\n ORDER BY f.$ob $od";
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

		if(!is_array($rows)) $rows = array();
        if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

        unset($db);
		return $rows;
	}

	public function LoadFile($id)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT * FROM #__pf_files WHERE id = ".$db->quote($id);
		       $db->setQuery($query);
		       $row = $db->loadObject();

		if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

		if(!is_null($row)) {
			$query = "SELECT task_id FROM #__pf_task_attachments WHERE attach_id = '$id' AND attach_type = 'file'";
			       $db->setQuery($query);
			       $row->attachments = $db->loadResultArray();

			if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
		}

		return $row;
	}

	public function SaveFolder($dir = 0)
	{
	    $db     = PFdatabase::GetInstance();
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();

		$title   = JRequest::getVar('title');
		$desc    = JRequest::getVar('description');
		$project = (int) $user->GetWorkspace();
		$tasks   = JRequest::getVar('tasks', array(), 'array');
		$now     = time();

		$query = "INSERT INTO #__pf_folders VALUES(NULL, ".$db->quote($title).", ".$db->quote($desc).","
		       . "\n ".$db->quote($user->GetId()).", ".$db->quote($project).", ".$db->quote($now).", ".$db->quote($now).")";
		         $db->setQuery($query);
		         $db->query();
		         $id = $db->insertid();

        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

		if(!$id) return false;

		$query = "INSERT INTO #__pf_folder_tree VALUES(NULL, ".$db->quote($id).", ".$db->quote($dir).")";
			   $db->setQuery($query);
			   $db->query();

        $id = $db->insertid();

        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

		if(!$id) return false;

		// Save task attachments
		if((int) $config->Get('attach_folders', 'filemanager')) {
		    $this->SaveAttachments($id, 'folder', $tasks);
		}

		// Call process event
        $data = array($id);
        PFprocess::Event('save_folder', $data);

		return true;
	}

	public function SaveNote($dir = 0)
	{
	    $db     = PFdatabase::GetInstance();
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();

		$title   = JRequest::getVar('title');
		$desc    = JRequest::getVar('description');
		$project = (int) $user->GetWorkspace();
		$tasks   = JRequest::getVar('tasks', array(), 'array');
		$now     = time();

		if(defined('PF_DEMO_MODE')) {
			$content = JRequest::getVar('text');
		}
		else {
			$content = JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW);
		}

		$query = "INSERT INTO #__pf_notes VALUES(NULL, ".$db->quote($title).", "
		       . "\n ".$db->quote($desc).", ".$db->quote($content).", "
		       . "\n ".$db->quote($user->GetId()).", ".$db->quote($project).","
		       . "\n ".$db->quote($dir).", ".$db->quote($now).", ".$db->quote($now).")";
		       $db->setQuery($query);
		       $db->query();

		$id = $db->insertid();

		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

		// Save task attachments
		if((int) $config->Get('attach_notes', 'filemanager')) {
		    $this->SaveAttachments($id, 'note', $tasks);
		}

        // Call process event
        $data = array($id);
        PFprocess::Event('save_note', $data);

		return true;
	}

	public function UploadFiles($files, $dir = 0)
	{
	    jimport('joomla.filesystem.file');

        $db     = PFdatabase::GetInstance();
        $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();

		$descs   = JRequest::getVar('description', array());
		$tasks   = JRequest::getVar('tasks', array(), 'array');
		$count   = count($files['name']);
		$project = $user->GetWorkspace();
		$ids     = array();
		$i       = 0;

		$allowed = $config->Get('allowed_exts', 'filemanager');
		
		if ($allowed) {
			$exts = explode(",",$allowed);
		}
		else {
			$exts = array();
		}
		while ($count > $i)
		{
			$file             = array();
			$file['size']     = $files['size'][$i];
			$file['tmp_name'] = $files['tmp_name'][$i];
			$file['name']     = JFile::makeSafe($files['name'][$i]);

            $desc = $descs[$i];
			$now  = time();
            $e    = false;

			if (isset($file['name'])) {
				// Generate prefix
				$prefix1  = "project_".$project;
				$prefix2  = uniqid(md5($file['name']).rand(1,1000))."_";
				$filepath = JPath::clean(JPATH_ROOT.DS.$config->Get('upload_path', 'filemanager').DS.$prefix1);
				$size     = $file['size'] / 1024;
				$name     = $file['name'];
				$file['ext']		= end(explode(".",$file['name']));

				// Create the upload path if it does not exist
				if(!JFolder::exists($filepath)) {
					JFolder::create($filepath, 0777);
				}
				else {
			        JPath::setPermissions($filepath, '0644', '0777');
				}

				// Upload the file
				if (in_array($file['ext'], $exts)){
					if (!JFile::upload($file['tmp_name'], $filepath.DS.$prefix2.strtolower($file['name']))) {
						$i++;
						$e = true;
						$this->AddError('MSG_FILE_E_UPLOAD');
						continue;
					}

					// Chmod upload folder
					JPath::setPermissions($filepath, '0644', '0755');

					$query = "INSERT INTO #__pf_files VALUES(NULL, ".$db->quote($name).", '".$prefix2."', ".$db->quote($desc).", ".$db->quote($user->GetId()).","
						   . "\n ".$db->quote($project).", ".$db->quote($dir).", ".$db->quote($size).", ".$db->quote($now).", ".$db->quote($now).")";
						   $db->setQuery($query);
						   $db->query();

					$id = $db->insertid();

					if(!$id) {
						$i++;
						$e = true;
						$this->AddError($db->getErrorMsg());
						continue;
					}

					// Save task connections
					if((int) $config->Get('attach_files', 'filemanager')) $this->SaveAttachments($id, 'file', $tasks);
					if($id) $ids[] = $id;
				}
				else {
					$i++;
					$e = true;
					$this->AddError('MSG_FILE_E_UPLOAD');
					continue;
				}
			}
			$i++;
		}
		if($e) return false;

        $data = array($ids);
        PFprocess::Event('save_file', $data);

		return true;
	}

	public function UpdateFolder($id)
	{
	    $db     = PFdatabase::GetInstance();
	    $config = PFconfig::GetInstance();

		$title = JRequest::getVar('title');
		$desc  = JRequest::getVar('description');
		$now   = time();
		$tasks = JRequest::getVar('tasks', array(), 'array');

		$query = "UPDATE #__pf_folders SET title = ".$db->quote($title).", description = ".$db->quote($desc).","
		       . "\n edate = ".$db->quote($now)." WHERE id = ".$db->quote($id);
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

		// Update task attachments
		if((int) $config->Get('attach_folders', 'filemanager')) {
			$this->UpdateAttachments($id, 'folder', $tasks);
		}

        // Call process event
        $data = array($id);
        PFprocess::Event('update_folder', $data);

		return true;
	}

	public function UpdateNote($id)
	{
	    $db     = PFdatabase::GetInstance();
	    $config = PFconfig::GetInstance();

		$title   = JRequest::getVar('title');
		$desc    = JRequest::getVar('description');
		$tasks   = JRequest::getVar('tasks', array(), 'array');
		$now     = time();

		if(defined('PF_DEMO_MODE')) {
			$content = JRequest::getVar('text');
		}
		else {
			$content = JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW);
		}

		$query = "UPDATE #__pf_notes SET title = ".$db->quote($title).", description = ".$db->quote($desc).","
		       . "\n content = ".$db->quote($content).", edate = ".$db->quote($now)." WHERE id = ".$db->quote($id);
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

		// Update task attachments
		if((int) $config->Get('attach_notes', 'filemanager')) {
			$this->UpdateAttachments($id, 'note', $tasks);
		}

        // Call process event
        $data = array($id);
        PFprocess::Event('update_note', $data);

		return true;
	}

	public function UpdateFile($id, $file)
	{
		jimport('joomla.filesystem.file');

		$user   = PFuser::GetInstance();
		$config = PFconfig::GetInstance();
		$db     = PFdatabase::GetInstance();

		$allowed = $config->Get('allowed_exts', 'filemanager');
		
		if ($allowed) {
			$exts = explode(",",$allowed);
		}
		else {
			$exts = array();
		}
		$file['name'] = JFile::makeSafe($file['name']);
		$file['ext']  = end(explode(".",$file['name']));
		$desc         = JRequest::getVar('description');
		$id           = (int) JRequest::getVar('id');
		$tasks        = JRequest::getVar('tasks', array(), 'array');
		$now          = time();
		$name         = null;

		if (@$file['name'] != '' && in_array($file['ext'], $exts)) {
			$project  = $user->GetWorkspace();
			$prefix1  = "project_".$project;
			$prefix2  = uniqid(md5($file['name']).rand(1,1000))."_";
			$filepath = JPath::clean(JPATH_ROOT.DS.$config->Get('upload_path', 'filemanager').DS.$prefix1);
            $size     = $file['size'] / 1024;
            $name     = JFile::makeSafe($file['name']);

            $query = "SELECT name, prefix, project FROM #__pf_files WHERE id = ".$db->quote($id);
                   $db->setQuery($query);
                   $tmp = $db->loadObject();

            if(!is_object($tmp)) {
                $this->AddError('MSG_FILES_FILE_NOT_FOUND');
                return false;
            }

           	JFile::delete(JPATH_ROOT.DS.$config->Get('upload_path', 'filemanager').DS."project_".$tmp->project.DS.$tmp->prefix.strtolower($tmp->name));

            // Create the upload path if it does not exist
			if(!JFolder::exists($filepath)) {
					JFolder::create($filepath, '0777');
			}
			else {
				if(JPath::canChmod($filepath)) {
			       JPath::setPermissions($filepath, '0644', '0777');
		        }
			}

			if (!JFile::upload($file['tmp_name'], $filepath.DS.$prefix2.strtolower(JFile::makeSafe($file['name'])))) {
				$name = null;
			}

			// chmod upload folder
		    if(JPath::canChmod($filepath)) {
		        JPath::setPermissions($filepath, '0644', '0755');
		    }
		    $up = ", prefix = '$prefix2'";
		}
		else {
			$up = "";
		}

		if($name) {
			$name = " name = ".$db->quote($name).",";
		}
		else {
			$name = "";
		}

		$query = "UPDATE #__pf_files SET $name description = ".$db->quote($desc).", edate = ".$db->quote($now)." "
		       . "\n $up WHERE id = ".$db->quote($id);
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		// Update task connections
		if($config->Get('attach_files', 'filemanager')) $this->UpdateAttachments($id, 'file', $tasks);

        $data = array($id);
        PFprocess::Event('update_file', $data);
		return true;
	}

	public function SaveAttachments($id, $type, $tasks)
	{
	    $db = PFdatabase::GetInstance();

		$id     = $db->Quote($id);
		$type   = $db->Quote($type);
		$looped = array();

		foreach ($tasks AS $task)
		{
			$task = (int) $task;

			if(!$task) continue;

			if(!in_array($task, $looped)) {
				$task2 = $db->Quote((int)$task);

			    $query = "INSERT INTO #__pf_task_attachments VALUES(NULL,$task2,$id,$type)";
			           $db->setQuery($query);
			           $db->query();

			    if($db->getErrorMsg()) {
				    $this->AddError($db->getErrorMsg());
				    continue;
			    }
			    $looped[] = $task;
			}
		}

        // Call process event
        $data = array($id, $type, $tasks);
        PFprocess::Event('save_attachment', $data);

        return true;
	}

	public function UpdateAttachments($id, $type, $tasks)
	{
	    $db = PFdatabase::GetInstance();

		$query = "DELETE FROM #__pf_task_attachments WHERE attach_id = '$id' AND attach_type = '$type'";
		       $db->setQuery($query);
			   $db->query();

        if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        // Call process Event
        $data = array($id, $type, $tasks);
        PFprocess::Event('update_attachments', $data);

        // Save attachments
		$this->SaveAttachments($id, $type, $tasks);

		return true;
	}

	public function DeleteFolders($dir, $folder)
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();

		$folders = array();

		if(is_array($folder)) $folders = array_merge($folders,$folder);

		if(!count($folders)) return array();

		$tmp_folder  = implode(',', $folder);
		$sub_folders = array();

		if($tmp_folder) {
            $query = "SELECT folder_id FROM #__pf_folder_tree"
                   . "\n WHERE parent_id IN($tmp_folder)";
		           $db->setQuery($query);
		           $sub_folders = $db->loadResultArray();

		    if($db->getErrorMsg()) {
			   $this->AddError($db->getErrorMsg());
			    return false;
		    }
        }

		if(count($sub_folders)) $folders = array_merge($folders, $sub_folders);

		while (count($sub_folders))
		{
			$tmp_sub_folders = implode(',', $sub_folders);

		    $query = "SELECT folder_id FROM #__pf_folder_tree"
                   . "\n WHERE parent_id IN($tmp_sub_folders)";
		           $db->setQuery($query);
		           $sub_folders = $db->loadResultArray();

		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

		    if(count($sub_folders)) $folders = array_merge($folders, $sub_folders);
		}

		$deleteable  = array();
		$move        = array();
        $looped      = array();

		$tmp_folders = implode(',', $folders);

		// Check folder access
		$query = "SELECT id,author FROM #__pf_folders WHERE id IN($tmp_folders)";
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

        if(!is_array($rows)) $rows = array();

		foreach($rows AS $row)
        {
            if(in_array($row->id, $looped)) continue;
            $looped[] = $row->id;

            if(!$user->Access('task_delete', 'filemanager', $row->author)) {
                $move[] = $row->id;
            }
            else {
                $deleteable[] = $row->id;
            }
        }

        $tmp_folders = implode(', ',$deleteable);

		if($tmp_folders == '') {
            $this->AddError('NOT_AUTHORIZED');
            return false;
        }

		if($tmp_folders != '') {
            $query = "DELETE FROM #__pf_folders WHERE id IN($tmp_folders)";
		       $db->setQuery($query);
		       $db->query();

    		if($db->getErrorMsg()) {
    			$this->AddError($db->getErrorMsg());
    			return false;
    		}

    		$query = "DELETE FROM #__pf_folder_tree WHERE folder_id IN($tmp_folders)";
    		       $db->setQuery($query);
    		       $db->query();

            if($db->getErrorMsg()) {
    			$this->AddError($db->getErrorMsg());
    			return false;
    		}

            $query = "DELETE FROM #__pf_task_attachments WHERE attach_id IN($tmp_folders)"
                   . "\n AND attach_type = 'folder'";
                   $db->setQuery($query);
                   $db->query();

            if($db->getErrorMsg()) {
    			$this->AddError($db->getErrorMsg());
    			return false;
    		}
        }

        // Move non-deletable folders to current level
        $tmp_folders = implode(', ',$move);

        if($tmp_folders != '') {
            $query = "UPDATE #__pf_folder_tree SET parent_id = '$dir'"
                   . "\n WHERE folder_id IN($tmp_folders)";
                   $db->setQuery($query);
                   $db->query();

            if($db->getErrorMsg()) {
    			$this->AddError($db->getErrorMsg());
    			return false;
    		}
        }

        // Process event
        $data = array($folder);
        PFprocess::Event('delete_folder', $data);

		return $deleteable;
	}

	public function DeleteNotes($dir, $document, $folders = array())
	{
	    $db          = PFdatabase::GetInstance();
	    $user        = PFuser::GetInstance();
		$document    = implode(',', $document);
		$tmp_folders = $folders;

		if($document) {
		    $delete = array();

		    // Check access
		    $query = "SELECT id, author FROM #__pf_notes WHERE id IN($document)";
		           $db->setQuery($query);
		           $rows = $db->loadObjectList();

            if(!is_array($rows)) $rows = array();

            foreach($rows AS $row)
            {
                if($user->Access('task_delete', 'filemanager', $row->author)) {
                    $delete[] = $row->id;
                }
            }

            $document = implode(', ',$delete);
            if(!$document && count($folders) == 0) {
                $this->AddError('NOT_AUTHORIZED');
                return false;
            }

            // Delete notes
            $query = "DELETE FROM #__pf_notes WHERE id IN($document)";
		           $db->setQuery($query);
		           $db->query();

		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

            $query = "DELETE FROM #__pf_task_attachments"
                   . "\n WHERE attach_id IN($document) AND attach_type = 'note'";
                   $db->setQuery($query);
                   $db->query();

            if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
        }

	    if(count($folders)) {
	    	$folders = implode(',', $tmp_folders);
            $delete  = array();
            $move    = array();

            $query = "SELECT id, author FROM #__pf_notes WHERE dir IN($folders)";
                   $db->setQuery($query);
                   $docs = $db->loadObjectList();

            if(!is_array($docs)) $docs = array();

            if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

		    // Check access
		    foreach($docs AS $row)
		    {
                if(!$user->Access('task_delete', 'filemanager', $row->author)) {
                    $move[] = $row->id;
                }
                else {
                    $delete[] = $row->id;
                }
            }

            $delete = implode(', ',$delete);
            $move   = implode(', ', $move);

            if($delete) {
                $query = "DELETE FROM #__pf_task_attachments"
                       . "\n WHERE attach_id IN($delete) AND attach_type = 'note'";
                       $db->setQuery($query);
                       $db->query();

                if($db->getErrorMsg()) {
			        $this->AddError($db->getErrorMsg());
			        return false;
		        }

		        $query = "DELETE FROM #__pf_notes WHERE id IN($delete)";
    		           $db->setQuery($query);
    		           $db->query();

    		    if($db->getErrorMsg()) {
    			    $this->AddError($db->getErrorMsg());
    			    return false;
    		    }
            }

            if($move) {
                $query = "UPDATE #__pf_notes SET dir = '$dir' WHERE id IN($move)";
    		           $db->setQuery($query);
    		           $db->query();

    		    if($db->getErrorMsg()) {
    			    $this->AddError($db->getErrorMsg());
    			    return false;
    		    }
            }
	    }

        $data = array($document, $folders);
        PFprocess::Event('delete_note', $data);

        return true;
	}

	public function DeleteFiles($dir, $file, $folders = array())
	{
		jimport('joomla.filesystem.file');

		$db     = PFdatabase::GetInstance();
		$config = PFconfig::GetInstance();
		$user   = PFuser::GetInstance();

		$file = implode(',', $file);
		$e    = false;

		if($file) {
		    $delete = array();

            $query = "SELECT id, name, author, project, prefix FROM #__pf_files WHERE id IN($file)";
    		       $db->setQuery($query);
    		       $files = $db->loadObjectList();

    		if(!is_array($files)) $files = array();

    		foreach ($files AS $f)
    		{
    		    if(!$user->Access('task_delete', 'filemanager', $f->author)) continue;
    		    $delete[] = $f->id;

    			$prefix1 = "project_".$f->project;
    			$prefix2 = $f->prefix;
    			$path = JPATH_ROOT.DS.$config->Get('upload_path', 'filemanager').DS.$prefix1.DS.$prefix2.strtolower($f->name);

    			if(file_exists($path)) {
    				JFile::delete($path);
    			}
    			else {
    				$e = true;
    				$msg = PFformat::Lang('MSG_FM_FILE_NOT_EXISTS');
    				$msg = str_replace('{file}', $path, $msg);
    				$this->AddError($msg);
    			}
    		}

    		$delete = implode(', ', $delete);

    		if(!$delete && !count($folders)) {
                $this->AddError('NOT_AUTHORIZED');
                return false;
            }

    		$query = "DELETE FROM #__pf_files WHERE id IN($delete)";
    		       $db->setQuery($query);
    		       $db->query();

            $query = "DELETE FROM #__pf_task_attachments WHERE attach_id IN($delete)"
                   . "\n AND attach_type = 'file'";
                   $db->setQuery($query);
                   $db->query();
        }

	    if(count($folders)) {
	    	$folders = implode(',', $folders);
	    	$delete  = array();
	    	$move    = array();

	    	$query = "SELECT id, name, author, project, prefix FROM #__pf_files WHERE dir IN($folders)";
		           $db->setQuery($query);
		           $files = $db->loadObjectList();

		    if(!is_array($files)) $files = array();

		    foreach ($files AS $f)
		    {
		        if(!$user->Access('task_delete', 'filemanager', $f->author)) {
                    $move[] = $f->id;
                    continue;
                }
    		    $delete[] = $f->id;

			    $prefix1 = "project_".$f->project;
			    $prefix2 = $f->prefix;
			    $path = JPATH_ROOT.DS.$config->get('upload_path', 'filemanager').DS.$prefix1.DS.$prefix2.strtolower($f->name);

			    if(file_exists($path)) {
				    JFile::delete($path);
			    }
			    else {
			    	$e = true;
			    	$msg = PFformat::Lang('MSG_FM_FILE_NOT_EXISTS');
				    $msg = str_replace('{file}', $path, $msg);
				    $this->AddError($msg);
			    }

                $query = "DELETE FROM #__pf_task_attachments WHERE attach_id = '$f->id'"
                       . "\n AND attach_type = 'file'";
                       $db->setQuery($query);
                       $db->query();
		    }

		    $delete = implode(', ', $delete);
		    $move   = implode(', ', $move);

		    if($delete) {
                $query = "DELETE FROM #__pf_files WHERE id IN($delete)";
		               $db->setQuery($query);
		               $db->query();
            }

		    if($move) {
                $query = "UPDATE #__pf_files SET dir = '$dir' WHERE id IN($move)";
                       $db->setQuery($query);
                       $db->query();
            }
	    }

	    if($e) return false;

        $data = array($file, $folders);
        PFprocess::Event('delete_files', $data);

	    return true;
	}

	public function Move($dir = 0, $folders = array(), $files = array(), $notes = array())
	{
	    $db = PFdatabase::GetInstance();

		foreach ($folders AS $id)
		{
			$id = (int) $id;

			$query = "UPDATE #__pf_folder_tree SET parent_id = '$dir' WHERE folder_id = '$id'";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
		}

		foreach ($files AS $id)
		{
			$id = (int) $id;

			$query = "UPDATE #__pf_files SET dir = '$dir' WHERE id = '$id'";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
		}

		foreach ($notes AS $id)
		{
			$id = (int) $id;

			$query = "UPDATE #__pf_notes SET dir = '$dir' WHERE id = '$id'";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
		}

        $data = array($dir, $folders, $files, $notes);
        PFprocess::Event('move_filemanager', $data);

		return true;
	}

	public function RenderAddressBar($dir)
	{
	    $core = PFcore::GetInstance();
	    $db   = PFdatabase::GetInstance();

		$task = $core->GetTask();

		$query = "SELECT parent_id FROM #__pf_folder_tree WHERE folder_id = ".$db->quote($dir);
		       $db->setQuery($query);
		       $parent = (int) $db->loadResult();

		$dir_array = array();

		if($parent) $dir_array[] = $parent;

		if($task == 'list_move') {
				$link = "javascript:list_move(0)";
		}
		else {
			$link = PFformat::Link('section=filemanager&dir=0');
		}

        $html = "<a href='".$link."'>".PFformat::Lang('FM_ROOT')."</a><span>".DS."</span>";

		while ($parent >= 1) {
		 	$query = "SELECT parent_id FROM #__pf_folder_tree WHERE folder_id = ".$db->quote($parent);
		           $db->setQuery($query);
		           $parent = (int) $db->loadResult();

		    if($parent) $dir_array[] = $parent;
		}

        // Reverse elements to correct display order
        $dir_array = array_reverse($dir_array);

		foreach ($dir_array AS $i => $id)
		{
			$query = "SELECT title FROM #__pf_folders WHERE id = ".$db->quote($id);
			       $db->setQuery($query);
			       $title = $db->loadResult();

			if($task == 'list_move') {
				$link = "javascript:list_move($id)";
			}
			else {
				$link = PFformat::Link('section=filemanager&dir='.$id);
			}
			$html .= "<a href='".$link."'>$title</a><span>".DS."</span>";
		}

		if($dir) {
			$query = "SELECT title FROM #__pf_folders WHERE id = ".$db->quote($dir);
			       $db->setQuery($query);
			       $title = $db->loadResult();

			$html .= "<a href='".PFformat::Link('section=filemanager&dir='.$dir)."'>$title</a><span>".DS."</span>";
		}

		return $html;
	}
}
?>