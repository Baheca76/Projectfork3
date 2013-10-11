<?php
/**
* $Id: upgrade.class.php 837 2010-11-17 12:03:35Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
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

class PFupgradeClass
{
    private $errors;
    
    public function __construct()
    {
        $this->errors = array();
    }
    
    private function AddError($msg)
    {
        $this->errors[] = $msg;
    }
    
    public function GetErrors()
    {
        return $this->errors;
    }
    
    public function GetOldTables($quote = false)
    {
        $tables = array('#__pf_access_flags', '#__pf_access_levels',
        '#__pf_comments', '#__pf_events', '#__pf_files', '#__pf_folders',
        '#__pf_folder_tree', '#__pf_groups', '#__pf_group_permissions',
        '#__pf_group_users', '#__pf_languages', '#__pf_milestones',
        '#__pf_mods', '#__pf_mod_data', '#__pf_notes', '#__pf_panels',
        '#__pf_processes', '#__pf_projects', '#__pf_project_invitations',
        '#__pf_project_members', '#__pf_sections', '#__pf_section_tasks',
        '#__pf_settings', '#__pf_tasks', '#__pf_task_attachments',
        '#__pf_task_users', '#__pf_themes', '#__pf_time_tracking',
        '#__pf_topics', '#__pf_topic_replies', '#__pf_topic_subscriptions',
        '#__pf_user_access_level', '#__pf_user_profile');
        
        if($quote) {
            $db = Jfactory::getDBO();
            $qtables = array();
            foreach($tables AS $table) { $qtables[] = $db->Quote($table); }
            $tables = $qtables;
        }
        
        return $tables;
    }
    
    public function GetNewTables($quote = false)
    {
        $tables = array('#__pf_access_flags', '#__pf_access_levels',
        '#__pf_comments', '#__pf_events', '#__pf_files', '#__pf_folders',
        '#__pf_folder_tree', '#__pf_groups','#__pf_group_users', 
        '#__pf_languages', '#__pf_milestones',
        '#__pf_mods', '#__pf_mod_files', '#__pf_notes', '#__pf_panels',
        '#__pf_processes', '#__pf_projects', '#__pf_project_invitations',
        '#__pf_project_members', '#__pf_sections', '#__pf_section_tasks',
        '#__pf_settings', '#__pf_tasks', '#__pf_task_attachments',
        '#__pf_task_users', '#__pf_themes', '#__pf_time_tracking',
        '#__pf_topics', '#__pf_topic_replies', '#__pf_topic_subscriptions',
        '#__pf_user_access_level', '#__pf_user_profile');
        
        if($quote) {
            $db = Jfactory::getDBO();
            $qtables = array();
            foreach($tables AS $table) { $qtables[] = $db->Quote($table); }
            $tables = $qtables;
        }
        
        return $tables;
    }
    
    public function OptimizeOldTables()
    {
        $tables = implode(', ', $this->GetOldTables(false) );
        $db = JFactory::getDBO();
        
        $query = "ANALYZE TABLE $tables";
               $db->setQuery($query);
               $db->query();
        
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }
        
        $query = "OPTIMIZE TABLE $tables";
               $db->setQuery($query);
               $db->query();
               
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }
        
        return true;
    }
    
    public function OptimizeNewTables()
    {
        $tables = implode(', ', $this->GetNewTables(false) );
        $db = JFactory::getDBO();
        
        $query = "ANALYZE TABLE $tables";
               $db->setQuery($query);
               $db->query();
        
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }
        
        $query = "OPTIMIZE TABLE $tables";
               $db->setQuery($query);
               $db->query();
               
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }
        
        return true;
    }
    
    public function RunSQL($file, $user_id = 0)
    {
        $db   = JFactory::getDBO();
        $user = JFactory::getUser();
        $file = dirname(__FILE__).DS.'mysql'.DS.$file;
        
        if(!file_exists($file)) {
            $this->AddError("$file does not exist!");
            return false;
        }

        $buffer = file_get_contents($file);

        if ( $buffer === false ) {
            $this->AddError("Failed to read file: $file");
		    return false;
		}

        $queries = $db->splitSql($buffer);

        if (count($queries) == 0) return true;

        foreach ($queries as $query)
		{
			$query = trim($query);
            
            if(!$user_id) {
                $query = str_replace('{uid},',$user->id.',', $query);
            }
            else {
                $query = str_replace('{uid},',$user_id.',', $query);
            }
            
			if ($query != '' && $query{0} != '#') {
                
				$db->setQuery($query);
				if (!$db->query()) {
                    $this->AddError("SQL error: ".$db->stderr(true));
					return false;
				}
			}
		}

        return true;
    }
    
    public function MigrateGroups()
    {
        $db = JFactory::getDBO();
        
        $query = "Select id FROM #__pf_groups";
               $db->setQuery($query);
               $group_ids = $db->loadResultArray();
               
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }
        
        // Update permission table
        $query = "UPDATE #__pf_section_tasks SET name = 'task_archive' WHERE name = 'task_archivate'";
               $db->setQuery($query);
               $db->query();
               
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }
        
        // Update groups
        foreach($group_ids AS $id)
        {
            $permissions = array();
            $permissions['sections'] = array();
            
            $query = "SELECT section, task FROM #__pf_group_permissions"
                   . "\n WHERE group_id = '$id'"
                   . "\n ORDER BY section, task ASC";
                   $db->setQuery($query);
                   $tmp_permissions = $db->loadObjectList();
                   
            if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }
            
            foreach($tmp_permissions AS $p)
            {
                $s = $p->section;
                $t = $p->task;
                
                if(!in_array($s, $permissions['sections'])) {
                    $permissions['sections'][] = $s;
                    $permissions[$s] = array();
                }
                
                if($t == 'task_archivate') $t = 'task_archive';
                if($t) $permissions[$s][] = $t;
            }
            
            $permissions = serialize($permissions);
            
            $query = "UPDATE #__pf_groups SET permissions = '$permissions'"
                   . "\n WHERE id = '$id'";
                   $db->setQuery($query);
                   $db->query();
             
            if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }
            
            unset($permissions, $tmp_permissions); 
        }
        
        // Rename groups
        $queries = array();
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_VISITOR', description = 'GROUP_VISITOR_DESC' WHERE title = 'PFL_GROUP_VISITOR'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_REGISTERED', description = 'GROUP_REGISTERED_DESC' WHERE title = 'PFL_GROUP_REGISTERED'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_AUTHOR', description = 'GROUP_AUTHOR_DESC' WHERE title = 'PFL_GROUP_AUTHOR'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_EDITOR', description = 'GROUP_EDITOR_DESC' WHERE title = 'PFL_GROUP_EDITOR'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_PUBLISHER', description = 'GROUP_PUBLISHER_DESC' WHERE title = 'PFL_GROUP_PUBLISHER'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_MANAGER', description = 'GROUP_MANAGER_DESC' WHERE title = 'PFL_GROUP_MANAGER'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_ADMINISTRATOR', description = 'GROUP_ADMINISTRATOR_DESC' WHERE title = 'PFL_GROUP_ADMINISTRATOR'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_SADMINISTRATOR', description = 'GROUP_SADMINISTRATOR_DESC' WHERE title = 'PFL_GROUP_SADMINISTRATOR'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_FOUNDER', description = 'GROUP_FOUNDER_DESC' WHERE title = 'PFL_GROUP_FOUNDER'";
        $queries[] = "UPDATE #__pf_groups SET title = 'GROUP_MEMBER', description = 'GROUP_MEMBER_DESC' WHERE title = 'PFL_GROUP_MEMBER'";
        
        foreach($queries AS $query)
        {
            $db->setQuery($query);
            $db->query();
            
            if($db->getErrorMsg()) {
                $this->AddError($db->getErrorMsg());
                return false;
            }
        }
        
        return true;
    }
    
    public function UpdateProfiles()
    {
        $db = JFactory::getDBO();
        
        $query = "UPDATE #__pf_user_profile SET `content` = 'english'"
               . "\n WHERE `parameter` = 'language'";
               $db->setQuery($query);
               $db->query();
               
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }
        
        $query = "UPDATE #__pf_user_profile SET `content` = 'default'"
               . "\n WHERE `parameter` = 'theme'";
               $db->setQuery($query);
               $db->query();
               
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return false;
        }
        
        return true;       
    }
    
    public function HasAkeebaBackup()
    {
        $component =& JComponentHelper::getComponent( 'com_akeeba', true );
        
        if($component->enabled) {
            // Figure out the version
            include_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_akeeba'.DS.'version.php';
            jimport('joomla.utilities.date');
            $date = new JDate(AKEEBA_DATE);
            // Check that the release date was after September 3rd, 2010
            return $date->toUnix() > 1283490000;
        }
        else {
            return false;
        }
    }
}
?>