<?php
/**
* $Id: time.controller.php 927 2012-06-25 15:21:25Z eaxs $
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

class PFtimeController extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

    public function DisplayList($id = 0)
    {
        // Load objects
        $load = PFload::GetInstance();
        $user = PFuser::GetInstance();
        $core = PFcore::GetInstance();

        // Load the class
        require_once($load->Section('time.class.php', 'time'));
        $class = new PFtimeClass();

        // Setup dynamic table
		$ob  = JRequest::getVar('ob', $user->GetProfile('timelist_ob','ti.id'));
		$od  = JRequest::getVar('od', $user->GetProfile('timelist_od','ASC'));
		$ts1 = array('DATE', 'TIME', 'TASK', 'USER', 'DESC', 'ID');
		$ts2 = array('ti.cdate', 'ti.timelog', 't.title', 'u.name', 'ti.content', 'ti.id');
		$ts3 = array('ASC', 'DESC');

        // sanitize table settings
		if(!in_array($ob, $ts2)) $ob = 'p.id';
		if(!in_array($od, $ts3)) $od = 'ASC';

		// Create table
		$table = new PFtable($ts1, $ts2, $ob, $od);

		$limit      = (int) JRequest::getVar('limit', $user->GetProfile('timelist_limit', 50));
		$ftask      = (int) JRequest::getVar('ftask', $user->GetProfile("timelist_task"));
        $fuser      = (int) JRequest::getVar('fuser', $user->GetProfile("timelist_user"));
		$limitstart = (int) JRequest::getVar('limitstart', 0);
        $keyword    = JRequest::getVar('keyword');
        $project    = $user->GetWorkspace();
        $config		= PFconfig::GetInstance();

        // Load items from database
        $total      = $class->Count($project, $keyword, $ftask, $fuser);
        $rows       = $class->LoadList( $limitstart, $limit, $ob, $od, $project, $keyword, $ftask, $fuser );

        // New form
		$form = new PFform();
		$form->SetBind(true, 'REQUEST');

        $flag     = $user->GetFlag();
        $ws_title = PFformat::WorkspaceTitle();

        $date_format 	= $config->Get('date_format');
		$now 			= strftime($date_format);

		// Load joomla pagination
		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		// Load the item to edit if requested
		$row = NULL;
        if($core->GetTask() == 'form_edit' && $id) {
            $row = $class->Load($id);
        }
        else {
            $row->cdate = null;
        }

		// Save filter and order settings in the profile
		$user->SetProfile("timelist_ob", $ob);
		$user->SetProfile("timelist_od", $od);
		$user->SetProfile("timelist_limit", $limit);
		$user->SetProfile("timelist_task", $ftask);
		$user->SetProfile("timelist_user", $fuser);

        // Check permissions
        $can_create = $user->Access('form_new', 'time');
        $can_edit   = $user->Access('form_edit', 'time');
        $can_delete = $user->Access('task_delete', 'time');

        // Include the output file
		require_once( $load->SectionOutput('list_time.php','time') );

		// Unset objects
		unset($core,$load,$user,$rows,$form,$class);
    }

    public function Save()
    {
        // Load objects
        $load = PFload::GetInstance();
        $user = PFuser::GetInstance();

        // Include the class
        require_once($load->Section('time.class.php'));
        $class = new PFtimeClass();

        // Capture user input
        $task    = (int) JRequest::getVar('time_task');
        $hours   = (int) JRequest::getVar('hours') * 60;
        $minutes = (int) JRequest::getVar('minutes');
        $cdate   = strtotime(JRequest::getVar('cdate'));
        $desc    = JRequest::getVar('text');
        $user    = $user->GetId();
        $time    = $hours + $minutes;

        if($desc == PFformat::Lang('QUICK_NOTE')) $desc = "";

        // Save record
        $success = $class->Save($user, $task, $desc, $cdate, $time);

        // Redirect
        if(!$success) {
            $this->SetRedirect("section=time", 'TIME_E_SAVE');
            return false;
        }

        $this->SetRedirect("section=time", 'TIME_S_SAVE');
        return true;
    }

    public function Update($id)
    {
        // Load objects
        $load = PFload::GetInstance();

        // Include the class
        require_once($load->Section('time.class.php'));
        $class = new PFtimeClass();

        // Get user input
        $task    = (int) JRequest::getVar('time_task');
        $hours   = (int) JRequest::getVar('hours') * 60;
        $minutes = (int) JRequest::getVar('minutes');
        $cdate   = strtotime(JRequest::getVar('cdate'));
        $desc    = JRequest::getVar('text');
        $time    = $hours + $minutes;

        if($desc == PFformat::Lang('QUICK_NOTE')) $desc = "";

        // Update
        $success = $class->Update($id, $task, $desc, $cdate, $time);

        // Redirect
        if(!$success) {
            $this->SetRedirect("section=time", 'MSG_E_UPDATE');
            return false;
        }

        $this->SetRedirect("section=time", 'MSG_S_UPDATE');
        return true;
    }

    public function Delete($cid)
    {
        // Load objects
        $load = PFload::GetInstance();

        // Include the class
        require_once($load->Section('time.class.php'));
        $class = new PFtimeClass();

        $success = $class->Delete($cid);

        if(!$success) {
            $this->SetRedirect("section=time", 'MSG_E_DELETE');
            return false;
        }

        $this->SetRedirect("section=time", 'MSG_S_DELETE');
        return true;
    }
}
?>