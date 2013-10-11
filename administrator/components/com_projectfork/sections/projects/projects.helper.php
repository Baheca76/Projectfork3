<?php
/**
* $Id: projects.helper.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Projects
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

class PFprojectsHelper
{
    public function SelectStatus($name, $preselect = NULL, $params = '')
	{
		$user = PFuser::GetInstance();
		$form = new PFform();
		$rows = array();
		
		$rows['0'] = PFformat::Lang('STATUS_ACTIVE');
		if($user->Access('task_activate', 'projects')) $rows['1'] = PFformat::Lang('STATUS_ARCHIVATED');
	    if($user->Access('task_approve', 'projects'))  $rows['2'] = PFformat::Lang('STATUS_UNAPROVED');

        unset($user);
        return $form->SelectList($name, $rows, $preselect, $params);
	}
	
	public function SelectCategory($name, $preselect = NULL, $params = '')
	{
        $config = PFconfig::GetInstance();
        $form   = new PFform();
        
        $tcats    = trim($config->Get('cats', 'projects'));
        $tmp_cats = $config->Get('cats', 'projects');
        
        $cats     = array();
        $cats[''] = PFformat::Lang('SELECT_CATEGORY');
        
        if(!$config->Get('use_cats', 'projects')) return "";
        if(!$tcats) return "";
        
        $tmp_cats = explode("\n", $tmp_cats);
        
        foreach($tmp_cats AS $cat)
        {
            $cat  = trim($cat);
            $ecat = explode(':', $cat);

            $cname = trim(htmlspecialchars($ecat[0], ENT_QUOTES));
            $cnamel = JFilterOutput::stringURLSafe($cname);
            $cats[$cnamel] = $cname;
        }
        
        return $form->SelectList($name, $cats, $preselect, $params);
    }
    
    public function SelectProjectAuthor($id, $name, $preselect = NULL, $params = '')
    {
        $db     = PFdatabase::GetInstance();
        $user   = PFdatabase::GetInstance();
        $form   = new PFform();
        $users  = array();
        
        // Get member list
        $query = "SELECT u.id, u.username, u.name FROM #__users AS u"
               . "\n RIGHT JOIN #__pf_project_members AS pm"
               . "\n ON (pm.user_id = u.id AND pm.approved = '1' AND pm.project_id = '$id')"
               . "\n WHERE u.block = '0'"
               . "\n ORDER BY u.name, u.username ASC";
               $db->setQuery($query);
               $mlist = $db->loadObjectList();
               
        if(!is_array($mlist)) $mlist = array();
        
        // Get author
        $query = "SELECT author FROM #__pf_projects"
               . "\n WHERE id = '$id'";
               $db->setQuery($query);
               $aid = $db->loadResult();
               
        $query = "SELECT username, name FROM #__users"
               . "\n WHERE id = '$aid'";
               $db->setQuery($query);
               $author = $db->loadObject();
               
        if(!is_object($author)) {
            $author = new stdclass();
            $author->name = '';
            $author->username = '';
        } 
        
        $author->id = $aid;
        $users[$author->id] =  htmlspecialchars($author->name.' ('.$author->username.')');
        
        foreach($mlist AS $u)
        {
            if(!$u->id) continue;
            
            $users[$u->id] = htmlspecialchars($u->name.' ('.$u->username.')');
        }
        
        return $form->SelectList($name, $users, $preselect, $params);
    }
}
?>