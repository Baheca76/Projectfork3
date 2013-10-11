<?php
/**
* $Id: demo.class.php 837 2010-11-17 12:03:35Z eaxs $
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
// DEMO CLASS
class PFdemo
{
	var $restricted;
	
	public function __construct()
	{
		$this->restricted = array();
		$this->restricted['filemanager'] = array();
		$this->restricted['filemanager'][] = 'form_new_file';
		$this->restricted['profile'] = array();
		$this->restricted['profile'][] = 'task_update';
	}
	
	public function IsRestricted($section, $task)
	{
		if(array_key_exists($section, $this->restricted)) {
			if(in_array($task, $this->restricted[$section])) {
				return true;
			}
		}
		return false;
	}
	
	public function ResetSystem()
	{
		$pf_core   = PFcore::GetInstance();
		$pf_db     = PFdatabase::GetInstance();
		$pf_config = PFconfig::GetInstance();
		
		// GATHER TABLES
		$tables = array('#__pf_comments','#__pf_events', 
		                '#__pf_files', '#__pf_folders', 
		                '#__pf_folder_tree', '#__pf_groups',
		                '#__pf_group_users', '#__pf_milestones', '#__pf_notes',
		                '#__pf_projects', '#__pf_project_members',
		                '#__pf_tasks', '#__pf_task_users', '#__pf_task_attachments',
		                '#__pf_user_profile', '#__pf_project_members',
		                '#__pf_topics', '#__pf_topic_replies', '#__pf_topic_subscriptions',
		                '#__pf_project_invitations', '#__pf_user_access_level',
                        '#__pf_time_tracking');
		                
		foreach ($tables AS $table)
		{
			switch ($table)
			{
				case '#__pf_group_permissions':
					$query = "DELETE FROM $table WHERE group_id > 10";
					break;
					
				case '#__pf_groups':
					$query = "DELETE FROM $table WHERE project != '0'";
					break;
					
				default:
					$query = "TRUNCATE TABLE $table";
					break;
			}
			$pf_db->setQuery($query);
			$pf_db->query();
		}

        // Load demo sql?
        $use_demo_sql = (int) $pf_config->Get('use_demo_sql', 'demo');
        $user_id      = (int) $pf_config->Get('demo_user', 'demo');

        if($use_demo_sql && $user_id) {
            $setup_class = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'_install'.DS.'setup.class.php';
            
            if(file_exists($setup_class)) {
                require_once($setup_class);
                $c = new PFsetupClass();

                $c->RunSQL('demo.sql', $user_id);
            }
            else {
                echo "Does not exist!";
            }
        }
		
		$pf_config->Set('last_reset', time(), 'demo');
	}
}
?>