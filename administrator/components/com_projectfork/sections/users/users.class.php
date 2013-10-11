<?php
/**
* $Id: users.class.php 930 2013-04-06 02:09:33Z eaxs $
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

class PFusersClass extends PFobject
{
	private $_params;

	public function __construct()
	{
        parent::__construct();
    }

	public function CountUsers($keyword = NULL, $project = 0)
	{
        if(!$project) return 0;

        $filter = "";
		$db     = PFdatabase::GetInstance();

		if($keyword) {
		    $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

			$filter .= "\n AND(u.username LIKE " . $keyword;
			$filter .= "\n OR (u.name LIKE " . $keyword . ")";
			$filter .= "\n OR (u.email LIKE " . $keyword . "))";
		}

		$query = "SELECT COUNT(pm.user_id) FROM #__pf_project_members AS pm"
		       . "\n RIGHT JOIN #__users AS u ON u.id = pm.user_id"
		       . "\n WHERE pm.project_id IN($project)"
		       . $filter
		       . "\n AND pm.approved = '1'"
		       . "\n GROUP BY pm.id";
		       $db->setQuery($query);
		       $total = (int) $db->loadResult();

        if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
		return $total;
	}

	public function CountAccessLevels($project = 0)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT COUNT(id) FROM #__pf_access_levels WHERE project = '$project'";
		       $db->setQuery($query);
		       $total = (int) $db->loadResult();

		if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

		return $total;
	}

	public function CountJoinRequests($id)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT COUNT(id) FROM #__pf_project_members WHERE approved = '0'"
		       . "\n AND project_id = '$id'";
		       $db->setQuery($query);
		       $total = (int) $db->loadResult();

		if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());

		return $total;
	}

	public function LoadUser($id)
	{
	    // Load objects
	    $jversion = new JVersion();
	    $usr  = PFuser::GetInstance();
	    $user = JFactory::getUser($id);
	    $db   = PFdatabase::GetInstance();

	    // Get workspace
		$ws = $usr->GetWorkspace();

		if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
		if(is_null($user)) return false;

		// Load access level
		if($ws) {
			$query = "SELECT accesslvl FROM #__pf_user_access_level"
                   . "\n WHERE user_id = '$id'"
                   . "\n AND project_id = '$ws'";
			       $db->setQuery($query);
			       $user->accesslvl = $db->loadResult();
		}

		// Load profile info
		$rows = $usr->LoadProfile($id);
		if(!is_array($rows)) $rows = array();

		$user->profile = new stdClass();

		foreach ($rows AS $p => $v)
		{
			$user->profile->$p = $v;
		}

		// Load groups
		$query = "SELECT gu.group_id, g.project FROM #__pf_group_users AS gu"
               . "\n RIGHT JOIN #__pf_groups AS g ON (g.id = gu.group_id)"
               . "\n WHERE gu.user_id = '$id'"
               . "\n GROUP BY gu.group_id"
               . "\n ORDER BY g.project ASC";
		       $db->setQuery($query);
		       $tmp_groups = $db->loadObjectList();

        if(!is_array($tmp_groups)) $tmp_groups = array();

        if($jversion->RELEASE == '1.5') {
            $user->groups_format = array();
            $user->groups = array();
            $cp = 0;

            foreach($tmp_groups AS $g)
            {
                if($cp != $g->project) {
                    $cp = $g->project;
                    $user->groups[$cp] = array();
                }
                $user->groups_format[$cp][] = $g->group_id;
                $user->groups[] = $g->group_id;
            }
        }
        else {
			$user->pfgroups_format = array();
			$user->pfgroups = array();
			$cp = 0;

			foreach($tmp_groups AS $g)
			{
				if($cp != $g->project) {
					$cp = $g->project;
					$user->pfgroups[$cp] = array();
				}
				$user->pfgroups_format[$cp][] = $g->group_id;
				$user->pfgroups[] = $g->group_id;
			}
			//retrieve Joomla User Group Info
			$query = "SELECT a.title,b.group_id FROM #__usergroups as a"
					. "\n INNER JOIN #__user_usergroup_map as b ON a.id = b.group_id"
					. "\n WHERE user_id='$id'";
			$db->setQuery($query);
			$joomla_groups = $db->loadObjectList();

			if(!is_array($joomla_groups)) $joomla_groups = array();

			foreach ($joomla_groups as $joomla_group) {
				$user->groups[$joomla_group->group_id] = $joomla_group->title;
			}
        }

		unset($rows,$tmp_groups,$usr,$db);
		return $user;
	}

	public function LoadUserList($limit, $limitstart, $order_by, $order_dir, $project = 0, $keyword = null)
	{
		if(!$project) return array();

		$filter = "";
		$db = PFdatabase::GetInstance();

		if($keyword) {
           $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

			$filter .= "\n AND(u.username LIKE " . $keyword;
			$filter .= "\n OR (u.name LIKE " . $keyword . ")";
			$filter .= "\n OR (u.email LIKE " . $keyword . "))";
		}


		$query = "SELECT u.* FROM #__users AS u"
		       . "\n RIGHT JOIN #__pf_project_members AS pm ON pm.user_id = u.id"
               . "\n AND pm.project_id IN($project)"
		       . "\n WHERE u.id = u.id"
		       . $filter
		       . "\n AND pm.approved = '1'"
			   . "\n GROUP BY u.id"
		       . "\n ORDER BY ".$order_by." ".$order_dir
		       . (($limit > 0) ? "\n LIMIT $limitstart, $limit" : "\n");
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

		if( !is_array($rows)) $rows = array();

		return $rows;
	}

	public function LoadAccessLevels($limit, $limitstart = 0, $order_by, $order_dir, $project = 0)
	{
	    $db = PFdatabase::GetInstance();
	    $jversion = new JVersion();

		$query = "SELECT l.*,p.title AS project_title,f.title AS flag_title FROM #__pf_access_levels AS l"
		       . "\n LEFT JOIN #__pf_projects AS p ON p.id = l.project"
		       . "\n LEFT JOIN #__pf_access_flags AS f ON f.name = l.flag"
		       . "\n WHERE l.project = '$project'"
		       . "\n GROUP BY l.id"
		       . "\n ORDER BY $order_by $order_dir"
		       . (($limit > 0) ? "\n LIMIT $limitstart, $limit" : "\n");
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

		if(!is_array($rows)) $rows = array();

		// Find joomla 1.6 group name
		if($jversion->RELEASE != '1.5' && $project == 0) {
		    $config   = PFconfig::GetInstance();
		    $acl_map  = $this->MapGlobalAccessLevels();
		    $tmp_rows = array();

		    foreach($rows AS $row)
		    {
		        if(!array_key_exists($row->id, $acl_map)) {
                    $tmp_rows[] = $row;
                    unset($row);
                    continue;
                }

		        $jgid = (int) $acl_map[$row->id];

                $query = "SELECT title FROM #__usergroups"
                       . "\n WHERE id = '$jgid'";
                       $db->setQuery($query);
                       $jgtitle = $db->loadResult();

                $row->jtitle = $jgtitle;
                $tmp_rows[]  = $row;
                unset($row);
            }
            $rows = $tmp_rows;
            unset($tmp_rows);
		}

		return $rows;
	}

	public function LoadJoinRequests($id, $limit = 0, $limitstart = 0, $order_by, $order_dir)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT u.* FROM #__users AS u"
		       . "\n RIGHT JOIN #__pf_project_members AS m ON m.user_id = u.id AND m.approved = '0' AND project_id = '$id'"
		       . "\n WHERE u.id = m.user_id"
		       . "\n GROUP BY u.id"
		       . "\n ORDER BY $order_by $order_dir"
		       . (($limit > 0) ? "\n LIMIT $limitstart, $limit" : "\n");
		       $db->setQuery($query);
			   $rows = $db->loadObjectList();

        if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
        if(!is_array($rows)) $rows = array();

		return $rows;
	}

	public function LoadAccessLevel($id)
	{
	    $db = PFdatabase::GetInstance();
	    $jversion = new JVersion();

		$query = "SELECT * FROM #__pf_access_levels WHERE id = '$id'";
		       $db->setQuery($query);
		       $row = $db->loadObject();

		if(is_object($row)) {
			$query = "SELECT user_id FROM #__pf_user_access_level WHERE accesslvl = '$id'";
			       $db->setQuery($query);
			       $row->users = $db->loadResultArray();

			if(!is_array($row->users)) $row->users = array();

			// Check if it's a global acl and find Joomla group name
            if($row->project == 0 && $jversion->RELEASE != '1.5') {
                $map = $this->MapGlobalAccessLevels();
                if(array_key_exists($row->id, $map)) {
                    $row->jtitle = $this->GetJoomlaGroupName($map[$row->id]);
                }
                else {
                    $row->jtitle = '';
                }
            }
		}
		return $row;
	}

	public function LoadAccessFlags()
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT * FROM #__pf_access_flags ORDER BY id ASC";
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

		if(!is_array($rows)) $rows = array();

		return $rows;
	}

	public function GetJoomlaGroupName($id)
    {
        $db = PFdatabase::GetInstance();

        $query = "SELECT title FROM #__usergroups"
               . "\n WHERE id = '$id'";
               $db->setQuery($query);
               $name = $db->loadResult();

        return $name;
    }

	public function LoadAcceptRequestList($tmp_cid)
	{
	    $db = PFdatabase::GetInstance();

		$cid = array();
		foreach ($tmp_cid AS $id)
		{
			$id = (int) $id;
			$cid[] = $id;
		}

		$cid = implode(',', $cid);

		$query = "SELECT * FROM #__users WHERE id IN($cid)";
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

        if(!is_array($rows)) $rows = array();
		return $rows;
	}

	public function SaveJoinRequest($data)
	{
		$db   = PFdatabase::GetInstance();
		$user = PFuser::GetInstance();

		$id    = (int) $data['id'];
		$acl   = (int) $data['acl'];
		$group = (int) $data['group'];
        $ws    = (int) $user->GetWorkspace();

		// Add to group
		if($group) {
			$query = "INSERT INTO #__pf_group_users VALUES (NULL, $group, $id)";
		           $db->setQuery($query);
		           $db->query();

		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
		}

		// Update access level
		if($acl) {
			$query = "INSERT INTO #__pf_user_access_level VALUES(NULL, '$acl', $id, '$ws')";
		           $db->setQuery($query);
		           $db->query();

		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
		}

		// Approve user
		$query = "UPDATE #__pf_project_members SET approved = '1' WHERE user_id = '$id' AND project_id = '$ws'";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		// get project title
		$query = "SELECT title FROM #__pf_projects WHERE id = '$ws'";
		       $db->setQuery($query);
		       $title = $db->loadResult();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		$query = "SELECT * FROM #__users WHERE id = '$id'";
		       $db->setQuery($query);
		       $user = $db->loadObject();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}


		// Setup email
		$subject = PFformat::Lang('PFL_JOIN_ACCEPT_SUBJECT');
		$subject = str_replace('$title', $title, $subject);
		$message = PFformat::Lang('PFL_JOIN_ACCEPT_BODY');
		$message = str_replace('$name', $user->name, $message);
		$message = str_replace('\n',"\n", $message);

		// notify user
    	$mail = &JFactory::getMailer();

		// $mail->setSender( array( $jconfig->get('mailfrom'), $jconfig->get('fromname') ) );
		$mail->setSubject( $subject );
	    $mail->setBody( $message );
	    $mail->addRecipient( $user->email );
	    $mail->Send();

		return true;
	}

	public function SendEmail(&$user)
	{
        $jconfig = JFactory::GetConfig();

        $usersConfig = &JComponentHelper::getParams( 'com_users' );
		$adminEmail  = $jconfig->get('mailfrom');
		$adminName	 = $jconfig->get('fromname');
        $activate    = $usersConfig->get( 'useractivation' );
        $siteURL	 = JURI::base();
        $sitename 	 = $jconfig->get( 'sitename' );

        // just send account info (admin)
        $subject = PFformat::Lang('PFL_NEW_USER_MESSAGE_SUBJECT');
        $message = sprintf ( PFformat::Lang('PFL_NEW_USER_MESSAGE'), $user->get('name'), JURI::root(), $user->get('username'), $user->password_clear );
        $message = str_replace('\n', "\n", $message);

        // send email
        $mail = &JFactory::getMailer();
        $mail->setSubject( $subject );
	    $mail->setBody( $message );
	    $mail->addRecipient( $user->get('email') );
	    $mail->Send();
	}

	public function SaveJoomlaUser($send_email = 1)
	{
		$jversion = new JVersion();
		$post     = JRequest::get('post');

		if($jversion->RELEASE != '1.5') {
		    $user = JUser::getInstance(0);
		}
		else {
            $user = new JUser(0);
        }

		$post['username']  = JRequest::getVar('username', '', 'post', 'username');
		$post['password']  = JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$post['password2'] = JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$post['params']    = array();
		$post['params']['timezone'] = JRequest::getVar('timezone','', 'post');

		if($jversion->RELEASE != '1.5') {
            $jform = JRequest::getVar('jform', array(), 'post', 'array');
            $post['groups'] = $jform['groups'];
        }

		if (!$user->bind($post)) {
			$this->AddError($user->getError());
			return false;
		}

		if (!$user->save()) {
            $this->AddError($user->getError());
			return false;
		}

		// TODO
		if($send_email) $this->SendEmail($user);

		return $user->id;
	}

	public function UpdateJoomla($user_id)
	{
	    $jversion = new JVersion();
		$post     = JRequest::get('post');

		if($jversion->RELEASE != '1.5') {
		    $user = JUser::getInstance($user_id);
		}
		else {
            $user = new JUser($user_id);
        }

		$post['username']	= JRequest::getVar('username', '', 'post', 'username');
		$post['password']	= JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$post['password2']	= JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$post['name']       = JRequest::getVar('name','', 'post');
		$post['params']     = array();
		$post['params']['timezone'] = JRequest::getVar('timezone','', 'post');
		$post['id']         = $user_id;

        if($jversion->RELEASE != '1.5') {
            $jform = JRequest::getVar('jform', array(), 'post', 'array');
            $post['groups'] = $jform['groups'];
        }

		if (!$user->bind($post)) {
			$this->AddError($user->getError());
			return false;
		}

		if (!$user->save(true)) {
            $this->AddError($user->getError());
			return false;
		}

		return true;
	}

	public function SavePFuser($user_id)
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();

		$acl    = JRequest::getVar('accesslvl');
		$groups = JRequest::getVar('group_id', array(), 'array');
		$ws     = $user->GetWorkspace();

		$params = array('accesslevel','timezone','language','phone', 'mobile_phone',
		                 'skype','msn','icq','street','city','zip','groups', 'avatar');

		if(!$user_id) return false;

		// Save params
		foreach ($params AS $param)
		{
			$user->SetProfile($param, JRequest::getVar($param, ''), $user_id);
		}

		// Save the groups
		foreach ($groups AS $group)
		{
			$query = "INSERT INTO #__pf_group_users VALUES(NULL,'$group','$user_id')";
			       $db->setQuery($query);
		           $db->query();
		}

		// Save access level
		if($acl && $ws) {
			$query = "INSERT INTO #__pf_user_access_level VALUES(NULL, '$acl', '$user_id', '$ws')";
				   $db->setQuery($query);
				   $db->query();
		}

		// Add to project
		if($ws) {
			$query = "INSERT INTO #__pf_project_members VALUES(NULL,'$ws', '$user_id', '1')";
			       $db->setQuery($query);
			       $db->query();
		}

		return true;
	}

	public function ValidateAccessLevel()
	{
		$title = JRequest::getVar('title');

		if(!$title) {
			$this->AddError('V_TITLE');
			return false;
		}

		return true;
	}

	public function SaveAccessLevel($return_id = false)
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();

		$title = JRequest::getVar('title');
		$score = (int) JRequest::getVar('score');
		$flag  = JRequest::getVar('f');
		$users = JRequest::getVar('user_id', array(), 'array');

		$project  = $user->GetWorkspace();
		$my_flag  = $user->GetFlag();
		$my_score = $user->GetScore();

		// sanitize
		if($my_flag != 'system_administrator') {
			if($score > $my_score) $score = $my_score;

			if($flag == 'system_administrator') $flag = "";

			if($flag == 'project_administrator' && $my_flag != 'project_administrator') $flag = "";

			if(!$project && !$return_id) {
				$this->AddError('V_PROJECT');
				return false;
			}
		}

		if(!$project && !$return_id) return false;

		$query = "INSERT INTO #__pf_access_levels VALUES(NULL, '$title', '$score', '$project', '$flag')";
		       $db->setQuery($query);
		       $db->query();

		$id = $db->insertid();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		$looped = array();
		if($id && count($users) > 0 && $project != 0) {
			foreach ($users AS $uid)
			{
				$uid = (int) $uid;

				$query = "DELETE FROM #__pf_user_access_level WHERE project_id = '$project' AND user_id = '$uid'";
				       $db->setQuery($query);
				       $db->query();

				if($db->getErrorMsg()) {
			        $this->AddError($db->getErrorMsg());
			        return false;
		        }

				if(!in_array($uid, $looped)) {
					$query = "INSERT INTO #__pf_user_access_level VALUES(NULL, '$id', '$uid', '$project')";
				           $db->setQuery($query);
				           $db->query();

				    if($db->getErrorMsg()) {
			            $this->AddError($db->getErrorMsg());
			            return false;
		            }
		            $looped[] = $uid;
				}
			}
		}

		if($return_id) return $id;
		return true;
	}

	public function UpdateUser($id)
	{
	    $user = PFuser::GetInstance();
	    $db   = PFdatabase::GetInstance();
	    $jversion = new JVersion();

		$username = JRequest::getVar('username');
		$name     = JRequest::getVar('name');
		$email    = JRequest::getVar('email');
		$gid      = (int) JRequest::getVar('gid');
		$acl      = (int) JRequest::getVar('accesslvl');
		$groups   = JRequest::getVar('group_id',array(), 'array');
		$flag     = $user->GetFlag();
		$ws       = $user->GetWorkspace();

		if(!is_array($groups)) $groups = array();

		if(!$id) return false;

		// Update profile
		if($flag == 'system_administrator') {
			$params = array('timezone','phone', 'mobile_phone',
		                    'skype','msn','icq','street','city','zip');

		    foreach ($params AS $param)
		    {
			    $user->SetProfile($param, JRequest::getVar($param), $id);
		    }
		}

		// Update access level
		if($ws) {
			$query = "DELETE FROM #__pf_user_access_level WHERE project_id = '$ws' AND user_id = '$id'";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

			if($acl) {
				$query = "INSERT INTO #__pf_user_access_level VALUES(NULL, '$acl', '$id', '$ws')";
				       $db->setQuery($query);
				       $db->query();

				if($db->getErrorMsg()) {
			        $this->AddError($db->getErrorMsg());
			        return false;
		        }
			}
		}

		// Update groups
		if($ws) {
			$query = "SELECT id FROM #__pf_groups WHERE project = '$ws'";
			       $db->setQuery($query);
			       $tmp_group = $db->loadResultArray();

            if(!is_array($tmp_group)) $tmp_group = array();

			if(@count($tmp_group)) {
				$query = "DELETE FROM #__pf_group_users"
		               . "\n WHERE user_id = '$id' AND group_id IN(".implode(',', $tmp_group).")";
		               $db->setQuery($query);
		               $db->query();

		        if($db->getErrorMsg()) {
			        $this->AddError($db->getErrorMsg());
			        return false;
		        }
			}

		    $looped = array();

		    foreach ($groups AS $pgroup)
		    {
                foreach($pgroup AS $group)
                {
                    $group = (int) $group;

                    if(in_array($group, $looped)) {
                        continue;
                    }
                    else {
                        $looped[] = $group;
                    }

		    	    if(!$group) {
		    		    continue;
		    	    }

		    	    $query = "INSERT INTO #__pf_group_users VALUES(NULL, '$group', '$id')";
		    	           $db->setQuery($query);
		    	           $db->query();

		    	    if($db->getErrorMsg()) {
			            $this->AddError($db->getErrorMsg());
			            return false;
		            }
                }
		    }
		}

		// Update joomla account
		if($flag == 'system_administrator') {
		    if($jversion->RELEASE != '1.5') {
    		    $juser = JUser::getInstance($id);
    		}
    		else {
                $juser = new JUser($id);
            }

		    $post  = JRequest::get('post');

		    $post['username'] = JRequest::getVar('username', '', 'post', 'username');
		    $post['params']   = array();
		    $post['id']       = $id;

		    if($jversion->RELEASE != '1.5') {
                $jform = JRequest::getVar('jform', array(), 'post', 'array');
                $post['groups'] = $jform['groups'];
            }

		    if (!$juser->bind($post)) {
			    $this->AddError( $juser->getError() );
			    return false;
		    }

		    if (!$juser->save(true)) {
                $this->AddError( $juser->getError() );
			    return false;
		    }
		}

		return true;
	}

	public function UpdateAccessLevel($id)
	{
	    $db     = PFdatabase::GetInstance();
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();

		$title = JRequest::getVar('title');
		$score = (int) JRequest::getVar('score');
		$flag  = JRequest::getVar('f');
		$users = JRequest::getVar('user_id', array(), 'array');

		$my_flag  = $user->GetFlag();
		$my_score = $user->GetScore();
		$project  = $user->GetWorkspace();

		$restricted = array();
		$params = array('accesslevel_0', 'accesslevel_18', 'accesslevel_19',
		                'accesslevel_20', 'accesslevel_21', 'accesslevel_23',
		                'accesslevel_24', 'accesslevel_25', 'accesslevel_pa');

		foreach ($params AS $param)
		{
			$restricted[] = $config->Get($param, 'system');
		}

		// sanitize
		if(!$id) return false;
		if(in_array($id, $restricted) && $my_flag != 'system_administrator') return false;

		if($my_flag != 'system_administrator') {
			if($score > $my_score) $score = $my_score;
			if($flag == 'system_administrator') $flag = "";
			if($flag == 'project_administrator' && $my_flag != 'project_administrator') $flag = "";

			if(!$project) {
				$this->AddError('V_PROJECT');
				return false;
			}
		}

		$query = "SELECT project FROM #__pf_access_levels WHERE id = '$id'";
		       $db->setQuery($query);
		       $project = (int) $db->loadResult();

		$query = "UPDATE #__pf_access_levels SET title = '$title', score = '$score', flag = '$flag'"
		       . "\n WHERE id = '$id'";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
		    $this->AddError($db->getErrorMsg());
			return false;
		}

		$looped = array();
		if(count($users) > 0) {
			$query = "DELETE FROM #__pf_user_access_level WHERE accesslvl = '$id'";
			       $db->setQuery($query);
			       $db->query();

			foreach ($users AS $uid)
			{
				$uid = (int) $uid;
				if(!$uid) continue;

				if(!in_array($uid, $looped)) {
					$query = "INSERT INTO #__pf_user_access_level VALUES(NULL, '$id', '$uid', '$project')";
				           $db->setQuery($query);
				           $db->query();

				    if($db->getErrorMsg()) {
			            $this->AddError($db->getErrorMsg());
			            return false;
		            }
		            $looped[] = $uid;
				}
			}
		}
		return true;
	}

	public function Delete($cid)
	{
		$user    = PFuser::GetInstance();
		$project = $user->GetWorkspace();
		$db = PFdatabase::GetInstance();
        $s  = true;

        $query = "SELECT id FROM #__pf_tasks WHERE project = '$project'";
			   $db->setQuery($query);
			   $tasks = $db->loadResultArray();

		if(!is_array($tasks)) $tasks = array();

        $query = "SELECT id FROM #__pf_groups WHERE project = '$project'";
               $db->setQuery($query);
               $groups = $db->loadResultArray();

        if(!is_array($groups)) $groups = array();

		foreach ($cid AS $id)
		{
			$id = (int) $id;
			$query = array();

			if(count($tasks)) {
                $query[] = "DELETE FROM #__pf_task_users WHERE task_id IN(".implode(',',$tasks).")"
                         . "\n AND user_id = '$id'";
            }

            if(count($groups)) {
                $query[] = "DELETE FROM #__pf_group_users WHERE group_id IN(".implode(',', $groups).")"
                         . "\n AND user_id = '$id'";
            }

			$query[] = "DELETE FROM #__pf_project_members WHERE user_id = '$id' AND project_id = '$project'";
			$query[] = "DELETE FROM #__pf_user_access_level WHERE user_id = '$id' AND project_id = '$project'";

			foreach ($query AS $q)
			{
				$db->setQuery($q);
				$db->query();
				$e = $db->getErrorNum();

				if($e) {
				    $this->AddError($db->getErrorMsg());
					$s = false;
				}
			}
		}
		return $s;
	}

	public function DeleteAccessLevel($cid = array())
	{
	    $db = PFdatabase::GetInstance();

		if(count($cid) < 1) return false;

		$cid = implode(',', $cid);

		$query = "DELETE FROM #__pf_access_levels WHERE id IN($cid)";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		$query = "DELETE FROM #__pf_user_access_level WHERE accesslvl IN($cid)";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		return true;
	}

	public function DenyRequests($tmp_cid)
	{
		global $mainframe;

		$cid  = array();
		$rows = array();

		$user = PFuser::GetInstance();
		$db   = PFdatabase::GetInstance();
		$ws   = $user->GetWorkspace();

		foreach ($tmp_cid AS $id)
		{
			$id    = (int) $id;
			$cid[] = $id;
		}

		$cid = implode(',', $cid);

		if($cid == '') return false;

		// Delete join requests
		$query = "DELETE FROM #__pf_project_members"
               . "\n WHERE user_id IN($cid) AND project_id = '$ws'"
               . "\n AND approved = '0'";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }

		return true;
	}

	public function Invite($users, $project, $force_join = 0)
    {
        $load = PFload::GetInstance();

    	require_once($load->Section('projects.class.php', 'projects'));

        if(!class_exists('PFprojectsClass')) return false;

        $class = new PFprojectsClass();
        $class->Invite($users, $project, $force_join);
        return true;
    }

    public function SyncJoomlaAccessLevels()
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();

        $query = "SELECT id FROM #__usergroups";
               $db->setQuery($query);
               $groups = $db->loadResultArray();

        if(!is_array($groups)) $groups = array();

        // Add non-existant access levels for each new group
        foreach($groups AS $group)
        {
            $pf_id     = 'accesslevel_'.intval($group);
            $pf_exists = (int) $config->Get($pf_id);

            if(!$pf_exists) {
                JRequest::setVar('title', 'PFL_ACL_AUTO_NAME');
                $new_id = (int) $this->SaveAccessLevel(true);
                if($new_id) $config->Set($pf_id, $new_id);
            }
        }

        // Delete deprecated group access levels
        $sys_config = $config->Get(NULL);
        $del_acl = array();

        foreach($sys_config AS $cfg => $value)
        {
            if(substr($cfg, 0, 12) == 'accesslevel_' && $cfg != 'accesslevel_pm' && $cfg != 'accesslevel_pa') {

                $acl_id = (int) substr($cfg, 12);

                if(!in_array($acl_id, $groups)) {

                    $del_acl[] = $acl_id;
                    $config->Delete($cfg);
                }

            }
        }

        if(count($del_acl)) $this->DeleteAccessLevel($del_acl);
    }

    public function MapGlobalAccessLevels()
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();

        $sys_config = $config->Get(NULL);
        $acls = array();

        foreach($sys_config AS $cfg => $value)
        {
            if(substr($cfg, 0, 12) == 'accesslevel_') {
                $acls[$value] = (int) substr($cfg, 12);
            }
        }

        return $acls;
    }

    public function LoadGlobalList($prefix = true)
	{
        // Load db object
        $db = PFdatabase::GetInstance();

        $query = "SELECT id FROM #__usergroups";
               $db->setQuery($query);
               $groups = $db->loadResultArray();

        if(!is_array($groups)) $groups = array();

        if($prefix) {
            $tmp_groups = array();
            foreach($groups AS $group)
            {
                $tmp_groups[] = 'accesslevel_'.intval($group);
            }
            $groups = $tmp_groups;
            unset($tmp_groups);
        }

        return $groups;
    }
}
?>
