<?php
/**
* $Id: filemanager.sef.php 859 2011-02-28 07:48:31Z angek $
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


function PFfilemanagerBuildRoute(&$query)
{
    static $cached_ws = array();
    static $cached_folders = array();
    static $cached_notes   = array();
    static $cached_files   = array();
    $segments = array();
    $task     = NULL;
    
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
    
    if(isset($query['dir'])) 
    {
        $dir = (int) $query['dir'];
        if(!array_key_exists($dir, $cached_folders)) {
            $db = JFactory::getDBO();
            $q = "SELECT title FROM #__pf_folders WHERE id = '$dir'";
                   $db->setQuery($q);
                   $title = $db->loadResult();
           
            if(!$title) $title = 'root';
            $cached_folders[$dir] = JFilterOutput::stringURLSafe($title);
            unset($db);
        }
        
        $segments[] = $query['dir'].':'.$cached_folders[$dir];
        unset($query['dir']);
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
            case 'form_edit_folder':
                if(!array_key_exists($id, $cached_folders)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT title FROM #__pf_folders WHERE id = '$id'";
                           $db->setQuery($q);
                           $title = $db->loadResult();
                   
                    $cached_folders[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_folders[$id];
                unset($query['id']);
                break;
                
            case 'form_edit_note':
            case 'display_note':
                if(!array_key_exists($id, $cached_notes)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT title FROM #__pf_notes WHERE id = '$id'";
                           $db->setQuery($q);
                           $title = $db->loadResult();
                   
                    $cached_notes[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_notes[$id];
                unset($query['id']);
                break;
                
            case 'task_download':
            case 'form_edit_file':
                if(!array_key_exists($id, $cached_files)) {
                    $db = JFactory::getDBO();
                    $q = "SELECT name FROM #__pf_files WHERE id = '$id'";
                           $db->setQuery($q);
                           $title = $db->loadResult();
                   
                    $cached_files[$id] = JFilterOutput::stringURLSafe($title);
                    unset($db);
                }
                
                $segments[] = $query['id'].':'.$cached_files[$id];
                unset($query['id']);
                break;
        }
    }
        
    return $segments;
}

function PFfilemanagerParseRoute($segments, $task = NULL)
{
    $vars  = array();
    $count = count($segments);

    if($count >= 1) {
        $ws = explode(':', $segments[0]);
        $vars['workspace'] = (int) $ws[0];
    }
    
    if($count > 1)  $vars['section'] = $segments[1];
    
    if ($count > 2) {
        $uid = explode(':', $segments[2]);
      if (count($uid) > 1) {
         $vars['dir'] = $segments[2];
      }
      else {
         $vars['task'] = $segments[2];
      }
   }
    
    if($count > 3)  $vars['task']    = $segments[3];
    
    if($count > 4) {
        $uid = explode(':', $segments[4]);
        $vars['id'] = (int) $uid[0];
    }
    
    return $vars;
}
?>