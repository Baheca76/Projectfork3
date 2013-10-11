<?php
/**
* $Id: groups.controller.php 934 2013-05-14 11:47:44Z eaxs $
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

class PFgroupsController extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

	public function DisplayList()
	{
        // Load objects
        $load     = PFload::GetInstance();
        $user     = PFuser::GetInstance();
        $config   = PFconfig::GetInstance();
        $jversion = new JVersion();

        // Include the class
		require_once($load->Section('groups.class.php'));
		$class = new PFgroupsClass();

		// Get user input
		$limit      = (int) JRequest::getVar('limit', 50);
		$limitstart = (int) JRequest::getVar('limitstart', 0);
		$order_by   = strval(JRequest::getVar('ob', 'g.id', 'POST'));
		$order_dir  = strval(JRequest::getVar('od', 'ASC', 'POST'));
		$keyword    = strval(JRequest::getVar('keyword', ''));
		$project    = $user->GetWorkspace();
		$ws_title   = PFformat::WorkspaceTitle();

        $ts1 = array('TITLE', 'DESC', 'GROUP_MEMBERS', 'ID');
		$ts2 = array('g.title', 'g.description', 'user_count', 'g.id');
		$ts3 = array('ASC', 'DESC');
		// Sanitize table settings
		if(!in_array($order_by, $ts2))  $order_by = 'g.id';
		if(!in_array($order_dir, $ts3)) $order_dir = 'ASC';
		// Create the table
		$table = new PFtable($ts1, $ts2, $order_by, $order_dir);

        // Load global groups which we cannot delete
		$restricted = array();
		$params = array('group_0', 'group_18', 'group_19',
		                'group_20', 'group_21', 'group_23',
		                'group_24', 'group_25', 'group_pa', 'group_pm');


		// Adjust for Joomla 1.6
		if($jversion->RELEASE == '1.6') {
		    // Search for new Joomla groups and create PF equivalent
		    if($project == 0) $class->SyncJoomlaGroups();

		    $params = $class->LoadGlobalList();
		    $params[] = 'group_0';
		    $params[] = 'group_pa';
		    $params[] = 'group_pm';
		}

		foreach ($params AS $param)
		{
			$restricted[] = (int) $config->Get($param, 'system');
		}

        // Load groups from db
		$total = $class->CountGroups($keyword, $project);
		$rows  = $class->LoadList($limit, $limitstart, $order_by, $order_dir, $keyword, $project);

		// Check permissions
		$can_delete = $user->Access('task_delete', 'groups');
		$can_copy   = $user->Access('task_copy', 'groups');
		$flag       = $user->GetFlag();

		// Create a new form
		$form = new PFform();
		$form->SetBind(true, 'REQUEST');

		// Include the Joomla pagination
		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		// Include the output file
		require_once( $load->SectionOutput('list_groups.php','groups') );

		// Unset objects
		unset($load,$user,$rows);
	}

	public function DisplayNew()
	{
        // Load objects
        $load = PFload::GetInstance();

        require_once($load->Section('groups.class.php'));

        // Create new form
		$form = new PFform();
		$form->SetBind(true, 'REQUEST');

		$select_user = $form->SelectUser('user_id[]', 0);
		$ws_title    = PFformat::WorkspaceTitle();

		require_once( $load->SectionOutput('form_new.php','groups') );
		unset($load,$form);
	}

	public function DisplayEdit()
	{
        // Load objects
        $load     = PFload::GetInstance();
        $user     = PFuser::GetInstance();
        $config   = PFconfig::GetInstance();
        $jversion = new JVersion();

        // Include the class
		require_once($load->Section('groups.class.php'));
		$class = new PFgroupsClass();

		// Load group data
		$id    = (int) JRequest::getVar('id');
		$row   = $class->Load($id);

		// Create new form
		$form  = new PFform();
		$form->SetBind(true, $row);

		// Get user flag
		$flag = $user->GetFlag();

		// Load global groups which we cannot delete
		$restricted = array();
		$params = array('group_0', 'group_18', 'group_19',
		                'group_20', 'group_21', 'group_23',
		                'group_24', 'group_25', 'group_pa', 'group_pm');


		// Adjust for Joomla 1.6
		if($jversion->RELEASE == '1.6') {
		    $params = $class->LoadGlobalList();
		    $params[] = 'group_0';
		    $params[] = 'group_pa';
		    $params[] = 'group_pm';
		}

		foreach ($params AS $param)
		{
			$restricted[] = (int) $config->Get($param, 'system');
		}

		// Only system administrators can edit global groups
		if(in_array($id, $restricted) && $flag != 'system_administrator') {
			$this->SetRedirect("section=groups", 'PFL_GROUP_EDIT_RESTRICT');
			return false;
		}

		$select_user = $form->SelectUser('user_id[]', 0);
		$ws_title    = PFformat::WorkspaceTitle();

		// Include the output form
		require_once( $load->SectionOutput('form_edit.php','groups') );

		// Unset data
		unset($load,$user,$config,$form,$row);
	}

	public function Save()
	{
        // Load objects
        $load = PFload::GetInstance();

        // Include class
		require_once($load->Section('groups.class.php'));
		$class = new PFgroupsClass();

		if(!$class->Save()) {
			$this->SetRedirect("section=groups", 'GROUP_E_SAVE');
			return false;
		}
		else {
		    // Clean user cache
		    PFcache::Clean('user');

			$this->SetRedirect("section=groups", 'GROUP_S_SAVE');
			return true;
		}
	}

	public function Update()
	{
        // Load objects
        $load     = PFload::GetInstance();
        $user     = PFuser::Getinstance();
        $config   = PFconfig::GetInstance();
        $jversion = new JVersion();

		require_once($load->Section('groups.class.php'));
		$class = new PFgroupsClass();

		// Get group id
		$id = (int) JRequest::getVar('id');

		// Get user flag
		$flag = $user->GetFlag();

		// Load global groups which we cannot delete
		$restricted = array();
		$params = array('group_0', 'group_18', 'group_19',
		                'group_20', 'group_21', 'group_23',
		                'group_24', 'group_25', 'group_pa', 'group_pm');


		// Adjust for Joomla 1.6
		if($jversion->RELEASE == '1.6') {
		    $params = $class->LoadGlobalList();
		    $params[] = 'group_0';
		    $params[] = 'group_pa';
		    $params[] = 'group_pm';
		}

		foreach ($params AS $param)
		{
			$restricted[] = (int) $config->Get($param, 'system');
		}

		// only system administrators can edit global groups
		if(in_array($id, $restricted) && $flag != 'system_administrator') {
			$this->SetRedirect("section=groups", 'GROUP_EDIT_RESTRICT');
			return false;
		}

		if(!$class->Update($id)) {
			$this->SetRedirect("section=groups", 'MSG_E_UPDATE');
			return false;
		}
		else {
		    // Clean user cache
		    PFcache::Clean('user');

			$this->SetRedirect("section=groups", 'MSG_S_UPDATE');
			return true;
		}
	}

	public function Delete($cid)
	{
        // Load objects
        $load     = PFload::GetInstance();
        $config   = PFconfig::GetInstance();
        $jversion = new JVersion();

		// Load global groups which we cannot delete
		$restricted = array();
		$params = array('group_0', 'group_18', 'group_19',
		                'group_20', 'group_21', 'group_23',
		                'group_24', 'group_25', 'group_pa', 'group_pm');

		// Include class
		require_once($load->Section('groups.class.php'));
		$class = new PFgroupsClass();

		// Adjust for Joomla 1.6
		if($jversion->RELEASE == '1.6') {
		    $params = $class->LoadGlobalList();
		    $params[] = 'group_0';
		    $params[] = 'group_pa';
		    $params[] = 'group_pm';
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



		if(!$class->Delete($cid)) {
			$this->SetRedirect("section=groups", 'MSG_E_DELETE');
			return false;
		}
		else {
		    // Clean user cache
		    PFcache::Clean('user');

			$this->SetRedirect("section=groups", 'MSG_S_DELETE');
			return true;
		}
	}

	public function Copy()
	{
        // Load objects
        $load = PFload::GetInstance();

        // Include class
		require_once($load->Section('groups.class.php'));
		$class = new PFgroupsClass();

		$cid = JRequest::getVar('cid', array());

		if(!$class->Copy($cid)) {
			$this->SetRedirect("section=groups", 'MSG_E_COPY');
			return false;
		}
		else {
			$this->SetRedirect("section=groups", 'MSG_S_COPY');
			return true;
		}
	}
}
?>