<?php
/**
* $Id: time.class.php 930 2013-04-06 02:09:33Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
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

class PFtimeClass extends PFobject
{
    public function Count($ws = 0, $keyword = null, $ftask = 0, $fuser = 0)
    {
        // Load objects
        $db   = PFdatabase::GetInstance();
        $user = PFuser::GetInstance();

        $project_filter = "";
        $filter = "";

        if($ws) {
            $project_filter = "\n WHERE project_id = '$ws'";
            $has_projects = 1;
        }
        else {
            $my_projects  = $user->Permission('projects');
            $has_projects = count($my_projects);
            $project_filter = "\n WHERE project_id IN(".implode(',',$my_projects).")";
        }

        if($keyword) {
            $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);
            $filter .= "\n AND content LIKE ".$keyword;
        }
        if($ftask) $filter .= "\n AND task_id = '$ftask'";
        if($fuser) $filter .= "\n AND user_id = '$fuser'";

        if(!$has_projects || $has_projects < 1) return 0;

        $query = "SELECT COUNT(id) FROM #__pf_time_tracking $project_filter $filter";
               $db->setQuery($query);
               $result = $db->loadResult();

        // Log any errors
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return 0;
        }

        unset($db,$user);
        return (int) $result;
    }

    public function LoadList($limitstart, $limit = 0, $ob = 'ti.cdate', $od = 'ASC', $ws = 0, $keyword = null, $ftask = 0, $fuser = 0)
    {
        // Load objects
        $db   = PFdatabase::GetInstance();
        $user = PFuser::GetInstance();

        // Setup filters
        $project_filter = "";
        $filter = "";

        if($ws) {
            $project_filter = "\n WHERE ti.project_id = '$ws'";
            $has_projects = 1;
        }
        else {
            $my_projects  = $user->Permission('projects');
            $has_projects = count($my_projects);
            $project_filter = "\n WHERE ti.project_id IN(".implode(',',$my_projects).")";
        }

        if(!$has_projects || $has_projects < 1) return array();
        if($keyword) {
            $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);
            $filter .= "\n AND ti.content LIKE ".$keyword;
        }
        if($ftask) $filter .= "\n AND ti.task_id = '$ftask'";
        if($fuser) $filter .= "\n AND ti.user_id = '$fuser'";

        // Do the query
        $query = "SELECT ti.*, u.name, t.title, p.title AS project_title FROM #__pf_time_tracking as ti"
               . "\n RIGHT JOIN #__pf_tasks AS t ON t.id = ti.task_id"
               . "\n RIGHT JOIN #__users AS u ON u.id = ti.user_id"
               . "\n RIGHT JOIN #__pf_projects AS p ON p.id = ti.project_id"
               . $project_filter
               . $filter
               . "\n GROUP BY ti.id"
               . "\n ORDER BY $ob $od"
    	       . (($limit > 0) ? "\n LIMIT $limitstart, $limit" : "\n");
               $db->setQuery($query);
               $rows = $db->loadObjectlist();

        // Log any errors
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return array();
        }

        if(!is_array($rows)) $rows = array();

        unset($db,$user);
    	return $rows;
    }

    public function Load($id)
    {
        // Load objects
        $db = PFdatabase::GetInstance();
        $id = $db->Quote($id);

        $query = "SELECT * FROM #__pf_time_tracking WHERE id = $id";
               $db->setQuery($query);
               $row = $db->loadObject();

        // Log any errors
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return NULL;
        }

        return $row;
    }

    public function Save($user_id, $task_id, $content, $cdate, $time)
    {
        $db = PFdatabase::GetInstance();

        if(!$task_id) {
    		$this->AddError('ERROR_SELECT_TASK');
    		return false;
    	}

        $user_id = $db->Quote($user_id);
        $task_id = $db->Quote($task_id);
        $content = $db->Quote($content);
        $cdate   = $db->Quote($cdate);
        $time    = $db->Quote($time);

        $query = "SELECT project FROM #__pf_tasks WHERE id = $task_id";
               $db->setQuery($query);
               $project_id = (int) $db->loadResult();

        $project_id = $db->Quote($project_id);

        // Log any errors
        if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		return false;
    	}

        $query = "INSERT INTO #__pf_time_tracking VALUES(NULL,"
               . "\n $task_id, $project_id, $user_id, $content, $cdate, $time"
               . "\n )";
               $db->setQuery($query);
               $db->query();
               $id = $db->insertid();

        // Log any errors
        if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		return false;
    	}

        // Load processes
        $data = array($id);
        PFprocess::Event('save_time', $data);

        return true;
    }

    public function Update($id, $task_id, $content, $cdate, $time)
    {
        $db = PFdatabase::GetInstance();

        $id      = $db->Quote($id);
        $task_id = $db->Quote($task_id);
        $content = $db->Quote($content);
        $cdate   = $db->Quote($cdate);
        $time    = $db->Quote($time);

        $query = "UPDATE #__pf_time_tracking"
               . "\n SET task_id = $task_id, content = $content, cdate = $cdate, timelog = $time"
               . "\n WHERE id = $id";
               $db->setQuery($query);
               $db->query();
               $id = $db->insertid();

        // Log any errors
        if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		return false;
    	}

        // Load processes
        $data = array($id);
        PFprocess::Event('update_time', $data);

        return true;
    }

    public function Delete($ids)
    {
        $db  = PFdatabase::GetInstance();
        $ids = implode(',', $ids);

        $query = "DELETE FROM #__pf_time_tracking WHERE id IN($ids)";
               $db->setQuery($query);
               $db->query();

        // Log any errors
        if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		return false;
    	}

    	// Load processes
        $data = array($ids);
        PFprocess::Event('delete_time', $data);

        return true;
    }
}
?>