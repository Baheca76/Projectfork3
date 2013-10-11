<?php
/**
* $Id: groups.sef.php 837 2010-11-17 12:03:35Z eaxs $
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


function PFgroupsBuildRoute(&$query)
{
    static $cached_ws = array();
    static $cached_u  = array();
    $segments = array();
    
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
        unset($query['task']);
    }
    
    if(isset($query['id']))
    {
        $u = (int) $query['id'];
        
        if(!array_key_exists($u, $cached_u)) {
            $db = JFactory::getDBO();
            $q = "SELECT title FROM #__pf_groups WHERE id = '$u'";
                   $db->setQuery($q);
                   $uname = $db->loadResult();
           
            $cached_u[$u] = JFilterOutput::stringURLSafe($uname);
            unset($db);
        }
        
        $segments[] = $query['id'].':'.$cached_u[$u];
        unset($query['id']);
    }
        
    return $segments;
}

function PFgroupsParseRoute($segments, $task = NULL)
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
    
    return $vars;
}
?>