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

// Include helper
require_once( PFobject::GetHelper('projects') );

// Load objects
$core = PFcore::GetInstance();
$user = PFuser::GetInstance();

// Get filter settings
$ls     = (int) JRequest::getVar('limitstart');
$k      = urlencode(JRequest::getVar('keyword'));
$status = (int) JRequest::getVar('status', $user->GetProfile('projectlist_status',0));
$print  = (int) JRequest::getVar('print');

$filter = "";
if($ls) $filter .= "&limitstart";
if($k) $filter .= "&keyword=$k";

// Create new form
$form = new PFform('adminForm_subnav');
$form->SetBind(true, 'REQUEST');

// Get section object
$sobj = $core->GetSectionObject();

$html = $form->Start();

if($print) return true;

// Decide which navigation to display
switch( $core->GetTask() )
{
    // Default view
	default:
		$html .= '
		<div class="pf_navigation">
            <ul>';
            if($status != 2) {
                $html .= '
                <li class="btn pf_new">'.$form->NavButton('NEW', 'section=projects&task=form_new'.$filter, 'TT_NEW_PROJECT', 'form_new').'</li>
                <li class="btn pf_copy">'.$form->NavButton('COPY', 'javascript:task_copy();', 'TT_COPY_PROJECT', 'task_copy').'</li>';
            }
            if($status == 0) {
                $html .= '
                <li class="btn pf_archive">'.$form->NavButton('ARCHIVE', 'javascript:task_archivate();', 'TT_ARCHIVE', 'task_archive').'</li>';
            } elseif ($status == 1) {
                $html .= '
                <li class="btn pf_activate">'.$form->NavButton('ACTIVATE', 'javascript:task_activate();', 'TT_ACTIVATE', 'task_activate').'</li>';
            } elseif ($status == 2) {
                $html .= '
                <li class="btn pf_approve">'.$form->NavButton('APPROVE', 'javascript:task_approve();', 'TT_APPROVE', 'task_approve').'</li>';
            }
            $html .= '
            <li class="btn pf_delete">'.$form->NavButton('DELETE', 'javascript:task_delete();', 'TT_DELETE', 'task_delete').'</li>';
            if($user->GetWorkspace()) {
                $html .= '
                <li class="btn pf_join">'.$form->NavButton('JOIN_REQUESTS', 'section=users&task=list_requests', 'TT_JOIN_REQUESTS', 'list_requests', 'users').'</li>
                <li class="btn pf_groups">'.$form->NavButton('GROUPS', 'section=groups', 'TT_MANAGE_GROUPS', NULL, 'groups').'</li>
                <li class="btn pf_access">'.$form->NavButton('ACCESS_LVLS', 'section=users&task=list_accesslvl', 'TT_ACCESS_LEVELS', 'list_accesslvl', 'users').'</li>';
            }
            $html .= '
            <!--<li class="btn pf_config">'.$form->NavButton('CONFIG', "section=config&task=form_edit_section&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section', 'config').'</li>-->
           </ul>
        </div>
        <div class="pfl_search">
            <span>'
                . "<strong>".PFformat::Lang('SEARCH')."</strong>&nbsp;"
                . $form->InputField('keyword')."&nbsp;"
                . "<strong>".PFformat::Lang('FILTER')."</strong>&nbsp;"
                . PFprojectsHelper::SelectStatus('status', $user->GetProfile('projectlist_status',0))
                . PFprojectsHelper::SelectCategory('cat', $user->GetProfile('projectlist_category')).
            '</span>
            <span class="btn">'.$form->NavButton('OK', "javascript:navsubmit('');").'</span>
        </div>';
		break;

    // New project form
	case 'form_new':
        $html .= '
		<div class="pf_navigation">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_save')", 'TT_CREATE_PROJECT', 'task_save').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=projects".$filter, 'TT_BACK_PROJECT').'</li>
           </ul>
        </div>';
		break;
		
	// Edit project form
	case 'form_edit':
	    $html .= '
        <div class="pf_navigation">
            <ul>
                <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update('0')", 'TT_UPDATE', 'task_update').'</li>
                <li class="btn pf_apply">'.$form->NavButton('APPLY', "javascript:task_update('1')", 'TT_APPLY', 'task_update').'</li>
                <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=projects".$filter, 'TT_BACK_PROJECT').'</li>
            </ul>
        </div>';
		break;
		
	// Project details page
	case 'display_details':
	    $load = PFload::GetInstance();
	    require_once( $load->Section('projects.class.php', 'projects') );
	    $class = new PFprojectsClass();
	    $id    = (int) JRequest::GetVar('id');
	    $author = $class->GetAuthor($id);
	    $html .= '
        <div class="pf_navigation">
            <ul>';
        if($user->Access('form_edit', 'projects', $author)) {
            $html .= '<li class="btn pf_edit">'.$form->NavButton('EDIT', "section=projects&task=form_edit&id=".$id.$filter, 'TT_EDIT', 'form_edit').'</li>
            <li class="btn pf_print">'.$form->NavButton('PRINT', 'javascript:task_print();', 'TT_PRINT', 'display_details').'</li>';
        }
        $html .= '
                <li class="btn pf_back">'.$form->NavButton('BACK', "section=projects".$filter, 'TT_BACK').'</li>
            </ul>
        </div>';
        
        unset($load,$class);
		break;	
}

$html .= $form->HiddenField("option");
$html .= $form->HiddenField("section");
$html .= $form->HiddenField("task");
$html .= $form->End();

echo $html;

// Unset objects
unset($core,$user,$sobj,$html);
?>