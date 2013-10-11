<?php
/**
* @version		$Id: router.php 13420 2009-11-04 02:17:02Z ian $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

function ProjectforkBuildRoute(&$query)
{
    static $v_sections    = NULL;
    static $base_path     = NULL;
    static $sef_files     = array();
    static $loaded_files  = array();
    static $missing_files = array();
    
	$segments = array();
	$default  = false;
	
	// Get valid sections
	if(is_null($v_sections)) {
        $db = JFactory::getDBO();
        
        $q = "SELECT name FROM #__pf_sections";
           $db->setQuery($q);
           $v_sections = $db->loadResultArray();
               
        if(!is_array($v_sections)) $v_sections = array();
        unset($db);       
    }
    
    // Setup base include path
    if(is_null($base_path)) {
        $base_path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'sections';
    }
    
    // Get a menu item based on Itemid or currently active
	$menu = &JSite::getMenu();
	if (empty($query['Itemid'])) {
		$menuItem = &$menu->getActive();
	} else {
		$menuItem = &$menu->getItem($query['Itemid']);
	}
    
    // Try to include section specific SEF file
    if(isset($query['section'])) {
        if(in_array($query['section'], $v_sections)) {
            if(in_array($query['section'], $loaded_files)) {
                $func_name = 'PF'.$query['section'].'BuildRoute';
                $segments = $func_name($query);
            }
            else {
                if(in_array($query['section'], $missing_files)) {
                    $default = true;
                }
                else {
                    $sef_file = $base_path.DS.$query['section'].DS.$query['section'].'.sef.php';
                    
                    if(file_exists($sef_file)) {
                        $loaded_files[] = $query['section'];
                        require_once($sef_file);
                        $func_name = 'PF'.$query['section'].'BuildRoute';
                        $segments = $func_name($query);
                    }
                    else {
                        $missing_files[] = $query['section'];
                        $default = true;
                    }
                }
            }
        }
        else {
            $default = true;
        }
    }
    else {
        $default = true;
    }
    
    // Section SEF file not found, perform basic procedure
    if($default) {
        if(isset($query['workspace'])) 
        {
            $segments[] = $query['workspace'];
            unset($query['workspace']);
        }
        
        if(isset($query['section'])) 
        {
            $segments[] = $query['section'];
            unset($query['section']);
        }
        
        if(isset($query['task'])) 
        {
            $segments[] = $query['task'];
            unset($query['task']);
        }
        
        if(isset($query['id'])) 
        {
            $segments[] = $query['id'];
            unset($query['id']);
        }
    }
    
	return $segments;
}

function ProjectforkParseRoute($segments)
{
    static $v_sections    = NULL;
    static $base_path     = NULL;
    static $sef_files     = array();
    static $loaded_files  = array();
    static $missing_files = array();
    
	$vars    = array();
    $default = false;
	
	// Get valid sections
	if(is_null($v_sections)) {
        $db = JFactory::getDBO();
        
        $query = "SELECT name FROM #__pf_sections";
               $db->setQuery($query);
               $v_sections = $db->loadResultArray();
               
        if(!is_array($v_sections)) $v_sections = array();
        unset($db);       
    }
    
    // Setup base include path
    if(is_null($base_path)) {
        $base_path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'sections';
    }
    
	// Get the active menu item
	$menu =& JSite::getMenu();
	$item =& $menu->getActive();

	// Count route segments
	$count = count($segments);
	
	// Get section
	$section = ($count > 1) ? $segments[1] : NULL;
	$task    = ($count > 2) ? $segments[2] : NULL;
	
	// Try to include section specific SEF file
    if($section) {
        if(in_array($section, $v_sections)) {
            if(in_array($section, $loaded_files)) {
                $func_name = 'PF'.$section.'ParseRoute';
                $vars = $func_name($segments, $task);
            }
            else {
                if(in_array($section, $missing_files)) {
                    $default = true;
                }
                else {
                    $sef_file = $base_path.DS.$section.DS.$section.'.sef.php';
                    
                    if(file_exists($sef_file)) {
                        $loaded_files[] = $section;
                        require_once($sef_file);
                        $func_name = 'PF'.$section.'ParseRoute';
                        $vars = $func_name($segments, $task);
                    }
                    else {
                        $missing_files[] = $section;
                        $default = true;
                    }
                }
            }
        }
        else {
            $default = true;
        }
    }
    else {
        $default = true;
    }
    
	// Section SEF file not found, perform basic procedure
    if($default) {
        if($count >= 1) $vars['workspace'] = $segments[0];
	    if($count > 1)  $vars['section']   = $segments[1];
	    if($count > 2)  $vars['task']      = $segments[2];
	    if($count > 3)  $vars['id']        = $segments[3];
    }

	return $vars;
}
