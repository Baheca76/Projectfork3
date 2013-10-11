<?php
/**
* $Id: users.controller.php 911 2011-07-20 14:02:11Z eaxs $
* @package    Projectfork
* @subpackage Users
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

class PFusersController extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

	public function DisplayUsers()
	{
		$class = new PFusersClass();
		$user  = PFuser::GetInstance();

		$ob  = strval(JRequest::getVar('ob', $user->GetProfile('userlist_ob', 'u.id')));
		$od  = strval(JRequest::getVar('od', $user->GetProfile('userlist_od', 'ASC')));
		$ts1 = array('NAME', 'USERNAME', 'EMAIL', 'ID');
		$ts2 = array('u.name', 'u.username', 'u.email', 'u.id');
		$ts3 = array('ASC', 'DESC');

		if(!in_array($ob, $ts2)) $ob = 'u.id';
		if(!in_array($od, $ts3)) $od = 'ASC';

		// Create table
		$table = new PFtable($ts1, $ts2, $ob, $od);

		$limit        = (int) JRequest::getVar('limit', $user->GetProfile('userlist_limit', 50));
		$limitstart   = (int) JRequest::getVar('limitstart', 0);
		$keyword      = strval(JRequest::getVar('keyword', NULL));
		$project      = (int) $user->GetWorkspace();
		if(!$project) $project = implode(',', $user->Permission('projects'));

		$total = $class->CountUsers($keyword, $project);
		$rows  = $class->LoadUserList($limit, $limitstart, $ob, $od, $project, $keyword);

        $ws_title = PFformat::WorkspaceTitle();

		$form  = new PFform();
		$form->SetBind(true, 'REQUEST');

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		// save filter and order settings in the session
		$user->SetProfile("userlist_ob", $ob);
		$user->SetProfile("userlist_od", $od);
		$user->SetProfile("userlist_limit", $limit);

		require_once( $this->GetOutput('list_users.php', 'users') );
	}

	public function DisplayAccessLevels()
	{
		$class  = new PFusersClass();
		$user   = PFuser::GetInstance();
		$config = PFconfig::GetInstance();
		$jversion = new JVersion();

		// Setup dynamic table
		$ob  = JRequest::getVar('ob', $user->GetProfile('accesslist_ob','id'));
		$od  = JRequest::getVar('od', $user->GetProfile('accesslist_od','ASC'));
		$ts1 = array('TITLE', 'SCORE', 'PROJECT', 'FLAG', 'ID');
		$ts2 = array('title', 'score', 'project', 'flag', 'id');
		$ts3 = array('ASC', 'DESC');

		$project = $user->GetWorkspace();

		// Sanitize table settings
		if(!in_array($ob, $ts2)) $ob = 'id';
		if(!in_array($od, $ts2)) $od = 'ASC';

		// init table
		$table = new PFtable($ts1, $ts2, $ob, $od);

		// Load global access levels which we cannot delete
		$restricted = array();
		$params = array('accesslevel_0', 'accesslevel_18', 'accesslevel_19',
		                'accesslevel_20', 'accesslevel_21', 'accesslevel_23',
		                'accesslevel_24', 'accesslevel_25', 'accesslevel_pa', 'accesslevel_pm');


		// Adjust for Joomla 1.6
		if($jversion->RELEASE != '1.5') {
		    // Search for new Joomla groups and create PF equivalent access level
		    if($project == 0) $class->SyncJoomlaAccessLevels();

		    $params = $class->LoadGlobalList();
		    $params[] = 'accesslevel_0';
		    $params[] = 'accesslevel_pa';
		    $params[] = 'accesslevel_pm';
		}

		foreach ($params AS $param)
		{
			$restricted[] = (int) $config->Get($param, 'system');
		}

		$limit        = (int) JRequest::getVar('limit', (int) $user->GetProfile('accesslist_limit',50));
		$limitstart   = (int) JRequest::getVar('limitstart', 0);
        $ws_title     = PFformat::WorkspaceTitle();

        // load access levels
		$total = $class->CountAccessLevels($project);
		$rows  = $class->LoadAccessLevels($limit, $limitstart, $ob, $od, $project);

		// get user flag
		$flag = $user->GetFlag();

		// init form
		$form  = new PFform();
		$form->SetBind(true, 'REQUEST');

		// joomla pagination
		$pagination = new JPagination($total, $limitstart, $limit);

		// save filter and order settings in the session
		$user->SetProfile("accesslist_ob", $ob);
		$user->SetProfile("accesslist_od", $od);
		$user->SetProfile("accesslist_limit", $limit);

		$use_score = (int) $config->Get('use_score');

		// include output file
		require_once( $this->GetOutput('list_accesslvl.php', 'users') );
	}

	public function DisplayJoinRequests()
	{
	    $user  = PFuser::GetInstance();
		$class = new PFusersClass();

		$ob  = JRequest::getVar('ob', $user->GetProfile("joinrequests_ob", 'u.name'));
		$od  = JRequest::getVar('od', $user->GetProfile("joinrequests_od", 'ASC'));
		$ts1 = array('NAME', 'USERNAME', 'EMAIL', 'ID');
		$ts2 = array('u.name', 'u.username', 'u.email', 'u.id');
		$ts3 = array('ASC', 'DESC');

		if(!in_array($ob, $ts2)) $ob = 'u.name';
		if(!in_array($od, $ts3)) $od = 'ASC';

		// init table
		$table = new PFtable($ts1, $ts2, $ob, $od);

		$limit      = (int) JRequest::getVar('limit', 50);
		$limitstart = (int) JRequest::getVar('limitstart', 0);

		$ws_title   = PFformat::WorkspaceTitle();
		$workspace  = $user->GetWorkspace();

		$total = $class->CountJoinRequests($workspace);
		$rows  = $class->LoadJoinRequests($workspace, $limit, $limitstart, $ob, $od);

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$form = new PFform('adminForm');
		$form->SetBind(true, 'REQUEST');

		require_once( $this->GetOutput('list_requests.php', 'users') );
	}

	public function DisplayNewAccessLevel()
	{
		$class  = new PFusersClass();
		$form   = new PFform();
		$user   = PFuser::GetInstance();
		$config = PFconfig::GetInstance();

		$flags       = $class->LoadAccessFlags();
		$ws_title    = PFformat::WorkspaceTitle();
		$select_user = $form->SelectUser('user_id[]', 0);
        $use_score   = (int) $config->Get('use_score');

		$flag = $user->GetFlag();

		require_once( $this->GetOutput('form_new_accesslvl.php', 'users') );
	}

	public function DisplayNewUser()
	{
		$form  = new PFform();
		$cid   = JRequest::getVar('cid');

		$ws_title     = PFformat::WorkspaceTitle();
		$select_group = $form->SelectGroup('group_id[]', -1);

		require_once( $this->GetOutput('form_new.php', 'users') );
	}

	public function DisplayEditUser($id)
	{
	    $user   = PFuser::GetInstance();
		$class  = new PFusersClass();
		$form   = new PFform();

		$row = $class->LoadUser($id);

        $ws_title = PFformat::WorkspaceTitle();
        $flag     = $user->GetFlag();
        $return   = "section=users";

        $select_group = $form->SelectGroup('group_id[new][]', -1);
		$form->SetBind(true, $row);

		require_once( $this->GetOutput('form_edit.php', 'users') );
	}

	public function DisplayEditAccessLevel($id)
	{
		$class  = new PFusersClass();
		$form   = new PFform();
		$user   = PFuser::GetInstance();
		$config = PFconfig::GetInstance();
		$jversion = new JVersion();

		$row   = $class->LoadAccessLevel($id);
		$flags = $class->LoadAccessFlags();

		$ws_title    = PFformat::WorkspaceTitle();
		$flag        = $user->GetFlag();
		$select_user = $form->SelectUser('user_id[]', 0);
		$use_score   = (int) $config->Get('use_score');

		// Load global access levels which we cannot delete
		$restricted = array();
		$params = array('accesslevel_0', 'accesslevel_18', 'accesslevel_19',
		                'accesslevel_20', 'accesslevel_21', 'accesslevel_23',
		                'accesslevel_24', 'accesslevel_25', 'accesslevel_pa', 'accesslevel_pm');

		// Adjust for Joomla 1.6
		if($jversion->RELEASE != '1.5') {
		    // Search for new Joomla groups and create PF equivalent access level
		    if($project == 0) $class->SyncJoomlaAccessLevels();

		    $params = $class->LoadGlobalList();
		    $params[] = 'accesslevel_0';
		    $params[] = 'accesslevel_pa';
		    $params[] = 'accesslevel_pm';
		}

		foreach ($params AS $param)
		{
			$restricted[] = (int) $config->Get($param, 'system');
		}

		if(in_array($id, $restricted) && $flag != 'system_administrator') {
			$this->SetRedirect("section=users&task=list_accesslvl", 'RESTRICTED_ACL_EDIT');
			return false;
		}

		$form->SetBind(true, $row);

		require_once( $this->GetOutput('form_edit_accesslvl.php', 'users') );
	}

	public function DisplayAcceptRequests($cid)
	{
		$class = new PFusersClass();
		$rows  = $class->LoadAcceptRequestList($cid);

		$ws_title = PFformat::WorkspaceTitle();

		$form = new PFform();
		$form->SetBind(true, 'REQUEST');

		require_once( $this->GetOutput('form_accept_requests.php', 'users') );
	}

	public function DisplayInvite()
	{
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();
	    $load   = PFload::GetInstance();
	    $form   = new PFform('adminForm');

		$ws_title = PFformat::WorkspaceTitle();
		$flag     = $user->GetFlag();

		$invite_select = (int) $config->Get('invite_select', 'projects');

		$form->SetBind(true, 'REQUEST');

        $select_user = $form->SelectUser('member[]',-1, '', true, 'username');

		require_once( $this->GetOutput('form_invite.php', 'users') );
	}

	public function SaveUser($type = 'new')
	{
		$class       = new PFusersClass();
		$usersConfig = &JComponentHelper::getParams( 'com_users' );

		$user_id        = (int) $class->SaveJoomlaUser(1);
		$useractivation = $usersConfig->get( 'useractivation' );

		if($type == 'new') {
			$e_link = "section=users&task=form_new";
			$s_link = "section=users";
		}
		else {
			$e_link = "section=users&task=form_register";
			$s_link = "section=users&task=form_register";
		}

		if(!$user_id) {
			// an error has occured
			$this->SetRedirect($e_link, 'E_USERS_SAVE');
			return false;
		}
		else {
			// joomla account has been created, save PF information
			if(!$class->SavePFuser($user_id, $type)) {
				$this->SetRedirect($e_link, 'E_USERS_SAVE');
				return false;
			}
			else {
				if($type == 'new') {
				    // Clean user cache
		            PFcache::Clean('user');

					$this->SetRedirect($s_link, 'S_USERS_SAVE');
					return true;
				}
				else {
					if ( $useractivation == 1 ) {
			           $message  = JText::_( 'REG_COMPLETE_ACTIVATE' );
		            } else {
			           $message = JText::_( 'REG_COMPLETE' );
		            }

		            // Clean user cache
		            PFcache::Clean('user');

		            $this->SetRedirect($s_link, $message);
		            return true;
				}

			}
		}
	}

	public function SaveAccessLevel()
	{
		$class = new PFusersClass();

		if(!$class->SaveAccessLevel()) {
			$this->SetRedirect("section=users&task=list_accesslvl", "E_ACCESSLVL_SAVE");
			return false;
		}

		// Clean user cache
        PFcache::Clean('user');

        $this->SetRedirect("section=users&task=list_accesslvl", "S_ACCESSLVL_SAVE");
        return true;
	}

	public function UpdateUser($id)
	{
		$class = new PFusersClass();

		if(!$class->UpdateUser($id)) {
			$this->SetRedirect("section=users&task=form_edit&id=$id", 'E_USERS_UPDATE');
			return false;
		}
		else {
		    // Clean user cache
		    PFcache::Clean('user');

			$this->SetRedirect("section=users", 'S_USERS_UPDATE');
			return true;
		}
	}

	public function UpdateAccessLevel($id)
	{
		$class  = new PFusersClass();
		$config = PFconfig::GetInstance();
		$user   = PFuser::GetInstance();

		$restricted = array();
		$params = array('accesslevel_0', 'accesslevel_18', 'accesslevel_19',
		                'accesslevel_20', 'accesslevel_21', 'accesslevel_23',
		                'accesslevel_24', 'accesslevel_25', 'accesslevel_pa');

		foreach ($params AS $param)
		{
			$restricted[] = (int) $config->Get($param, 'system');
		}

		if(in_array($id, $restricted) && $user->GetFlag() != 'system_administrator') {
			$this->SetRedirect("section=users&task=list_accesslvl", 'NOT_AUTHORIZED');
			return false;
		}

		if(!$class->UpdateAccessLevel($id)) {
			$this->SetRedirect("section=users&task=list_accesslvl", 'MSG_E_UPDATE');
			return false;
		}
		else {
		    // Clean user cache
		    PFcache::Clean('user');

			$this->SetRedirect("section=users&task=list_accesslvl", 'MSG_S_UPDATE');
			return true;
		}
	}

	public function DeleteUser($cid)
	{
	    $class = new PFusersClass();

		$l   = (int) JRequest::getVar('limit');
		$ls  = (int) JRequest::getVar('limitstart');
		$ob  = JRequest::getVar('ob', 'u.id');
		$od  = JRequest::getVar('od', 'ASC');
		$k   = JRequest::getVar('keyword');
		$r   = "section=users&limit=$l&limitstart=$ls&keyword=$k";

		if(!count($cid)) {
            $this->SetRedirect($r, 'ALERT_LIST');
            return false;
		}

		if(!$class->Delete($cid)) {
			$this->SetRedirect($r, 'MSG_E_DELUSER');
			return false;
		}

		$this->SetRedirect($r, 'MSG_S_DELUSER');
		return true;
	}

	public function DeleteAccessLevel($cid)
	{
		$class    = new PFusersClass();
		$config   = PFconfig::GetInstance();
		$jversion = new JVersion();

		$l     = (int) JRequest::getVar('limit');
		$ls    = (int) JRequest::getVar('limitstart');
		$r     = "section=users&task=list_accesslvl&limit=$l&limitstart=$ls";

		// Load global access levels which we cannot delete
		$restricted = array();
		$params = array('accesslevel_0', 'accesslevel_18', 'accesslevel_19',
		                'accesslevel_20', 'accesslevel_21', 'accesslevel_23',
		                'accesslevel_24', 'accesslevel_25', 'accesslevel_pa', 'accesslevel_pm');


		// Adjust for Joomla 1.6
		if($jversion->RELEASE != '1.5') {
		    $params = $class->LoadGlobalList();
		    $params[] = 'accesslevel_0';
		    $params[] = 'accesslevel_pa';
		    $params[] = 'accesslevel_pm';
		}

		foreach ($params AS $param)
		{
			$restricted[] = (int) $config->Get($param, 'system');
		}

		$tmp_cid = array();

		foreach($cid AS $id)
		{
            if(in_array($id, $restricted)) continue;
            $tmp_cid[] = (int) $id;
        }

        $cid = $tmp_cid;
        unset($tmp_cid);

		if(!$class->DeleteAccessLevel($cid)) {
			$this->SetRedirect($r, 'MSG_E_DELETE');
			return false;
		}
		else {
		    // Clean user cache
		    PFcache::Clean('user');

			$this->SetRedirect($r, 'MSG_S_DELETE');
			return true;
		}
	}

	public function SaveRequests($import_data)
	{
		$class = new PFusersClass();

		foreach ($import_data AS $data)
		{
			if(!$class->SaveJoinRequest($data)) {
                $this->SetRedirect("section=users&task=list_requests", 'MSG_E_ACCEPT_REQUEST');
                return false;
            }
		}

		// Clean user cache
        PFcache::Clean('user');

		$this->SetRedirect("section=users&task=list_requests", 'MSG_S_ACCEPT_REQUEST');
		return true;
	}

	public function DenyRequests($cid)
	{
		$class = new PFusersClass();

		if(!$class->DenyRequests($cid)) {
			$this->SetRedirect("section=users&task=list_requests", 'DENY_ERROR');
			return false;
		}

        $this->SetRedirect("section=users&task=list_requests", 'DENY_SUCCESS');
		return true;
	}

	public function Invite()
	{
		$class  = new PFusersClass();
		$user   = PFuser::GetInstance();
		$config = PFconfig::GetInstance();

		$invite  = JRequest::getVar('invite', array(), 'array');
		$member  = JRequest::getVar('member', array());
		$fj      = (int) JRequest::getVar('force_join');
		$project = $user->GetWorkspace();


        $invite_select = (int) $config->Get('invite_select', 'projects');

        if($invite_select) {
            $looped = array();
            $invite = array();

            foreach($member AS $m)
            {
                if(!in_array($m, $looped)) {
                    if($m == "0") continue;
                    $invite[] = $m;
                    $looped[] = $m;
                }
            }
            $invite = implode(',',$invite);
        }

		if(!$class->Invite($invite, $project, $fj)) {
			$this->SetRedirect("section=users", 'INVITE_FAIL');
			return false;
		}

		$this->SetRedirect("section=users", 'INVITE_SUCESS');
		return true;
	}
}
?>