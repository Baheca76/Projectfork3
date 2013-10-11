<?php
/**
* $Id: projects.sef.php 924 2012-06-16 13:21:06Z eaxs $
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


function PFprojectsBuildRoute(&$query)
{
    static $cached_ws = array();
    static $cached_p  = array();
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
        $p = (int) $query['id'];

        if(!array_key_exists($p, $cached_p)) {
            $db = JFactory::getDBO();
            $q = "SELECT title FROM #__pf_projects WHERE id = '$p'";
                   $db->setQuery($q);
                   $pname = $db->loadResult();

            $cached_p[$p] = JFilterOutput::stringURLSafe($pname);
            unset($db);
        }

        $segments[] = $query['id'].':'.$cached_p[$p];
        unset($query['id']);
    }

    if(isset($query['cid']))
    {
        $p = (int) $query['cid'][0];

        if(!array_key_exists($p, $cached_p)) {
            $db = JFactory::getDBO();
            $q = "SELECT title FROM #__pf_projects WHERE id = '$p'";
                   $db->setQuery($q);
                   $pname = $db->loadResult();

            $cached_p[$p] = JFilterOutput::stringURLSafe($pname);
            unset($db);
        }

        $segments[] = $query['cid'][0].':'.$cached_p[$p];
        unset($query['cid']);
    }

    //if(isset($query['limit'])) unset($query['limit']);
    //if(isset($query['start'])) unset($query['start']);

    return $segments;
}

function PFprojectsParseRoute($segments, $task = NULL)
{
    $vars  = array();
    $count = count($segments);

    if($count >= 1) {
        $ws = explode(':', $segments[0]);
        $vars['workspace'] = (int) $ws[0];
    }

    if($count > 3) {
        $vars['section'] = $segments[1];
        $vars['task']    = $segments[2];

        switch($vars['task'])
        {
            case 'task_archive':
            case 'task_activate':
                $p = explode(':', $segments[3]);
                $vars['cid'] = array();
                $vars['cid'][0] = (int) $p[0];
                break;

            default:
                $p = explode(':', $segments[3]);
                $vars['id'] = (int) $p[0];
                break;
        }
    }
    else {
        if($count > 1)  $vars['section'] = $segments[1];
        if($count > 2)  $vars['task']    = $segments[2];
        if($count > 3)  $vars['id']      = $segments[3];
    }


    return $vars;
}
?>