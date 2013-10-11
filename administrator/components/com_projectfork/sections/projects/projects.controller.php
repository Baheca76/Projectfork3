<?php
/**
* $Id: projects.controller.php 888 2011-06-25 21:12:06Z eaxs $
* @package    Projectfork
* @subpackage Projects
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

/**
* @package       Projectfork
* @subpackage    Projects
**/
class PFprojectsController extends PFobject
{
    /**
     * Constructor
     **/
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Loads projects from db and then shows them in the output file "list_projects.php"
     **/
	public function DisplayList()
	{
	    $load   = PFload::GetInstance();
	    $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();

		// Setup dynamic data table
		$ob  = JRequest::getVar('ob', $user->GetProfile('projectlist_ob', 'p.id'));
		$od  = JRequest::getVar('od', $user->GetProfile('projectlist_od', 'ASC'));
		$ts1 = array('TITLE', 'PROJECT_FOUNDER', 'CREATED_ON', 'DEADLINE', 'ID');
		$ts2 = array('p.title', 'u.name', 'p.cdate', 'p.edate', 'p.id');
		$ts3 = array('ASC', 'DESC');
		// Sanitize table settings
		if(!in_array($ob, $ts2)) $ob = 'p.id';
		if(!in_array($od, $ts3)) $od = 'ASC';
		// Create the table
		$table = new PFtable($ts1, $ts2, $ob, $od);

		// Check permissions
		$can_copy    = $user->Access('task_copy', 'projects');
		$can_delete  = $user->Access('task_delete', 'projects');
		$can_archive = $user->Access('task_archive', 'projects');
		$can_approve = $user->Access('task_approve', 'projects');
		$can_create  = $user->Access('form_new', 'projects');

		// Wizard related
		$wizard = (int) $config->Get('use_wizard');
		$my_projects = $user->Permission('author');

		$class      = new PFprojectsClass();
		$limit      = (int) JRequest::getVar('limit', $user->GetProfile('projectlist_limit', 50));
		$limitstart = (int) JRequest::getVar('limitstart', 0);
		$status     = (int) JRequest::getVar('status', $user->GetProfile('projectlist_status', 0));
		$cat        = JRequest::getVar('cat', $user->GetProfile('projectlist_category'));
        $keyword    = JRequest::getVar('keyword');

        $use_cats = (int) $config->Get('use_cats', 'projects');

        if(!$use_cats) $cat = "";

        // Prepare category colors
        $tmp_cats = trim($config->Get('cats', 'projects'));
        $tmp_cats = explode("\n", $tmp_cats);
        $cats = array();
        $cat_names = array();

        foreach($tmp_cats AS $c)
        {
            $ec    = explode(':', $c);
            $ename = trim($ec[0]);
            $alias = trim(JFilterOutput::stringURLSafe(trim($ec[0])));
            $cat_names[$alias] = $ename;

            if(count($ec) == 2) {
                $cname = htmlspecialchars(trim($ec[0]), ENT_QUOTES);
                $cname = JFilterOutput::stringURLSafe($cname);
                $cats[$cname] = $ec[1];
            }
        }

        // Sanitize filter
        if($status == 1 && !$can_archive) $status = 0;
        if($status == 2 && !$can_approve) $status = 0;

        // Get project list
		$total      = $class->Count($keyword, $status, $cat);
        $rows       = $class->LoadList($limitstart, $limit, $keyword, $ob, $od, $status, $cat);
        $flag       = $user->Permission('flag');
        $ws_title   = PFformat::WorkspaceTitle();
        $keyword    = urlencode($keyword);

        // Filter
		$filter = "";
		if($limitstart) $filter .= "&limitstart=$limitstart";
		if($keyword) $filter .= "&keyword=$keyword";

		// Load joomla pagination
		$pagination = new JPagination($total, $limitstart, $limit);

		// Create a new form
		$form = new PFform();
		$form->SetBind(true, 'REQUEST');

		// Save filter and order settings
		$user->SetProfile("projectlist_ob", $ob);
		$user->SetProfile("projectlist_od", $od);
		$user->SetProfile("projectlist_status", $status);
		$user->SetProfile("projectlist_limit", $limit);
		$user->SetProfile("projectlist_category", $cat);

		require_once( $load->SectionOutput('list_projects.php', 'projects') );
		unset($load,$user,$rows,$pagination,$class);
	}

