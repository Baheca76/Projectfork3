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
require_once(PFobject::GetHelper('config'));

// Load objects
$core = PFcore::GetInstance();
$user = PFuser::GetInstance();
$form = new PFform('adminForm_subnav', NULL, 'post', 'enctype="multipart/form-data"');

$render   = JRequest::getVar('render');
$jversion = new JVersion();

// Capture user input
$ls  = (int) JRequest::getVar('limitstart');
$rts = (int) JRequest::getVar('rts');
$id  = (int) JRequest::getVar('id');

$position = NULL;
$event    = NULL;

if($core->GetTask() == 'list_panels') $position = JRequest::getVar('position', $user->GetProfile('panellist_position', ''));
if($core->GetTask() == 'list_processes') $event = JRequest::getVar('position', $user->GetProfile('processlist_position', ''));

// Setup query filter
$filter = "";
if($ls) $filter .= "&limitstart=$ls";
if($rts) $filter .= "&rts=$rts";
if($id) $filter .= "&id=$id";

$html_ae = PFconfigHelper::SelectAutoEnable('auto_enable', 1);

// Base navi
$html = $form->Start();
$html .= '
<div class="pf_navigation">
   <ul>
      <li class="btn pf_global">'.$form->NavButton('GLOBAL', "section=config", 'TT_CFG_GLOBAL').'</li>
      <li class="btn pf_sections">'.$form->NavButton('SECTIONS', "section=config&task=list_sections", 'TT_LIST_SECTIONS', 'list_sections').'</li>
      <li class="btn pf_panels">'.$form->NavButton('PANELS', "section=config&task=list_panels", 'TT_LIST_PANELS', 'list_panels').'</li>
      <li class="btn pf_processes">'.$form->NavButton('PROCESSES', "section=config&task=list_processes", 'TT_LIST_PROCESSES', 'list_processes').'</li>
      <li class="btn pf_mods">'.$form->NavButton('MODS', "section=config&task=list_mods", 'TT_LIST_MODS', 'list_mods').'</li>
      <li class="btn pf_languages">'.$form->NavButton('LANGUAGES', "section=config&task=list_languages", 'TT_LIST_LANGUAGE', 'list_languages').'</li>
      <li class="btn pf_themes">'.$form->NavButton('THEMES', "section=config&task=list_themes", 'TT_LIST_THEMES', 'list_themes').'</li>
   </ul>
</div>';

if($render == 'section_ajax') $html = $form->Start();

