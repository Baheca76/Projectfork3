<?php
/**
* $Id: calendar.sef.php 838 2010-11-25 20:49:32Z eaxs $
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


function PFcalendarBuildRoute(&$query)
{
    static $cached_ws = array();
    static $cached_e  = array();
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
        $e = (int) $query['id'];
        
        if(!array_key_exists($e, $cached_e)) {
            $db = JFactory::getDBO();
            $q = "SELECT title FROM #__pf_events WHERE id = '$e'";
                   $db->setQuery($q);
                   $ename = $db->loadResult();
           
            $cached_e[$e] = JFilterOutput::stringURLSafe($ename);
            unset($db);
        }
        
        $segments[] = $query['id'].':'.$cached_e[$e];
        unset($query['id']);
    }
    
    if(isset($query['year']))
    {
        $segments[] = $query['year'];
        unset($query['year']);
    }
    
    if(isset($query['month']))
    {
        $segments[] = $query['month'];
        unset($query['month']);
    }
    
    if(isset($query['day']))
    {
        $segments[] = $query['day'];
        unset($query['day']);
    }
       
    return $segments;
}

function PFcalendarParseRoute($segments, $task = NULL)
{
    $vars  = array();
    $count = count($segments);

    if($count >= 1) {
        $ws = explode(':', $segments[0]);
        $vars['workspace'] = (int) $ws[0];
    }
    
    if($count > 1) $vars['section'] = $segments[1];
    if($count > 2) $vars['task']    = $segments[2];
    
    if($count == 5 || $count == 6) {
        $vars['year']  = $segments[3];
        $vars['month'] = $segments[4];
        $vars['day']   = $segments[5];
    }
    
    if($count > 6) {
        $p = explode(':', $segments[3]);
        $vars['id'] = (int) $p[0];
        $vars['year']  = $segments[4];
        $vars['month'] = $segments[5];
        $vars['day']   = $segments[6];
    }
    
    return $vars;
}
?>