    /**
     * Loads the form for creating a new project. Includes output file "form_new.php"
     **/
	public function DisplayNew()
	{
	    // Include helper
	    require_once($this->GetHelper('projects'));

        $config = PFconfig::GetInstance();
        $user   = PFuser::GetInstance();
        $load   = PFload::GetInstance();

        // Setup form stuff
		$form   = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$editor = JFactory::getEditor();

		$date_format = $config->Get('date_format');
		//$now 	= JHTML::_('date',"",$date_format);
		$now 	= strftime($date_format);
		if($config->Get('12hclock')) {
			$hour		= date('h');
		    $minute 	= date('i');
			//round the minutes to increments of 5 given that the select list shows minutes in this increment.
			if ($minute > 55){
				$hour += 1;
			}
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
        $select_user = $form->SelectUser('member[]',-1, '', true, 'username');

        // Get config settings
		$allow_color   = (int) $config->Get('allow_color', 'projects');
		$allow_logo    = (int) $config->Get('allow_logo', 'projects');
		$use_editor    = (int) $config->Get('use_editor', 'projects');
		$invite_select = (int) $config->Get('invite_select', 'projects');

		$ws_title = PFformat::WorkspaceTitle();
		$flag     = $user->GetFlag();

		// Include the form
		require_once( $load->SectionOutput('form_new.php', 'projects') );

		// Unset objects
		unset($config,$user,$form,$editor,$load);
	}

    /**
     * Loads the form for editing a new project. Includes output file "form_edit.php"
     **/
	public function DisplayEdit($id)
	{
	    // Include helper
	    require_once($this->GetHelper('projects'));

	    // Load objects
	    $load   = PFload::GetInstance();
	    $config = PFconfig::GetInstance();
	    $user   = PFuser::GetInstance();
	    $user   = PFuser::GetInstance();
	    $editor = JFactory::getEditor();

	    // Include the class
		require_once( $load->Section('projects.class.php', 'projects') );
		$class = new PFprojectsClass();

	    // Load Project to edit
		$row = $class->Load($id);

	    // Check for valid id
	    if(!$id || !is_object($row)) {
            $this->SetRedirect('section=projects', 'MSG_ITEM_NOT_FOUND');
            return false;
        }

		// Check author permission
	    if(!$user->Access('form_edit', 'projects', $class->GetAuthor($id))) {
            $this->SetRedirect('section=projects', 'NOT_AUTHORIZED');
            return false;
        }



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

		// Get config settings
		$allow_color   = (int) $config->Get('allow_color', 'projects');
		$allow_logo    = (int) $config->Get('allow_logo', 'projects');
		$use_editor    = (int) $config->Get('use_editor', 'projects');
		$invite_select = (int) $config->Get('invite_select', 'projects');

		$date_format 	= $config->Get('date_format');
		$date_exists	= false;

		if ($row->edate) {
			$now = strftime($date_format, $row->edate);
			$date_exists = true;
		}
		else {
			$now = strftime($date_format);
		}

		$ws_title 		= PFformat::WorkspaceTitle();
		$flag     		= $user->GetFlag();

		// Create new form
		$form   = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$form->SetBind(true, $row);
		$select_user = $form->SelectUser('member[]',-1, '', true, 'username');

		// Include the edit form
		require_once( $load->SectionOutput('form_edit.php', 'projects') );
	}

	public function DisplayDetails($id)
	{
	    // Load Objects
	    $load = PFload::GetInstance();
	    $user = PFuser::GetInstance();
	    $com  = PFcomponent::GetInstance();

	    // Include the class
		require_once( $load->Section('projects.class.php', 'projects') );
		$class = new PFprojectsClass();

        $location = $com->Get('location');
        $ws_title = PFformat::WorkspaceTitle();

		// Load the project data
		$row = $class->Load($id);

		// Check for valid id
	    if(!$id || !is_object($row)) {
            $this->SetRedirect('section=projects', 'MSG_ITEM_NOT_FOUND');
            return false;
        }

		// Check author permission
	    if(!$user->Access('display_details', 'projects', $class->GetAuthor($id))) {
            $this->SetRedirect('section=projects', 'NOT_AUTHORIZED');
            return false;
        }

		// Check for valid record
		if(!$row) {
            $this->SetRedirect('section=projects', 'MSG_ITEM_NOT_FOUND');
            return false;
        }

		// Include the output file
		require_once( $load->SectionOutput('display_details.php', 'projects') );
	}

	public function Save()
	{
        $load  = PFload::GetInstance();
		$class = new PFprojectsClass();

		// Setup list filter
		$l  = (int) JRequest::getVar('limit');
        $ls = (int) JRequest::getVar('limitstart');
        $k  = (int) JRequest::getVar('keyword');
        if($k) $k = "&keyword=".urlencode($k);

		$link = "section=projects&limit=$l&limitstart=$ls".$k;

		// Capture user input for validation
		$title = JRequest::getVar('title');

		// Validate user input
		if(!$class->Validate($title)) {
            $this->SetRedirect($link, 'PROJECTS_E_SAVE');
            return false;
        }

		// Save, then redirect
		if(!$class->Save()) { // Error
			$this->setRedirect($link, 'PROJECTS_E_SAVE');
            return false;
		}

		// Success
		$this->setRedirect($link, 'PROJECTS_S_SAVE');
        return true;
	}

	public function Update($id)
	{
	    // Load objects
	    $load = PFload::GetInstance();
	    $user = PFuser::GetInstance();

	    // Include the class
		require_once( $load->Section('projects.class.php', 'projects') );
		$class = new PFprojectsClass();

		$row = $class->Load($id);

		// Setup list filter
		$l  = (int) JRequest::getVar('limit');
        $ls = (int) JRequest::getVar('limitstart');
        $k  = (int) JRequest::getVar('keyword');

        $a  = (int) JRequest::getVar('apply');
        $t  = "";

        if($a) $t = "&task=form_edit&id=$id";
        if($k) $k = "&keyword=".urlencode($k);

        $link = "section=projects$t&limit=$l&limitstart=$ls".$k;

        // Check for valid id
	    if(!$id || !is_object($row)) {
            $this->SetRedirect($link, 'MSG_ITEM_NOT_FOUND');
            return false;
        }

        // Check author permission
	    if(!$user->Access('task_update', 'projects', $class->GetAuthor($id))) {
            $this->SetRedirect($link, 'NOT_AUTHORIZED');
            return false;
        }

		// Validate user input
		if(!$class->Validate(JRequest::getVar('title'), $id)) {
			$this->SetRedirect($link, 'MSG_E_UPDATE');
			return false;
		}

		// Update, then redirect
		if(!$class->Update($id)) { // Error
			$this->SetRedirect($link, 'MSG_E_UPDATE');
			return false;
		}
		else { // Success
			$this->SetRedirect($link, 'MSG_S_UPDATE');
			return true;
		}
	}

	public function Delete($tmp_cid)
	{
	    // Load objects
	    $load = PFload::GetInstance();
	    $user = PFuser::GetInstance();

	    // Include the class
		require_once( $load->Section('projects.class.php', 'projects') );
		$class = new PFprojectsClass();

		// Setup list filter
		$l  = (int) JRequest::getVar('limit');
        $ls = (int) JRequest::getVar('limitstart');
        $k  = (int) JRequest::getVar('keyword');

        if($k) $k = "&keyword=".urlencode($k);
        $link = "section=projects&limit=$l&limitstart=$ls".$k;

        // Filter IDs
        $cid = array();
        foreach($tmp_cid AS $id)
        {
            $id = (int) $id;
            if(!$id) continue;
            if($user->Access('task_delete', 'projects', $class->GetAuthor($id))) {
                $cid[] = $id;
            }
        }

        // Check if there are any IDs left
        if(!count($cid)) {
            $this->SetRedirect($link, 'MSG_E_DELETE');
            return false;
        }

        // Delete, then redirect
		if(!$class->Delete($cid)) { // Error
			$this->SetRedirect($link, 'MSG_E_DELETE');
            return false;
		}
		else { // Success
			$this->SetRedirect($link, 'MSG_S_DELETE');
			return true;
		}
	}

	public function Copy($tmp_cid)
	{
	    // Load objects
	    $load = PFload::GetInstance();
	    $user = PFuser::GetInstance();

	    // Include the class
		require_once( $load->Section('projects.class.php', 'projects') );
		$class = new PFprojectsClass();

		// Setup list filter
		$l  = (int) JRequest::getVar('limit');
        $ls = (int) JRequest::getVar('limitstart');
        $k  = (int) JRequest::getVar('keyword');

        if($k) $k = "&keyword=".urlencode($k);
        $link = "section=projects&limit=$l&limitstart=$ls".$k;

        // Filter IDs
        $cid = array();
        foreach($tmp_cid AS $id)
        {
            $id = (int) $id;
            if(!$id) continue;
            if($user->Access('task_copy', 'projects', $class->GetAuthor($id))) {
                $cid[] = $id;
            }
        }

        // Check if there are any IDs left
        if(!count($cid)) {
            $this->SetRedirect($link, 'MSG_E_COPY');
            return false;
        }

        // Copy, then redirect
		if(!$class->Copy($cid)) { // Error
			$this->SetRedirect($link, 'MSG_E_COPY');
			return false;
		}
		else { // Success
			$this->SetRedirect($link, 'MSG_S_COPY');
			return true;
		}
	}

	public function Archive($tmp_cid)
	{
	    // Load objects
	    $load = PFload::GetInstance();
	    $user = PFuser::GetInstance();

	    // Include the class
		require_once( $load->Section('projects.class.php', 'projects') );
		$class = new PFprojectsClass();

		// Setup list filter
		$l  = (int) JRequest::getVar('limit');
        $ls = (int) JRequest::getVar('limitstart');
        $k  = (int) JRequest::getVar('keyword');

        if($k) $k = "&keyword=".urlencode($k);
        $link = "section=projects&limit=$l&limitstart=$ls".$k;

		// Filter IDs
        $cid = array();
        foreach($tmp_cid AS $id)
        {
            $id = (int) $id;
            if(!$id) continue;
            if($user->Access('task_archive', 'projects', $class->GetAuthor($id))) {
                $cid[] = $id;
            }
        }

        // Check if there are any IDs left
        if(!count($cid)) {
            $this->SetRedirect($link, 'MSG_E_ARCHIVE');
            return false;
        }

        // Archive, then redirect
		if(!$class->Archive($cid)) { // Error
			$this->SetRedirect($link, 'MSG_E_ARCHIVE');
			return false;
		}
		else { // Success
			$this->SetRedirect($link, 'MSG_S_ARCHIVE');
			return true;
		}
	}

	public function Activate($tmp_cid)
	{
	    // Load objects
	    $load = PFload::GetInstance();
	    $user = PFuser::GetInstance();

		// Include the class
		require_once( $load->Section('projects.class.php', 'projects') );
		$class = new PFprojectsClass();

		// Setup list filter
		$l  = (int) JRequest::getVar('limit');
        $ls = (int) JRequest::getVar('limitstart');
        $k  = (int) JRequest::getVar('keyword');

        if($k) $k = "&keyword=".urlencode($k);
        $link = "section=projects&limit=$l&limitstart=$ls".$k;

		// Filter IDs
        $cid = array();
        foreach($tmp_cid AS $id)
        {
            $id = (int) $id;
            if(!$id) continue;
            if($user->Access('task_activate', 'projects', $class->GetAuthor($id))) {
                $cid[] = $id;
            }
        }

        // Check if there are any IDs left
        if(!count($cid)) {
            $this->SetRedirect($link, 'MSG_E_ACTIVATE');
            return false;
        }

		// Activate, then redirect
		if(!$class->Activate($cid)) { // Error
			$this->SetRedirect($link, 'MSG_E_ACTIVATE');
			return false;
		}
		else { // Success
			$this->SetRedirect($link, 'MSG_S_ACTIVATE');
			return true;
		}
	}

	public function Approve($tmp_cid)
	{
		// Load objects
	    $load = PFload::GetInstance();
	    $user = PFuser::GetInstance();

		// Include the class
		require_once( $load->Section('projects.class.php', 'projects') );
		$class = new PFprojectsClass();

		// Setup list filter
		$l  = (int) JRequest::getVar('limit');
        $ls = (int) JRequest::getVar('limitstart');
        $k  = (int) JRequest::getVar('keyword');

        if($k) $k = "&keyword=".urlencode($k);
        $link = "section=projects&limit=$l&limitstart=$ls".$k;

		// Filter IDs
        $cid = array();
        foreach($tmp_cid AS $id)
        {
            $id = (int) $id;
            if(!$id) continue;
            if($user->Access('task_approve', 'projects', $class->GetAuthor($id))) {
                $cid[] = $id;
            }
        }

        // Check if there are any IDs left
        if(!count($cid)) {
            $this->SetRedirect($link, 'MSG_E_APPROVE');
            return false;
        }

		// Approve, then redirect
		if(!$class->approve($cid)) { // Error
			$this->SetRedirect($link, 'MSG_E_APPROVE');
			return false;
		}
		else { // Success
			$this->SetRedirect($link, 'MSG_S_APPROVE');
			return true;
		}
	}

	public function RequestJoin($id, $user_id)
	{
		$class = new PFprojectsClass();

		if(!$class->SendJoinRequest($id, $user_id)) {
			$this->SetRedirect("section=projects&task=display_details&id=".$id, 'MSG_E_JOINREQ');
			return false;
		}

		$this->SetRedirect("section=projects&task=display_details&id=".$id, 'MSG_S_JOINREQ');
		return true;
	}

	public function AcceptInvitation()
	{
		$class = new PFprojectsClass();
		$hash  = JRequest::getVar('iid');

		if(!$class->AcceptInvitation($hash)) {
			$this->SetRedirect("section=projects", 'MSG_INVITE_S_FAILED');
			return false;
		}

        $this->SetRedirect("section=projects", 'MSG_INVITE_S_SUCCESS');
        return true;
	}

	public function DeclineInvitation($hash, $email)
	{
		$class = new PFprojectsClass();

		if(!$class->DeclineInvitation($hash, $email)) {
			$this->SetRedirect("section=projects", 'MSG_INVITE_E_FAILED');
			return false;
		}

		$this->SetRedirect("section=projects", 'MSG_INVITE_E_SUCCESS');
		return true;
	}
}
?>