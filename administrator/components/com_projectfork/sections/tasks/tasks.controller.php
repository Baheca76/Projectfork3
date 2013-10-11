<?php
/**
* $Id: tasks.controller.php 920 2011-10-10 13:44:30Z eaxs $
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

class PFtasksController extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

	public function DisplayList()
	{
	    // Include the helper
	    require_once( $this->GetHelper('tasks') );

	    // Get objects
	    $user    = PFuser::GetInstance();
        $config  = PFconfig::GetInstance();
        $jconfig = JFactory::getConfig();

        // Setup table
		$ob  = JRequest::getVar('ob', $user->GetProfile("tasklist_ob", 't.ordering,t.title'));
		$od  = JRequest::getVar('od', $user->GetProfile("tasklist_od", 'ASC'));
		$ts1 = array('ORDERING', 'TITLE', 'ASSIGNED_TO', 'PROGRESS', 'PRIORITY', 'DEADLINE', 'ID');
		$ts2 = array('t.ordering,t.title', 't.title', 'u.name', 't.progress', 't.priority', 't.edate', 't.id');
		$ts3 = array('ASC', 'DESC');

		if(!in_array($ob, $ts2)) $ob = 't.ordering,t.title';
		if(!in_array($od, $ts3)) $od = 'ASC';

		$table = new PFtable($ts1, $ts2, $ob, $od);

		// Check permissions
		$can_reorder = $user->Access('task_reorder', 'tasks');
		$can_delete  = $user->Access('task_delete', 'tasks');
		$can_copy    = $user->Access('task_copy', 'tasks');
		$can_createt = $user->Access('form_new_task', 'tasks');
		$can_createm = $user->Access('form_new_milestone', 'tasks');

		// Get user input
		$uws        = $user->GetWorkspace();
		$user_id    = $user->GetId();
		$limit      = (int) JRequest::getVar('limit', $user->GetProfile('tasklist_limit', 50));
		$limitstart = (int) JRequest::getVar('limitstart', 0);
		$status     = (int) JRequest::getVar('status', $user->GetProfile("tasklist_status"));
		$assigned   = (int) JRequest::getVar('assigned', $user->GetProfile("tasklist_assigned"));
		$priority   = (int) JRequest::getVar('priority', $user->GetProfile("tasklist_priority"));
		$fms        = (int) JRequest::getVar('fms', $user->GetProfile("tasklist_ms_".$uws));
		$keyword    = JRequest::getVar('keyword', '');

		// Get config settings
		$use_milestones = (int) $config->Get('use_milestones', 'tasks');
		$use_comments   = (int) $config->Get('use_comments', 'tasks');
		$uncat_tasks    = (int) $config->Get('uncat_tasks', 'tasks');
		$list_comments  = (int) $config->Get('list_comments', 'tasks');
		$list_finish    = (int) $config->Get('list_quick_finish', 'tasks');
		$use_progperc   = (int) $config->Get('use_progpercent', 'tasks');
		$hide_ms_empty  = (int) $config->Get('hide_ms_empty', 'tasks');
		$wizard         = (int) $config->Get('use_wizard');
		$display_avatar = (int) $config->Get('display_avatar');

		// Wizard related vars
		if(!$use_milestones) $fms = 0;
		$p_tasks = 0;
        $p_ms    = 0;

		if($wizard && $uws && $user_id && ($can_createt || $can_createm)) {
            require_once($this->GetClass('controlpanel'));
            $cpclass = new PFcontrolpanelClass();
            $p_tasks = $cpclass->CountTasks($uws);
            $p_ms    = $cpclass->CountMilestones($uws);
        }

		// Get user profile
		$display_all = false;
		$project = $user->GetWorkspace();
		if(!$project) {
            $project = $user->Permission('projects');

            $display_all = true;
        }

		// Load tasks
		$class = new PFtasksClass();
		$total = $class->Count($keyword, $status, $assigned, $priority, $project, $fms);
		$rows  = $class->LoadList( $limitstart, $limit, $ob, $od, $keyword, $status, $assigned, $priority, $project, $fms );

		// New form
		$form = new PFform();
		$form->SetBind(true, 'REQUEST');

		$k     = 0;
        $uncat = 0;

		// Setup pagination
		$pagination = new JPagination($total, $limitstart, $limit);

		// URL filter
		$filter = "";
		$keyword = urlencode($keyword);
		if($limitstart)  $filter .= "&limitstart=$limitstart";
		if($keyword) $filter .= "&keyword=$limitstart";

		// Save filter and order settings
		$user->SetProfile("tasklist_ob", $ob);
		$user->SetProfile("tasklist_od", $od);
		$user->SetProfile("tasklist_status", $status);
		$user->SetProfile("tasklist_assigned", $assigned);
		$user->SetProfile("tasklist_priority", $priority);
		$user->SetProfile("tasklist_ms_".$uws, $fms);
		$user->SetProfile("tasklist_limit", $limit);

		// Include output file
		require_once( $this->GetOutput('list_tasks.php', 'tasks') );
	}

	public function DisplayNewTask()
	{
		$form   = new PFform();
		$editor = &JFactory::getEditor();
		$config = PFconfig::GetInstance();
		$load   = PFload::GetInstance();

		$date_format = $config->Get('date_format');

		$now 	= strftime($date_format);

		$select_user    = $form->SelectUser('assigned[]', -1);
		$use_milestones = (int) $config->Get('use_milestones', 'tasks');
		$use_editor     = (int) $config->Get('use_editor', 'tasks');
		$use_progperc   = (int) $config->Get('use_progpercent', 'tasks');
		$ws_title       = PFformat::WorkspaceTitle();

		if($config->Get('12hclock')) {
			$hour		= date('h');
		    $minute 	= date('i');
			if ($minute > 55){
				$hour += 1;
			}
			//round the minutes to increments of 5 given that the select list shows minutes in this increment.
			$minute 	= round($minute/5)*5;
			$ampm 		= (date('a') == 'pm') ? '1' : '0';
		}
		else {
			$hour		= date('H');
			$minute 	= date('i');
			if ($minute > 55){
				$hour += 1;
			}
			$minute 	= round($minute/5)*5;
		    $ampm   	= 0;
        }

		$form->SetBind(true, 'REQUEST');


		require_once( $this->GetOutput('form_new_task.php', 'tasks') );
	}

	public function DisplayNewMilestone()
	{
		$form   = new PFform();
		$editor = JFactory::getEditor();
		$load   = PFload::GetInstance();
		$config = PFconfig::GetInstance();

		$date_format = $config->Get('date_format');

		$now 	= strftime($date_format);

		$ws_title = PFformat::WorkspaceTitle();

		if($config->Get('12hclock')) {
			$hour		= date('h');
		    $minute 	= date('i');
			if ($minute > 55){
				$hour += 1;
			}
			//round the minutes to increments of 5 given that the select list shows minutes in this increment.
			$minute 	= round($minute/5)*5;
			$ampm 		= (date('a') == 'pm') ? '1' : '0';
		}
		else {
			$hour		= date('H');
			$minute 	= date('i');
			if ($minute > 55){
				$hour += 1;
			}
			$minute 	= round($minute/5)*5;
		    $ampm   	= 0;
        }

		$form->SetBind(true, 'REQUEST');

		require_once( $this->GetOutput('form_new_milestone.php', 'tasks') );
	}

	public function DisplayEditTask($id)
	{
		$class  = new PFtasksClass();

		$editor = JFactory::getEditor();
		$load   = PFload::GetInstance();
		$config = PFconfig::GetInstance();
		$user   = PFuser::GetInstance();

		$row = $class->LoadTask($id);

		$date_format = $config->Get('date_format');
		// Check for valid id
	    if(!$id || !is_object($row)) {
            $this->SetRedirect('section=tasks', 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author permission
	    if(!$user->Access('form_edit_task', 'tasks', $row->author)) {
            $this->SetRedirect('section=tasks', 'NOT_AUTHORIZED');
            return false;
        }

		$form 		= new PFform();

		$date_exists	= false;
		if ($row->edate) {
			$now = strftime($date_format, $row->edate);
			$date_exists = true;
		}
		else {
			$now = strftime($date_format);
		}
		$ws_title 	= PFformat::WorkspaceTitle();

		$use_milestones = (int) $config->Get('use_milestones', 'tasks');
		$use_editor     = (int) $config->Get('use_editor', 'tasks');
		$use_progperc   = (int) $config->Get('use_progpercent', 'tasks');

		if($config->Get('12hclock')) {
			$row->hour		= ($row->edate > 0) ? date('h', $row->edate) : date('h');
		    $row->minute 	= ($row->edate > 0) ? date('i', $row->edate) : date('i');
			if ($row->minute > 55){
				$row->hour += 1;
			}
			//round the minutes to increments of 5 given that the select list shows minutes in this increment.
			$row->minute 	= round($row->minute/5)*5;
		    $ampm   		= ($row->edate > 0) ? date('a', $row->edate) : date('a');
			$row->ampm 		= ($ampm == 'pm') ? '1' : '0';
		}
		else {
 			$row->hour		= ($row->edate > 0) ? date('H', $row->edate) : date('H');
			$row->minute 	= ($row->edate > 0) ? date('i', $row->edate) : date('i');
			if ($row->minute > 55){
				$row->hour += 1;
			}
			$row->minute 	= round($row->minute/5)*5;
		    $row->ampm   	= 0;
        }


		$form->SetBind(true, $row);
		$select_user = $form->SelectUser('assigned[]',-1);

		require_once( $this->GetOutput('form_edit_task.php', 'tasks') );
	}

	public function DisplayEditMilestone($id)
	{
		$class  = new PFtasksClass();
		$row    = $class->LoadMilestone($id);
		$form   = new PFform();
		$load   = PFload::GetInstance();
		$config = PFconfig::GetInstance();
		$user   = PFuser::GetInstance();

		$date_format 	= $config->Get('date_format');
		$date_exists	= false;
		if ($row->edate) {
			$now = strftime($date_format, $row->edate);
			$date_exists = true;
		}
		else {
			$now = strftime($date_format);
		}

		// Check for valid id
	    if(!$id || !is_object($row)) {
            $this->SetRedirect('section=tasks', 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author permission
	    if(!$user->Access('form_edit_milestone', 'tasks', $row->author)) {
            $this->SetRedirect('section=tasks', 'NOT_AUTHORIZED');
            return false;
        }

		$ws_title = PFformat::WorkspaceTitle();

		if($config->Get('12hclock')) {
			$row->hour		= ($row->edate > 0) ? date('h', $row->edate) : date('h');
			$row->minute 	= ($row->edate > 0) ? date('i', $row->edate) : date('i');
			if ($row->minute > 55){
				$row->hour += 1;
			}
			//round the minutes to increments of 5 given that the select list shows minutes in this increment.
			$row->minute 	= round($row->minute/5)*5;

			$ampm   		= ($row->edate > 0) ? date('a', $row->edate) : date('a');
			$row->ampm 		= ($ampm == 'pm') ? '1' : '0';

		}
		else {
            $row->hour		= ($row->edate > 0) ? date('H', $row->edate) : date('H');
		    $row->minute 	= ($row->edate > 0) ? date('i', $row->edate) : date('i');
			if ($row->minute > 55){
				$row->hour += 1;
			}
			$row->minute 	= round($row->minute/5)*5;
		    $row->ampm   	= 0;
        }

		$form->SetBind(true, $row);

		require_once( $this->GetOutput('form_edit_milestone.php', 'tasks') );
	}

	public function DisplayEditComment($id)
	{
		$this->DisplayDetails($id);
	}

	public function DisplayDetails($id)
	{
	    $user = PFuser::GetInstance();
		$class = new PFtasksClass();
		$row   = $class->LoadTask($id);

        // Check for valid id
	    if(!$id || !is_object($row)) {
            $this->SetRedirect('section=tasks', 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author permission
	    if(!$user->Access('display_details', 'tasks', $row->author)) {
            $this->SetRedirect('section=tasks', 'NOT_AUTHORIZED');
            return false;
        }

		$ws_title = PFformat::WorkspaceTitle();

		require_once( $this->GetOutput('display_details.php', 'tasks') );
		unset($class,$row);
	}

	public function SaveTask()
	{
		$class  = new PFtasksClass();
		$config = PFconfig::GetInstance();

		$use_progperc = (int) $config->Get('use_progpercent', 'tasks');
		$progress     = (int) JRequest::getVar('progress');
		if(!$use_progperc && $progress == 1) JRequest::setVar('progress', 100);

        $ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k) $filter .= "&keyword=$k";

		if(!$class->SaveTask()) {
			$this->SetRedirect("section=tasks".$filter, 'MSG_TASKS_E_SAVE');
			return false;
		}

		$this->SetRedirect("section=tasks".$filter, 'MSG_TASKS_S_SAVE');
		return true;
	}

	public function SaveMilestone()
	{
		$class = new PFtasksClass();
        $ls    = (int) JRequest::getVar('limitstart');
        $k     = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k)  $filter .= "&keyword=$k";

		if(!$class->SaveMilestone()) {
			$this->SetRedirect("section=tasks".$filter, 'MSG_MILESTONE_E_SAVE');
			return false;
		}

		$this->SetRedirect("section=tasks".$filter, 'MSG_MILESTONE_S_SAVE');
		return true;
	}

	public function SaveComment($id)
	{
	    $id = (int) JRequest::getVar('id');
        $ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k)  $filter .= "&keyword=$k";

        if(defined('PF_COMMENTS_PROCESS')) {
        	$config   = PFconfig::GetInstance();
        	$user     = PFuser::GetInstance();
            $comments = new PFcomments();

        	$comments->Init('tasks', $id);
        	$title   = JRequest::getVar('title');
        	$content = JRequest::getVar('ctext');

            if($config->Get('use_ce', 'comments') && !defined('PF_DEMO_MODE')) {
                $content = JRequest::getVar('ctext', '', 'default', 'none', JREQUEST_ALLOWRAW);
            }

            if(!$comments->Save($title, $content, $user->GetId())) {
			    $this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_E_SAVE');
			    return false;
		    }
		    else {
		    	// Send notification
		    	$n_author   = (int) $config->Get('notify_author', 'tasks');
		        $n_members  = (int) $config->Get('notify_members', 'tasks');
		        $n_assigned = (int) $config->Get('notify_assigned', 'tasks');

		        if(($n_author || $n_members || $n_assigned)  && (int) $config->Get('notify_on_comment', 'tasks')) {
		            $class = new PFtasksClass();
			        $class->SendNotification($id, 'save_comment');
		        }

		        // Success Redirect
			    $this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_S_SAVE');
			    return true;
		    }
        }
        else {
        	$this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_E_SAVE');
        	return false;
        }
	}

	public function UpdateTask($id, $apply = 0)
	{
		$class  = new PFtasksClass();
		$config = PFconfig::GetInstance();
		$user   = PFuser::GetInstance();

		$row = $class->LoadTask($id);

		// Check for valid id
	    if(!$id || !is_object($row)) {
            $this->SetRedirect('section=tasks', 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author permission
	    if(!$user->Access('form_edit_task', 'tasks', $row->author)) {
            $this->SetRedirect('section=tasks', 'NOT_AUTHORIZED');
            return false;
        }

		$use_progperc = (int) $config->Get('use_progpercent', 'tasks');
		$progress     = (int) JRequest::getVar('progress');
		if(!$use_progperc && $progress == 1) JRequest::setVar('progress', 100);

        $ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k)  $filter .= "&keyword=$k";

		$link = 'section=tasks';

		if($apply) $link .= "&task=form_edit_task&id=".$id;
		$link .= $filter;

		if(!$class->UpdateTask($id)) {
		    $this->SetRedirect($link, 'MSG_TASKS_E_UPDATE');
		    return false;
		}

		$this->SetRedirect($link, 'MSG_TASKS_S_UPDATE');
		return true;
	}

	public function UpdateMilestone($id)
	{
		$class = new PFtasksClass();
		$user  = PFuser::GetInstance();

		$row = $class->LoadMilestone($id);

		// Check for valid id
	    if(!$id || !is_object($row)) {
            $this->SetRedirect('section=tasks', 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author permission
	    if(!$user->Access('form_edit_task', 'tasks', $row->author)) {
            $this->SetRedirect('section=tasks', 'NOT_AUTHORIZED');
            return false;
        }

		$ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k)  $filter .= "&keyword=$k";

		$link = "section=tasks";
		if((int)JRequest::getVar('apply')) $link .= "&task=form_edit_milestone&id=$id";
        $link .= $filter;

		if(!$class->UpdateMilestone($id)) {
			$this->SetRedirect($link, 'MSG_MILESTONE_E_UPDATE');
			return false;
		}

		$this->SetRedirect($link, 'MSG_MILESTONE_S_UPDATE');
		return true;
	}

	public function UpdateProgress($id, $progress)
	{
		$class = new PFtasksClass();

        $ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k)  $filter .= "&keyword=$k";
		$link = "section=tasks".$filter;

        if(is_array($progress)) $progress = $progress[$id];

		if(!$class->UpdateProgress($id, $progress)) {
			$this->SetRedirect($link, 'MSG_PROGRESS_E_UPDATE');
			return false;
		}

		$this->SetRedirect($link, 'MSG_PROGRESS_S_UPDATE');
		return true;
	}

	public function UpdateComment($id, $cid)
	{
        $ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k)  $filter .= "&keyword=$k";

        if(defined('PF_COMMENTS_PROCESS')) {
            $config   = PFconfig::GetInstance();
            $user     = PFuser::GetInstance();
        	$comments = new PFcomments();
        	$comments->Init('tasks', $id);

        	$title   = JRequest::getVar('title');
        	$content = JRequest::getVar('ctext');

            if($config->Get('use_ce', 'comments') && !defined('PF_DEMO_MODE')) {
                $content = JRequest::getVar('ctext', '', 'default', 'none', JREQUEST_ALLOWRAW);
            }

            if(!$comments->Update($title, $content, $cid)) {
			    $this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_E_UPDATE');
			    return false;
		    }
		    else {
			    $this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_S_UPDATE');
			    return true;
		    }
        }
        else {
        	$this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_E_UPDATE');
        	return false;
        }
	}

	public function Delete($cid, $mid)
	{
		$class = new PFtasksClass();

		$ls= (int) JRequest::getVar('limitstart');
        $k = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k)  $filter .= "&keyword=$k";

		if(!$class->Delete($cid, $mid)) {
			$this->SetRedirect('section=tasks'.$filter, 'MSG_E_DELETE');
			return false;
		}

		$this->SetRedirect('section=tasks'.$filter, 'MSG_S_DELETE');
		return true;
	}

	public function DeleteComment($id, $cid)
	{
        $ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k) $filter .= "&keyword=$k";

        if(defined('PF_COMMENTS_PROCESS')) {
            $user     = PFuser::GetInstance();
        	$comments = new PFcomments();

        	$comments->Init('tasks', $id);

        	$title   = JRequest::getVar('title');
        	$content = JRequest::getVar('content');

            if(!$comments->Delete($cid)) {
			    $this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_E_DELETE');
			    return false;
		    }
		    else {
			    $this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_S_DELETE');
			    return true;
		    }
        }
        else {
        	$this->SetRedirect("section=tasks&task=display_details&id=$id".$filter, 'COMMENT_E_DELETE');
        	return true;
        }
	}

	public function Copy($cid, $mid)
	{
		$class = new PFtasksClass();

		$ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k) $filter .= "&keyword=$k";
		$link = "section=tasks".$filter;

		if(!$class->Copy($cid, $mid)) {
			$this->SetRedirect($link, 'MSG_E_COPY');
			return false;
		}

		$this->SetRedirect($link, 'MSG_S_COPY');
		return false;
	}

	public function ReOrder($ids, $mids)
	{
		$class = new PFtasksClass();

		$ls = (int) JRequest::getVar('limitstart');
        $k  = urlencode(JRequest::getVar('keyword'));

        $filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		if($k) $filter .= "&keyword=$k";

		$link = "section=tasks".$filter;

		if(!$class->reorder($ids, $mids)) {
			$this->SetRedirect($link, 'MSG_E_REORDER');
			return false;
		}

		$this->SetRedirect($link, 'MSG_S_REORDER');
		return true;
	}
}
?>