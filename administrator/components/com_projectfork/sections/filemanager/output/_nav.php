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

// Get core objects
$core = PFcore::GetInstance();
$form = new PFform('adminForm_subnav');
$sobj = $core->GetSectionObject();
$print= (int) JRequest::getVar('print');

$form->SetBind(true, 'REQUEST');

// Get user input
$dir  = (int) JRequest::getVar('dir', 0);

if($print) return true;

$html = $form->Start();

switch( $core->GetTask() )
{
	default:
	case 'list_directory':
		$class = new PFfilemanagerClass();
	    $html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_new_folder">'.$form->NavButton('NEW_FOLDER', 'section=filemanager&dir='.$dir.'&task=form_new_folder', 'TT_NEW_FOLDER', 'form_new_folder').'</li>
              <li class="btn pf_new_file">'.$form->NavButton('NEW_FILE', 'section=filemanager&dir='.$dir.'&task=form_new_file', 'TT_UPLOAD_FILE', 'form_new_file').'</li>
              <li class="btn pf_new_note">'.$form->NavButton('NEW_NOTE', 'section=filemanager&dir='.$dir.'&task=form_new_note', 'TT_NEW_NOTE', 'form_new_note').'</li>
              <li class="btn pf_move">'.$form->NavButton('MOVE', 'javascript:list_move()', 'TT_MOVE', 'list_move').'</li>
              <li class="btn pf_delete">'.$form->NavButton('DELETE', 'javascript:task_delete()', 'TT_DELETE', 'task_delete').'</li>
              <!--<li class="btn pf_config">'.$form->NavButton('CONFIG', "section=config&task=form_edit_section&&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section', 'config').'</li>-->
           </ul>
        </div>
        <div class="pfl_search">
              <span>
                 '.PFformat::Lang('SEARCH').
                 $form->InputField('keyword').'
              </span>
              <span class="btn">'.$form->NavButton('OK', "javascript:navsubmit('');").'</span>
        </div>
        <div class="pf_addressbar">
              '.$class->RenderAddressBar($dir).'
        </div>';
        unset($class);
		break;
		
	case 'list_move':
		$class = new PFfilemanagerClass();
		$html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_move">'.$form->NavButton('MOVE', "javascript:submitbutton('task_move')", 'TT_FM_MOVE_HERE', 'task_move').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', 'section=filemanager&dir='.$dir, 'TT_BACK').'</li>
           </ul>
        </div>
        <div class="pf_addressbar">
              '.$class->RenderAddressBar($dir).'
        </div>';
        unset($class);
		break;	
		
	case 'form_new_folder':
	case 'form_new_file':
	case 'form_new_note':
		$task = explode('_', $core->GetTask());
		$task = array_pop($task);
		$html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_save_$task()", 'TT_CREATE_'.strtoupper($task), 'task_save_'.$task).'</li>';
        if($task == 'file') {
            $html .= '<li class="btn pf_add"><a class="pf_button_submit" href="javascript:addFile(\'add_file\', \'file_container\')"><span>'.PFformat::Lang('ADD').'</span></a></li>';
        }
        $html .= '
        <li class="btn pf_cancel">'.$form->NavButton('CANCEL', 'section=filemanager&dir='.$dir, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit_folder':
	case 'form_edit_note':
	case 'form_edit_file':
		$task = explode('_', $core->GetTask());
		$task = array_pop($task);
		$html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update_$task()", 'TT_UPDATE', 'task_update_'.$task).'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', 'section=filemanager&dir='.$dir, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'display_note':
	    $html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_back">'.$form->NavButton('BACK', 'section=filemanager&dir='.$dir, 'TT_BACK').'</li>
              <li class="btn pf_print">'.$form->NavButton('PRINT', 'javascript:task_print();', 'TT_PRINT', 'display_note').'</li>
           </ul>
        </div>';
		break;
}

$html .= $form->HiddenField("option");
$html .= $form->HiddenField("section");
$html .= $form->HiddenField("task");
$html .= $form->End();

echo $html;
unset($core,$form,$sobj,$html);
?>