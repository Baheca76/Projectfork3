<?php
/**
* $Id: config.init.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Config
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
$load = PFload::GetInstance();

// Capture user input
$type  = JRequest::GetVar('type');
$id    = (int) JRequest::GetVar('id');
$ae    = (int) JRequest::getVar('auto_enable', 1);
$cid   = JRequest::GetVar('cid');
$order = JRequest::getVar('ordering', array(), 'post','array');
$pack  = JRequest::getVar('pack', null, 'files');

// Load the controller
$controller = new PFconfigController();

switch( $core->GetTask() )
{
	default:
		$controller->DisplayGlobal();
		break;

	case 'list_sections':
        $controller->DisplaySections();
		break;
		
	case 'list_panels':
		$controller->DisplayPanels();
		break;
		
	case 'list_processes':
		$controller->DisplayProcesses();
		break;
		
	case 'list_mods':
		$controller->DisplayMods();
		break;
		
	case 'list_languages':
		$controller->DisplayLanguages();
		break;
		
	case 'list_themes':
		$controller->DisplayThemes();
		break;
		
	case 'form_edit_section':
        $controller->DisplayEditSection($id);
		break;

	case 'form_edit_panel':
        $controller->DisplayEditPanel($id);
		break;
		
	case 'form_edit_process':
        $controller->DisplayEditProcess($id);
		break;

    case 'form_edit_theme':
        $controller->DisplayEditTheme($id);
		break;
		
	case 'form_edit_mod':
        $controller->DisplayEditMod($id);
		break;	
		
	case 'task_save_global':
		$controller->SaveGlobal();
		break;
			
	case 'task_reorder':
		$controller->ReOrder($type, $order);
		break;
			
	case 'task_publish':
		$controller->Publish($type, $id, 1);
		break;
		
	case 'task_unpublish':
        $controller->Publish($type, $id, 0);
		break;
			
	case 'task_default_section':
		$controller->SetDefaultSection($id);
		break;
		
	case 'task_default_language':
		$controller->SetDefaultLanguage($id);
		break;	
		
	case 'task_default_theme':
		$controller->SetDefaultTheme($id);
		break;
		
	case 'task_update_section':
		$controller->UpdateSection($id);
		break;
		
	case 'task_update_panel':
		$controller->UpdatePanel($id);
		break;
		
	case 'task_update_process':
		$controller->UpdateProcess($id);
		break;

    case 'task_update_theme':
		$controller->UpdateTheme($id);
		break;
		
	case 'task_update_mod':
		$controller->UpdateMod($id);
		break;	
			
	case 'task_install':
		$controller->Install($pack, $ae);
		break;
		
	case 'task_uninstall':
        $controller->Uninstall($type, $cid);
	    break;
}
?>