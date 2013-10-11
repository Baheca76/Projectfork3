<?php
/**
* $Id: _nav.php 909 2011-07-19 12:00:37Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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
$core   = PFcore::GetInstance();
$user   = PFuser::GetInstance();
$config = PFconfig::GetInstance();

$year  = (int) JRequest::getVar('year',date("Y"));
$month = (int) JRequest::getVar('month',date("n"));
$today = (int) JRequest::getVar('day', date("j"));

$sections = $core->GetSections();
$fm = array_key_exists('filemanager_pro', $sections) ? 'filemanager_pro' : 'filemanager';

// Create new form
$form = new PFform('adminForm_subnav');
$html = '';

// Decide which navigation to display
switch( $core->GetTask() )
{
	default:
	$html .= '
	<div class="pf_navigation">
	    <ul>';
	    if(!$user->GetWorkspace()) {
	        $html .= '
	        <li class="btn pf_new_project">'.$form->NavButton('NEW_PROJECT', 'section=projects&task=form_new', 'TT_NEW_PROJECT', 'form_new', 'projects', true).'</li>';
	    }
	    else {
	        if($config->Get('use_milestones', 'tasks')) {
                $html .= '<li class="btn pf_new_milestone">'.$form->NavButton('NEW_MILESTONE', "section=tasks&task=form_new_milestone", 'TT_NEW_MS', 'form_new_milestone', 'tasks', true).'</li>';
            }
            $html .= '
	 	    <li class="btn pf_new_task">'.$form->NavButton('NEW_TASK', "section=tasks&task=form_new_task", 'TT_NEW_TASK', 'form_new_task', 'tasks', true).'</li>
	 	    <li class="btn pf_new_file">'.$form->NavButton('NEW_FILE', 'section='.$fm.'&task=form_new_file', 'TT_UPLOAD_FILE', 'form_new_file', $fm, true).'</li>
	 	    <li class="btn pf_new_event">'.$form->NavButton('NEW_EVENT', 'section=calendar&task=form_new&year='.$year.'&month='.$month.'&day='.$today, 'TT_NEW_EVENT', 'form_new', 'calendar', true).'</li>
	 	    <li class="btn pf_new_topic">'.$form->NavButton('NEW_TOPIC', "section=board&task=form_new_topic", 'TT_CREATE_TOPIC', 'form_new_topic', 'board', true).'</li>
	 	    <li class="btn pf_new_user">'.$form->NavButton('NEW_USER', 'section=users&task=form_new', 'TT_NEW_USER', 'form_new', 'users', true).'</li>';
        }
	    $html .= '
        </ul>
    </div>';
    if(!$user->GetId()) $html = '';
	break;
}

echo $html;

// Unset objects
unset($core,$user,$html);
?>