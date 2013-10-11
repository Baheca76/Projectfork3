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
$core = PFcore::GetInstance();
$user = PFuser::GetInstance();

// Create new form
$form = new PFform('adminForm_subnav');
$form->SetBind(true, 'REQUEST');

// Get section object
$sobj = $core->GetSectionObject();

$html = $form->Start();

// Decide which navigation to display
switch( $core->GetTask() )
{
	default:
	case 'list_groups':
        $html .= '
                 <div class="pf_navigation list_groups">
                     <ul>
                         <li class="btn pf_new">'.$form->NavButton('NEW', 'section=groups&task=form_new', 'TT_NEW_GROUP', 'form_new').'</li>
                         <li class="btn pf_delete">'.$form->NavButton('DELETE', 'javascript:task_delete();', 'TT_DELETE', 'task_delete').'</li>
                         <li class="btn pf_copy">'.$form->NavButton('COPY', 'javascript:task_copy();', 'TT_COPY', 'task_copy').'</li>
                         <!--<li class="btn pf_config">'.$form->NavButton('CONFIG', "section=config&task=form_edit_section&&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section', 'config').'</li>-->
                     </ul>
                 </div>
                 <div class="pfl_search">
                              <span>'.PFformat::Lang('SEARCH').$form->InputField('keyword').'</span>
                              <span class="btn">'.$form->NavButton('OK', "javascript:navsubmit('');").'</span>
                          </div>';
		break;
		
	case 'form_new':
        $html .= '<div class="pf_navigation form_new">
                     <ul>
                         <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_save()", 'TT_SAVE_GROUP', 'task_save').'</li>
                         <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=groups", 'TT_BACK').'</li>
                     </ul>
                 </div>';
		break;
		
	case 'form_edit':
        $html .= '<div class="pf_navigation form_edit">
                     <ul>
                         <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update()", 'TT_UPDATE', 'task_update').'</li>
                         <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=groups", 'TT_BACK').'</li>
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