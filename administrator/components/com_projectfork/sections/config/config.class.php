<?php
/**
* $Id: config.class.php 849 2011-01-20 13:06:23Z eaxs $
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


class PFconfigClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function Count($type, $position = '')
    {
        $db = PFdatabase::GetInstance();
    	$filter = "";
    	
    	if($position && $type == 'panels') $filter .= "\n WHERE position = ".$db->Quote($position);
    	if($position && $type == 'processes') $filter .= "\n WHERE event = ".$db->Quote($position);
    	
    	$query = "SELECT COUNT(id) FROM #__pf_$type"
    	       . $filter;
    	       $db->setQuery($query);
    	       $count = $db->loadResult();
    	        
    	if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
        
        unset($db);        
    	return $count;
    }
	
	public function LoadList($type, $limitstart, $limit, $ob, $od, $position = '')
	{
        $db = PFdatabase::GetInstance();
        $filter = "";
        $table = '#__pf_'.$type;
        
        if($position && $type == 'panels') $filter .= "\n WHERE position = ".$db->Quote($position);
        if($position && $type == 'processes') $filter .= "\n WHERE event = ".$db->Quote($position);
        
        $query = "SELECT * FROM $table"
               . $filter
    	       . "\n ORDER BY $ob $od"
    	       . (($limit > 0) ? "\n LIMIT $limitstart, $limit" : "\n");
    	       $db->setQuery($query);
    	       $rows = $db->loadObjectList();
    	       
    	if(!is_array($rows)) $rows = array();
        if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
    	
    	unset($db);
    	return $rows; 
    }
    
    public function Load($type, $id)
    {
        $db = PFdatabase::GetInstance();
        
    	$query = "SELECT * FROM #__pf_$type WHERE id = '$id'";
    	       $db->setQuery($query);
    	       $row = $db->loadObject();
    	       
    	if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
        
        unset($db);
    	return $row; 
    }
    
    public function LoadXMLParams($type, $name)
    {
        $load  = PFload::GetInstance();
        $debug = PFdebug::GetInstance();
        
        $name = strtolower($name);
        
        // Find the xml file
        switch($type)
        {
            case 'section': $file = $load->Section($name.'.xml', $name); break;
            case 'panel':   $file = $load->Panel($name.'.xml', $name);   break;
            case 'process': $file = $load->Process($name.'.xml', $name); break;
            case 'theme':   $file = $load->Theme($name.'.xml', $name);   break;
            case 'mod':     $file = $load->Mod($name.'.xml', $name);     break;
        }
        
        // File not found?
        if(!file_exists($file)) {
            $this->AddError('MSG_XML_NOT_FOUND');
            unset($load,$debug);
            return false;
        }
        
        // Init XML parser
        $xml = JFactory::getXMLParser('simple');
		
		// Try to parse the file
		if(!$xml->loadFile($file)) {
		    $this->AddError('MSG_XML_PARSE_ERROR');
		    unset($xml,$load,$debug);
			return false;
		}
		
        // Find the root element		
		$root = $xml->document;
		if (!is_object($root) || ($root->name() != "install")){
		    unset($xml,$load,$debug,$root);
			return false;
		}

		// Get root children and find params
		$children = $root->children();
		$params = NULL;
		foreach($children AS $child)
		{
            if(strtolower($child->name()) == 'params') $params = $child;
            unset($child);
        }
        
        unset($children,$xml,$root);
        
        if(!is_object($params)) {
            $debug->_('w', 'PFconfigClass::LoadXMLParams - No params found!');
            unset($params,$load,$debug);
            return false;
        }
        
        unset($load,$debug);
        return $params;
    }

    public function ReOrder($type, $order)
    {
        $db = PFdatabase::GetInstance();
        
    	switch ($type)
    	{
    		case 'section': $table = "#__pf_sections";  break;	
    		case 'panel':   $table = "#__pf_panels";    break;
    		case 'process': $table = "#__pf_processes"; break;	
    	}
    	
    	foreach ($order AS $id => $o)
    	{
    	    $id = (int) $id;
    	    $o  = (int) $o;
    	    
    		$query = "UPDATE $table SET ordering = '$o' WHERE id = '$id'";
    		       $db->setQuery($query);
    		       $db->query();
    		       
    		if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                unset($db,$order);
                return false;
            }
    	}

    	unset($db,$order);
    	return true;
    }
    
    public function Publish($type, $id, $state = 0)
    {
        $db   = PFdatabase::GetInstance();
        $user = PFuser::GetInstance();
        
    	switch ($type)
    	{
    		case 'section':  $table = "#__pf_sections";  break;	
    		case 'panel':    $table = "#__pf_panels";    break;
    		case 'process':  $table = "#__pf_processes"; break;
    		case 'mod':      $table = "#__pf_mods";      break;
    		case 'language': $table = "#__pf_languages"; break;	
    		case 'theme':    $table = "#__pf_themes";    break;	
    	}
    	
    	$field = 'enabled';
    	if($type == 'language') $field = 'published';
    	
    	$query = "UPDATE $table SET $field = '$state' WHERE id = '$id'";
    	       $db->setQuery($query);
    	       $db->query();
    	       
    	if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            unset($db,$user);
            return false;
        }
    	  
    	// Update profiles to default language/theme
    	if( ($type == 'language' || $type == 'theme') && $state == 0) {
    		$query = "SELECT name FROM $table WHERE is_default = '1'";
    		       $db->setQuery($query);
    		       $default = $db->loadResult();
    		       
    		if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                unset($db,$user);
                return false;
            }
                  
    		$query = "SELECT name FROM $table WHERE id = '$id'";
    		       $db->setQuery($query);
    		       $old = $db->loadResult();
    		      
            if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                unset($db,$user);
                return false;
            }
                   
    		$query = "UPDATE #__pf_user_profile SET content = '$default' WHERE parameter = '$type'"
    		       . "\n AND content = '$old'";
    		       $db->setQuery($query);
    		       $db->query();
    		       
    		if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                unset($db,$user);
                return false;
            }

    		if($user->GetProfile($type) == $old) {
    			$user->SetProfile($type, $default);
    		}
    	}
    	
    	return true;       
    }
    
    public function SetDefault($type, $id)
    {
        $db = PFdatabase::GetInstance();
        
    	switch ($type)
    	{
    		case 'section':  $table = "#__pf_sections"; break;
    		case 'language': $table = "#__pf_languages"; break;	
    		case 'theme':    $table = "#__pf_themes"; break;
    	}
    	
    	$query = "UPDATE $table SET is_default = '0'";
    	       $db->setQuery($query);
    	       $db->query();
    	
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            unset($db);
            return false;
        }
                   
    	$query = "UPDATE $table SET is_default = '1' WHERE id = '$id'";
    	       $db->setQuery($query);
    	       $db->query();
    	       
    	if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            unset($db);
            return false;
        }
    	
    	return true;
    }
    
    public function UpdateSection($id)
    {
        $db = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();
        
    	$params = JRequest::getVar('params', array(), 'array');
    	$row    = $this->Load('sections', $id);
    	$publish= $db->Quote((int) JRequest::getVar('published'));
    	$score  = $db->Quote((int) JRequest::getVar('score'));
    	$flag   = $db->Quote(JRequest::getVar('flag'));
    	$tags   = $db->Quote(JRequest::getVar('tags'));
    	
    	// Dont allow config to be disabled
    	if($id == 9) $publish = $db->Quote("1");
    	
    	$query = "UPDATE #__pf_sections SET enabled = $publish, score = $score, flag = $flag, tags = $tags"
    	       . "\n WHERE id = '$id'";
    	       $db->setQuery($query);
    	       $db->query();
   	 
   	    if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;   
        }
        
    	foreach ($params AS $name => $value)
    	{
    		$config->Set($name, $value, $row->name);
    	}
    	
    	// Update permissions
    	$permissions = JRequest::getVar('permission', array(), 'array');
    	
    	foreach ($permissions AS $row)
    	{
    		
    		$id       = $db->Quote((int) $row['id']);
    		$score    = $db->Quote((int) $row['score']);
    		$ordering = $db->Quote((int) $row['ordering']);
    		$flag     = $db->Quote($row['flag']);
    		$tags     = $db->Quote($row['tags']);
    		
    		$query = "UPDATE #__pf_section_tasks SET score = $score, ordering = $ordering, flag = $flag, tags = $tags WHERE id = $id";
    		       $db->setQuery($query);
    		       $db->query();
     
    		if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }       
    	}

    	return true;
    }
    
    public function UpdatePanel($id)
    {
    	$params = JRequest::getVar('params', array(), 'post', 'array');
    	$row    = $this->Load('panels', $id);
    	
    	$db     = PFdatabase::GetInstance();
    	$config = PFconfig::GetInstance();
    	
    	$publish= $db->Quote((int) JRequest::getVar('published'));
    	$score  = $db->Quote((int) JRequest::getVar('score'));
    	$cache  = $db->Quote((int) JRequest::getVar('cache'));
    	$flag   = $db->Quote(JRequest::getVar('flag'));
    	$pos    = $db->Quote(JRequest::getVar('pos'));

    	$params['show_title']     = (int)JRequest::getVar('show_title');
    	$params['override_title'] = JRequest::getVar('override_title');
    	
    	$query = "UPDATE #__pf_panels SET enabled = $publish, score = $score,"
               . "\n flag = $flag, position = $pos, caching = $cache"
    	       . "\n WHERE id = '$id'";
    	       $db->setQuery($query);
    	       $db->query();
    	       
    	if($db->getErrorMsg()) {
	        $this->AddError($db->getErrorMsg());
	        return false;
	    }
    	       
    	foreach ($params AS $name => $value)
    	{
    		$config->Set($name, $value, $row->name);
    	}
    	
    	// Clean theme cache
    	$cache = &JFactory::getCache('com_projectfork.theme');
    	$cache->clean('com_projectfork.theme');
    	
    	return true;
    }

    public function UpdateTheme($id)
    {
        $config = PFconfig::GetInstance();
    	$params = JRequest::getVar('params', array(), 'array');
    	$row    = $this->Load('themes', $id);

    	foreach ($params AS $name => $value)
    	{
    		$config->Set($name, $value, "theme_".$row->name);
    	}

    	return true;
    }
    
    public function UpdateMod($id)
    {
        $config = PFconfig::GetInstance();
    	$params = JRequest::getVar('params', array(), 'array');
    	$row    = $this->Load('mods', $id);

    	foreach ($params AS $name => $value)
    	{
    		$config->Set($name, $value, $row->name);
    	}

    	return true;
    }
    
    public function UpdateProcess($id)
    {
        $db = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();
        
    	$params = JRequest::getVar('params', array(), 'array');
    	$row    = $this->Load('processes', $id);
    	$publish= $db->Quote((int) JRequest::getVar('published'));
    	$score  = $db->Quote((int) JRequest::getVar('score'));
    	$flag   = $db->Quote(JRequest::getVar('flag'));
    	$pos    = $db->Quote(JRequest::getVar('pos'));
    	
    	$query = "UPDATE #__pf_processes SET enabled = $publish, score = $score, flag = $flag, event = $pos"
    	       . "\n WHERE id = '$id'";
    	       $db->setQuery($query);
    	       $db->query();
    	       
    	if($db->getErrorMsg()) {
	        $this->AddError($db->getErrorMsg());
	        return false;
	    }       
    	       
        // Update params
    	foreach ($params AS $name => $value)
    	{
    		$config->Set($name, $value, $row->name);
    	}
    	
    	return true;
    }
}
?>