switch($core->GetTask())
{
	default:
	    $html .= '
        <div class="pf_navigation">
            <ul>
                <li class="btn pf_install_file"><input type="file" name="pack" size="10"/>&nbsp;'.$html_ae.'</li>
                <li class="btn pf_install">'.$form->NavButton('INSTALL', "javascript:task_install();", 'TT_INS_EXT', 'task_install').'</li>
            </ul>
        </div>
        <div class="pf_navigation">
            <ul>
                <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_save_global')", 'TT_UPDATE', 'task_save_global').'</li>
            </ul>
        </div>';
        if($render == 'section_ajax') $html = '';
		break;
		
	case 'list_sections':
        $html .= '
        <div class="pf_navigation">
            <ul>
                <li class="btn pf_uninstall">'.$form->NavButton('UNINSTALL', "javascript:task_uninstall();", 'TT_UINS_EXT', 'task_uninstall').'</li>
                <li class="btn pf_order">'.$form->NavButton('REORDER', "javascript:submitbutton('task_reorder');", 'TT_REORDER', 'task_reorder').'</li>
        	</ul></div>';
        if($render == 'section_ajax') $html = '';	
		break;
		
	case 'list_panels':
	        $html .= '
	        <div class="pf_navigation">
	            <ul>
	                <li class="btn pf_uninstall">'.$form->NavButton('UNINSTALL', "javascript:task_uninstall();", 'TT_UINS_EXT', 'task_uninstall').'</li>
	                <li class="btn pf_order">'.$form->NavButton('REORDER', "javascript:submitbutton('task_reorder');", 'TT_REORDER', 'task_reorder').'</li>
	             </ul></div>
	         <div class="pf_filter">
	         	'.$form->SelectPanelPosition('position', $position).' '.$form->NavButton('OK', "javascript:navsubmit('list_panels')").'
	         </div>';
	        if($render == 'section_ajax') $html = ''; 
			break;
			
	case 'list_processes':
	        $html .= '
	        <div class="pf_navigation">
	            <ul>
	                <li class="btn pf_uninstall">'.$form->NavButton('UNINSTALL', "javascript:task_uninstall();", 'TT_UINS_EXT', 'task_uninstall').'</li>
	                <li class="btn pf_order">'.$form->NavButton('REORDER', "javascript:submitbutton('task_reorder');", 'TT_REORDER', 'task_reorder').'</li>
	             </ul></div>
	        <div class="pf_filter">
	        	'.$form->SelectProcessEvent('position', $event).' '.$form->NavButton('OK', "javascript:navsubmit('list_processes')").'
	        </div>';
			break;		
		
	case 'list_mods':
	case 'list_languages':	
	case 'list_themes':	
	    $html .= '
        <div class="pf_navigation">
            <ul>
                <li class="btn pf_uninstall">'.$form->NavButton('UNINSTALL', "javascript:task_uninstall();", 'TT_UINS_EXT', 'task_uninstall').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit_section':
	    $ajax_save   = "";
	    $back_link   = "section=config&task=list_sections&limitstart=$ls";
	    
		if($rts) {
			$db = PFdatabase::GetInstance();
			
			$query = "SELECT name FROM #__pf_sections WHERE id = '$id'";
			       $db->setQuery($query);
			       $section_name = $db->loadResult();
			       
			$back_link = "section=$section_name";       
		}
		
	    if($render == 'section_ajax') {
	        if($jversion->RELEASE == '1.6') {
	            $ajax_save = "setTimeout('window.top.SqueezeBox.close();', 700);";
                $back_link = "javascript:window.top.SqueezeBox.close();";
	        }
	        else {
                $ajax_save = "window.top.setTimeout('window.parent.document.getElementById(\'sbox-window\').close();', 700);";
                $back_link = "javascript:window.parent.document.getElementById('sbox-window').close();";
            }
            
        }
		$html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update_section('0');$ajax_save", 'TT_UPDATE', 'task_update_section').'</li>';
              if($render != 'section_ajax') $html .= '<li class="btn pf_apply">'.$form->NavButton('APPLY', "javascript:task_update_section('1');$ajax_save", 'TT_APPLY', 'task_update_section').'</li>';
              $html .= '<li class="btn pf_cancel">'.$form->NavButton('CANCEL', $back_link, 'TT_BACK').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit_panel':
	    $ajax_save = "";
	    $ajax_cancel = "section=config&task=list_panels".$filter;
	    if($render == 'section_ajax') {
	        if($jversion->RELEASE == '1.6') {
                $ajax_save = "setTimeout('window.top.SqueezeBox.close();', 700);";
                $ajax_cancel = "javascript:window.top.SqueezeBox.close();";
            }
            else {
                $ajax_save = "window.top.setTimeout('window.parent.document.getElementById(\'sbox-window\').close();', 700);";
                $ajax_cancel = "javascript:window.parent.document.getElementById('sbox-window').close();";
            }
        }
		$html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_update_panel');$ajax_save", 'TT_UPDATE', 'task_update_panel').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', $ajax_cancel, 'TT_BACK', 'list_panels').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit_process':
	    $html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_update_process');", 'TT_UPDATE', 'task_update_process').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=config&task=list_processes".$filter, 'TT_BACK', 'list_processes').'</li>
           </ul>
        </div>';
		break;

    case 'form_edit_theme':
        $html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_update_theme');", 'TT_UPDATE', 'task_update_theme').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=config&task=list_themes", 'TT_BACK', 'list_themes').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit_mod':
        $html .= '
        <div class="pf_navigation">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:submitbutton('task_update_mod');", 'TT_UPDATE', 'task_update_mod').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "section=config&task=list_mods", 'TT_BACK', 'list_mods').'</li>
           </ul>
        </div>';
		break;	
}
$form->setBind(true, 'REQUEST');
$html .= $form->HiddenField("option");
$html .= $form->HiddenField("section");
$html .= $form->HiddenField("task");
switch($core->GetTask())
{
    case 'list_panels':    $html .= $form->HiddenField("type", "panel"); break;
    case 'list_sections':  $html .= $form->HiddenField("type", "section"); break;
    case 'list_processes': $html .= $form->HiddenField("type", "process"); break;
    case 'list_mods':      $html .= $form->HiddenField("type", "mods"); break;
    case 'list_languages': $html .= $form->HiddenField("type", "language"); break;
    case 'list_themes':    $html .= $form->HiddenField("type", "theme"); break;
}
$html .= $form->End();

echo $html;

unset($html,$core,$user,$form);
?>