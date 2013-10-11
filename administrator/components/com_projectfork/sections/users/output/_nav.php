<?php
/**
* $Id: _nav.php 837 2010-11-17 12:03:35Z eaxs $
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

// Load core objects
$core = PFcore::GetInstance();
$user = PFuser::GetInstance();

$form = new PFform('adminForm_subnav');

$workspace  = $user->GetWorkspace();
$flag       = $user->GetFlag();
$author     = 0;
$ls         = (int) JRequest::getVar('limitstart');

$filter = "";
if($ls) $filter .= "&limitstart=$ls";

$form->SetBind(true, 'REQUEST');

// Get section object
$sobj = $core->GetSectionObject();
$html = $form->Start();

switch( $core->GetTask() )
{
	default:
	    $html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_new">'.$form->NavButton('NEW', 'section=users&task=form_new', 'TT_NEW_USER', 'form_new').'</li>
              <li class="btn pf_import">'.$form->NavButton('IMPORT_USER', 'section=users&task=form_invite', 'TT_IMPORT_USER', 'form_invite').'</li>              
              <li class="btn pf_access">'.$form->NavButton('ACCESS_LVLS', 'section=users&task=list_accesslvl', 'TT_ACCESS_LEVELS', 'list_accesslvl').'</li>
              <li class="btn pf_join">'.$form->NavButton('JOIN_REQUESTS', 'section=users&task=list_requests', 'TT_JOIN_REQUESTS', 'list_requests').'</li>
              <li class="btn pf_delete">'.$form->NavButton('DELETE', 'javascript:task_delete();', 'TT_DELETE_USER', 'task_delete').'</li>
              <!--<li class="btn pf_config">'.$form->NavButton('CONFIG', "section=config&task=form_edit_section&&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section', 'config').'</li>-->
           </ul>
        </div>
        <div class="pfl_search">
            <span>'.PFformat::Lang('SEARCH').$form->InputField('keyword').'
            </span>
            <span class="btn">'.$form->NavButton('OK', "javascript:navsubmit('');").'</span>
        </div>';
		break;
		
	case 'list_requests':
	    $html .= '
        <div class="pf_navigation list_request">
           <ul>
              <li class="btn pf_accept">'.$form->NavButton('ACCEPT', "javascript:form_accept_request();", 'TT_ACCEPT_REQUEST', 'form_accept_request').'</li>
              <li class="btn pf_deny">'.$form->NavButton('DENY', "javascript:task_deny();", 'TT_DENY_REQUEST', 'task_deny').'</li>
              <li class="btn pf_back">'.$form->NavButton('BACK', 'section=users'.$filter, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_new':
	    $html .= '
        <div class="pf_navigation form_new"> 
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_save()", '', 'task_save').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=users").'</li>
           </ul>
        </div>';
		break;
			
	case 'form_edit':
	    $html .= '
        <div class="pf_navigation form_edit"> 
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update()", 'TT_UPDATE', 'task_update').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=users".$filter, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'list_accesslvl':
	    $html .= '
        <div class="pf_navigation list_accesslvl"> 
           <ul>
              <li class="btn pf_new">'.$form->NavButton('NEW', "section=users&task=form_new_accesslvl", 'TT_NEW_ACL', 'form_new_accesslvl').'</li>
              <li class="btn pf_delete">'.$form->NavButton('DELETE', "javascript:task_delete_accesslvl()", 'TT_DELETE', 'task_delete_accesslvl').'</li>
              <li class="btn pf_back">'.$form->NavButton('BACK', "section=users".$filter, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_new_accesslvl':
	    $html .= '
        <div class="pf_navigation form_new_accesslvl"> 
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_save_accesslvl()", 'TT_SAVE_ACL', 'task_save_accesslvl').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=users&task=list_accesslvl".$filter, 'TT_BACK', 'list_accesslvl').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit_accesslvl':
	    $html .= '
        <div class="pf_navigation form_edit_accesslvl"> 
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update_accesslvl()", 'TT_UPDATE', 'task_update_accesslvl').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=users&task=list_accesslvl".$filter, 'TT_BACK', 'list_accesslvl').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_accept_request':
	    $html .= '
        <div class="pf_navigation form_accept_request"> 
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_accept_requests')", 'TT_ACCEPT_REQUEST', 'task_accept_requests').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=users&task=list_requests".$filter, 'TT_BACK', 'list_requests').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_invite':
	    $html .= '
        <div class="pf_navigation form_invite"> 
           <ul>
              <li class="btn pf_import">'.$form->NavButton('IMPORT_USER', "javascript:submitbutton('task_invite')", 'TT_IMPORT_USER', 'task_invite').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=users".$filter, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;	
}

$html .= $form->HiddenField("option");
$html .= $form->HiddenField("section");
$html .= $form->HiddenField("task");
$html .= $form->End();

echo $html;
?>