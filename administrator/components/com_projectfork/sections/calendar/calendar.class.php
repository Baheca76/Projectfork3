<?php
/**
* $Id: calendar.class.php 838 2010-11-25 20:49:32Z eaxs $
* @package    Projectfork
* @subpackage Calendar
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

class PFcalendarClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }
    
	public function LoadMonth($year, $month, $days_of_month)
	{
	    $user   = PFuser::GetInstance();
	    $db     = PFdatabase::GetInstance();
	    $config = PFconfig::GetInstance();
	    
		$start_date = mktime(0,0,0,$month,1,$year);
		$end_date   = mktime(23,59,0,$month,$days_of_month,$year);
		$workspace  = $user->GetWorkspace();
		$day        = 1;
		$rows       = array();
		$events     = array();
        $tasks      = array();
        $milestones = array();
        $projects   = array();

		if(!$workspace) $workspace = implode(',', $user->Permission('projects'));
        $syntax = (strlen($workspace)>1) ? "project IN($workspace)" : "project = '$workspace'";
        
		// Load events
        if($workspace) {
            $query = "SELECT * FROM #__pf_events"
                   . "\n WHERE sdate BETWEEN '$start_date' AND '$end_date'"
                   . "\n AND $syntax"
                   . "\n GROUP BY id";
                   $db->setQuery($query);
                   $events = $db->loadObjectList();
                   
            if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }
        }

		if(!is_array($events)) $events = array();
		       
		// Load milestones
		if($config->Get('display_milestones', 'calendar') && $workspace) {
		    $query = "SELECT * FROM #__pf_milestones"
                   . "\n WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . "\n AND $syntax"
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $milestones = $db->loadObjectList();
		       
		    if(!is_array($milestones)) $milestones = array();
		    
		    if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }
		}
		else {
			$milestones = array();
		}
		
		// Load tasks
		if($config->Get('display_tasks', 'calendar') && $workspace) {
			$query = "SELECT * FROM #__pf_tasks"
                   . "\n WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . "\n AND $syntax"
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $tasks = $db->loadObjectList();
		       
		    if(!is_array($tasks)) $tasks = array();
		    
		    if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }
		}
		else {
			$tasks = array();
		}
		
		// Load projects
		$syntax = (strlen($workspace)>1) ? "id IN($workspace)" : "id = '$workspace'";
		
		if($config->Get('display_projects', 'calendar') && $workspace) {
			$query = "SELECT * FROM #__pf_projects"
                   . "\n WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . "\n AND $syntax"
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $projects = $db->loadObjectList();
		       
		    if(!is_array($projects)) $projects = array();
		    
		    if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }
		}
		else {
			$projects = array();
		}
		
		while ($day <= $days_of_month) 
		{
		    $start_date = mktime(0,0,0,$month,$day,$year);
		    $end_date   = mktime(23,59,59,$month,$day,$year);
		    $rows[$day] = array();
            $rows[$day]['events']     = array();
            $rows[$day]['milestones'] = array();
            $rows[$day]['tasks']      = array();
            $rows[$day]['projects']   = array();
            
            // Add events
		    foreach ($events AS $r)
		    {
		    	if($r->sdate >= $start_date && $r->sdate <= $end_date) $rows[$day]['events'][] = $r;

		    }
		    // Add milestones
		    foreach ($milestones AS $r)
		    {
		    	if($r->edate >= $start_date && $r->edate <= $end_date) $rows[$day]['milestones'][] = $r;
		    }
		    // Add tasks
		    foreach ($tasks AS $r)
		    {
		    	if($r->edate >= $start_date && $r->edate <= $end_date) $rows[$day]['tasks'][] = $r;
		    }
		    // Add projects
		    foreach ($projects AS $r)
		    {
		    	if($r->edate >= $start_date && $r->edate <= $end_date) $rows[$day]['projects'][] = $r;
		    }
			$day++;
		}

		return $rows; 
	}
	
	public function LoadWeek($start_of_week_y, $start_of_week_m, $start_of_week, $end_of_week_y, $end_of_week_m, $end_of_week)
	{
	    $user   = PFuser::GetInstance();
	    $db     = PFdatabase::GetInstance();
	    $config = PFconfig::GetInstance();
	    
		$start_date = mktime(0,0,0,$start_of_week_m, $start_of_week, $start_of_week_y);
		$end_date   = mktime(23,59,0,$end_of_week_m, $end_of_week, $end_of_week_y);
		$workspace  = $user->GetWorkspace();
		$tmp_date   = $start_date;
		$rows       = array();
        $day        = 1;
        $hour       = 0;
		
		// load events
		$query = "SELECT * FROM #__pf_events"
               . "\n WHERE sdate BETWEEN '$start_date' AND '$end_date'"
               . $db->projectFilter('project', $workspace)
		       . "\n GROUP BY id";
		       $db->setQuery($query);
		       $events = $db->loadObjectList();
        
		if(!is_array($events)) $events = array();
		       
		// Load milestones
		if($config->Get('display_milestones', 'calendar') && $workspace != '') {
		    $query = "SELECT * FROM #__pf_milestones WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . $db->projectFilter('project', $workspace)
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $milestones = $db->loadObjectList();
		       
		    if(!is_array($milestones)) $milestones = array();
		}
		else {
			$milestones = array();
		}
		
		// load tasks
		if($config->Get('display_tasks', 'calendar') && $workspace != '') {
			$query = "SELECT * FROM #__pf_tasks WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . $db->projectFilter('project', $workspace)
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $tasks = $db->loadObjectList();
		       
		    if(!is_array($tasks)) $tasks = array();
		}
		else {
			$tasks = array();
		}
		
		// Load projects
		if($config->Get('display_projects', 'calendar') && $workspace != '') {
			$query = "SELECT * FROM #__pf_projects WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . $db->projectFilter('id', $workspace)
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $projects = $db->loadObjectList();
		       
		    if(!is_array($projects)) $projects = array();
		}
		else {
			$projects = array();
		}
		       
		while($day <= 7) 
		{
			$rows[$day] = array();
			
			if($day != 1) {
				$tmp_date   = $tmp_date + 86400;
				$start_date = $tmp_date;
				$end_date   = $start_date;
			}
			
			while($hour < 24)
			{
				$rows[$day][$hour] = array();
				$rows[$day][$hour]['events']     = array();
			    $rows[$day][$hour]['milestones'] = array();
			    $rows[$day][$hour]['tasks']      = array();
			    $rows[$day][$hour]['projects']   = array();
			    
				if($hour != 0) {
					$start_date = $start_date + 3600;
				}
				else {
					$end_date = $start_date + 3599; 
				}
				
		        $end_date = $start_date + 3599;
		        
		        foreach ($events AS $r)
		        {
		        	if($r->sdate >= $start_date && $r->sdate <= $end_date) {
		        		$rows[$day][$hour]['events'][] = $r;
		        	}
		        }
		        foreach ($milestones AS $r)
		        {
		        	if($r->edate >= $start_date && $r->edate <= $end_date) {
		        		$rows[$day][$hour]['milestones'][] = $r;
		        	}
		        }
		        foreach ($tasks AS $r)
		        {
		        	if($r->edate >= $start_date && $r->edate <= $end_date) {
		        		$rows[$day][$hour]['tasks'][] = $r;
		        	}
		        }
		        foreach ($projects AS $r)
		        {
		        	if($r->edate >= $start_date && $r->edate <= $end_date) {
		        		$rows[$day][$hour]['projects'][] = $r;
		        	}
		        }
				$hour++;
			}
		    $day++;
		    $hour = 0;
		}
		
		return $rows;
	}
	
	public function LoadDay($year, $month, $day)
	{
	    $user   = PFuser::GetInstance();
	    $db     = PFdatabase::GetInstance();
	    $config = PFconfig::GetInstance();
	    
		$start_date = mktime(0,0,0,$month,$day,$year);
		$end_date   = mktime(23,59,59,$month,$day,$year);
		$workspace  = (int) $user->GetWorkspace();
		$hour       = 0;
		$rows       = array();
		
		$query = "SELECT * FROM #__pf_events WHERE sdate BETWEEN '$start_date' AND '$end_date'"
		       . $db->projectFilter('project', $workspace)
		       . "\n GROUP BY id";
		       $db->setQuery($query);
		       $events = $db->loadObjectList();
		       
		if(!is_array($events)) $events = array();      
		
		// Load milestones
		if($config->Get('display_milestones', 'calendar') && $workspace != '') {
		    $query = "SELECT * FROM #__pf_milestones WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . $db->projectFilter('project', $workspace)
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $milestones = $db->loadObjectList();
		       
		    if(!is_array($milestones)) $milestones = array();
		}
		else {
			$milestones = array();
		}
		
		// Load tasks
		if($config->Get('display_tasks', 'calendar')) {
			$query = "SELECT * FROM #__pf_tasks WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . $db->projectFilter('project', $workspace)
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $tasks = $db->loadObjectList();
		       
		    if(!is_array($tasks)) $tasks = array();
		}
		else {
			$tasks = array();
		}
		
		// Load projects
		if($config->Get('display_projects', 'calendar') && $workspace != '') {
			$query = "SELECT * FROM #__pf_projects WHERE edate BETWEEN '$start_date' AND '$end_date'"
		           . $db->projectFilter('id', $workspace)
		           . "\n GROUP BY id";
		           $db->setQuery($query);
		           $projects = $db->loadObjectList();
		       
		    if(!is_array($projects)) $projects = array();
		}
		else {
			$projects = array();
		}
		
		while($hour < 24)
		{
			$rows[$hour]               = array();
			$rows[$hour]['events']     = array();
			$rows[$hour]['milestones'] = array();
			$rows[$hour]['tasks']      = array();
			$rows[$hour]['projects']   = array();
			
			if($hour != 0) {
				$start_date = $start_date + 3600;
			}
			else {
				$end_date = $start_date + 3599; 
			}
				
		    $end_date = $start_date + 3599;
		        
		    foreach ($events AS $r)
		    {
		    	if($r->sdate >= $start_date && $r->sdate <= $end_date) {
		    		$rows[$hour]['events'][] = $r;
		    	}
		    }
		    
		    foreach ($milestones AS $r)
		    {
		    	if($r->edate >= $start_date && $r->edate <= $end_date) {
		    		$rows[$hour]['milestones'][] = $r;
		    	}
		    }
		    
		    foreach ($tasks AS $r)
		    {
		    	if($r->edate >= $start_date && $r->edate <= $end_date) {
		    		$rows[$hour]['tasks'][] = $r;
		    	}
		    }
		    
		    foreach ($projects AS $r)
		    {
		    	if($r->edate >= $start_date && $r->edate <= $end_date) {
		    		$rows[$hour]['projects'][] = $r;
		    	}
		    }
		    
		    $hour++;
		}
		
		return $rows;
	}
	
	public function Load($id)
	{
	    $db = PFdatabase::GetInstance();
		$query = "SELECT * FROM #__pf_events WHERE id = '$id'";
		       $db->setQuery($query);
		       $row = $db->loadObject();
		
		if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
		
        unset($db);
		return $row;
	}
	
	public function Save()
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();
	    
		$s_hour  = (int) JRequest::getVar('s_hour');
		$s_min   = (int) JRequest::getVar('s_minute');
		$s_ampm  = (int) JRequest::getVar('s_ampm');
		$e_hour  = (int) JRequest::getVar('e_hour');
		$e_min   = (int) JRequest::getVar('e_minute');
		$e_ampm  = (int) JRequest::getVar('e_ampm');
		$edate   = JRequest::getVar('edate');
		$sdate   = JRequest::getVar('sdate');
		$title   = $db->Quote(JRequest::getVar('title'));
		$project = $db->Quote((int) $user->GetWorkspace());
		$now     = $db->Quote(time());
		
		$ts_sdate = $db->Quote(PFformat::ToTime($sdate,$s_hour,$s_min,$s_ampm));
		$ts_edate = $db->Quote(PFformat::ToTime($edate,$e_hour,$e_min,$e_ampm));
		
		if(defined('PF_DEMO_MODE')) {
			$content = $db->Quote(JRequest::getVar('text'));
		}
		else {
			$content = $db->Quote(JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW));
		}
		
		$query = "INSERT INTO #__pf_events VALUES(NULL, $title, $content, ".$user->GetId().","
		       . "\n $project, $now, $ts_sdate, $ts_edate)";
		       $db->setQuery($query);
		       $db->query();
		       $id = $db->insertid();

		if(!$id) {
		    $this->AddError($db->getErrorMsg());
			return false;
		}

        $data = array($id);
        PFprocess::Event('save_event', $data);
        return true;
	}
	
	public function Update($id)
	{
		$db = PFdatabase::GetInstance();
		
		$s_hour  = (int) JRequest::getVar('s_hour');
		$s_min   = (int) JRequest::getVar('s_minute');
		$s_ampm  = (int) JRequest::getVar('s_ampm');
		$e_hour  = (int) JRequest::getVar('e_hour');
		$e_min   = (int) JRequest::getVar('e_minute');
		$e_ampm  = (int) JRequest::getVar('e_ampm');
		$edate   = JRequest::getVar('edate');
		$sdate   = JRequest::getVar('sdate');
		$title   = $db->Quote(JRequest::getVar('title'));
		
		$ts_sdate = $db->Quote(PFformat::ToTime($sdate,$s_hour,$s_min,$s_ampm));
		$ts_edate = $db->Quote(PFformat::ToTime($edate,$e_hour,$e_min,$e_ampm));
		
		if(defined('PF_DEMO_MODE')) {
			$content = $db->Quote(JRequest::getVar('text'));
		}
		else {
			$content = $db->Quote(JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW));
		}
		
		$query = "UPDATE #__pf_events SET title = $title, content = $content,"
		       . "\n sdate = $ts_sdate, edate = $ts_edate"
		       . "\n WHERE id = $id";
		       $db->setQuery($query);
		       $db->query();

        if($db->getErrorMsg()) {
		    $this->AddError($db->getErrorMsg());
			return false;
		}
		
        // Load prcess event
        $data = array($id);
        PFprocess::Event('update_event', $data);

		return true;
	}
	
	public function Delete($cid)
	{
	    $db  = PFdatabase::GetInstance();
		$cid = implode(',', $cid);
		
		$query = "DELETE FROM #__pf_events WHERE id IN($cid)";
		       $db->setQuery($query);
		       $db->query();

        if($db->getErrorMsg()) {
		    $this->AddError($db->getErrorMsg());
			return false;
		}
		
		// Load prcess event
        $data = array($cid);
        PFprocess::Event('delete_event', $data);
		       
		return true;       
	}
}
?>