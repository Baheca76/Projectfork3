<?php
/**
* $Id: installer.php 914 2011-09-12 22:00:17Z angek $
* @package    Projectfork
* @subpackage Framework
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

class PFinstaller
{
    private $db;
    private $db_vnum;
    private $db_vstate;
    private $file_vnum;
    private $file_vstate;
    private $is_installed;
    private $is_2100;
    
    public function __construct()
    {
        $this->db = JFactory::getDBO();
        
        // Setup file version
        $this->file_vnum   = PF_VERSION_NUM;
        $this->file_vstate = PF_VERSION_STATE;
        
        // Projectfork 2.1 indicator
        $this->is_2100 = 0;
    }
    
    public function GetInstance()
    {
        static $self;
        
        if(is_object($self)) return $self;
        $self = new PFinstaller();
        return $self;
    }
    
    public function CheckInstalled()
    {
        $error = false;
        
        $query = "SELECT `content` FROM #__pf_settings"
               . "\n WHERE `parameter` = 'installed'"
               . "\n AND `scope` = 'system'";
               $this->db->setQuery($query);
               $this->is_installed = (int) $this->db->loadResult();
               
        if($this->is_installed == 1) return true;
        if($this->db->GetErrorMsg()) $error = true;
        
        // Check for old version
        if($error) {
            $query = "SELECT `value` FROM #__pf_settings"
                   . "\n WHERE `parameter` = 'is_installed'"
                   . "\n AND `scope` = 'system'";
                   $this->db->setQuery($query);
                   $this->is_installed = (int) $this->db->loadResult();
                   
            if($this->is_installed == 1) {
                $this->is_2100 = 1;
                return true;
            }
        }
            
        return false;
    }

    public function CheckUpgraded()
    {
        if($this->is_2100 == 0) return true;
        
        $query = "SELECT `value` FROM #__pf_settings"
               . "\n WHERE `parameter` = 'version'"
               . "\n AND `scope` = 'system'";
               $this->db->setQuery($query);
               $this->db_vnum = $this->db->loadResult();
        
        if(is_null($this->db_vnum)) return true;
        
        $this->db_vnum = (int) $this->db_vnum;
        
        if($this->db_vnum != 0 && $this->db_vnum < 2200) return false;
        
        return true;
    }
    
    public function UpgradeComponent()
    {
        $file = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'_install'.DS.'upgrade.init.php';
        
        if(file_exists($file)) {
            require_once($file);
            return true;
        }
        
        echo 'Upgrade file: "'.$file.'" not found! Unable to upgrade the component!';
        return false;
    }
    
    public function InstallComponent()
    {
        $file = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'_install'.DS.'setup.init.php';
        
        if(file_exists($file)) {
            require_once($file);
            return true;
        }
        
        echo 'Installation file: "'.$file.'" not found! Please re-install the component!';
        return false;
    }
}

class PFextensionInstaller
{
    private $package;
    private $error;
    private $is_checked;
    private $is_multipackage;
    private $is_updatelist;
    private $tmp_path;
    private $tmp_unpack;
    private $xml_files;
    private $ins_types;
    private $installed;
    private $num_packages;
    
    public function __construct($package = NULL)
    {
        $this->package = $package;
        
        $this->is_checked      = false;
        $this->is_multipackage = false;
        $this->is_updatelist   = false;
        
        $this->error      = NULL;
        $this->tmp_unpack = NULL;
        
        $this->xml_files = array();
        $this->installed = array();
        
        $this->num_packages = 0;
        
        // Import joomla classes needed for the install
        jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.archive');
		jimport('joomla.installer.helper');
		
		// Set temp folder from j config
		$config = JFactory::getConfig();
		$this->tmp_path = $config->getValue('config.tmp_path');
		unset($config);
		
		// Set valid install types
		$this->ins_types = array('section','panel','process','mod','language','theme','system');
    }
    
    public function GetError()
    {
        return $this->error;
    }
    
    public function Check()
    {
        if(!(bool) ini_get('file_uploads')) {
			$this->SetError('MSG_FILE_UPLOAD_DISABLED');
			return false;
		}
		if(!extension_loaded('zlib')) {
			$this->SetError('MSG_ZLIB_NOT_INSTALLED');
			return false;
		}
		if(!is_array($this->package)) {
			$this->SetError('MSG_NO_FILE_UPLOADED');
			return false;
		}
		if($this->package['error']) {
			$this->SetError('MSG_UPLOAD_ERROR');
			return false;
		}
		
		$this->is_checked = true;
		
		return true;    
    }
    
    public function CheckUpdateCompat($name, $type, $uversion)
    {
        $db   = PFdatabase::GetInstance();
        $name = $db->Quote($name);
        
        $tmp_uversion    = explode(',',$uversion);
        $compat_versions = array();
        
        foreach($tmp_uversion AS $v)
        {
            $compat_versions[] = trim($v);
        }
        
        switch ($type)
		{
			case 'section':  $table = "#__pf_sections";  break;
			case 'panel':    $table = "#__pf_panels";    break;
			case 'process':  $table = "#__pf_processes"; break;
			case 'mod':      $table = "#__pf_mods";      break;		
			case 'language': $table = "#__pf_languages"; break;	
			case 'theme':    $table = "#__pf_themes";    break;	
		}
		
		// Check for system package
		if($type == 'system') {
            $query = "SELECT content FROM #__pf_settings"
                   . "\n WHERE `parameter` = 'version_num'"
                   . "\n AND `scope` = 'system'";
                   $db->setQuery($query);
                   $v_num = trim($db->loadResult());
                   
            $query = "SELECT content FROM #__pf_settings"
                   . "\n WHERE `parameter` = 'version_state'"
                   . "\n AND `scope` = 'system'";
                   $db->setQuery($query);
                   $v_state = trim($db->loadResult());       
            
            // Check version number only
            if(!in_array($v_num, $compat_versions)) {
                // Not found. Check with version state
                $numstate = $v_num.'::'.$v_state;
                if(!in_array($numstate, $compat_versions)) return false;
            }
        }
        else {
            // Standard extension
            $query = "SELECT version FROM $table WHERE name = $name";
		           $db->setQuery($query);
		           $version = trim($db->loadResult());
		           
		    if(!in_array($version, $compat_versions)) return false;       
        }

		return true;
    }
    
    public function Install($auto_enable = 0, $update_list = true, $upload = true)
    {
        $core = PFcore::GetInstance();
            
        if(!$this->is_checked) return false;
        
        // Upload the file
        if($upload) {
            if(!$this->Upload()) return false;
        }
        
        // Unpack the file
        if(!$this->Unpack()) return false;
        
        // Search for update list xml
        if($update_list && $this->is_updatelist == false) {
            $list_object = $this->FindXML(true);
            if(is_object($list_object)) {
                return $this->UpdateFromList($list_object, $auto_enable);
            }
        }
        
        // Find the regular install xml file(s)
        if(!$this->FindXML()) {
            $this->CleanTMP();
            return false;
        }
        
        // Install extension(s)
        $i = 1;
        
        foreach($this->xml_files AS $xml_file => $ext)
        {
            $type     = $ext['type'];
            $update   = $ext['update'];
            $object   = $ext['object'];
            $uversion = $ext['uversion'];
            $list     = $this->XMLchildrenList($object);
            
            switch($type)
            {
                case 'section':  $method = 'RegisterSection';  break;
                case 'panel':    $method = 'RegisterPanel';    break;
                case 'process':  $method = 'RegisterProcess';  break;
                case 'mod':      $method = 'RegisterMod';      break;
                case 'language': $method = 'RegisterLanguage'; break;
                case 'theme':    $method = 'RegisterTheme';    break;
                case 'system':   $method = 'RegisterSystem';   break;
            }
            
            // Check if all mandatory xml tags are there
            if(!$this->CheckXML($type, $object)) {
                $this->CleanTMP(true);
                return false;
            }
            
            // Get extension name
            $name = $object->name[0]->data();

            // Check if the extension is already installed
            if($this->IsInstalled($type, $name)) {
                if(!$update) {
                    $this->SetError($name.' - '.PFformat::Lang('MSG_EXT_ALREADY_INSTALLED'));
                    $this->CleanTMP(true);
                    return false;
                }
                else {
                    // Check if the update is compatible with the version currently installed
                    if(!$this->CheckUpdateCompat($name, $type, $uversion)) {
                        $this->SetError($name.' - '.PFformat::Lang('MSG_EXT_UPDATE_NOTCOMPAT'));
                        $this->CleanTMP(true);
                        return false;
                    }
                }
            }
            else {
                // Cannot update an extension that isnt installed
                if($update) {
                    // Skip error if update is contained in multi-package
                    if($this->is_multipackage) {
                        $i++;
                        continue;
                    }
                    $this->SetError($name.' - '.PFformat::Lang('MSG_EXT_NOT_INS_UPDATE'));
                    $this->CleanTMP(true);
                    return false;
                }
            }
            
            // Move files to their destination folder
            if(!$this->MoveFiles($type, $object, $xml_file, $update)) {
                $this->CleanTMP(true);
                return false;
            }
            
            // Register extension in database
            if(!$this->$method($object, $update, $auto_enable)) {
                if(!$update) $this->DeleteFiles($type, $name);
                $this->CleanTMP(true);
                return false;
            }
            
            
            // Register params
            if(array_key_exists('params', $list) && $type != 'language') {
                $params = $object->params[0]->children();
                if(!is_array($params)) $params = array();
            
                if(!$this->RegisterParams($type, $name, $params, $update)) {
                    $core->AddMessage($this->GetError());
                }
            
                unset($params);
            }
            
            // Custom queries
            if(array_key_exists('install_sql', $list) && $type != 'language') {
                $sql = $object->install_sql[0]->children();
                if(!is_array($sql)) $sql = array();
                
                if(!$this->RunQueries($sql)) {
                    $core->AddMessage($this->GetError());
                }
                
                unset($sql);
            }
            
            // Include custom file
            if(array_key_exists('install_file', $list) && $type != 'language') {
                $file = trim($list['install_file']);
                if($file) {
                    if(!$this->CustomFile($type, $name, $file)) {
                        $core->AddMessage($this->GetError());
                    }
                }
            }
            
            // Register extension as installed
            if(!$update) $this->installed[$name] = $type;
            
            // Add success message
            $msg = (!$update) ? PFformat::Lang('MSG_INS_EXTENSION') : PFformat::Lang('MSG_INS_UPDATED_GENERIC');
            $msg = str_replace('{name}', $name, $msg);
                
            switch($type)
            {
                case 'section':  $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_SECTION'), $msg); break;
                case 'panel':    $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_PANEL'), $msg); break;
                case 'process':  $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_PROCESS'), $msg); break;
                case 'mod':      $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_MOD'), $msg); break;
                case 'language': $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_LANGUAGE'), $msg); break;
                case 'theme':    $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_THEME'), $msg); break;
                case 'system':   $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_SYSTEM'), $msg); break;
            }
            
            if($upload) $core->AddMessage($msg);
            
            unset($ext,$object,$list);
            $i++;
        }
        
        unset($core);
        $this->xml_files = array();
        $this->CleanTMP();
        
        return true;
    }
    
    public function Uninstall($type, $id)
    {
        $core = PFcore::GetInstance();
        
        if(!$id) {
            $this->SetError('MSG_UNINS_NO_ID');
            return false;
        }
        
        // Load the extension main record
        $row = $this->LoadExtension($type, $id);
        
        if(!is_object($row)) {
            $this->SetError('MSG_UNINS_EXT_NOTFOUND');
            return false;
        }
        
        // Check if we have a core extension
        $core_data = array();
        
        switch($type)
        {
            case 'section':  $core_data = PFcontent::Sections(); break;
            case 'panel':    $core_data = PFcontent::Panels(); break;
            case 'process':  $core_data = PFcontent::Processes(); break;
            case 'language': $core_data = PFcontent::Languages(); break;
            case 'theme':    $core_data = PFcontent::Themes(); break;
        }
        
        if(in_array($row->name, $core_data)) {
            $this->SetError('MSG_UNINS_CORE_EXT');
            return false;
        }
        
        // Read the xml file for additional uninstall instructions
        $xml = $this->ReadXML($type, $row->name);
        
        if($xml === false) {
            // Unregister
            if(!$this->Unregister($type, $row->name)) return false;
        
            // Delete files
            if(!$this->DeleteFiles($type, $row->name)) return false;
            return false;
        }
        
        // Get child tags
        $list = $this->XMLchildrenList($xml);
        
        // Run uninstall queries
        if(array_key_exists('uninstall_sql', $list) && $type != 'language') {
            $sql = $xml->uninstall_sql[0]->children();
            if(!is_array($sql)) $sql = array(); 
            $this->RunQueries($sql);
        }
        
        // Run custom uninstall file
        if(array_key_exists('uninstall_file', $list) && $type != 'language') {
            $cfile = $xml->uninstall_file[0]->data();
            
            if($cfile) $this->CustomFile($type, $row->name, $cfile);
        }
        
        // Unregister
        if(!$this->Unregister($type, $row->name)) return false;
        
        // Delete files
        if(!$this->DeleteFiles($type, $row->name)) return false;
        
        // Uninstall linked extensions
        if(array_key_exists('uninstall_ext', $list)) {
            $unins_list = $xml->uninstall_ext[0]->children();
            $db = PFdatabase::GetInstance();
            
            foreach($unins_list AS $uni_ex)
            {
                $ex_name = strtolower(trim($uni_ex->data()));
                $ex_type = $uni_ex->attributes('type');
                
                if(!$ex_type || !$ex_name) continue;
                
                switch($ex_type)
                {
                    case 'section':  $table = "#__pf_sections";  break;
                    case 'panel':    $table = "#__pf_panels";    break;
                    case 'process':  $table = "#__pf_processes"; break;
                    case 'mod':      $table = "#__pf_mods";      break;
                    case 'language': $table = "#__pf_languages"; break;
                    case 'theme':    $table = "#__pf_themes";    break;
                }
                
                $qname = $db->Quote($ex_name);
                $query = "SELECT id FROM $table WHERE LOWER(name) = $qname";
                       $db->setQuery($query);
                       $ex_id = (int) $db->loadResult();
                       
                // Do another uninstall       
                if($ex_id) {
                    if(!$this->Uninstall($ex_type, $ex_id)) return false;
                } 
            }
            unset($db);
        }
        
        // Add success message
        $msg = PFformat::Lang('MSG_UNINS_EXTENSION');
        $msg = str_replace('{name}', $row->name, $msg);
                
        switch($type)
        {
            case 'section':  $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_SECTION'), $msg); break;
            case 'panel':    $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_PANEL'), $msg); break;
            case 'process':  $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_PROCESS'), $msg); break;
            case 'mod':      $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_MOD'), $msg); break;
            case 'language': $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_LANGUAGE'), $msg); break;
            case 'theme':    $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_THEME'), $msg); break;
            case 'system':   $msg = str_replace('{type}', PFformat::Lang('TYPE_OF_SYSTEM'), $msg); break;
        }
        
        $core->AddMessage($msg);
        
        return true;
    }
    
    private function UpdateFromList($object, $auto_enable = 0)
    {
        $core = PFcore::GetInstance();
        
        $updates = $object->children();
        $list    = array();
        $i       = 0;
        
        $found_update = false;
        $tmp_unpack   = $this->tmp_unpack;
        $tmp_package  = $this->package;
        $tmp_path     = $this->tmp_path;
        
        foreach($updates AS $update)
        {
            $tag      = $update->name();
            $versions = trim($update->attributes('from'));
            $name     = trim($update->attributes('name'));
            $title    = trim($update->attributes('title'));
            $type     = trim($update->attributes('type'));
            $vname    = trim($update->attributes('to'));
            $package  = trim($update->data());
            
            if($tag != 'update') continue;
            if(!$versions)       continue;
            if(!$name)           continue;
            if(!$title)          continue;
            if(!$type)           continue;
            if(!$package)        continue;
            if(!$vname)          continue;
            
            $list[$i]['name']     = $name;
            $list[$i]['title']    = $title;
            $list[$i]['type']     = $type;
            $list[$i]['package']  = $package;
            $list[$i]['versions'] = $versions;
            $list[$i]['vname']    = $vname;

            unset($update);
            $i++;
        }
        
        unset($updates, $update, $object);
        
        // Loop through each possible update and try to install it
        foreach($list AS $update)
        {
            // Reset installer after each try
            $this->is_multipackage = false;
            $this->error           = NULL;
            $this->tmp_unpack      = NULL;
            $this->tmp_path        = NULL;
            $this->xml_files       = array();
            $this->installed       = array();
            $this->package         = array();
            $this->num_packages    = 0;
            
            // Check compat
            $is_compat = $this->CheckUpdateCompat($update['name'], $update['type'], $update['versions']);
            if(!$is_compat) continue;

            // Override package
            $this->package['name'] = $update['package'];
            $this->tmp_path = $tmp_unpack;
            
            // Try install
            $this->is_updatelist = true;
            if(!$this->Install($auto_enable, false, false)) {
                $this->package    = $tmp_package;
                $this->tmp_unpack = $tmp_unpack;
                $this->tmp_path   = $tmp_path;
                $this->CleanTMP();
                return false;
            }
            else {
                $found_update = true;
                $msg = PFformat::Lang('MSG_INS_UPDATED');
                $msg = str_replace('{title}', $update['title'], $msg).' '.$update['vname'];
                $core->AddMessage($msg);
            }
        }

        $this->is_updatelist = false;
        
        // Reset package
        $this->package    = $tmp_package;
        $this->tmp_unpack = $tmp_unpack;
        $this->tmp_path   = $tmp_path;
        
        if(!$found_update) {
            $this->SetError('MSG_INS_NOUPDATE_FOUND');
            $this->CleanTMP();
            return false;
        }
        
        $this->CleanTMP();
        return true;
    }
    
    private function CustomFile($type, $name, $file)
    {
        $com = PFcomponent::GetInstance();
        
        $base_path = $com->Get('path_backend');
        unset($com);
        
        switch($type)
        {
            case 'section':  $folder = 'sections';  break;
            case 'panel':    $folder = 'panels';    break;
            case 'process':  $folder = 'processes'; break;
            case 'theme':    $folder = 'themes';    break;
            case 'mod':      $folder = 'mods';      break;
            case 'language': $folder = 'languages'; break;
        }
        
        if($type == 'system') {
            $path = JPath::clean($base_path.DS.$file);
        }
        else {
            $path = JPath::clean($base_path.DS.$folder.DS.$name.DS.$file);
        }
        
        if(!file_exists($path)) {
            $this->SetError('MSG_CUSTOM_FILE_NOT_FOUND');
            return false;
        }
        
        require_once($path);
        
        return true;
    }
    
    private function RunQueries($queries)
    {
        $db = PFdatabase::GetInstance();
        
        foreach($queries AS $q)
        {
            $query = $q->data();
            
            $db->setQuery($query);
            $db->query();
            
            if($db->GetErrorMsg()) {
               $this->SetError($db->GetErrorMsg());
               unset($queries,$db);
               return false;
            }
        }
        
        unset($queries,$db);
        return true;
    }
    
    private function IsInstalled($type, $name)
    {
        $db   = PFdatabase::GetInstance();
        $name = $db->Quote($name);
        
        // Return true for system packages
        if($type == 'system') return true;
        
        switch($type)
        {
            case 'section':  $table = 'sections';  break;
            case 'panel':    $table = 'panels';    break;
            case 'process':  $table = 'processes'; break;
            case 'mod':      $table = 'mods';      break;
            case 'language': $table = 'languages'; break;
            case 'theme':    $table = 'themes';    break;
        }
            
        $query = "SELECT id FROM #__pf_$table"
               . "\n WHERE name = $name";
               $db->setQuery($query);
               $result = (int) $db->loadResult();
        
        unset($db);
        if($result) return true;

        return false;
    }
    
    private function XMLchildrenList($object)
    {
        $children = $object->children();
        $list = array();
        
        foreach($children AS $child)
        {
            $name = $child->name();
            $data = $child->data();
            $list[$name] = $data;
            unset($child);
        }
        
        unset($object,$children);
        
        return $list;
    }
    
    private function Unregister($type, $name)
    {
        $db = PFdatabase::GetInstance();
        $cname   = $db->Quote($name);
        $queries = array();
        
        if($type == 'section') {
            $queries[] = "DELETE FROM #__pf_sections WHERE name = $cname";
            $queries[] = "DELETE FROM #__pf_section_tasks WHERE section = $cname";
            $queries[] = "DELETE FROM #__pf_settings WHERE scope = $cname";
        }
        if($type == 'panel') {
            $queries[] = "DELETE FROM #__pf_panels WHERE name = $cname";
            $queries[] = "DELETE FROM #__pf_settings WHERE scope = $cname";
        }
        if($type == 'process') {
            $queries[] = "DELETE FROM #__pf_processes WHERE name = $cname";
            $queries[] = "DELETE FROM #__pf_settings WHERE scope = $cname";
        }
        if($type == 'theme') {
            $queries[] = "DELETE FROM #__pf_themes WHERE name = $cname";
            $queries[] = "DELETE FROM #__pf_settings WHERE scope = ".$db->Quote('theme_'.$name);
        }
        if($type == 'mod') {
            $queries[] = "DELETE FROM #__pf_mods WHERE name = $cname";
            $queries[] = "DELETE FROM #__pf_mod_files WHERE name = $cname";
            $queries[] = "DELETE FROM #__pf_settings WHERE scope = $cname";
        }
        if($type == 'language') {
            $queries[] = "DELETE FROM #__pf_languages WHERE name = $cname";
        }
        
        foreach($queries AS $query)
        {
            $db->setQuery($query);
            $db->query();
            
            if($db->getErrorMsg()) {
                $this->SetError($db->getErrorMsg());
                return false;
            }
        }
        
        return true;
    }
    
    private function RegisterSection($object, $update = false, $auto_enable = 0)
    {
        $db   = PFdatabase::GetInstance();
        $list = $this->XMLchildrenList($object);

        $name    = $db->Quote( (array_key_exists('name', $list)    ? $list['name']    : '') );
        $title   = $db->Quote( (array_key_exists('title', $list)   ? $list['title']   : '') );
        $score   = $db->Quote( (array_key_exists('score', $list)   ? $list['score']   : '') );
        $flag    = $db->Quote( (array_key_exists('flag', $list)    ? $list['flag']    : '') );
        $tags    = $db->Quote( (array_key_exists('tags', $list)    ? $list['tags']    : '') );
        $author  = $db->Quote( (array_key_exists('author', $list)  ? $list['author']  : '') );
        $email   = $db->Quote( (array_key_exists('email', $list)   ? $list['email']   : '') );
        $website = $db->Quote( (array_key_exists('website', $list) ? $list['website'] : '') );
        $version = $db->Quote( (array_key_exists('version', $list) ? $list['version'] : '') );
        $license = $db->Quote( (array_key_exists('license', $list) ? $list['license'] : '') );
        $date    = $db->Quote( (array_key_exists('date', $list)    ? $list['date']    : '') );
        $copy    = $db->Quote( (array_key_exists('copyright', $list) ? $list['copyright']    : '') );
        $now     = time();
        
        // Register new section
        if(!$update) {
            // Get max ordering
            $query = "SELECT MAX(ordering) FROM #__pf_sections";
                   $db->setQuery($query);
                   $order = (int) $db->loadResult();
               
            $order ++;
        
            // Register section
            $query = "INSERT INTO #__pf_sections VALUES("
                   . "\n NULL, $name, $title, '$auto_enable', $score, $flag, $tags, '0',"
                   . "\n $order, $author, $email, $website, $version,"
                   . "\n $license, $copy, $date, '$now');";
                   $db->setQuery($query);
                   $db->query();
                  
            $id = (int) $db->insertid();       
            if($db->getErrorMsg() || !$id) {
                $this->SetError($db->getErrorMsg());
                unset($object,$db);
                return false;
            }
            
            // Register section tasks
            if(array_key_exists('permissions', $list)) {
                $permissions = $object->permissions[0]->children();
                if(!is_array($permissions)) $permissions = array();
                
                foreach($permissions AS $permission)
                {
                    $p_name  = $db->Quote( $permission->attributes('name') );
                    $p_title = $db->Quote( $permission->attributes('title') );
                    $p_desc  = $db->Quote( $permission->attributes('desc') );
                    $p_score = $db->Quote( $permission->attributes('score') );
                    $p_flag  = $db->Quote( $permission->attributes('flag') );
                    $p_tags  = $db->Quote( $permission->attributes('tags') );
                    $p_parent = $db->Quote( $permission->attributes('parent') );
                    $p_order = $db->Quote( $permission->attributes('ordering') );
                    
                    if(!$permission->attributes('name')) continue;
                    
                    $query = "INSERT INTO #__pf_section_tasks VALUES("
                           . "\n NULL, $name, $p_name, $p_title, $p_desc,"
                           . "\n $p_score, $p_flag, $p_tags, $p_parent, $p_order)";
                           $db->setQuery($query);
                           $db->query();
                           
                    if($db->getErrorMsg()) {
                        $this->SetError($db->getErrorMsg());
                        $this->Unregister('section', $list['name']);
                        unset($object,$db);
                        return false;
                    }
                }
            }
        }
        else {
            // Update existing section
            $query = "UPDATE #__pf_sections"
                   . "\n SET author = $author, email = $email,"
                   . "\n website = $website, version = $version,"
                   . "\n license = $license, copyright = $copy,"
                   . "\n create_date = $date"
                   . "\n WHERE name = $name";
                   $db->setQuery($query);
                   $db->query();
                   
            if($db->getErrorMsg()) {
                $this->SetError($db->getErrorMsg());
                unset($object,$db);
                return false;
            }
                   
            // Update section tasks
            if(array_key_exists('permissions', $list)) {
                $permissions = $object->permissions[0]->children();
                if(!is_array($permissions)) $permissions = array();
                
                foreach($permissions AS $permission)
                {
                    $p_name  = $db->Quote( $permission->attributes('name') );
                    $p_title = $db->Quote( $permission->attributes('title') );
                    $p_desc  = $db->Quote( $permission->attributes('desc') );
                    $p_score = $db->Quote( $permission->attributes('score') );
                    $p_flag  = $db->Quote( $permission->attributes('flag') );
                    $p_tags  = $db->Quote( $permission->attributes('tags') );
                    $p_parent = $db->Quote( $permission->attributes('parent') );
                    $p_order = $db->Quote( $permission->attributes('ordering') );
                    
                    if(!$permission->attributes('name')) continue;
                    
                    $query = "SELECT id FROM #__pf_section_tasks"
                           . "\n WHERE section = $name AND task = $p_name";
                           $db->setQuery($query);
                           $exists = (int) $db->loadResult();
                    
                    if($exists) continue;
                    
                    $query = "INSERT INTO #__pf_section_tasks VALUES("
                           . "\n NULL, $name, $p_name, $p_title, $p_desc,"
                           . "\n $p_score, $p_flag, $p_tags, $p_parent, $p_order)";
                           $db->setQuery($query);
                           $db->query();
                           
                    if($db->getErrorMsg()) {
                        $this->SetError($db->getErrorMsg());
                        unset($object,$db);
                        return false;            
                    }
                }
            }
        }
        
        unset($object,$db);
        return true;
    }

    private function RegisterPanel($object, $update = false, $auto_enable = 0)
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();
        $list   = $this->XMLchildrenList($object);

        $name    = $db->Quote( (array_key_exists('name', $list)    ? $list['name']    : '') );
        $title   = $db->Quote( (array_key_exists('title', $list)   ? $list['title']   : '') );
        $score   = $db->Quote( (array_key_exists('score', $list)   ? $list['score']   : '') );
        $flag    = $db->Quote( (array_key_exists('flag', $list)    ? $list['flag']    : '') );
        $author  = $db->Quote( (array_key_exists('author', $list)  ? $list['author']  : '') );
        $email   = $db->Quote( (array_key_exists('email', $list)   ? $list['email']   : '') );
        $website = $db->Quote( (array_key_exists('website', $list) ? $list['website'] : '') );
        $version = $db->Quote( (array_key_exists('version', $list) ? $list['version'] : '') );
        $license = $db->Quote( (array_key_exists('license', $list) ? $list['license'] : '') );
        $date    = $db->Quote( (array_key_exists('date', $list)    ? $list['date']    : '') );
        $copy    = $db->Quote( (array_key_exists('copyright', $list) ? $list['copyright'] : '') );
        $pos     = $db->Quote( (array_key_exists('position', $list) ? $list['position'] : '') );
        $cache   = $db->Quote( (array_key_exists('cache', $list) ? $list['cache'] : '') );
        $cachet  = $db->Quote( (array_key_exists('cache_trigger', $list) ? $list['cache_trigger'] : '') );
        
        $show_title = (array_key_exists('show_title', $list) ? $list['show_title'] : '1');
        $ov_title   = (array_key_exists('override_title', $list) ? $list['override_title'] : '') ;
        $now = time();
        
        if(!$update) {
             // Find max ordering
             $query = "SELECT MAX(ordering) FROM #__pf_panels"
                    . "\n WHERE position = $pos";
                    $db->setQuery($query);
                    $order = (int) $db->loadResult();
                    
             $order ++;
             
             // Register panel
             $query = "INSERT INTO #__pf_panels VALUES("
                    . "\n NULL, $name, $title, $score, $flag, $auto_enable, $pos,"
                    . "\n '$order', $author, $email, $website, $version,"
                    . "\n $license, $copy, $date, '$now', $cache, $cachet)";
                    $db->setQuery($query);
                    $db->query();
                    
             if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db, $config);
                 return false;
             }
             
             // Set settings
             $config->Set('show_title', $show_title, $list['name']);
             $config->Set('override_title', $ov_title, $list['name']);
        }
        else {
            // Update panel
            $query = "UPDATE #__pf_panels"
                   . "\n SET author = $author, email = $email,"
                   . "\n website = $website, version = $version,"
                   . "\n license = $license, create_date = $date,"
                   . "\n caching = $cache, cache_trigger = $cachet"
                   . "\n WHERE name = $name";
                   $db->setQuery($query);
                   $db->query();
                   
            if($db->getErrorMsg()) {
                $this->SetError($db->getErrorMsg());
                unset($object,$db);
                return false;
            }
        }
        
        unset($object,$db, $config);
        return true;
    }
    
    private function RegisterProcess($object, $update = false, $auto_enable = 0)
    {
        $db   = PFdatabase::GetInstance();
        $list = $this->XMLchildrenList($object);

        $name    = $db->Quote( (array_key_exists('name', $list)    ? $list['name']    : '') );
        $title   = $db->Quote( (array_key_exists('title', $list)   ? $list['title']   : '') );
        $score   = $db->Quote( (array_key_exists('score', $list)   ? $list['score']   : '') );
        $flag    = $db->Quote( (array_key_exists('flag', $list)    ? $list['flag']    : '') );
        $author  = $db->Quote( (array_key_exists('author', $list)  ? $list['author']  : '') );
        $email   = $db->Quote( (array_key_exists('email', $list)   ? $list['email']   : '') );
        $website = $db->Quote( (array_key_exists('website', $list) ? $list['website'] : '') );
        $version = $db->Quote( (array_key_exists('version', $list) ? $list['version'] : '') );
        $license = $db->Quote( (array_key_exists('license', $list) ? $list['license'] : '') );
        $date    = $db->Quote( (array_key_exists('date', $list)    ? $list['date']    : '') );
        $copy    = $db->Quote( (array_key_exists('copyright', $list) ? $list['copyright']    : '') );
        $event   = $db->Quote( (array_key_exists('event', $list) ? $list['event']    : '') );
        $now     = time();
        
        if(!$update) {
             // Find max ordering
             $query = "SELECT MAX(ordering) FROM #__pf_processes"
                    . "\n WHERE event = $event";
                    $db->setQuery($query);
                    $order = (int) $db->loadResult();
                    
             $order ++;
             
             $query = "INSERT INTO #__pf_processes VALUES("
                    . "\n NULL, $name, $title, $score, $flag, $auto_enable, $event,"
                    . "\n '$order', $author, $email, $website, $version,"
                    . "\n $license, $copy, $date, '$now')";
                    $db->setQuery($query);
                    $db->query();
                    
             if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
             }
        }
        else {
            $query = "UPDATE #__pf_processes"
                   . "\n SET author = $author, email = $email,"
                   . "\n website = $website, version = $version,"
                   . "\n license = $license, create_date = $date"
                   . "\n WHERE name = $name";
                   $db->setQuery($query);
                   $db->query();
                   
            if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
             }
        }
        
        unset($object,$db);
        return true;
    }
    
    private function RegisterMod($object, $update = false, $auto_enable = 0)
    {
        $db   = PFdatabase::GetInstance();
        $list = $this->XMLchildrenList($object);

        $name    = $db->Quote( (array_key_exists('name', $list)    ? $list['name']    : '') );
        $title   = $db->Quote( (array_key_exists('title', $list)   ? $list['title']   : '') );
        $author  = $db->Quote( (array_key_exists('author', $list)  ? $list['author']  : '') );
        $email   = $db->Quote( (array_key_exists('email', $list)   ? $list['email']   : '') );
        $website = $db->Quote( (array_key_exists('website', $list) ? $list['website'] : '') );
        $version = $db->Quote( (array_key_exists('version', $list) ? $list['version'] : '') );
        $license = $db->Quote( (array_key_exists('license', $list) ? $list['license'] : '') );
        $date    = $db->Quote( (array_key_exists('date', $list)    ? $list['date']    : '') );
        $copy    = $db->Quote( (array_key_exists('copyright', $list) ? $list['copyright']    : '') );
        $now     = time();
        
        if(!$update) {
             $query = "INSERT INTO #__pf_mods VALUES("
                    . "\n NULL, $title, $name, $auto_enable,"
                    . "\n $author, $email, $website, $version,"
                    . "\n $license, $copy, $date, '$now')";
                    $db->setQuery($query);
                    $db->query();
                    
             if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
             }
        }
        else {
            $query = "UPDATE #__pf_mods"
                   . "\n SET author = $author, email = $email,"
                   . "\n website = $website, version = $version,"
                   . "\n license = $license, create_date = $date"
                   . "\n WHERE name = $name";
                   $db->setQuery($query);
                   $db->query();
                   
            if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
            }
        }
        
        unset($object,$db);
        return true;
    }
    
    private function RegisterLanguage($object, $update = false, $auto_enable = 0)
    {
        $db   = PFdatabase::GetInstance();
        $list = $this->XMLchildrenList($object);

        $name    = $db->Quote( (array_key_exists('name', $list)    ? $list['name']    : '') );
        $title   = $db->Quote( (array_key_exists('title', $list)   ? $list['title']   : '') );
        $author  = $db->Quote( (array_key_exists('author', $list)  ? $list['author']  : '') );
        $email   = $db->Quote( (array_key_exists('email', $list)   ? $list['email']   : '') );
        $website = $db->Quote( (array_key_exists('website', $list) ? $list['website'] : '') );
        $version = $db->Quote( (array_key_exists('version', $list) ? $list['version'] : '') );
        $license = $db->Quote( (array_key_exists('license', $list) ? $list['license'] : '') );
        $date    = $db->Quote( (array_key_exists('date', $list)    ? $list['date']    : '') );
        $copy    = $db->Quote( (array_key_exists('copyright', $list) ? $list['copyright']    : '') );
        $now     = time();
        
        if(!$update) {
             $query = "INSERT INTO #__pf_languages VALUES("
                    . "\n NULL, $name, $title, $auto_enable, 0,"
                    . "\n $author, $email, $website, $version,"
                    . "\n $license, $copy, $date, '$now')";
                    $db->setQuery($query);
                    $db->query();
                    
             if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
             }
        }
        else {
            $query = "UPDATE #__pf_languages"
                   . "\n SET author = $author, email = $email,"
                   . "\n website = $website, version = $version,"
                   . "\n license = $license, create_date = $date"
                   . "\n WHERE name = $name";
                   $db->setQuery($query);
                   $db->query();
                   
            if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
            }
        }
        
        unset($object,$db);
        return true;
    }
    
    private function RegisterTheme($object, $update = false, $auto_enable = 0)
    {
        $db   = PFdatabase::GetInstance();
        $list = $this->XMLchildrenList($object);

        $name    = $db->Quote( (array_key_exists('name', $list)    ? $list['name']    : '') );
        $title   = $db->Quote( (array_key_exists('title', $list)   ? $list['title']   : '') );
        $author  = $db->Quote( (array_key_exists('author', $list)  ? $list['author']  : '') );
        $email   = $db->Quote( (array_key_exists('email', $list)   ? $list['email']   : '') );
        $website = $db->Quote( (array_key_exists('website', $list) ? $list['website'] : '') );
        $version = $db->Quote( (array_key_exists('version', $list) ? $list['version'] : '') );
        $license = $db->Quote( (array_key_exists('license', $list) ? $list['license'] : '') );
        $date    = $db->Quote( (array_key_exists('date', $list)    ? $list['date']    : '') );
        $copy    = $db->Quote( (array_key_exists('copyright', $list) ? $list['copyright']    : '') );
        $now     = time();
        
        if(!$update) {
             $query = "INSERT INTO #__pf_themes VALUES("
                    . "\n NULL, $name, $title, $auto_enable, 0,"
                    . "\n $author, $email, $website, $version,"
                    . "\n $license, $copy, $date, '$now')";
                    $db->setQuery($query);
                    $db->query();
                    
             if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
             }
        }
        else {
            $query = "UPDATE #__pf_themes"
                   . "\n SET author = $author, email = $email,"
                   . "\n website = $website, version = $version,"
                   . "\n license = $license, create_date = $date"
                   . "\n WHERE name = $name";
                   $db->setQuery($query);
                   $db->query();
                   
            if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
            }
        }
        
        unset($object,$db);
        return true;
    }
    
    private function RegisterSystem($object, $update = false, $auto_enable = 0)
    {
        $db      = PFdatabase::GetInstance();
        $list    = $this->XMLchildrenList($object);
        $queries = array();
        
        $v_num    = $db->Quote( (array_key_exists('version', $list) ? $list['version'] : '') );
        $v_state  = $db->Quote( (array_key_exists('version_state', $list) ? $list['version_state'] : '') );

        if($v_num != "''") {
            $queries[] = "UPDATE #__pf_settings SET content = $v_num"
                       . "\n WHERE `parameter` = 'version_num'"
                       . "\n AND `scope` = 'system'";
        }
        
        if($v_state != "''") {
            $queries[] = "UPDATE #__pf_settings SET content = $v_state"
                       . "\n WHERE `parameter` = 'version_state'"
                       . "\n AND `scope` = 'system'";
        }
        
        foreach($queries AS $query)
        {
            $db->setQuery($query);
            $db->query();
            
            if($db->getErrorMsg()) {
                 $this->SetError($db->getErrorMsg());
                 unset($object,$db);
                 return false;
            }
        }
        
        unset($object,$db);
        return true;
    }
    
    private function RegisterParams($type, $extname, $params, $update = false)
    {
        $db = PFdatabase::GetInstance();
        $extname = $db->Quote($extname);
        
        foreach($params AS $param)
        {
            $name    = $db->Quote( trim($param->attributes('name')) );
            $default = $db->Quote( $param->attributes('default') );
            
            if($name == "''") continue;
            
            if($update) {
               $query = "SELECT id FROM #__pf_settings"
                      . "\n WHERE scope = $extname AND parameter = $name";
                      $db->setQuery($query);
                      $exists = (int) $db->loadResult();
                      
               if($exists) continue;       
            }
            
            $query = "INSERT INTO #__pf_settings VALUES("
                   . "\n NULL, $name, $default, $extname)";
                   $db->setQuery($query);
                   $db->query();
                 
            if($db->getErrorMsg()) {
                $this->SetError($db->getErrorMsg());
                unset($params,$param,$db);
                return false;            
            }

            unset($param);
        }
        
        unset($params,$db);
        return true;
    }
    
    private function MoveFiles($type, $xml, $xml_file, $update = false)
    {
        $com = PFcomponent::GetInstance();
        $base_path = ($type == 'system') ? $com->Get('path_root') : $com->Get('path_backend');
        unset($com);
        
        switch($type)
        {
            case 'section':  $base_folder = 'sections';  break;
            case 'panel':    $base_folder = 'panels';    break;
            case 'process':  $base_folder = 'processes'; break;
            case 'mod':      $base_folder = 'mods';      break;
            case 'language': $base_folder = 'languages'; break;
            case 'theme':    $base_folder = 'themes';    break;
        }
        
        if($type == 'system') $base_folder = '';
        
        $name = $xml->name[0]->data();
        $original_name = $name;
        $ext_folder    = $name;
        
        // Check for extension specific language package
        if($type == 'language') {
            $tmp_name = explode('.', $name);
            if(count($tmp_name) == 2) {
                $name = $tmp_name[0];
                $ext_folder = $name;
            }
        }
        
        if($type == 'system') {
            $path = $base_path;
        }
        else {
            $path = JPath::clean($base_path.DS.$base_folder.DS.$ext_folder);
        }
        
        // Create destination folder
        if(!is_dir($path) && $type != 'system') {
            if(!JFolder::create($path)) {
                $this->SetError($original_name.' - '.PFformat::Lang('MSG_INSFOLDER_FAILED'));
                unset($xml);
                return false;
            }
        }
        
        // Get mod file list?
        if($type == 'mod') {
            $db = PFdatabase::GetInstance();
            $mod_files = array();
            
            $query = "SELECT name, filepath FROM #__pf_mod_files"
                   . "\n ORDER BY name, filepath";
                   $db->setQuery($query);
                   $tmp_list = $db->loadObjectList();
                   
            if($db->getErrorMsg()) {
                $this->SetError($db->getErrorMsg());
                unset($xml,$files);
                return false;
            }
                  
            if(!is_array($tmp_list)) $tmp_list = array();
            
            foreach($tmp_list AS $item)
            {
                $mod_files[$item->filepath] = $item->name;
            }
        }
        
        // Move the files
        $files = $xml->files[0]->children();
        $file_folder = $xml->files[0]->attributes('folder');
        
        // Move files from folder
        if($file_folder) {
            $file_folder = JPath::clean($this->tmp_unpack.DS.$file_folder);
            if(!is_dir($file_folder)) {
                $this->SetError($original_name.' - '.PFformat::Lang('MSG_UNPACK_FOLDER_NOT_FOUND'));
                unset($xml,$files);
                return false;
            }

            $dirlist = array_merge(JFolder::files($file_folder, ''), JFolder::folders($file_folder, ''));
            
            // Check if a file is already in use by another mod
            if($type == 'mod') {
                $package_files = JFolder::files($file_folder, '', true, true);
                
                foreach($package_files AS $f)
                {
                    $f = str_replace($file_folder.'/', '', $f);
                    $f = str_replace('/', '.DS.', $f);

                    if(array_key_exists($f, $mod_files)) {
                        $mod_name = $db->Quote($mod_files[$f]);
                        
                        if($mod_name != $original_name && !$update) {
                            $query = "Select name FROM #__pf_mods WHERE name = $mod_name";
                                   $db->setQuery($query);
                                   $mod_title = $db->loadResult();
                               
                            $e = PFformat::Lang('MSG_MOD_FILE_EXISTS');
                            $e = str_replace('{mod}', $mod_title, $e);      
                            $e = str_replace('{file}', $f, $e);
                        
                            $this->SetError($e);
                            unset($xml,$files);
                            return false;
                        }
                    }
                    else {
                        if(substr($f, -3) != 'ini' && substr($f, -10) != 'index.html') {
                            // Register the file
                            $query = "INSERT INTO #__pf_mod_files VALUES"
                                   . "\n (NULL, ".$db->Quote($name).", ".$db->Quote($f).")";
                                   $db->setQuery($query);
                                   $db->query();
                                   
                            if($db->getErrorMsg()) {
                                $this->SetError($db->getErrorMsg());
                                unset($xml,$files);
                                return false;
                            }
                        }
                    }
                }
            }
            
            foreach($dirlist AS $item)
            {
                $src  = JPath::clean($file_folder.DS.$item);
                $dest = JPath::clean($path.DS.$item);
                
                // We have a file
                if(is_file($src)) {
                    
                    // Check if file exists
                    if(file_exists($dest)) {
                        if(!$update) {
                            $this->SetError(PFformat::Lang('MSG_FILE_EXISTS').' - '.$dest);
                            $this->DeleteFiles($type, $name);
                            unset($xml,$files);
                            return false;
                        }
                        else {
                            if(!JFile::delete($dest)) {
                                $this->SetError(PFformat::Lang('MSG_DELETE_FILE_FAILED').' - '.$dest);
                                unset($xml,$files);
                                return false;
                            }
                        }
                    }
                    
                    // Move the file
                    if(!JFile::copy($src, $dest)) {
                        $this->SetError(PFformat::Lang('MSG_FAILED_MOVE_FILE').' - '.$src);
                        if(!$update) $this->DeleteFiles($type, $name);
                        unset($xml,$files);
                        return false;
                    }
                }
                
                // We have a directory
                if(is_dir($src)) {
                    // Check if exists
                    if(is_dir($dest) && !$update) {
                        $this->SetError(PFformat::Lang('MSG_FOLDER_EXISTS').' - '.$dest);
                        $this->DeleteFiles($type, $name);
                        unset($xml,$files);
                        return false;
                    }
                    
                    if(!$update) {
                        // Move the dir
                        if(!JFolder::copy($src, $dest)) {
                            $this->SetError(PFformat::Lang('MSG_MOVE_FOLDER_FAILED').' - '.$src);
                            $this->DeleteFiles($type, $name);
                            unset($xml,$files);
                            return false;
                        }
                    }
                    else {
                        // Need to move update files individually
                        if(!is_array($files)) $files = array();
                        $new_files = JFolder::files($src, '.', true, true);
                        
                        foreach($new_files AS $fn)
                        {
                            $data   = str_replace($this->tmp_unpack.DS, '', $fn);
                            $object = new JSimpleXMLElement('file');
                            $object->setData($data);
                            $files[] = $object;
                            unset($object);
                        }
                    }
                }
            }
        }
        
        // Move individually listed files
        if(!is_array($files)) $files = array();
        $file_folder_tag = $xml->files[0]->attributes('folder');
        
        foreach($files AS $file_object)
        {
            $tag_name  = $file_object->name();
            $file_name = $file_object->data();
            
            if($tag_name != 'file') continue;
            
            $src  = JPath::clean($this->tmp_unpack.DS.$file_name);
            $dest = JPath::clean($path.DS.$file_name);
            
            // Correct path
            if($file_folder_tag) $dest = str_replace($path.DS.$file_folder_tag, $path, $dest);

            // Are we installing a mod - Check if the file is already "in use"?
            if($type == 'mod' && !$update) {
            
                $f = str_replace($path.DS, '', $dest);
                $f = str_replace(DS, '.DS.', $f);
                
                if(array_key_exists($f, $mod_files)) {
                    $mod_name = $db->Quote($mod_files[$f]);
                    
                    $query = "Select name FROM #__pf_mods WHERE name = $mod_name";
                           $db->setQuery($query);
                           $mod_title = $db->loadResult();
                           
                    $e = PFformat::Lang('MSG_MOD_FILE_EXISTS');
                    $e = str_replace('{mod}', $mod_title, $e);      
                    $e = str_replace('{file}', $dest, $e);
                    
                    $this->SetError($e);
                    $this->DeleteFiles($type, $name);
                    unset($xml,$files);
                    return false;
                }
            }
                    
            // Check if exists
            if(file_exists($dest)) {
                if(!$update) {
                    $this->SetError(PFformat::Lang('MSG_FILE_EXISTS').' - '.$dest);
                    $this->DeleteFiles($type, $name);
                    unset($xml,$files);
                    return false;
                }
                else {
                    if(!JFile::delete($dest)) {
                        $this->SetError(PFformat::Lang('MSG_DELETE_FILE_FAILED').' - '.$dest);
                        unset($xml,$files);
                        return false;
                    }
                }
            }
            
            // Check if dest folder exists
            $tmp_folder_parts = str_replace($path.DS, '', $dest);
            $tmp_folder_parts = explode(DS, $tmp_folder_parts);
            $tmp_folder_count = count($tmp_folder_parts);
            $tmp_folder_num   = 0;
            $tmp_folder_path  = $path.DS;
            
            foreach($tmp_folder_parts AS $folder_part)
            {
                $tmp_folder_num++;
                if($tmp_folder_num == $tmp_folder_count) continue;
                
                $tmp_folder_path .= $folder_part.DS;

                if(!is_dir($tmp_folder_path)) {
                    if(!JFolder::create($tmp_folder_path)) {
                        $this->SetError(PFformat::Lang('MSG_INSFOLDER_FAILED').' - '.$tmp_folder_path);
                        if(!$update) $this->DeleteFiles($type, $name);
                        unset($xml,$files);
                        return false;
                    }
                }
            }
            
            // Move the file
            if(!JFile::copy($src, $dest)) {
                $this->SetError(PFformat::Lang('MSG_FAILED_MOVE_FILE').' - '.$src);
                if(!$update) $this->DeleteFiles($type, $name);
                unset($xml,$files);
                return false;
            }
            
            // Register the mod file
            if($type == 'mod') {
                $f = str_replace($path.DS, '', $dest);
                $f = str_replace(DS, '.DS.', $f);
                if(!array_key_exists($f, $mod_files) && substr($f, -3) != 'ini' && substr($f, -10) != 'index.html') {
                    $query = "INSERT INTO #__pf_mod_files VALUES"
                           . "\n (NULL, ".$db->Quote($name).", ".$db->Quote($f).")";
                           $db->setQuery($query);
                           $db->query();
                }
            }
            
            unset($file_object);
        }
        
        // Move the xml install file
        if(!$update) {
            if(!file_exists($path.DS.$original_name.'.xml')) {
                if(!JFile::copy($xml_file, $path.DS.$original_name.'.xml')) {
                    $this->SetError(PFformat::Lang('MSG_FAILED_MOVE_FILE').' - '.$xml_file);
                    if(!$update) $this->DeleteFiles($type, $original_name);
                    unset($xml,$files);
                    return false;
                }
            }
        }
        else {
            if($type == 'system') {
                $u_xml_src  = JPath::clean($this->tmp_unpack.DS.$original_name.'.xml-update');
                $u_xml_desc = JPath::clean($path.DS.'administrator'.DS.'components'.DS.'com_projectfork'.DS.$original_name.'.xml');
            }
            else {
                $u_xml_src  = JPath::clean($this->tmp_unpack.DS.$original_name.'.xml-update');
                $u_xml_desc = JPath::clean($path.DS.$original_name.'.xml');
            }
            
            if(file_exists($u_xml_src)) {
                if(file_exists($u_xml_desc)) {
                    if(!JFile::delete($u_xml_desc)) {
                        $this->SetError(PFformat::Lang('MSG_DELETE_FILE_FAILED').' - '.$u_xml_desc);
                        unset($xml,$files);
                        return false;
                    }
                }
                
                if(!JFile::copy($u_xml_src, $u_xml_desc)) {
                    $this->SetError(PFformat::Lang('MSG_FAILED_MOVE_FILE').' - '.$u_xml_src);
                    unset($xml,$files);
                    return false;
                }
            }
        }
        
        // Install language files
        $list = $this->XMLchildrenList($xml);
        
        if(array_key_exists('languages', $list) && $type != 'language') {
            $langs = $xml->languages[0]->children();
            
            foreach($langs AS $lang)
            {
                $fname = $lang->data();
                $ltype = $lang->attributes('name');
                
                $lang_path = JPath::clean($base_path.DS.'languages'.DS.$ltype);
                $src  = JPath::clean($this->tmp_unpack.DS.$fname);
                $dest = JPath::clean($lang_path.DS.$type.'_'.$name.'.ini');
                
                if(!$ltype) continue;
                
                // Create the language folder if it does not yet exist
                if(!is_dir($lang_path)) {
                    if(!JFolder::create($lang_path)) {
                        $this->SetError(PFformat::Lang('MSG_INSFOLDER_FAILED').' - '.$lang_path);
                        unset($xml);
                        return false;
                    }
                }
                
                // Move the file
                if($update && file_exists($dest)) {
                    if(!JFile::delete($dest)) {
                        $this->SetError(PFformat::Lang('MSG_DELETE_FILE_FAILED').' - '.$dest);
                        unset($xml,$files);
                        return false;
                    }
                }
                
                if(!JFile::copy($src, $dest)) {
                    $this->SetError(PFformat::Lang('MSG_FAILED_MOVE_FILE').' - '.$src);
                    unset($xml,$files);
                    return false;
                }
            }
        }
        
        unset($xml,$files);
        return true;
    }
    
    private function LoadExtension($type, $id)
    {
        $db = PFdatabase::GetInstance();
        
        switch ($type)
		{
			case 'section':  $table = "#__pf_sections";  break;
			case 'panel':    $table = "#__pf_panels";    break;
			case 'process':  $table = "#__pf_processes"; break;
			case 'mod':      $table = "#__pf_mods";      break;		
			case 'language': $table = "#__pf_languages"; break;	
			case 'theme':    $table = "#__pf_themes";    break;	
		}
		
		$query = "SELECT * FROM $table WHERE id = '$id'";
		       $db->setQuery($query);
		       $row = $db->loadObject();
		      
		return $row;
    }
    
    private function Upload()
    {
        $from = $this->package['tmp_name'];
        $to   = $this->tmp_path.DS.$this->package['name'];
        
        $success = JFile::upload($from, $to);
        
        if(!$success) $this->SetError('MSG_UPLOAD_FAILED');
        
        return $success;
    }
    
    private function Unpack($package = NULL)
    {
		$tmpdir = uniqid('install_');
		$this->tmp_unpack = JPath::clean($this->tmp_path.DS.$tmpdir);

		$success = JArchive::extract($this->tmp_path.DS.$this->package['name'], $this->tmp_unpack);
		
		if(!$success) {
            $this->SetError('MSG_UNPACK_FAILED');
            $this->CleanTMP();
        }
        
        // Check if the package content was nested in a folder
        if($success) {
            $dirlist = array_merge(JFolder::files($this->tmp_unpack, ''), JFolder::folders($this->tmp_unpack, ''));
		    
		    // Adjust tmp unpack location
		    if (count($dirlist) == 1){
			    if (JFolder::exists($this->tmp_unpack.DS.$dirList[0])) {
				    $this->tmp_unpack = JPath::clean($this->tmp_unpack.DS.$dirList[0]);
			    }
		    }
        }
		
		return $success;
    }
    
    private function FindXML($update_list = false)
    {
        // Search update list xml
        if($update_list) {
            $upd_file = $this->tmp_unpack.DS.'update_list.xml';
        
            // Return if not found
            if(!file_exists($upd_file)) return false;
            
            $xml = JFactory::getXMLParser('simple');
            
            // Try to load the file
            if(!$xml->loadFile($upd_file)) {
			    unset($xml);
				return false;
			}
			
			// Get root element
			$root = $xml->document;
			
			// Is object?
			if(!is_object($root)) {
                unset($xml);
                return false;
            }
            
            // Is called "updates"?
            if($root->name() != 'install') {
                unset($xml,$root);
                return false;
            }
            
            unset($xml);
            return $root;
        }
        
        // Search the unpack dir for an xml file
		$files   = JFolder::files($this->tmp_unpack, '\.xml$', false, true);
        $xml_num = count($files);
        $this->num_packages = 0;
        
		if($xml_num > 0) {
		    // Loop through each file found
			foreach ($files as $file)
			{
			    $xml = JFactory::getXMLParser('simple');

                // Try to load the file
				if(!$xml->loadFile($file)) {
					unset($xml);
					continue;
				}
				// Get root element
				$root = $xml->document;
				
				// Is object?
				if(!is_object($root)) {
                    unset($xml);
                    continue;
                }
                
                // Is called "install"?
                if($root->name() != 'install') {
                    if($xml_num == 1) {
                        $this->SetError('MSG_EXT_INSTAG');
                        $this->CleanTMP();
                        return false;
                    }
                    unset($xml,$root);
                    continue;
                }
                
                // Check install type
                $type = $root->attributes('type');
                if(!in_array($type,$this->ins_types)) {
                    if($xml_num == 1) {
                        $this->SetError('MSG_EXT_NOTYPE');
                        $this->CleanTMP();
                        return false;
                    }
                    unset($xml,$root);
                    continue;
                }
                
                // Check install version
                $version = $root->attributes('version');
                $compat = explode(',', PF_VERSION_EXTINS);
                if(!in_array($version, $compat)) {
                    if($xml_num == 1) {
                        $this->SetError('MSG_EXT_NOTCOMPAT');
                        $this->CleanTMP();
                        return false;
                    }
                    unset($xml,$root);
                    continue;
                }
                
                // Check update package type
                $update = $root->attributes('update');
                if($update == 'true' || $update == '1') {
                    $update = true;
                    $uversion = $root->attributes('uversion');
                }
                else {
                    $update = false;
                    $uversion = NULL;
                }
                
                $this->xml_files[$file] = array();
                $this->xml_files[$file]['type']     = trim(strtolower($type));
                $this->xml_files[$file]['update']   = $update;
                $this->xml_files[$file]['uversion'] = $uversion;
                $this->xml_files[$file]['object']   = $root;
                
                $this->num_packages++;
                unset($xml,$root);
			}
		}
        
        if(!$xml_num) {
            $this->SetError('MSG_NO_INSTALLXML_FOUND');
            $this->CleanTMP();
            return false;
        }
        
        if($this->num_packages > 1) {
            $this->is_multipackage = true;
        }
        
        return true;
    }
    
    private function ReadXML($type, $name)
    {
        $original_name = $name;
        
        if($type == 'language') {
            $tmp_name = explode('.', $name);
            if(count($tmp_name) == 2) $name = $tmp_name[0];
        }
        
        switch($type)
        {
            case 'section':  $folder = 'sections';  break;
            case 'panel':    $folder = 'panels';    break;
            case 'process':  $folder = 'processes'; break;
            case 'theme':    $folder = 'themes';    break;
            case 'mod':      $folder = 'mods';      break;
            case 'language': $folder = 'languages'; break;
        }
        
        $com  = PFcomponent::GetInstance();
        $xml  = JFactory::getXMLParser('simple');
        $file = $com->Get('path_backend').DS.$folder.DS.$name.DS.$original_name.'.xml';
        
        if(!file_exists($file)) {
            $this->SetError('MSG_NO_INSTALLXML_FOUND');
            return false;
        }
        
        if(!$xml->loadFile($file)) {
		    $this->SetError('MSG_XML_UNREADABLE');
		    return false;
		}
		
		$root = $xml->document;
		
		if(!is_object($root)) {
            $this->SetError('MSG_XML_UNREADABLE');
            return false;
        }
        
        return $root;
    }
    
    private function CheckXML($type, $root)
    {
        $children = $root->children();
        $name  = false;
        $title = false;
        $files = false;
        $event = false;
        $pos   = false;
        
        foreach($children AS $child)
        {
            $n = $child->name();
            
            if($n == 'name')     $name  = true;
            if($n == 'title')    $title = true;
            if($n == 'files')    $files = true;
            if($n == 'event')    $event = true;
            if($n == 'position') $pos   = true;
            
            unset($child);
        }
        
        unset($root,$children);
        
        if(!$name) {
            $this->SetError('MSG_XML_NO_NAME');
            return false;
        }
        if(!$title) {
            $this->SetError('MSG_XML_NO_TITLE');
            return false;
        }
        if(!$files) {
            $this->SetError('MSG_XML_NO_FILES');
            return false;
        }
        if(!$event && $type == 'process') {
            $this->SetError('MSG_XML_NO_EVENT');
            return false;
        }
        if(!$pos && $type == 'panel') {
            $this->SetError('MSG_XML_NO_POS');
            return false;
        }
        
        return true;
    }
    
    private function CleanTMP($remove_installed = false)
    {
        $package = $this->tmp_path.DS.$this->package['name'];

        // Delete package
        if(file_exists($package)) JFile::delete($package);
        $this->package = NULL;
        
        // Delete extraction folder
        if(is_dir($this->tmp_unpack)) JFolder::delete($this->tmp_unpack);
        $this->tmp_unpack = NULL;
        
        // Uninstall previously installed extensions? - Used for multi-packages
        if($remove_installed) {
            $db = PFdatabase::GetInstance();
            
            foreach($this->installed AS $name => $type)
            {
                if($type == 'system') continue;
                
                $q_name = $db->Quote($name);
                switch ($type)
        		{
        			case 'section':  $table = "#__pf_sections";  break;
        			case 'panel':    $table = "#__pf_panels";    break;
        			case 'process':  $table = "#__pf_processes"; break;
        			case 'mod':      $table = "#__pf_mods";      break;		
        			case 'language': $table = "#__pf_languages"; break;	
        			case 'theme':    $table = "#__pf_themes";    break;	
        		}
        		
        		$query = "SELECT id FROM $table WHERE name = $q_name";
        		       $db->setQuery($query);
        		       $id = (int) $db->loadResult();
        		       
        		if($id) $this->Uninstall($type, $id);       
            }
        }
    }
    
    private function DeleteFiles($type, $name)
    {
        $com = PFcomponent::GetInstance();
        
        $base_path = $com->Get('path_backend');
        unset($com);
        
        switch($type)
        {
            case 'section':  $base_folder = 'sections';  break;
            case 'panel':    $base_folder = 'panels';    break;
            case 'process':  $base_folder = 'processes'; break;
            case 'mod':      $base_folder = 'mods';      break;
            case 'language': $base_folder = 'languages'; break;
            case 'theme':    $base_folder = 'themes';    break;
        }
        
        $lang_file = '';
        $original_name = $name;
        if($type == 'language') {
            $tmp_name = explode('.', $name);
            if(count($tmp_name) == 2) $lang_file = trim($tmp_name[1]);
            $name = $tmp_name[0];
        }
        
        $path = $base_path.DS.$base_folder.DS.$name;
        
        // Delete mod file records
        if($type == 'mod') {
            $db = PFdatabase::GetInstance();
            
            $query = "DELETE FROM #__pf_mod_files WHERE name = ".$db->Quote($name);
                   $db->setQuery($query);
                   $db->query();
                   
            if($db->getErrorMsg()) {
                $this->SetError($db->getErrorMsg());
                return false;
            }
        }
        
        // Delete install folder
        if(is_dir($path) && !$lang_file) {
            if(!JFolder::delete($path)) {
                $this->SetError('MSG_UNINS_DELDATA_FAILED');
                return false;
            }
        }
        else {
            // Delete extension specific lang package
            if($lang_file) {
                if(!JFile::delete($path.DS.$lang_file.'.ini')) {
                    $this->SetError('MSG_UNINS_DELDATA_FAILED');
                    return false;
                }
                if(!JFile::delete($path.DS.$original_name.'.xml')) {
                    $this->SetError('MSG_UNINS_DELDATA_FAILED');
                    return false;
                }
            }
            else {
                $this->SetError('MSG_UNINS_FOLDER_NOT_FOUND');
                return false;
            }
        }
        
        // Delete language files
        $langs = JFolder::folders($base_path.DS.'languages');
               
        foreach($langs AS $lang)
        {
            $file = JPath::clean($base_path.DS.'languages'.DS.$lang.DS.$type.'_'.$name.'.ini');

            if(file_exists($file)) {
                if(!JFile::delete($file)) {
                    $this->SetError('MSG_UNINS_LANG_E_DEL');
                    return false;
                }
            }
        }
        
        return true;      
    }
    
    private function SetError($msg)
    {
        $this->error = $msg;
        unset($msg);
    }
}
?>