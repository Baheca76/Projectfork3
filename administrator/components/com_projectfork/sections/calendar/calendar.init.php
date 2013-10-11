<?php
/**
* $Id: calendar.init.php 837 2010-11-17 12:03:35Z eaxs $
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

// Load objects
$core = PFcore::GetInstance();

// Setup controller
$controller = new PFcalendarController();

// Get user input
$year  = (int) JRequest::getVar('year',date("Y"));
$month = (int) JRequest::getVar('month',date("n"));
$today = (int) JRequest::getVar('day', date("j"));
$hour  = (int) JRequest::getVar('hour');
$id    = (int) JRequest::getVar('id');
$cid   = JRequest::getVar('cid', array(), 'POST', 'array');

switch( $core->GetTask() )
{
	default:
	case 'display_month':	
		$controller->DisplayMonth($year, $month, $today);
		break;

	case 'display_week':
		$controller->DisplayWeek($year, $month, $today);
		break;
		
	case 'display_day':
		$controller->DisplayDay($year, $month, $today);
		break;
		
	case 'form_new':
		$controller->DisplayNew($year, $month, $today, $hour);
		break;

	case 'form_edit':
		$controller->DisplayEdit($id, $year, $month, $today);
		break;
		
	case 'task_save':
        $controller->Save();
		break;
		
	case 'task_update':
		$controller->Update($id, $year, $month, $today);
		break;
		
	case 'task_delete':
		$controller->Delete($cid, $year, $month, $today);
		break;
}
?>