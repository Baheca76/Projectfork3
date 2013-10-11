<?php
/**
* $Id: _nav.php 837 2010-11-17 12:03:35Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
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

$form = new PFform('adminForm_subnav');
$form->SetBind(true, 'REQUEST');

// Config settings
$use_ms = $config->Get('use_milestones', 'tasks');

// Get user input
$status   = (int) JRequest::getVar('status', $user->GetProfile("tasklist_status"));
$assigned = (int) JRequest::getVar('assigned', $user->GetProfile("tasklist_assigned"));
$priority = (int) JRequest::getVar('priority', $user->GetProfile("tasklist_priority"));
$ms       = (int) JRequest::getVar('fms', $user->GetProfile("tasklist_ms_".$user->GetWorkspace()));
$ls       = (int) JRequest::getVar('limitstart');
$id       = (int) JRequest::getVar('id');
$print    = (int) JRequest::getVar('print');
$k        = urlencode(JRequest::getVar('keyword'));

if($print) return true;

// Setup url filter
$filter = "";
if($ls) $filter .= "&limitstart=$ls";
if($k) $filter .= "&keyword=$k";

// Get section object
$sobj = $core->GetSectionObject();

// Load the tasks helper class
require_once( PFobject::GetHelper('tasks') );

$html = $form->Start();

switch($core->GetTask())
{
	default:
	    $html .= '
        <div class="pf_navigation tasks_navigation">
           <ul>
              <li class="btn pf_new">'.$form->NavButton('NEW_TASK', "section=tasks&task=form_new_task".$filter, 'TT_NEW_TASK', 'form_new_task').'</li>';
              if($use_ms) {
                  $html .= '<li class="btn pf_new">'.$form->NavButton('NEW_MILESTONE', "section=tasks&task=form_new_milestone".$filter, 'TT_NEW_MS', 'form_new_milestone').'</li>';
              }
              $html .= '
              <li class="btn pf_order">'.$form->NavButton('REORDER', "javascript:submitbutton('task_reorder');", 'TT_REORDER', 'task_reorder').'</li>
              <li class="btn pf_copy">'.$form->NavButton('COPY', 'javascript:task_copy();', 'TT_COPY', 'task_copy').'</li> 
              <li class="btn pf_delete">'.$form->NavButton('DELETE', 'javascript:task_delete();', 'TT_DELETE', 'task_delete').'</li>
              <!--<li class="btn pf_config">'.$form->NavButton('CONFIG', "section=config&task=form_edit_section&&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section', 'config').'</li>-->
           </ul>
        </div>
        <div class="pfl_search">
              <span>
                 '.PFformat::Lang('SEARCH')
                 . $form->InputField('keyword')
                 . (($user->GetWorkspace() && $use_ms) ? $form->SelectMilestone('fms', $ms) : '')
                 . PFtasksHelper::SelectUserFilter('assigned', $assigned)
                 . $form->SelectTaskStatus('status', $status)
                 . $form->SelectPriority('priority', $priority).'
               </span>
               <span class="btn">'.$form->NavButton('OK', "javascript:navsubmit('');").'</span>
        </div>';
		break;
		
	case 'form_new_task':
	    $html .= '
        <div class="pf_navigation new_task">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_save_task')", 'TT_CREATE_TASK', 'task_save_task').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=tasks".$filter, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit_task':
		$html .= '
        <div class="pf_navigation edit_task">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update_task('0')", 'TT_UPDATE', 'task_update_task').'</li>
              <li class="btn pf_apply">'.$form->NavButton('APPLY', "javascript:task_update_task('1')", 'TT_APPLY', 'task_update_task').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=tasks".$filter, 'TT_BACK').'</li>
           </ul>
        </div>';

		break;
		
	case 'form_new_milestone':
	    $html .= '
        <div class="pf_navigation new_milestone">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_save_milestone')", 'TT_CREATE_MS', 'task_save_milestone').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=tasks".$filter, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit_milestone':
	    $html .= '
        <div class="pf_navigation edit_milestone">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update_milestone('0')", 'TT_UPDATE', 'task_update_milestone').'</li>
              <li class="btn pf_apply">'.$form->NavButton('APPLY', "javascript:task_update_milestone('1')", 'TT_APPLY', 'task_update_milestone').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=tasks".$filter, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'display_details':
	case 'form_edit_comment':
        $html .= '
        <div class="pf_navigation edit_comment">
           <ul>
              <li class="btn pf_back">'.$form->NavButton('BACK', "section=tasks".$filter, 'TT_BACK').'</li>
              <li class="btn pf_edit">'.$form->NavButton('EDIT', "section=tasks&task=form_edit_task&id=".$id.$filter, 'TT_EDIT', 'form_edit_task').'</li>
              <li class="btn pf_print">'.$form->NavButton('PRINT', 'javascript:task_print();', 'TT_PRINT', 'display_details').'</li>
              <li class="btn pf_timeadd">'.$form->NavButton('TI_FN', "section=time&ftask=$id&fuser=0&keyword=", 'TI_FN', 'form_new', 'time').'</li>
           </ul>
        </div>';	
		break;
}

$html .= $form->HiddenField("option");
$html .= $form->HiddenField("section");
$html .= $form->HiddenField("task");
$html .= $form->End();

echo $html;

unset($core,$cuser,$config,$form,$html,$sobj);
?>