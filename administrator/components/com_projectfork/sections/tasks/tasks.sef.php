<?php
/**
* $Id: tasks.sef.php 919 2011-09-21 16:47:47Z eaxs $
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


function PFtasksBuildRoute(&$query)
{
    static $cached_ws = array();
    static $cached_t  = array();
    static $cached_m  = array();
    $segments = array();
    $task = NULL;
    $tid  = 0;
    
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
        $t   = (int) $query['id'];
        $tid = $t;
        
        if($task) {
            switch($task)
            {
                case 'form_edit_milestone':
                    if(!array_key_exists($t, $cached_m)) {
                        $db = JFactory::getDBO();
                        $q = "SELECT title FROM #__pf_milestones WHERE id = '$t'";
                               $db->setQuery($q);
                               $tname = $db->loadResult();
                       
                        $cached_m[$t] = JFilterOutput::stringURLSafe($tname);
                        unset($db);
                    }
                    
                    $segments[] = $query['id'].':'.$cached_m[$t];
                    unset($query['id']);
                    break;
                    
                case 'task_update_progress':
                    if(!array_key_exists($t, $cached_t)) {
                        $db = JFactory::getDBO();
                        $q = "SELECT title FROM #__pf_tasks WHERE id = '$t'";
                               $db->setQuery($q);
                               $tname = $db->loadResult();
                       
                        $cached_t[$t] = JFilterOutput::stringURLSafe($tname);
                        unset($db);
                    }
                    
                    $segments[] = $query['id'].':'.$cached_t[$t];
                    unset($query['id']);
                    break;    
                    
                default:
                    if(!array_key_exists($t, $cached_t)) {
                        $db = JFactory::getDBO();
                        $q = "SELECT title FROM #__pf_tasks WHERE id = '$t'";
                               $db->setQuery($q);
                               $tname = $db->loadResult();
                       
                        $cached_t[$t] = JFilterOutput::stringURLSafe($tname);
                        unset($db);
                    }
                    
                    $segments[] = $query['id'].':'.$cached_t[$t];
                    unset($query['id']);
                    break;
            }
        }
    }
    
    if(isset($query['progress']) && $tid) {
        $segments[] = $query['progress'][$tid];
        unset($query['progress']);
    } 
  
    return $segments;
}

function PFtasksParseRoute($segments, $task = NULL)
{
    $vars  = array();
    $count = count($segments);
	$vars['task'] = null;
    if($count >= 1) {
        $ws = explode(':', $segments[0]);
        $vars['workspace'] = (int) $ws[0];
    }
    
    if($count > 1)  $vars['section'] = $segments[1];
    if($count > 2)  $vars['task']    = $segments[2];
    if($count > 3)  $vars['id']      = $segments[3];

    if(isset($vars['task'])) {
        if($vars['task'] == 'task_update_progress') {
             $id = explode(':', $vars['id']);
             $id = (int) $id[0];
             $vars['progress'][$id] = $segments[4];
        }
    }
    
    return $vars;
}
?>