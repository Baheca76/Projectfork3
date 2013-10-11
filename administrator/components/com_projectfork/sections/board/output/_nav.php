<?php
/**
* $Id: _nav.php 863 2011-03-21 00:00:29Z angek $
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
$core = PFcore::GetInstance();
$user = PFuser::GetInstance();

// Create new form
$form = new PFform('adminForm_subnav');
$form->SetBind(true, 'REQUEST');

// Get section object
$sobj = $core->GetSectionObject();

// Get user input
$id = (int) JRequest::GetVar('id');

$html = $form->Start();

// Decide which navigation to display
switch( $core->GetTask() )
{
	default:
	    $html .= '
        <div class="pf_navigation">
            <ul>
                <li class="btn pf_new">'.$form->NavButton('NEW', "section=board&task=form_new_topic", 'TT_CREATE_TOPIC', 'form_new_topic').'</li>
                <li class="btn pf_subscribe">'.$form->NavButton('SUBSCRIBE', "javascript:task_subscribe()", 'TT_SUB_TOPIC', 'task_subscribe').'</li>
                <li class="btn pf_unsubscribe">'.$form->NavButton('UNSUBSCRIBE', "javascript:task_unsubscribe()", 'TT_UNSUB_TOPIC', 'task_unsubscribe').'</li>
                <!--<li class="btn pf_config">'.$form->NavButton('CONFIG', "section=config&task=form_edit_section&&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section', 'config').'</li>-->
            </ul>
        </div>';
		break;
		
	case 'form_new_topic':
	    $html .= '
        <div class="pf_navigation form_new_topic">
            <ul>
                <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_save_topic();", 'TT_SAVE_TOPIC', 'task_save_topic').'</li>
                <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=board", 'TT_BACK').'</li>
            </ul>
        </div>';
		break;
		
	case 'form_edit_topic':
	    $html .= '
        <div class="pf_navigation form_edit_topic">
            <ul>
                <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update_topic();", 'TT_UPDATE', 'task_update_topic').'</li>
                <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=board", 'TT_BACK').'</li>
            </ul>
        </div>';
		break;
		
	case 'display_details':
	case 'form_edit_reply':	
	    $html .= '
        <div class="pf_navigation form_edit_reply">
            <ul>';
            
        if($user->Access('task_save_reply')) {
			$link = PFformat::Link("section=board&task=".$core->GetTask()."&id=$id").'#pf_reply';
			$html .= '<li class="btn pf_reply">'.$form->NavButton('REPLY',$link, 'TT_REPLY').'</li>';
		}
        
        $html .= '
                <li class="btn pf_subscribe">'.$form->NavButton('SUBSCRIBE', "section=board&task=task_subscribe&cid[]=$id&rtt=1", 'TT_SUB_TOPIC2', 'task_subscribe').'</li>
                <li class="btn pf_unsubscribe">'.$form->NavButton('UNSUBSCRIBE', "section=board&task=task_unsubscribe&cid[]=$id&rtt=1", 'TT_UNSUB_TOPIC2', 'task_unsubscribe').'</li>
                <li class="btn pf_back">'.$form->NavButton('BACK', "section=board", 'TT_BACK').'</li>
            </ul>
        </div>';
		break;		
}

$html .= $form->HiddenField("option");
$html .= $form->HiddenField("section");
$html .= $form->HiddenField("task");
$html .= $form->End();

echo $html;

// Unset objects
unset($core,$user,$form,$html,$sobj);
?>