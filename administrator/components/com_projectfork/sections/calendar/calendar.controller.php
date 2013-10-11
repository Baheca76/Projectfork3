<?php
/**
* $Id: calendar.controller.php 856 2011-02-24 15:07:15Z angek $
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

class PFcalendarController extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }
    
	public function DisplayMonth($year, $month, $today)
	{
		$class  = new PFcalendarClass();
        $config = PFconfig::GetInstance();
        $user   = PFuser::GetInstance();
        
        $this_day   = date("j");
        $this_year  = date('Y');
        $this_month = date('n');

        $days_of_month  = date("t", mktime(0,0,0,$month,1,$year));
        $month_start    = date('w',date(mktime(0, 0, 0, $month, 1, $year)));
        $first_day      = date("w",mktime(0,0,0,$month,1,$year)) - 1;
        $days           = date('t', mktime(0, 0, 0, $month, 1, $year));
        
        $counter        = 0;
        $current_day    = 1;
        $ctoday         = 0;
        $form           = new PFform();
        $rows           = $class->LoadMonth($year, $month, $days_of_month);
        $ws_title       = PFformat::WorkspaceTitle();
        
        JRequest::setVar('year', $year);
        JRequest::setVar('month', $month);
        JRequest::setVar('day', $today);
        
        $project_bg   = $config->Get('project_bg', 'calendar');
        $milestone_bg = $config->Get('milestone_bg', 'calendar');
        $task_bg      = $config->Get('task_bg', 'calendar');
        
        if($project_bg) {
        	$project_bg = "style='background-color:#$project_bg'";
        }
        else {
        	$project_bg = "";
        }
        
        if($milestone_bg) {
        	$milestone_bg = "style='background-color:#$milestone_bg'";
        }
        else {
        	$milestone_bg = "";
        }
        
        if($task_bg) {
        	$task_bg = "style='background-color:#$task_bg'";
        }
        else {
        	$task_bg = "";
        }
        
        $form->SetBind(true, 'REQUEST');
        
        if($month_start == 0) { 
        	$month_start = 6; 
        }
        else {
        	$month_start--;
        }
        
        $can_add = $user->Access('form_new', 'calendar');
        
        require_once($this->GetOutput('display_month.php', 'calendar'));
	}
	
	public function DisplayWeek($year, $month, $today)
	{
		$class  = new PFcalendarClass();
        $config = PFconfig::GetInstance();
        
        $week            = date("W", mktime(0,0,0,$month,$today,$year));
        $day_of_week     = strftime( '%w', mktime(0,0,0,$month,$today,$year)) - 1;
        $start_of_week   = date("d", mktime(0,0,0,$month,$today - $day_of_week,$year));
        $start_of_week_y = date("Y", mktime(0,0,0,$month,$today - $day_of_week,$year));
        $start_of_week_m = date("m", mktime(0,0,0,$month,$today - $day_of_week,$year));
        $end_of_week     = date("d", mktime(0,0,0,$month,$today + (6 - $day_of_week),$year));
        $end_of_week_y   = date("Y", mktime(0,0,0,$month,$today + (6 - $day_of_week),$year));
        $end_of_week_m   = date("m", mktime(0,0,0,$month,$today + (6 - $day_of_week),$year));
        
        
        $rows = $class->LoadWeek($start_of_week_y, $start_of_week_m, $start_of_week, $end_of_week_y, $end_of_week_m, $end_of_week);
        $ws_title = PFformat::WorkspaceTitle();
        
        $day_names = array('0' => PFformat::Lang('MONDAY'), '1' => PFformat::Lang('TUESDAY'), 
                           '2' => PFformat::Lang('WEDNESDAY'), '3' => PFformat::Lang('THURSDAY'), 
                           '4' => PFformat::Lang('FRIDAY'), '5' => PFformat::Lang('SATURDAY'),
                           '6' => PFformat::Lang('SUNDAY'));

        $hours = array('00','01','02','03','04','05','06','07','08','09','10','11','12',
		         '13','14','15','16','17','18','19','20','21','22','23');

		$project_bg   = $config->Get('project_bg', 'calendar');
        $milestone_bg = $config->Get('milestone_bg', 'calendar');
        $task_bg      = $config->Get('task_bg', 'calendar');
        
        if($project_bg) {
        	$project_bg = "style='background-color:#$project_bg'";
        }
        else {
        	$project_bg = "";
        }
        
        if($milestone_bg) {
        	$milestone_bg = "style='background-color:#$milestone_bg'";
        }
        else {
        	$milestone_bg = "";
        }
        
        if($task_bg) {
        	$task_bg = "style='background-color:#$task_bg'";
        }
        else {
        	$task_bg = "";
        }
                  
        JRequest::setVar('year', $year);
        JRequest::setVar('month', $month);
        JRequest::setVar('day', $today);
        
        $form = new PFform();
        $form->Setbind(true, 'REQUEST');
        
        require_once($this->GetOutput('display_week.php', 'calendar'));
	}
	
	public function DisplayDay($year, $month, $today)
	{
		$class  = new PFcalendarClass();
        $config = PFconfig::GetInstance();
        $load   = PFload::GetInstance();
        $user   = PFuser::GetInstance();
        
        $week  = date("W", mktime(0,0,0,$month,$today,$year));
        $rows  = $class->LoadDay($year, $month, $today);
        $ws_title = PFformat::WorkspaceTitle();
		$hours = array('00','01','02','03','04','05','06','07','08','09','10','11','12',
		         '13','14','15','16','17','18','19','20','21','22','23');
		         
		$form = new PFform();
        $form->SetBind(true, 'REQUEST');
        
        $project_bg   = $config->Get('project_bg', 'calendar');
        $milestone_bg = $config->Get('milestone_bg', 'calendar');
        $task_bg      = $config->Get('task_bg', 'calendar');
        
        if($project_bg) {
        	$project_bg = "style='background-color:#$project_bg'";
        }
        else {
        	$project_bg = "";
        }
        
        if($milestone_bg) {
        	$milestone_bg = "style='background-color:#$milestone_bg'";
        }
        else {
        	$milestone_bg = "";
        }
        
        if($task_bg) {
        	$task_bg = "style='background-color:#$task_bg'";
        }
        else {
        	$task_bg = "";
        }
        
        JRequest::setVar('year', $year);
        JRequest::setVar('month', $month);
        JRequest::setVar('day', $today);
        
        $can_add = $user->Access('form_new', 'calendar');
        
        require_once($this->GetOutput('display_day.php', 'calendar'));
        unset($config,$class,$rows,$form,$load);
	}
	
	public function DisplayNew($year, $month, $today, $hour = 0)
	{
	    $config = PFconfig::GetInstance();
	    $editor = JFactory::getEditor();
		$form   = new PFform();

		$date_format 	= $config->Get('date_format');

		$thedate 		= strtotime($year."-".$month."-".$today);

		$now 			= strftime($date_format, $thedate);
		if ($now == $date_format) {
			$thedate = $year."-".$month."-".$today;
		}

		$ws_title   = PFformat::WorkspaceTitle();
        $use_editor = (int) $config->Get('use_editor', 'calendar');
        
        JRequest::setVar('year', $year);
        JRequest::setVar('month', $month);
        JRequest::setVar('day', $today);
        JRequest::setVar('hour', $hour);
        
        $form->SetBind(true, 'REQUEST');
        
		require_once($this->GetOutput('form_new.php', 'calendar'));
		unset($config,$editor,$form);
	}
	
	public function DisplayEdit($id, $year, $month, $today)
	{
		$class  = new PFcalendarClass();
        $editor = JFactory::getEditor();
        $config = PFconfig::GetInstance();
        $form   = new PFform();
        
		$date_format = $config->Get('date_format');
		
        $ws_title = PFformat::WorkspaceTitle();
		$row = $class->Load($id);

		$start_date = strftime($date_format,$row->sdate);
		$end_date   = strftime($date_format,$row->edate);
		
		if($config->Get('12hclock')) {
		    $s_hour = date('h', $row->sdate);
		    $s_min  = date('i', $row->sdate);
		    $ampm =  date('a', $row->sdate);
			$s_ampm = ($ampm == 'pm') ? '1' : '0';
		    $e_hour = date('h', $row->edate);
		    $e_min  = date('i', $row->edate);
			$ampm =  date('a', $row->edate);
			$e_ampm = ($ampm == 'pm') ? '1' : '0';
		}
		else {
            $s_hour = date('H', $row->sdate);
		    $s_min  = date('i', $row->sdate);
		    $s_ampm = 0;
		    $e_hour = date('H', $row->edate);
		    $e_min  = date('i', $row->edate);
		    $e_ampm = 0;
        }

        $form->SetBind(true, $row);
        
        $use_editor = (int) $config->Get('use_editor', 'calendar');
        
        require_once($this->GetOutput('form_edit.php', 'calendar'));
        unset($class,$editor,$config,$form);
	}
	
	public function Save()
	{
		$class = new PFcalendarClass();
		
		if(!$class->Save()) {
			$this->SetRedirect("section=calendar&task=display_month", 'EVENT_E_ADD');
			return false;
		}
		
		$this->SetRedirect("section=calendar&task=display_month", 'EVENT_S_ADD');
		return true;
	}
	
	public function Update($id, $year, $month, $today)
	{
        $class = new PFcalendarClass();
        
		if(!$class->Update($id)) {
			$this->SetRedirect('section=calendar&task=display_month&year='.$year.'&month='.$month.'&day='.$today, 'EVENT_E_UPDATE');
			return false;
		}
		
		$this->SetRedirect('section=calendar&task=display_month&year='.$year.'&month='.$month.'&day='.$today, 'EVENT_S_UPDATE');
		return true;
	}
	
	public function Delete($cid, $year, $month, $today)
	{
		$class  = new PFcalendarClass();
		
		if(!$class->Delete($cid)) {
			$this->SetRedirect('section=calendar&task=display_month&year='.$year.'&month='.$month.'&day='.$today, 'MSG_E_DELETE');
			return false;
		}

		$this->SetRedirect('section=calendar&task=display_month&year='.$year.'&month='.$month.'&day='.$today, 'MSG_S_DELETE');
		return true;
	}
}
?>