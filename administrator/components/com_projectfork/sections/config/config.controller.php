<?php
/**
* $Id: config.controller.php 916 2011-09-19 17:07:42Z eaxs $
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

class PFconfigController extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }
    
	public function DisplayGlobal()
	{
		$form = new PFform();
		$form->setBind(true, 'REQUEST');
		
		$config = PFconfig::GetInstance();
		$html_checked = ' checked="checked"';
		
		require_once($this->GetOutput('display_global.php', 'config'));
	}
	
	public function DisplaySections()
	{
		$class = new PFconfigClass();
		$form  = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$form->SetBind(true, 'REQUEST');
		
		$limit      = (int) JRequest::getVar('limit', 50);
		$limitstart = (int) JRequest::getVar('limitstart', 0);
		$ob         = JRequest::getVar('ob', 'ordering', 'POST');
		$od         = JRequest::getVar('od', 'ASC', 'POST');
		
		$rows  = $class->LoadList('sections', $limitstart, $limit, $ob, $od);
		$total = $class->Count('sections');
		$pagination = new JPagination($total, $limitstart, $limit);
		
        $table = new PFtable(array('ORDERING', 'ENABLED', 'TITLE', 'DEFAULT', 'VERSION', 'AUTHOR',  'ID'),
		                     array('ordering', 'enabled', 'title', 'is_default', 'version', 'author', 'id'),
		                     'ordering',
		                     'ASC');

        $core_data = PFcontent::Sections();
        
        // Include output file
		require_once( $this->GetOutput('list_sections.php', 'config') );
		
		// Unset data
		unset($class,$form,$rows,$pagination,$table);
	}
	
	public function DisplayPanels()
	{
		$class = new PFconfigClass();
		$user  = PFuser::GetInstance();
		
		// Setup table
		$ob  = JRequest::getVar('ob', $user->GetProfile("panellist_ob", 'position,ordering'));
		$od  = JRequest::getVar('od', $user->GetProfile("panellist_od", 'ASC'));
		$ts1 = array('ORDERING', 'ENABLED', 'TITLE', 'POSITION', 'VERSION', 'AUTHOR', 'ID');
		$ts2 = array('ordering, position', 'enabled', 'title,ordering', 'position,ordering', 'version', 'author', 'id');
		$ts3 = array('ASC', 'DESC');
		
		if(!in_array($ob, $ts2)) $ob = 'position,ordering';
		if(!in_array($od, $ts3)) $od = 'ASC';
		
		$table = new PFtable($ts1, $ts2, $ob, $od);
		
		$position   = JRequest::getVar('position', $user->GetProfile("panellist_position", ''));
		$limit      = (int) JRequest::getVar('limit', $user->GetProfile("panellist_limit", 50));
		$limitstart = (int) JRequest::getVar('limitstart', 0);
		$total      = $class->Count('panels', $position);
		$rows       = $class->LoadList('panels', $limitstart, $limit, $ob, $od, $position);
		
		// Setup form
		$form = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$form->SetBind(true, 'REQUEST');

		// Filter
		$filter = "";
		if($limitstart) $filter .= "&limitstart=$limitstart";
		
		// Load paginaton
		$pagination = new JPagination($total, $limitstart, $limit);
		
		// save filter and order settings in the session
		$user->SetProfile("panellist_ob", $ob);
		$user->SetProfile("panellist_od", $od);
		$user->SetProfile("panellist_limit", $limit);
		$user->SetProfile("panellist_position", $position);
		
		// Load panel language files
        $lang = PFlanguage::GetInstance();

        foreach($rows AS $row)
        {
            $lang->Load($row->name, 'panel');
        }
            
        $core_data = PFcontent::Panels();
            
		// Include output file
		require_once($this->GetOutput('list_panels.php', 'config'));
		
		// Unset data
		unset($class,$table,$rows,$user,$pagination);
	}
	
	public function DisplayProcesses()
	{
		$class = new PFconfigClass();
		$user  = PFuser::GetInstance();
		
		// Create table
		$ob  = JRequest::getVar('ob', $user->GetProfile("processlist_ob", 'position,ordering'));
		$od  = JRequest::getVar('od', $user->GetProfile("processlist_od", 'ASC'));
		$ts1 = array('ORDERING', 'ENABLED', 'TITLE', 'EVENT', 'VERSION', 'AUTHOR', 'ID');
		$ts2 = array('ordering, event', 'enabled', 'title,event', 'event,ordering', 'version', 'author', 'id');
		$ts3 = array('ASC', 'DESC');
		
		if(!in_array($ob, $ts2)) $ob = 'event,ordering';
		if(!in_array($od, $ts3)) $od = 'ASC';

		$table = new PFtable($ts1, $ts2, $ob, $od);
		
		$position   = JRequest::getVar('position', $user->GetProfile("processlist_position", ''));
		$limit      = (int) JRequest::getVar('limit', $user->GetProfile("processlist_limit", 50));
		$limitstart = (int) JRequest::getVar('limitstart', 0);

		$rows  = $class->LoadList('processes', $limitstart, $limit, $ob, $od, $position);
		$total = $class->Count('processes', $position); 
		
		// Setup form
		$form = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$form->SetBind(true, 'REQUEST');
		
		// Filter
		$filter = "";
		if($limitstart) $filter .= "&limitstart=$limitstart";
		
		$pagination = new JPagination($total, $limitstart, $limit);
		
		// Load process language files
        $lang = PFlanguage::GetInstance();

        foreach($rows AS $row)
        {
            $lang->Load($row->name, 'process');
        }
        
		// save filter and order settings in the session
		$user->SetProfile("processlist_ob", $ob);
		$user->SetProfile("processlist_od", $od);
		$user->SetProfile("processlist_limit", $limit);
		$user->SetProfile("processlist_position", $position);
		
		$core_data = PFcontent::Processes();
		
		require_once($this->GetOutput('list_processes.php', 'config'));
	}
	
	public function DisplayMods()
	{
		$class = new PFconfigClass();
		$user  = PFuser::GetInstance();
		
		$limit      = (int) JRequest::getVar('limit', 50);
		$limitstart = (int) JRequest::getVar('limitstart', 0);
		$ob         = JRequest::getVar('ob', 'id', 'POST');
		$od         = JRequest::getVar('od', 'ASC', 'POST');
		$rows       = $class->LoadList('mods', $limitstart, $limit, $ob, $od);
		$total      = $class->Count('mods');
		
		$table = new PFtable(array('ENABLED', 'TITLE', 'VERSION', 'AUTHOR', 'ID'),
		                     array('enabled', 'title', 'version', 'author', 'id'),
		                     'id',
		                     'ASC');
		                     
		
		$form = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$form->SetBind(true, 'REQUEST');
		
		$pagination = new JPagination($total, $limitstart, $limit);
		
		// Load mod language files
        $lang = PFlanguage::GetInstance();

        foreach($rows AS $row)
        {
            $lang->Load($row->name, 'mod');
        }
        
		require_once($this->GetOutput('list_mods.php', 'config'));
	}
	
	public function DisplayLanguages()
	{
		$class = new PFconfigClass();

		$limit      = (int) JRequest::getVar('limit', 50);
		$limitstart = (int) JRequest::getVar('limitstart', 0);
		$ob         = JRequest::getVar('ob', 'id', 'POST');
		$od         = JRequest::getVar('od', 'ASC', 'POST');
		
		$rows  = $class->LoadList('languages', $limitstart, $limit, $ob, $od);
		$total = $class->Count('languages');
		
		$table = new PFtable(array('ENABLED', 'TITLE', 'DEFAULT', 'VERSION', 'AUTHOR', 'ID'),
		                     array('published', 'title', 'is_default', 'version', 'author', 'id'),
		                     'id',
		                     'ASC');
		                     
		$form = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$form->SetBind(true, 'REQUEST');
		
		$pagination = new JPagination($total, $limitstart, $limit);
		
		$core_data = PFcontent::Languages();
		
		require_once($this->GetOutput('list_languages.php', 'config'));
	}
	
	public function DisplayThemes()
	{
		$class    = new PFconfigClass();
		$form     = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		
		$limit        = (int) JRequest::getVar('limit', 50);
		$limitstart   = (int) JRequest::getVar('limitstart', 0);
		$ob           = JRequest::getVar('ob', 'id', 'POST');
		$od           = JRequest::getVar('od', 'ASC', 'POST');
		$rows         = $class->LoadList('themes', $limitstart, $limit, $ob, $od);
		$total        = $class->Count('themes');
		
		$table = new PFtable(array('ENABLED', 'TITLE', 'DEFAULT', 'VERSION', 'AUTHOR', 'ID'),
		                     array('enabled', 'title', 'is_default', 'version', 'author', 'id'),
		                     'id',
		                     'ASC');
		                     
		$form->SetBind('REQUEST');
		
		$pagination = new JPagination($total, $limitstart, $limit);
		
		// Load theme language files
        $lang = PFlanguage::GetInstance();

        foreach($rows AS $row)
        {
            $lang->Load($row->name, 'theme');
        }
        
        $core_data = PFcontent::Themes();
        
		require_once($this->GetOutput('list_themes.php', 'config'));
	}
	
	public function DisplayEditSection($id)
	{
	    require_once($this->GetHelper('config'));
	    
		$class  = new PFconfigClass();
		$config = PFconfig::GetInstance();
		$form   = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');

        // Load section
		$row = $class->Load('sections', $id);
		
		if(!$row) {
            $this->AddError('MSG_ITEM_NOT_FOUND');
            $this->SetRedirect('section=config&task=list_sections');
            return false;
        }
        
        // Load params
		$params = $class->LoadXMLParams('section', $row->name);

		// Format params
		$params_html = PFconfigHelper::HTMLparams($params, $row->name);
		unset($params);
		
		// Use score?
		$use_score = (int) $config->Get('use_score');

		$form->SetBind(true, $row);
		
		require_once($this->GetOutput('form_edit_section.php', 'config'));
	}
	
	public function DisplayEditPanel($id)
	{
	    require_once($this->GetHelper('config'));
	    
		$class  = new PFconfigClass();
		$config = PFconfig::GetInstance();
		$form   = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');

		$row = $class->Load('panels', $id);

        // Load panel language files
        $lang = PFlanguage::GetInstance();
        $lang->Load($row->name, 'panel');
        
		// Load params
		$params = $class->LoadXMLParams('panel', $row->name);
		
		// Format params
		$params_html = PFconfigHelper::HTMLparams($params, $row->name);
		unset($params);
		
		// Use score?
		$use_score = (int) $config->Get('use_score');

		$form->SetBind(true, $row);
		
		require_once($this->GetOutput('form_edit_panel.php', 'config'));
	}

    public function DisplayEditTheme($id)
	{
	    require_once($this->GetHelper('config'));
	    
		$class  = new PFconfigClass();
		$config = PFconfig::GetInstance();
		$form   = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');

		$row = $class->Load('themes', $id);

        // Load theme language files
        $lang = PFlanguage::GetInstance();
        $lang->Load($row->name, 'theme');
        
		// Load params
		$params = $class->LoadXMLParams('theme', $row->name);
		
		// Format params
		$params_html = PFconfigHelper::HTMLparams($params, 'theme_'.$row->name);
		unset($params);

		$form->SetBind(true, $row);

		require_once($this->GetOutput('form_edit_theme.php', 'config'));
	}
	
	public function DisplayEditProcess($id)
	{
	    require_once($this->GetHelper('config'));
	    
		$class  = new PFconfigClass();
		$config = PFconfig::GetInstance();
		$form   = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		
		$row    = $class->Load('processes', $id);
		$config = PFconfig::GetInstance();
		
		// Load process language files
        $lang = PFlanguage::GetInstance();
        $lang->Load($row->name, 'process');
        
		// Load params
		$params = $class->LoadXMLParams('process', $row->name);
		
		// Format params
		$params_html = PFconfigHelper::HTMLparams($params, $row->name);
		unset($params);
		
		// Use score?
		$use_score = (int) $config->Get('use_score');

		$form->SetBind(true, $row);
		
		require_once($this->GetOutput('form_edit_process.php', 'config'));
	}
	
	public function DisplayEditMod($id)
	{
        require_once($this->GetHelper('config'));
	    
		$class  = new PFconfigClass();
		$config = PFconfig::GetInstance();
		$form   = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		
		$row    = $class->Load('mods', $id);
		$config = PFconfig::GetInstance();
		
		// Load process language files
        $lang = PFlanguage::GetInstance();
        $lang->Load($row->name, 'mod');
        
		// Load params
		$params = $class->LoadXMLParams('mod', $row->name);
		
		// Format params
		$params_html = PFconfigHelper::HTMLparams($params, $row->name);
		unset($params);

		$form->SetBind(true, $row);
		
		require_once($this->GetOutput('form_edit_mod.php', 'config'));
    }
	
	public function ReOrder($type, $order)
	{
		$class = new PFconfigClass();
		$ls    = (int) JRequest::getVar('limitstart');
		
		$filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		
		switch ($type)
		{
		    case 'section': $task = "list_sections";  break;
			case 'panel':   $task = "list_panels";    break;
			case 'process': $task = "list_processes"; break;
        }
			
		if(!$class->ReOrder($type, $order)) {
            $this->SetRedirect("section=config&task=".$task.$filter, 'MSG_REORDER_FAILED');
			return false;
		}
		
		$this->SetRedirect("section=config&task=".$task.$filter, 'MSG_REORDER_SUCCESS');
		return true;
	}
	
	public function Publish($type, $id, $state = 0)
	{
		$class = new PFconfigClass();
		$ls    = (int) JRequest::getVar('limitstart');
		
		$filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		
		switch($type)
		{
            case 'section':  $task = 'list_sections';  break;
            case 'panel':    $task = 'list_panels';    break;
            case 'process':  $task = 'list_processes'; break;
            case 'mod':      $task = 'list_mods';      break;
            case 'language': $task = 'list_languages'; break;
            case 'theme':    $task = 'list_themes';    break;
        }
        
        $link = 'section=config&task='.$task.$filter;
        
        switch($state)
        {
            case 0:
                $success = 'MSG_UNPUBLISH_SUCCESS';
                $failed  = 'MSG_UNPUBLISH_FAILED';
                break;
                
            case 1:
                $success = 'MSG_PUBLISH_SUCCESS';
                $failed  = 'MSG_PUBLISH_FAILED';
                break;
        }
		
		if(!$class->Publish($type, $id, $state)) {
            $this->SetRedirect($link, $failed);
            return false;
        }
        else {
            $this->SetRedirect($link, $success);
            return true;
        }
	}
	
	public function SaveGlobal()
	{
	    $db     = PFdatabase::GetInstance();
	    $config = PFconfig::GetInstance();
	    
		$params = array('debug', 'debug_panels', 'hide_template', 'date_format', 'display_avatar',
		                'tooltip_restricted', 'tooltip_help', 'html_emails', 'panel_edit',
                        'cache_panels', 'cache_core', 'cache_user', 'cache_mods', 'cache_lang',
                        'use_score', 'use_ssl_fe', 'use_ssl_be', 'edit_lightbox', '12hclock',
                        'use_wizard');
		                
		
		// Save settings
		foreach ($params AS $param)
		{
			$value = JRequest::getVar($param);
			
			// Enable/Disable sys console?
			if($param == 'debug' && $value == '1') {
                $query = "UPDATE #__pf_panels SET enabled = '1'"
                       . "\n WHERE name = 'system_console'";
                       $db->setQuery($query);
                       $db->query();
            }
            
            if($param == 'debug' && $value == '') {
                $query = "UPDATE #__pf_panels SET enabled = '0'"
                       . "\n WHERE name = 'system_console'";
                       $db->setQuery($query);
                       $db->query();
            }
            
            // Save setting
			$config->Set($param, $value, 'system');
		}
		
		// Clean cache
		PFcache::Clean();
		
		// Set redirect
		$this->SetRedirect("section=config", 'SETTINGS_SAVED');
	}
	
	public function SetDefaultSection($id)
	{
		$class = new PFconfigClass();
		
		if(!$class->SetDefault('section', $id)) {
            $this->SetRedirect("section=config&task=list_sections", 'MSG_DEFSECTION_FAILED');
            return false;
        }
		
		// Clean core cache
		PFcache::Clean('core');
		
		$this->SetRedirect("section=config&task=list_sections", 'MSG_DEFSECTION_SUCCESS');
		return true;
	}
	
	public function SetDefaultLanguage($id)
	{
		$class = new PFconfigClass();
		
		if(!$class->SetDefault('language', $id)) {
            $this->SetRedirect("section=config&task=list_languages", 'MSG_DEFLANG_FAILED');
            return false;
        }
        
        // Clean core cache
		PFcache::Clean('core');
		
		$this->SetRedirect("section=config&task=list_languages", 'MSG_DEFLANG_SUCCESS');
		return true;
	}
	
	public function SetDefaultTheme($id)
	{
		$class = new PFconfigClass();
		
		if(!$class->SetDefault('theme', $id)) {
            $this->SetRedirect("section=config&task=list_themes", 'MSG_DEFTHEME_FAILED');
            return false;
        }
        
        // Clean core cache
		PFcache::Clean('core');
		
		$this->SetRedirect("section=config&task=list_themes", 'MSG_DEFTHEME_SUCCESS');
		return true;
	}
	
	public function UpdateSection($id)
	{
		$ls  = (int) JRequest::getVar('limitstart');
		$l   = (int) JRequest::getVar('limit');
		$rts = (int) Jrequest::getVar('rts');
		$apply = (int) Jrequest::getVar('apply');
		
		$class = new PFconfigClass();
		
		$link = "section=config&task=list_sections&limitstart=$ls";
		
		if($rts) {
		    $db = PFdatabase::GetInstance();
			$query = "SELECT name FROM #__pf_sections WHERE id = '$id'";
			       $db->setQuery($query);
			       $section = $db->loadResult();
			       
			$link = "section=$section";
		}
		
		if($apply) {
			$link = "section=config&task=form_edit_section&id=$id&limitstart=$ls";
		}
		
		if(!$class->UpdateSection($id)) {
			$this->SetRedirect($link, 'MSG_E_UPDATE');
			return false;
		}
		else {
		    // Clean core cache
		    PFcache::Clean('core');
		
			$this->SetRedirect($link, 'MSG_S_UPDATE');
			return true;
		}
	}
	
	public function UpdatePanel($id)
	{
		$ls = (int) JRequest::getVar('limitstart');
		$filter = "";
		
		if($ls) $filter .= "&limitstart=$ls";

		$class = new PFconfigClass();
		
		if(!$class->UpdatePanel($id)) {
			$this->SetRedirect("section=config&task=list_panels".$filter, 'MSG_E_UPDATE');
			return false;
		}
		else {
		    // Clean core cache
		    PFcache::Clean('core');
		    
		    // Clean panels cache
		    PFcache::Clean('panels');
		
			$this->SetRedirect("section=config&task=list_panels".$filter, 'MSG_S_UPDATE');
			return true;
		}
	}

    public function UpdateTheme($id)
	{
		$ls = (int) JRequest::getVar('limitstart');

		$filter = "";
		if($ls) $filter .= "&limitstart=$ls";

		$class = new PFconfigClass();

		if(!$class->UpdateTheme($id)) {
			$this->SetRedirect("section=config&task=list_themes".$filter, 'MSG_E_UPDATE');
			return false;
		}
		else {
			$this->SetRedirect("section=config&task=list_themes".$filter, 'MSG_S_UPDATE');
			return true;
		}
	}
	
	public function UpdateProcess($id)
	{
		$ls = (int) JRequest::getVar('limitstart');
		$filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		
		$class = new PFconfigClass();
		
		if(!$class->UpdateProcess($id)) {
			$this->SetRedirect("section=config&task=list_processes".$filter, 'MSG_E_UPDATE');
			return false;
		}
		else {
		    // Clean core cache
		    PFcache::Clean('core');
		
			$this->SetRedirect("section=config&task=list_processes".$filter, 'MSG_S_UPDATE');
			return true;
		}
	}
	
	public function UpdateMod($id)
	{
		$ls = (int) JRequest::getVar('limitstart');
		$filter = "";
		if($ls) $filter .= "&limitstart=$ls";
		
		$class = new PFconfigClass();
		
		if(!$class->UpdateMod($id)) {
			$this->SetRedirect("section=config&task=list_mods".$filter, 'MSG_E_UPDATE');
			return false;
		}
		else {
		    // Clean core cache
		    PFcache::Clean('core');
		
			$this->SetRedirect("section=config&task=list_mods".$filter, 'MSG_S_UPDATE');
			return true;
		}
	}
	
	public function Install($package, $auto_enable = 1)
	{
	    $ins = new PFextensionInstaller($package);
		
		// Check server settings and upload
		if(!$ins->Check()) {
            $this->SetRedirect('section=config', $ins->GetError());
            return false;
        }
        
        // Install the package
        if(!$ins->Install($auto_enable)) {
            $this->SetRedirect('section=config', $ins->GetError());
            return false;
        }

        // Clean core cache
		PFcache::Clean('core');
		// Clean panels cache
		PFcache::Clean('panels');
		// Clean mods cache
		PFcache::Clean('mods');
		    
		$this->SetRedirect('section=config', 'MSG_EXTENSION_INS_SUCCESS');
		return true;
	}
	
	public function Uninstall($type, $cid)
	{
	    $id = (int) $cid[0];
		$class = new PFextensionInstaller();
		
		switch ($type)
		{
			case 'section':  $task = "list_sections";  break;
			case 'panel':    $task = "list_panels";    break;
			case 'process':  $task = "list_processes"; break;
			case 'mod':      $task = "list_mods";      break;
			case 'language': $task = "list_languages"; break;
			case 'theme':    $task = "list_themes";    break;
		}
		
		if(!$class->Uninstall($type, $id)) {
			$this->SetRedirect('section=config&task='.$task, $class->getError());
			return false;
		}
		
		// Clean core cache
		PFcache::Clean('core');
		// Clean panels cache
		PFcache::Clean('panels');
		// Clean mods cache
		PFcache::Clean('mods');
		
		$this->SetRedirect('section=config&task='.$task, 'MSG_EXTENSION_UNINS_SUCCESS');
		return true;
	}
}
?>