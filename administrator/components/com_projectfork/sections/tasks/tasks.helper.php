<?php
/**
* $Id: tasks.helper.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Tasks
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

class PFtasksHelper
{
    public function SelectUserFilter($name, $preselect = NULL, $params = '')
    {
        $user = PFuser::GetInstance();
        $ws   = $user->GetWorkspace();
        $rows = array('1' => PFformat::Lang('EVERYONE'), '2' => PFformat::Lang('ME'));
        
        if($ws) {
            $rows[0] = "=========";
            $db = PFdatabase::GetInstance();
            $query = "SELECT u.id,u.name,u.username FROM #__users AS u"
                   . "\n RIGHT JOIN #__pf_project_members AS m ON m.project_id = '$ws'"
                   . "\n AND m.user_id = u.id"
                   . "\n GROUP BY u.id"
                   . "\n ORDER BY u.name,u.username ASC";
                   $db->setQuery($query);
                   $tmp_rows = $db->loadObjectList();
                   
            foreach($tmp_rows AS $row)
            {
                JFilterOutput::objectHTMLSafe($row);
                $id = $row->id;
                if(!$id) continue;
                $rows[$id] = $row->name." (".$row->username.")";
            }
        }
        
        $form = new PFform();
		return $form->Selectlist($name, $rows, $preselect, $params);
    }
    
    public function RenderPriority($priority = 0)
	{
		switch ($priority)
		{
			case 0: return PFformat::Lang('NOT_SET');        break;	
			case 1: return PFformat::Lang('PRIO_VERY_LOW');  break;	
			case 2: return PFformat::Lang('PRIO_LOW');       break;	
			case 3: return PFformat::Lang('PRIO_MEDIUM');    break;	
			case 4: return PFformat::Lang('PRIO_HIGH');      break;
			case 5: return PFformat::Lang('PRIO_VERY_HIGH'); break;			
		}
	}
}
?>