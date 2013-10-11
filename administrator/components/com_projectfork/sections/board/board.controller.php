<?php
/**
* $Id: board.controller.php 839 2010-12-18 05:57:01Z eaxs $
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

class PFboardController extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }
    
	public function DisplayList()
	{
	    // Load core objects
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();
	    $load   = PFload::GetInstance();
		$class  = new PFboardClass();
		
		// Wizard settings
		$wizard = (int) $config->Get('use_wizard');
		$can_ctopic = $user->Access('form_new_topic', 'board');
		
        // Get user input
		$keyword    = JRequest::getVar('keyword', '');
		$limit      = (int) JRequest::getVar('limit', $user->GetProfile('boardlist_limit', 50));
		$limitstart = (int) JRequest::getVar('limitstart', 0);
		
		// Setup table
		$ob  = JRequest::getVar('ob', $user->GetProfile("boardlist_ob", 'last_active'));
		$od  = JRequest::getVar('od', $user->GetProfile("boardlist_od", 'DESC'));
		$ts1 = array('CREATED_BY', 'TITLE', 'REPLIES', 'CREATED_ON', 'LAST_ACTIVITY', 'ID');
		$ts2 = array('u.name,u.username', 't.title', 'replies', 't.cdate', 'last_active', 't.id');
		$ts3 = array('ASC', 'DESC');
		
		if(!in_array($ob, $ts2)) $ob = 't.cdate';
		if(!in_array($od, $ts3)) $od = 'DESC';
		
		// Create table
		$table = new PFtable($ts1, $ts2, $ob, $od);
		
		// Get workspace
		$project  = $user->GetWorkspace();
		$ws_title = PFformat::WorkspaceTitle();
		if(!$project) $project = implode(',', $user->Permission('projects'));

		// Load topics
		$total = $class->CountTopics($project, $keyword);
		$rows  = $class->LoadTopicList($limitstart, $limit, $project, $ob, $od, $keyword);
		
		// Load subscriptions
		$subscriptions = $class->LoadSubscriptions();
		
		// Load joomla pagination 
		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);
		
		// Save table params in profile
		$user->SetProfile("boardlist_ob", $ob);
		$user->SetProfile("boardlist_od", $od);
		$user->SetProfile("boardlist_limit", $limit);
		
		// Check permissions
		$can_subscribe   = $user->Access('task_subscribe', 'board');
		$can_unsubscribe = $user->Access('task_unsubscribe', 'board');
        $my_flag         = $user->GetFlag();
        $my_projects     = $user->Permission('projects');
        $can_edit        = false;
        
        // Load config settings
        $preview = $config->Get('preview', 'board');
        
        // Create new form
		$form = new PFform();
		$form->SetBind(true, 'REQUEST');

        // Include the output file
		require_once($this->GetOutput('list_topics.php', 'board'));
		
		// Unset data
		unset($user,$config,$class,$rows,$table);
	}
	
	public function DisplayDetails($id)
	{
	    // Load objects
	    $load   = PFload::GetInstance();
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();
	    $core   = PFcore::GetInstance();
	    $editor = JFactory::getEditor();
	    
	    $limitstart = (int) JRequest::getVar('limitstart', 0);
	    // Include the class
		require_once($this->GetClass('board'));
		$class = new PFboardClass();
		
		// Check id
		if(!$id) {
            $this->SetRedirect('section=board', 'PFL_BOARD_INVALID_TOPIC');
            return false;
        }
        
		// Check permission
	    if(!$user->Access('display_details', 'board', $class->GetTopicAuthor($id))) {
            $this->SetRedirect('section=board', 'PFL_BOARD_TOPIC_NO_ACCESS');
            return false;
        }
        
        // Load data
        $row  = $class->LoadTopic($id);
	    $rows = $class->LoadTopicReplies($id);
	    
		$ws_title = PFformat::WorkspaceTitle();
		
		$use_editor = (int) $config->Get('use_editor', 'board');
		$flag       = $user->GetFlag();
		$task       = $core->GetTask();
		$can_edit   = false;
		
		if($flag == 'system_administrator' || $flag == 'project_administrator') $can_edit = true;
		
		// Create new form
		$form = new PFform();
		$form->SetBind(true, 'REQUEST');
		
		// Include the output file
		require_once( $this->GetOutput('display_details.php', 'board') );
		
		// Unset data
		unset($load,$core,$user,$rows,$config,$editor);
	}
	
	public function DisplayNewTopic()
	{
		$class    = new PFboardClass();
		$config   = PFconfig::GetInstance();
		$ws_title = PFformat::WorkspaceTitle();
		$editor   = &JFactory::getEditor();
		$form     = new PFform();
		
		$use_editor = (int) $config->Get('use_editor', 'board');
		
		$form->SetBind(true, 'REQUEST');
		
		require_once( $this->GetOutput('form_new_topic.php', 'board') );
	}
	
	public function DisplayEditTopic($id)
	{
		$class    = new PFboardClass();
		$ws_title = PFformat::WorkspaceTitle();
		$editor   = &JFactory::getEditor();
		$config   = PFconfig::GetInstance();
		$form     = new PFform();

		$row = $class->LoadTopic($id);
		$use_editor = (int) $config->Get('use_editor', 'board');
		
		$form->SetBind(true, $row);
		
		require_once( $this->GetOutput('form_edit_topic.php', 'board') );
	}
	
	public function DisplayEditReply($id, $rid)
	{
		$class    = new PFboardClass();
        $user     = PFuser::GetInstance();
        $config   = PFconfig::GetInstance();
        $core     = PFcore::GetInstance();
        $load     = PFload::GetInstance();
        $editor   = &JFactory::getEditor();
        
		$limitstart = (int) JRequest::getVar('limitstart');
		$limit      = (int) JRequest::getVar('limit', $user->GetProfile("boardtopic_limit", 50));
		$keyword    = JRequest::getVar('keyword');
		
		$ob         = $config->Get('reply_ob', 'board') ? $config->Get('reply_ob', 'board') : 'cdate';
		$od         = $config->Get('reply_od', 'board') ? $config->Get('reply_od', 'board') : 'ASC';
		$use_editor = (int) $config->Get('use_editor', 'board');
		
		$ws_title = PFformat::WorkspaceTitle();
		$row      = $class->LoadTopic($id);
		$rows     = $class->LoadTopicReplies($id, $limitstart, $limit, $ob, $od, $keyword);

		// can edit
		$can_edit = false;
		$flag     = $user->GetFlag();
		
		if($flag == 'system_administrator' || $flag == 'project_administrator') {
			$can_edit = true;
		}
		
		// get task
		$task = $core->GetTask();
		
		// load reply
		$edit = $class->LoadReply($rid);
		
		$form = new PFform();
		$form->SetBind(true, 'REQUEST');
		
		require_once( $this->GetOutput('display_details.php', 'board') );
	}
	
	public function SaveTopic()
	{
		$class = new PFboardClass();
		$ls    = (int) JRequest::getVar('limitstart');
		
		if(!$class->SaveTopic()) {
			$this->SetRedirect('section=board&limitstart='.$ls, 'TOPIC_E_SAVE');
			return false;
		}
		
		$this->SetRedirect('section=board&limitstart='.$ls, 'TOPIC_S_SAVE');
		return true;
	}
	
	public function SaveReply($id)
	{
		$class = new PFboardClass();
		$ls    = (int) JRequest::getVar('limitstart');
		
		if(!$class->SaveReply($id)) {
			$this->SetRedirect('section=board&task=display_details&id='.$id.'&limitstart='.$ls, 'REPLY_E_SAVE');
			return false;
		}
		
		$this->SetRedirect('section=board&task=display_details&id='.$id.'&limitstart='.$ls, 'REPLY_S_SAVE');
		return true;
	}
	
	public function UpdateTopic($id)
	{
		$class = new PFboardClass();
		$ls    = (int) JRequest::getVar('limitstart');
		
		if(!$class->UpdateTopic($id)) {
			$this->SetRedirect('section=board&limitstart='.$ls, 'TOPIC_E_UPDATE');
			return false;
		}
		
		$this->SetRedirect('section=board&limitstart='.$ls, 'TOPIC_S_UPDATE');
		return true;
	}
	
	public function UpdateReply($id, $rid)
	{
		$class = new PFboardClass();
		$ls = (int) JRequest::getVar('limitstart');
		
		if(!$class->UpdateReply($rid)) {
			$this->SetRedirect('section=board&task=display_details&id='.$id.'&limitstart='.$ls, 'REPLY_E_UPDATE');
			return false;
		}

        $this->SetRedirect('section=board&task=display_details&id='.$id.'&limitstart='.$ls, 'REPLY_S_UPDATE');
		return true;
	}
	
	public function DeleteTopic($id)
	{
	    $user  = PFuser::GetInstance();
		$class = new PFboardClass();
		
		// Check access
		if(!$user->Access('task_delete_topic', 'board', $class->GetTopicAuthor($id))) {
            $this->AddError('NOT_AUTHORIZED');
            return false;
        }
        
		if(!$class->DeleteTopic($id)) {
			$this->SetRedirect('section=board', 'MSG_E_DELETE');
			return false;
		}
		
		$this->SetRedirect('section=board', 'MSG_S_DELETE');
		return true;
	}
	
	public function DeleteReply($id, $rid)
	{
	    // Load core objects
	    $user = PFuser::GetInstance();
	    
	    // Load the class
		require_once($this->GetClass('board'));
		$class = new PFboardClass();
		
		// Check access
		if(!$user->Access('task_delete_reply', 'board', $class->GetReplyAuthor($rid))) {
            $this->AddError('NOT_AUTHORIZED');
            return false;
        }
        
		if(!$class->DeleteReply($rid)) {
			$this->SetRedirect('section=board&task=display_details&id='.$id, 'MSG_E_DELETE');
			return false;
		}
		
		$this->SetRedirect('section=board&task=display_details&id='.$id, 'MSG_S_DELETE');
		return true;
	}
	
	public function Subscribe($tmp_cid)
	{
	    // Load core objects
	    $user = PFuser::GetInstance();
	    
	    // Load the class
		require_once($this->GetClass('board'));
		$class = new PFboardClass();
		
		// Get user input
		$rtt = (int) JRequest::getVar('rtt');
		
		// Filter ids
		$cid = array();
		
		foreach($tmp_cid AS $id)
		{
            $id = (int) $id;
            if($user->Access('task_subscribe', 'board', $class->GetTopicAuthor($id))) {
                $cid[] = $id;
            }
        }
        
		$link = 'section=board'.$filter;
		if($rtt) $link = 'section=board&task=display_details&id='.intval($cid[0]);
		
		if(!$class->Subscribe($cid, $user->GetId())) {
			$this->SetRedirect($link, 'MSG_E_SUBSCRIBE');
			return false;
		}
		
		$this->SetRedirect($link, 'MSG_S_SUBSCRIBE');
		return true;
	}
	
	public function Unsubscribe($tmp_cid)
	{
	    // Load core objects
	    $user = PFuser::GetInstance();
	    
		// Load the class
		require_once($this->GetClass('board'));
		$class = new PFboardClass();
		
		// Get user input
		$rtt = (int) JRequest::getVar('rtt');

        // Filter ids
		$cid = array();
		
		foreach($tmp_cid AS $id)
		{
            $id = (int) $id;
            $cid[] = $id;
        }
        
		$link = 'section=board';
		if($rtt) $link = 'section=board&task=display_details&id='.intval($cid[0]);
		
		if(!$class->unsubscribe($cid, $user->GetId())) {
			$this->SetRedirect($link, 'MSG_E_UNSUBSCRIBE');
			return false;
		}
		
		$this->SetRedirect($link, 'MSG_S_UNSUBSCRIBE');
		return true;
	}
}
?>