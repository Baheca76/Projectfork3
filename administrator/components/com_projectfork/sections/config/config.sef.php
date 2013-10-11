<?php
/**
* $Id: config.sef.php 860 2011-02-28 22:24:00Z angek $
* @package   Projectfork
* @copyright Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License,
* or any later version.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public License
* along with Projectfork.  If not, see <http://www.gnu.org/licenses/lgpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );


function PFconfigBuildRoute(&$query)
{
    static $cached_ws = array();
    static $cached_sections  = array();
    static $cached_panels    = array();
    static $cached_mods      = array();
    static $cached_processes = array();
    static $cached_themes    = array();
    static $cached_langs     = array();
    $segments = array();
    $task = NULL;
    
    // Get workspace name
    if(isset($query['workspace'])) 
    {
        $ws = (int) $query['workspace'];
        if(!array_key_exists($ws, $cached_ws)) {
            $db = JFactory::getDBO();
            $q = "SELECT title FROM #__pf_projects WHERE id = '$ws'";
                   $db->setQuery($q);
                   $pname = $db->loadResult();
           
            $cached_ws[$ws] = JFilterOutput::stringURLSafe($pname);
            unset($db);
        }
        $segments[] = $ws.':'.$cached_ws[$ws];
        unset($query['workspace']);
    }
    else {
        $segments[] = '0:global';
    }
    
    if(isset($query['section'])) 
    {
        $segments[] = $query['section'];
        unset($query['section']);
    }
    
    if(isset($query['task']))
    {
        $segments[] = $query['task'];
        $task = $query['task'];
        unset($query['task']);
    }
    
    if(isset($query['id']))
    {
        $id = (int) $query['id'];
        
        switch($task)
        {
            case 'form_edit_section':
            case 'task_default_section':
                if(!array_key_exists($id, $cached_sections)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT name FROM #__pf_sections WHERE id = '$id'";
                       $db->setQuery($q);
                       $title = $db->loadResult();
                   
                    $cached_sections[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_sections[$id];
                unset($query['id']);
                break;
                
            case 'form_edit_panel':
                if(!array_key_exists($id, $cached_panels)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT name FROM #__pf_panels WHERE id = '$id'";
                       $db->setQuery($q);
                       $title = $db->loadResult();
                   
                    $cached_panels[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_panels[$id];
                unset($query['id']);
                break;
                
            case 'form_edit_process':
                if(!array_key_exists($id, $cached_processes)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT name FROM #__pf_processes WHERE id = '$id'";
                       $db->setQuery($q);
                       $title = $db->loadResult();
                   
                    $cached_processes[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_processes[$id];
                unset($query['id']);
                break;
                
            case 'form_edit_mod':
                if(!array_key_exists($id, $cached_mods)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT name FROM #__pf_mods WHERE id = '$id'";
                       $db->setQuery($q);
                       $title = $db->loadResult();
                   
                    $cached_mods[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_mods[$id];
                unset($query['id']);
                break;
                
            case 'form_edit_theme':
            case 'task_default_theme':
                if(!array_key_exists($id, $cached_themes)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT name FROM #__pf_themes WHERE id = '$id'";
                       $db->setQuery($q);
                       $title = $db->loadResult();
                   
                    $cached_themes[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_themes[$id];
                unset($query['id']);
                break;
                
            case 'task_default_language':
                if(!array_key_exists($id, $cached_langs)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT name FROM #__pf_languages WHERE id = '$id'";
                       $db->setQuery($q);
                       $title = $db->loadResult();
                   
                    $cached_langs[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_langs[$id];
                unset($query['id']);
                break;
                
            case 'task_publish':
            case 'task_unpublish':
                if(isset($query['type'])) {
                    $type = $query['type'];
                    
                    switch($type)
                    {
						case 'language':
							if(!array_key_exists($id, $cached_langs)) {
                                $db = JFactory::getDBO();
                                $q = "SELECT name FROM #__pf_languages WHERE id = '$id'";
                                   $db->setQuery($q);
                                   $title = $db->loadResult();
                               
                                $cached_langs[$id] = JFilterOutput::stringURLSafe($title);
                                unset($db);
                            }
                            
                            $segments[] = $query['id'].':'.$cached_langs[$id];
							unset($query['id']);
                            break;
							
                        case 'section':
                            if(!array_key_exists($id, $cached_sections)) {
                                $db = JFactory::getDBO();
                                $q = "SELECT name FROM #__pf_sections WHERE id = '$id'";
                                   $db->setQuery($q);
                                   $title = $db->loadResult();
                               
                                $cached_sections[$id] = JFilterOutput::stringURLSafe($title);
                                unset($db);
                            }
                            
                            $segments[] = $query['id'].':'.$cached_sections[$id];
                            unset($query['id']);
                            break;
                            
                        case 'panel':
                            if(!array_key_exists($id, $cached_panels)) {
                                $db = JFactory::getDBO();
                                $q = "SELECT name FROM #__pf_panels WHERE id = '$id'";
                                   $db->setQuery($q);
                                   $title = $db->loadResult();
                               
                                $cached_panels[$id] = JFilterOutput::stringURLSafe($title);
                                unset($db);
                            }
                            
                            $segments[] = $query['id'].':'.$cached_panels[$id];
                            unset($query['id']);
                            break;
                            
                        case 'process':
                            if(!array_key_exists($id, $cached_processes)) {
                                $db = JFactory::getDBO();
                                $q = "SELECT name FROM #__pf_processes WHERE id = '$id'";
                                   $db->setQuery($q);
                                   $title = $db->loadResult();
                               
                                $cached_processes[$id] = JFilterOutput::stringURLSafe($title);
                                unset($db);
                            }
                            
                            $segments[] = $query['id'].':'.$cached_processes[$id];
                            unset($query['id']);
                            break;
                            
                        case 'mod':
                            if(!array_key_exists($id, $cached_mods)) {
                                $db = JFactory::getDBO();
                                $q = "SELECT name FROM #__pf_mods WHERE id = '$id'";
                                   $db->setQuery($q);
                                   $title = $db->loadResult();
                               
                                $cached_mods[$id] = JFilterOutput::stringURLSafe($title);
                                unset($db);
                            }
                            
                            $segments[] = $query['id'].':'.$cached_mods[$id];
                            unset($query['id']);
                            break;
                            
                        case 'theme':
                            if(!array_key_exists($id, $cached_themes)) {
                                $db = JFactory::getDBO();
                                $q = "SELECT name FROM #__pf_themes WHERE id = '$id'";
                                   $db->setQuery($q);
                                   $title = $db->loadResult();
                               
                                $cached_themes[$id] = JFilterOutput::stringURLSafe($title);
                                unset($db);
                            }
                            
                            $segments[] = $query['id'].':'.$cached_themes[$id];
                            unset($query['id']);
                            break;
                    }
                    
                    $segments[] = $query['type'];
                    unset($query['type']);
                }
                break;
        }
    }
    
    if(isset($query['rts'])) 
    {
        $segments[] = $query['rts'];
        unset($query['rts']);
    }
        
    return $segments;
}

function PFconfigParseRoute($segments, $task = NULL)
{
    $vars  = array();
    $count = count($segments);

    if($count >= 1) {
        $ws = explode(':', $segments[0]);
        $vars['workspace'] = (int) $ws[0];
    }
    
    if($count > 1)  $vars['section'] = $segments[1];
    if($count > 2)  $vars['task']    = $segments[2];
    if($count > 3) {
        $uid = explode(':', $segments[3]);
        $vars['id'] = (int) $uid[0];
    }
    if($count > 4) {
        switch($vars['task'])
        {
            case 'task_publish':
            case 'task_unpublish':
                $vars['type'] = $segments[4];
                break;
                
            case 'task_edit_section':    
            case 'task_edit_panel':
                $vars['rts'] = (int) $segments[4];
                break;
        }
    }
    
    return $vars;
}
?>