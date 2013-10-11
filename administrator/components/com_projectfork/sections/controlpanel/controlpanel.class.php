<?php
/**
* $Id: controlpanel.class.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Controlpanel
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

class PFcontrolpanelClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function CountTasks($project)
    {
        $db = PFdatabase::GetInstance();
        
        $query = "SELECT COUNT(id) FROM #__pf_tasks"
               . "\n WHERE project = '$project'";
               $db->setQuery($query);
               $count = (int) $db->loadResult();
               
        return $count;       
    }
    
    public function CountMilestones($project)
    {
        $db = PFdatabase::GetInstance();
        
        $query = "SELECT COUNT(id) FROM #__pf_milestones"
               . "\n WHERE project = '$project'";
               $db->setQuery($query);
               $count = (int) $db->loadResult();
               
        return $count;
    }
    
    public function CountUsers($project)
    {
        $db = PFdatabase::GetInstance();
        
        $query = "SELECT COUNT(id) FROM #__pf_project_members"
               . "\n WHERE project_id = '$project'";
               $db->setQuery($query);
               $count = (int) $db->loadResult();
               
        return $count;
    }
}
?>