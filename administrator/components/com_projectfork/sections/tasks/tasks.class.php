<?php
/**
* $Id: tasks.class.php 930 2013-04-06 02:09:33Z eaxs $
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

class PFtasksClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

	public function Count($keyword = '', $status = 0, $assigned = 0, $priority = -1, $project = 0, $ms = 0)
	{
	    $all = false;
        if(is_array($project)) {
            $project = implode(',',$project);
            $all = true;
        }
        if(!$project) return 0;

		$filter = "";
		$syntax = "WHERE";
		$db     = PFdatabase::GetInstance();

		if($assigned == 2) {
        	$user = PFuser::GetInstance();
       	    $filter .= "\n LEFT JOIN #__pf_task_users AS tu ON tu.task_id = t.id";
        	$filter .= "\n $syntax tu.user_id = ".$db->Quote($user->GetId());
        	$syntax = "AND";
        }
        if($assigned > 2) {
            $filter .= "\n LEFT JOIN #__pf_task_users AS tu ON tu.task_id = t.id";
        	$filter .= "\n $syntax tu.user_id = ".$db->Quote($assigned);
        	$syntax = "AND";
        }
		if($keyword) {
		    $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

			$filter .= "\n $syntax t.title LIKE ".$keyword;
			$syntax = "AND";
		}
        if($status == 1) {
            $filter .= "\n $syntax(t.progress < 100)";
            $syntax = "AND";
        }
        if($status == 2) {
        	$filter .= "\n $syntax(t.progress = 100)";
        	$syntax = "AND";
        }
        if($priority > 0) {
        	$filter .= "\n $syntax(t.priority = $priority)";
        	$syntax = "AND";
        }
        if($ms > 0) {
            $filter .= "\n $syntax(t.milestone = $ms)";
        	$syntax = "AND";
        }

        if($all) {
            $filter .= "\n $syntax(t.project IN($project))";

        }
        else {
            $filter .= "\n $syntax(t.project = '$project')";
        }

		$query = "SELECT COUNT(t.id) FROM #__pf_tasks AS t"
		       . $filter;
		       $db->setQuery($query);
		       $count = (int) $db->loadResult();

        if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

        unset($db);
		return $count;
	}

	public function LoadList($limitstart, $limit, $ob = 't.id', $od = 'ASC', $keyword = '', $status = 0, $assigned = 0, $priority = -1, $project = 0, $ms = 0)
	{
	    $all = false;
	    if(is_array($project)) {
            $project = implode(',',$project);
            $all = true;
        }
        if(!$project) {
            $return = array(array(), array());
            return $return;
        }

		$filter = "";
		$syntax = "WHERE";
		$db     = PFdatabase::GetInstance();
		$config = PFconfig::GetInstance();

		$use_milestones = (int) $config->Get('use_milestones', 'tasks');
		$show_uncat     = (int) $config->Get('uncat_tasks', 'tasks');
		$show_empty_ms  = (int) $config->Get('hide_ms_empty', 'tasks');

		// Setup query filter
		if($keyword) {
		    $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

			$filter .= "\n $syntax t.title LIKE ".$keyword;
			$syntax = "AND";
		}
		if($ms > 0) {
            $filter .= "\n $syntax(t.milestone = $ms)";
        	$syntax = "AND";
        }
        if($status == 1) {
            $filter .= "\n $syntax(t.progress < 100)";
            $syntax = "AND";
        }
        if($status == 2) {
        	$filter .= "\n $syntax(t.progress = 100)";
        	$syntax = "AND";
        }
        if($priority > 0) {
        	$filter .= "\n $syntax(t.priority = $priority)";
        	$syntax = "AND";
        }
        if($assigned == 2) {
        	$user = PFuser::GetInstance();
        	$filter .= "\n $syntax (tu.user_id = ".$user->GetId()." AND tu.task_id = t.id)";
        	$syntax = "AND";
        }
        if($assigned > 2) {
        	$filter .= "\n $syntax (tu.user_id = ".$assigned." AND tu.task_id = t.id)";
        	$syntax = "AND";
        }
        if($all) {
            $filter .= "\n $syntax(t.project IN($project))";
        }
        else {
            $filter .= "\n $syntax(t.project = '$project')";
        }

        // Join and sort on the milestone table ?
        $join_ms = "\n";
        $obt     = $ob;
        if($limit > 0 && $use_milestones) {
            $join_ms = "\n LEFT JOIN #__pf_milestones AS ms ON ms.id = t.milestone";
            $ms_ob   = 'ms.ordering,ms.title,';

            if(!in_array($ob, array('u.name', 't.progress'))) $ms_ob = str_replace('t.', 'ms.', $ob).',';
		    $obt = $ms_ob.$ob;
        }

        // Load the tasks
		$query = "SELECT t.*, u.name, p.title AS project_title, COUNT(DISTINCT(c.id)) AS comments FROM #__pf_tasks AS t"
               . $join_ms
		       . "\n LEFT JOIN #__pf_projects AS p ON p.id = t.project"
		       . "\n LEFT JOIN #__pf_comments AS c ON c.item_id = t.id AND c.scope = 'tasks'"
               . "\n LEFT JOIN #__pf_task_users AS tu ON tu.task_id = t.id"
               . "\n LEFT JOIN #__users AS u ON u.id = tu.user_id"
               . $filter
		       . "\n GROUP BY t.id"
		       . "\n ORDER BY $obt $od"
		       . (($limit > 0) ? "\n LIMIT $limitstart, $limit" : "\n");
		       $db->setQuery($query);
		       $tmp_tasks = $db->loadObjectList();

		if(!is_array($tmp_tasks)) $tmp_tasks = array();

		// Load all assigned users for each task
		$tasks = array();

		foreach($tmp_tasks AS $t)
		{
		    $nt = $t;
            $query = "SELECT u.id,u.name,u.username FROM #__users AS u"
                   . "\n RIGHT JOIN #__pf_task_users AS tu ON tu.user_id = u.id"
                   . "\n WHERE tu.task_id = '$t->id'";
                   $db->setQuery($query);
                   $assigned = $db->loadObjectList();

            $nt->assigned = $assigned;
            $tasks[] = $nt;
            unset($t,$nt);
        }
        unset($tmp_tasks);

		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            $return = array(array(), array());
            return $return;
        }

		if(!in_array($ob, array('u.name', 't.progress'))) {
			$ob = str_replace('t.', 'm.', $ob);
		}
		else {
			$ob = 'm.ordering,m.title';
		}

		// Load the milestones
		if($use_milestones) {
            $filter = "";
            if($ms) $filter = "\n AND m.id = '$ms'";
            $query = "SELECT m.*, u.name, COUNT(t.id) AS tt, SUM(t.progress) AS tp, p.title AS project_title"
    		       . "\n FROM #__pf_milestones AS m LEFT JOIN #__users AS u ON u.id = m.author"
    		       . "\n LEFT JOIN #__pf_tasks AS t ON t.milestone = m.id"
    		       . "\n LEFT JOIN #__pf_projects AS p ON p.id = m.project"
    		       . "\n WHERE m.project IN($project)"
    		       . $filter
    		       . "\n GROUP BY m.id"
    		       . "\n ORDER BY $ob $od";
    		       $db->setQuery($query);
    		       $milestones = $db->loadObjectList();
        }
        else {
            $milestones = array();
        }

        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            $return = array(array(), array());
            return $return;
        }

		if(!is_array($milestones) || !$use_milestones) $milestones = array();

		// Fix for dissappearing tasks inside a milestone when a list limit is applied
		if($limit > 0 && $use_milestones) {
            $i = 0;
            $new_milestones = array();
            $new_tasks      = array();
            $ms_with_tasks  = array();

            // Uncategorized tasks first?
            if($show_uncat == 1) {
                foreach($tasks AS $task)
                {
                    if($i >= $limit) continue;

                    if($task->milestone == '0') {
                        $new_tasks[] = $task;
                        $i++;
                    }
                }
            }

            // Milestone tasks
            if($limit > $i) {
                foreach($milestones AS $ms)
                {
                    if($i >= $limit) continue;
                    $new_milestones[] = $ms;

                    foreach($tasks AS $task)
                    {
                        if($i >= $limit) continue;
                        if($task->milestone == $ms->id) {
                            $new_tasks[] = $task;
                            $ms_with_tasks[] = $task->milestone;
                            $i++;
                        }
                    }
                }

                // Dont show milestones without tasks
                $tmp_ms = array();
                foreach($new_milestones AS $ms)
                {
                    if(in_array($ms->id,$ms_with_tasks) || $show_empty_ms == 0) {
                        $tmp_ms[] = $ms;
                    }
                }
                $new_milestones = $tmp_ms;
                unset($tmp_ms);
            }

            // Uncategorized tasks last?
            if($show_uncat == 2) {
                foreach($tasks AS $task)
                {
                    if($i >= $limit) continue;

                    if($task->milestone == '0') {
                        $new_tasks[] = $task;
                        $i++;
                    }
                }
            }

            $tasks = $new_tasks;
            $milestones = $new_milestones;
        }

		$rows = array($tasks, $milestones);

		unset($db,$config);
		return $rows;
	}

	public function LoadTask($id)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT * FROM #__pf_tasks WHERE id = ".$db->Quote($id);
		       $db->setQuery($query);
		       $row = $db->loadObject();

        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

        $query = "SELECT user_id FROM #__pf_task_users WHERE task_id = ".$db->Quote($id);
               $db->setQuery($query);
               $row->assigned = $db->loadResultArray();

        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

        if(!is_array($row->assigned)) $row->assigned = array();

		return $row;
	}

	public function LoadMilestone($id)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT * FROM #__pf_milestones WHERE id = '$id'";
		       $db->setQuery($query);
		       $row = $db->loadObject();

        if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

		return $row;
	}

	public function SaveTask()
	{
	    // Get objects
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();
	    $core = PFcore::GetInstance();

	    // Get user input
		$title     = JRequest::getVar('title');
		$deadline  = JRequest::getVar('has_deadline');
		$edate     = JRequest::getVar('edate');
		$assigned  = JRequest::getVar('assigned', array());
		$hour      = (int) JRequest::getVar('hour');
		$min       = (int) JRequest::getVar('minute');
		$ampm      = (int) JRequest::getVar('ampm');
		$progress  = (int) JRequest::getVar('progress');
		$priority  = (int) JRequest::getVar('prio');
		$milestone = (int) JRequest::getVar('milestone');

		$now     = time();
		$project = $user->GetWorkspace();
		$edate   = PFformat::ToTime($edate,$hour,$min,$ampm);

		if(defined('PF_DEMO_MODE')) {
			$content = JRequest::getVar('text');
		}
		else {
			$content = JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW);
		}

		if(!$deadline) {
			$edate = 0;
		}
		else {
			if($milestone) {
				$query = "SELECT edate FROM #__pf_milestones WHERE id = '$milestone'";
				       $db->setQuery($query);
		               $medate = $db->loadResult();

		        if($edate > $medate && $medate != 0) {
				    $edate = $medate;
				    $core->AddMessage('MSG_EDATE_EXCEEDS_PDATE');
			    }
			}
			$query = "SELECT edate FROM #__pf_projects WHERE id = '$project'";
		           $db->setQuery($query);
		           $pedate = $db->loadResult();

			if($edate > $pedate && $pedate != 0) {
				$edate = $pedate;
				$core->AddMessage('MSG_EDATE_EXCEEDS_PDATE');
			}
		}

		// Get the max ordering
		$query = "SELECT MAX(ordering) FROM #__pf_tasks"
               . "\n WHERE milestone = '$milestone' AND project = '$project'"
               . "\n LIMIT 1";
		       $db->setQuery($query);
		       $ordering = $db->loadResult();
		       $ordering++;

		// Save the task
		$query = "INSERT INTO #__pf_tasks VALUES("
               . "\n NULL, ".$db->Quote($title).", ".$db->Quote($content).","
		       . "\n ".$db->Quote($user->GetId()).", ".$db->Quote($project).", ".$db->Quote($now).", '0', "
		       . "\n ".$db->Quote($now).", ".$db->Quote($edate).", ".$db->Quote($progress).", "
		       . "\n ".$db->Quote($priority).",'$milestone', '$ordering'"
		       . "\n )";
		       $db->setQuery($query);
		       $db->query();
		       $id = $db->insertid();

        // Call process event
        $data = array($id);
        PFprocess::Event('save_task', $data);

        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

		if(!$id) return false;

        // Assign users to tasks
        $this->AssignTask($id, $assigned);

		// Send notification
        $this->SendNotification($id, 'save_task');

		return true;
	}

    public function AssignTask($id, $assigned)
    {
        $db = PFdatabase::GetInstance();

        $query = "SELECT user_id FROM #__pf_task_users"
               . "\n WHERE task_id = '$id'";
               $db->setQuery($query);
               $looped = $db->loadResultArray();

        if(!is_array($looped)) $looped = array();

        foreach($assigned AS $user)
        {
            $user = (int) $user;
            if($user <= 0) continue;
            if(in_array($user, $looped)) continue;

            $query = "INSERT INTO #__pf_task_users VALUES("
                   . "\n NULL, ".$db->quote($id).", ".$db->quote($user).", "
                   . "\n '0', '0')";
                   $db->setQuery($query);
                   $db->query();

            $looped[] = $user;
        }

        return true;
    }

	public function SaveMilestone()
	{
	    // Load objects
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();
	    $core = PFcore::GetInstance();

	    // Get user input
		$title    = $db->quote(JRequest::getVar('title'));
		$content  = $db->quote(JRequest::getVar('content'));
		$deadline = JRequest::getVar('has_deadline');
		$edate    = JRequest::getVar('edate');
		$hour     = (int) JRequest::getVar('hour');
		$min      = (int) JRequest::getVar('minute');
		$ampm     = (int) JRequest::getVar('ampm');
		$edate    = PFformat::ToTime($edate, $hour, $min, $ampm);
		$priority = $db->quote((int) JRequest::getVar('prio'));
		$project  = $user->GetWorkspace();
		$now      = time();

		// Check deadline
		if(!$deadline) {
			$edate = 0;
		}
		else {
			$query = "SELECT edate FROM #__pf_projects WHERE id = '$project'";
		           $db->setQuery($query);
		           $pedate = $db->loadResult();

			if($edate > $pedate && $pedate != 0) {
				$edate = $pedate;
				$core->AddMessage('MSG_EDATE_EXCEEDS_PDATE');
			}
			$edate = $db->Quote($edate);
		}

		// Set ordering
		$query = "SELECT MAX(ordering) FROM #__pf_milestones WHERE project = ".$project;
		       $db->setQuery($query);
		       $ordering = $db->loadResult();

		$ordering = $db->Quote($ordering);

		$query = "INSERT INTO #__pf_milestones VALUES("
               . "\n NULL,$title,$content,$project,$priority,"
               . "\n '".$user->GetId()."','$now', $edate, $ordering)";
		       $db->setQuery($query);
		       $db->query();
		       $id = $db->insertid();

		// Load processes
        $data = array($id);
        PFprocess::Event('save_milestone', $data);

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		// Send notification
		$this->SendNotification($id, 'save_milestone');

		return true;
	}

	public function UpdateTask($id)
	{
		if(!$id) return false;

		$db   = PFdatabase::GetInstance();
		$user = PFuser::GetInstance();
		$core = PFcore::GetInstance();

		$title    = JRequest::getVar('title');
		$deadline = JRequest::getVar('has_deadline');
		$edate    = JRequest::getVar('edate');
		$hour     = (int) JRequest::getVar('hour');
		$min      = (int) JRequest::getVar('minute');
		$ampm     = (int) JRequest::getVar('ampm');
		$edate    = PFformat::ToTime($edate, $hour, $min, $ampm);
		$assigned = JRequest::getVar('assigned', array());
		$progress = (int) JRequest::getVar('progress');
		$priority = (int) JRequest::getVar('prio');
		$project  = (int) $user->GetWorkspace();
		$milestone= (int) JRequest::getVar('milestone');
		$now      = time();

		if(defined('PF_DEMO_MODE')) {
			$content = JRequest::getVar('text');
		}
		else {
			$content = JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW);
		}

		if(!$deadline) {
			$edate = 0;
		}
		else {
			if($milestone) {
				$query = "SELECT edate FROM #__pf_milestones WHERE id = '$milestone'";
				       $db->setQuery($query);
		               $medate = $db->loadResult();

		        if(($edate > $medate) && $medate != 0) {
				    $edate = $medate;
				    $core->AddMessage('MSG_EDATE_EXCEEDS_PDATE');
			    }
			}
			$query = "SELECT edate FROM #__pf_projects WHERE id = '$project'";
		           $db->setQuery($query);
		           $pedate = $db->loadResult();

			if(($edate > $pedate) && $pedate != 0) {
				$edate = $pedate;
				$core->AddMessage('MSG_EDATE_EXCEEDS_PDATE');
			}
		}

		// Update the task
		$query = "UPDATE #__pf_tasks SET title = ".$db->quote($title).", content = ".$db->quote($content).","
		       . "\n mdate = ".$db->quote($now).", edate = ".$db->quote($edate)
		       . "\n , progress = ".$db->quote($progress).", milestone = ".$db->quote($milestone).","
		       . "\n priority = ".$db->quote($priority)
		       . "\n WHERE id = ".$db->quote($id);
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        // Delete assigned users
        $query = "DELETE FROM #__pf_task_users WHERE task_id = ".$db->quote($id)."";
               $db->setQuery($query);
               $db->query();

        // Re-add users
        $this->AssignTask($id, $assigned);

        // Call process event
        $data = array($id);
        PFprocess::Event('update_task', $data);

		// send notification
		$this->SendNotification($id, 'update_task');

		return true;
	}

	public function UpdateMilestone($id)
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();
	    $core = PFcore::GetInstance();

		$title    = $db->Quote(JRequest::getVar('title'));
		$content  = $db->Quote(JRequest::getVar('content'));
		$deadline = JRequest::getVar('has_deadline');
		$edate    = JRequest::getVar('edate');
		$hour     = (int) JRequest::getVar('hour');
		$min      = (int) JRequest::getVar('minute');
		$ampm     = (int) JRequest::getVar('ampm');
		$edate    = PFformat::ToTime($edate, $hour, $min, $ampm);
		$priority = $db->Quote((int) JRequest::getVar('prio'));
		$project  = $user->GetWorkspace();

		if(!$deadline) {
			$edate = 0;
		}
		else {
			$query = "SELECT edate FROM #__pf_projects WHERE id = '$project'";
		           $db->setQuery($query);
		           $pedate = $db->loadResult();

			if($edate > $pedate && $pedate != 0) {
				$edate = $pedate;
				$core->AddMessage('MSG_EDATE_EXCEEDS_PDATE');
			}
			$edate = $db->Quote($edate);
		}

		$query = "UPDATE #__pf_milestones"
               . "\n SET title = $title, content = $content,"
               . "\n edate = $edate, priority = $priority"
		       . "\n WHERE id = '$id'";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        // Call processes
        $data = array($id);
        PFprocess::Event('update_milestone', $data);

		// Send notification
		$this->SendNotification($id, 'update_milestone');

		return true;
	}

	public function UpdateProgress($id, $progress)
	{
		$db     = PFdatabase::GetInstance();
		$config = PFconfig::GetInstance();

		$progress = (int) $progress;
		$id       = (int) $id;

		$query = "UPDATE #__pf_tasks SET progress = '$progress' WHERE id = '$id'";
			   $db->setQuery($query);
			   $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        // Call process event
        $data = array($id);
        PFprocess::Event('update_progress', $data);

        // Send notification
		$n_author   = (int) $config->Get('notify_author', 'tasks');
		$n_members  = (int) $config->Get('notify_members', 'tasks');
		$n_assigned = (int) $config->Get('notify_assigned', 'tasks');

		if(($n_author || $n_members || $n_assigned)  && (int) $config->Get('notify_on_update', 'tasks')) {
			$this->SendNotification($id, 'update_task');
		}

		return true;
	}

	public function Delete($cid = array(), $mid = array())
	{
	    $db     = PFdatabase::GetInstance();
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();

	    // Check author permission
		$new_cid = array();
		$new_mid = array();

		foreach($cid AS $id => $o)
		{
		    $id = (int) $id;
            $query = "SELECT author FROM #__pf_tasks WHERE id = '$id'";
		           $db->setQuery($query);
		           $a = (int) $db->loadResult();

		    if($user->Access('task_delete', 'tasks', $a)) $new_cid[$id] = $o;
        }

        foreach($mid AS $id => $o)
        {
            $id = (int) $id;
            $query = "SELECT author FROM #__pf_milestones WHERE id = '$id'";
		           $db->setQuery($query);
		           $a = (int) $db->loadResult();

		    if($user->Access('task_delete', 'tasks', $a)) $new_mid[$id] = $o;
        }

        if( (count($cid) && !count($new_cid)) || (count($mid) && !count($new_mid)) ) {
            $this->AddError('NOT_AUTHORIZED');
            return false;
        }

        $cid  = $new_cid;
        $mid = $new_mid;

		// Delete Tasks
		if(count($cid) >= 1) {
			$cid = implode(',', $cid);

		    $query = "DELETE FROM #__pf_tasks WHERE id IN($cid)";
		           $db->setQuery($query);
		           $db->query();

		    if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }

            $query = "DELETE FROM #__pf_task_users WHERE task_id IN($cid)";
                   $db->setQuery($query);
		           $db->query();

		    if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }

            $query = "DELETE FROM #__pf_task_attachments WHERE task_id IN($cid)";
                   $db->setQuery($query);
		           $db->query();

		    if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }
		}

		// Delete Milestones
		if(count($mid) >= 1) {
			$mid = implode(',', $mid);
			$handle_tasks = $config->Get('delete_ms_tasks', 'tasks');
			if(!$handle_tasks) $handle_tasks = 2;

			$query = "DELETE FROM #__pf_milestones WHERE id IN ($mid)";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }

		    // Delete Child tasks? ...
			if($handle_tasks == 1) {
                $query = "SELECT id FROM #__pf_tasks WHERE milestone IN($mid)";
                       $db->setQuery($query);
                       $tids = $db->loadResultArray();

                if(!is_array($tids)) $tids = array();
                $tids = implode(',',$tids);

                if($db->getErrorMsg()) {
                    $this->AddError($db->getErrorMsg());
                    return false;
                }

			 	$query = "DELETE FROM #__pf_tasks WHERE milestone IN($mid)";
			 	       $db->setQuery($query);
			 	       $db->query();

			 	if($db->getErrorMsg()) {
                    $this->AddError($db->getErrorMsg());
                    return false;
                }

                if($tids) {
                    $query = "DELETE FROM #__pf_task_users WHERE task_id IN($tids)";
                           $db->setQuery($query);
		                   $db->query();

		            if($db->getErrorMsg()) {
                        $this->AddError($db->getErrorMsg());
                        return false;
                    }

                    $query = "DELETE FROM #__pf_task_attachments WHERE task_id IN($tids)";
                           $db->setQuery($query);
		                   $db->query();

		            if($db->getErrorMsg()) {
                        $this->AddError($db->getErrorMsg());
                        return false;
                    }
                }
			}

			// ... Or move to uncategorized?
			if($handle_tasks == 2) {
			    $query = "UPDATE #__pf_tasks SET milestone = '0' WHERE milestone IN($mid)";
			 	       $db->setQuery($query);
			 	       $db->query();

			 	if($db->getErrorMsg()) {
                    $this->AddError($db->getErrorMsg());
                    return false;
                }
			}
		}

        // Call process event
        $data = array($cid, $mid);
        PFprocess::Event('delete_task', $data);

		return true;
	}

	public function Copy($cid, $mid)
	{
	    $db     = PFdatabase::GetInstance();
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();

	    // Check author permission
		$new_cid = array();
		$new_mid = array();

		foreach($cid AS $id => $o)
		{
		    $id = (int) $id;
            $query = "SELECT author FROM #__pf_tasks WHERE id = '$id'";
		           $db->setQuery($query);
		           $a = (int) $db->loadResult();

		    if($user->Access('task_delete', 'tasks', $a)) $new_cid[$id] = $o;
        }

        foreach($mid AS $id => $o)
        {
            $id = (int) $id;
            $query = "SELECT author FROM #__pf_milestones WHERE id = '$id'";
		           $db->setQuery($query);
		           $a = (int) $db->loadResult();

		    if($user->Access('task_delete', 'tasks', $a)) $new_mid[$id] = $o;
        }

        if( (count($cid) && !count($new_cid)) || (count($mid) && !count($new_mid)) ) {
            $this->AddError('NOT_AUTHORIZED');
            return false;
        }

        $cid = $new_cid;
        $mid = $new_mid;
		$now = time();

		$copy_tasks = (int) $config->Get('copy_milestone_tasks', 'tasks');

		// Copy tasks
		foreach ($cid AS $id)
		{
			$row = $this->LoadTask($id);

			if(!$row) return false;

			$row->title = PFformat::Lang('COPY_OF').' '.$row->title;

			$query = "INSERT INTO #__pf_tasks VALUES (NULL, ".$db->quote($row->title).", ".$db->quote($row->content).","
                   . "\n ".$db->quote($user->GetId()).", ".$db->quote($row->project).", "
		           . "\n ".$db->quote($now).", '0', ".$db->quote($now).", ".$db->quote($row->edate).", ".$db->quote($row->progress).", "
		           . "\n ".$db->quote($row->priority). ", ".$db->quote($row->milestone).", ".$db->quote($row->odering)." )";
		           $db->setQuery($query);
		           $db->query();
		           $new_id = $db->insertid();

		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

            // assign users to new task
            $this->AssignTask($new_id, $row->assigned);
		}

		// copy milestones
		foreach ($mid AS $id)
		{
			$row = $this->LoadMilestone($id);
            if(!$row) return false;


			$row->title = PFformat::Lang('COPY_OF').' '.$row->title;

			$query = "INSERT INTO #__pf_milestones VALUES(NULL, ".$db->quote($row->title).","
				   . "\n ".$db->quote($row->content).", ".$db->quote($row->project).", ".$db->quote($row->priority).","
				   . "\n ".$db->quote($user->GetId()).", ".$now.", '0', '$row->ordering')";
				   $db->setQuery($query);
		           $db->query();
		           $new_mid = $db->insertid();

            if($db->getErrorMsg()) {
		        $this->AddError($db->getErrorMsg());
			    return false;
		    }

		    if($copy_tasks) {
		        $query = "SELECT id FROM #__pf_tasks WHERE milestone = '$row->id'";
		        	   $db->setQuery($query);
		        	   $tasks = $db->loadResultArray();

		        if($db->getErrorMsg()) {
			        $this->AddError($db->getErrorMsg());
			        return false;
		        }

		        if(!is_array($tasks)) continue;

		        // Copy milestone tasks
		        foreach ($tasks AS $tid)
		        {
		            $row2 = $this->LoadTask($tid);
		        	if(!$row2) return false;

		        	$query = "INSERT INTO #__pf_tasks VALUES (NULL, ".$db->quote($row2->title).", ".$db->quote($row2->content).","
		                   . "\n ".$db->quote($user->GetId()).", ".$db->quote($row2->project).", "
		                   . "\n ".$db->quote($now).", '0', ".$db->quote($now).", ".$db->quote($row2->edate).", ".$db->quote($row2->progress).", "
		                   . "\n ".$db->quote($row2->priority). ", ".$db->quote($new_mid).", ".$db->quote($row2->odering)." )";
		        		   $db->setQuery($query);
		        		   $db->query();
		        		   $new_tid = $db->insertid();

		        	if($db->getErrorMsg()) {
			            $this->AddError($db->getErrorMsg());
			            return false;
		            }

                    // assign users to new task
                    $this->AssignTask($new_tid, $row2->assigned);
		        }
		    }
        }

		return true;
	}

	public function ReOrder($ids, $mids)
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();

	    // Check author permission
		$new_cid = array();
		$new_mid = array();

		foreach($ids AS $id => $o)
		{
		    $id = (int) $id;
            $query = "SELECT author FROM #__pf_tasks WHERE id = '$id'";
		           $db->setQuery($query);
		           $a = (int) $db->loadResult();

		    if($user->Access('task_reorder', 'tasks', $a)) $new_cid[$id] = $o;
        }

        foreach($mids AS $id => $o)
        {
            $id = (int) $id;
            $query = "SELECT author FROM #__pf_milestones WHERE id = '$id'";
		           $db->setQuery($query);
		           $a = (int) $db->loadResult();

		    if($user->Access('task_reorder', 'tasks', $a)) $new_mid[$id] = $o;
        }

        if( (count($cid) && !count($new_cid)) || (count($mid) && !count($new_mid)) ) {
            $this->AddError('NOT_AUTHORIZED');
            return false;
        }

        $ids  = $new_cid;
        $mids = $new_mid;

		// Update task order
		foreach ($ids AS $id => $ordering)
		{
			$id       = (int) $id;
			$ordering = (int) $ordering;

			$query = "UPDATE #__pf_tasks SET ordering = $ordering WHERE id = $id";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
		}

		// Update milestone order
		foreach ($mids AS $id => $ordering)
		{
			$id       = (int) $id;
			$ordering = (int) $ordering;

			$query = "UPDATE #__pf_milestones SET ordering = $ordering WHERE id = $id";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
			    $this->_setError($db->getErrorMsg());
			    return false;
		    }
		}

		return true;
	}

	public function SendNotification($task_id, $type)
	{
		$config = PFconfig::GetInstance();
		$core = PFcore::GetInstance();

		if(defined('PF_DEMO_MODE') || !class_exists('PFtasksmailer')) {
			return true;
		}
		$n_author		= (int) $config->Get('notify_author', 'tasks');
		$n_members		= (int) $config->Get('notify_members', 'tasks');
		$n_assigned		= (int) $config->Get('notify_assigned', 'tasks');
		$n_on_create	= (int) $config->Get('notify_on_create', 'tasks');
		$n_on_update	= (int) $config->Get('notify_on_update', 'tasks');
		$n_condition	= $n_on_create;

		if (preg_match("/update/i",$type)){
			$n_condition = $n_on_update;
		}

		if(($n_author || $n_members || $n_assigned) && $n_condition) {
			$mail = new PFtasksmailer();
			$mail->SetId($task_id);
			$mail->SetType($type);
			$mail->SetRecipients();
			$mail->Send();

			$core->AddMessage("Notification sent");
		}
		else {
			return true;
		}
	}
}
?>