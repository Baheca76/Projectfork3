<?php
/**
* $Id: groups.class.php 930 2013-04-06 02:09:33Z eaxs $
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

class PFgroupsClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

	public function Load($id)
	{
        // Load db object
        $db = PFdatabase::GetInstance();
        $jversion = new JVersion();

        // Load group data
		$query = "SELECT `id`,`title`,`description`,`project`,`permissions`"
               . "\n FROM #__pf_groups WHERE id = '$id'";
		       $db->setQuery($query);
		       $row = $db->loadObject();

		// Log any errors
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return NULL;
        }

		if(!is_object($row)) return NULL;

        // Load group members
		$query = "SELECT user_id FROM #__pf_group_users"
               . "\n WHERE group_id = '$id'";
		       $db->setQuery($query);
		       $row->users = $db->loadResultArray();

        // Log any errors
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return NULL;
        }

        // Check if it's a global group and find Joomla group name
        if($row->project == 0 && $jversion->RELEASE == '1.6') {
            $map = $this->MapGlobalGroups();
            if(array_key_exists($row->id, $map)) {
                $row->jtitle = $this->GetJoomlaGroupName($map[$row->id]);
            }
            else {
                $row->jtitle = '';
            }
        }

		return $row;
	}

	public function CountGroups($keyword = NULL, $project = 0)
	{
        // Load db objects
        $db = PFdatabase::GetInstance();

        // Setup query filter
		$filter = "";

		if($keyword) {
		    $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

			$filter .= "\n AND (title LIKE " . $keyword;
			$filter .= "\n OR description LIKE " . $keyword . ")";
		}

        // Do the query
		$query = "SELECT COUNT(id) FROM #__pf_groups WHERE project = '$project'"
				. $filter
				. "\n GROUP BY id";
		       $db->setQuery($query);
		       $total = (int) $db->loadResult();

		// Log any errors
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return array();
        }

        // Unset db
        unset($db);

		return $total;
	}

	public function LoadList($limit, $limitstart, $order_by, $order_dir, $keyword = NULL, $project = 0)
	{
        // Load db object
        $db = PFdatabase::GetInstance();
        $jversion = new JVersion();

        // Setup query filter
		$filter = "\n WHERE g.project = '$project'";

		if($keyword) {
		    $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);

			$filter .= "\n AND ((g.title LIKE " . $keyword . ")";
			$filter .= "\n OR (g.description LIKE " . $keyword . "))";
		}

		$query = "SELECT g.*, p.title AS project_title, COUNT(u.user_id) AS user_count"
				. "\n FROM #__pf_groups AS g"
				. "\n LEFT JOIN #__pf_group_users AS u ON u.group_id = g.id"
				. "\n LEFT JOIN #__pf_projects AS p ON p.id = g.project"
				. $filter
				. "\n GROUP BY g.id"
				. "\n ORDER BY ".$order_by." ".$order_dir
				. (($limit > 0) ? "\n LIMIT ".$limitstart.", ".$limit : "\n");
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

        // Log any errors
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return array();
        }

        // Make sure we have an array
		if( !is_array($rows)) $rows = array();

		// Find joomla 1.6 group name
		if($jversion->RELEASE == '1.6' && $project == 0) {
		    $config    = PFconfig::GetInstance();
		    $group_map = $this->MapGlobalGroups();
		    $tmp_rows  = array();

		    foreach($rows AS $row)
		    {
		        if(!array_key_exists($row->id, $group_map)) {
                    $tmp_rows[] = $row;
                    unset($row);
                    continue;
                }

		        $jgid = (int) $group_map[$row->id];

                $query = "SELECT title FROM #__usergroups"
                       . "\n WHERE id = '$jgid'";
                       $db->setQuery($query);
                       $jgtitle = $db->loadResult();

                $row->jtitle = $jgtitle;
                $tmp_rows[] = $row;
                unset($row);
            }
            $rows = $tmp_rows;
            unset($tmp_rows);
		}

		// Unset object
		unset($db);

		return $rows;
	}

	public function LoadGlobalList($prefix = true)
	{
        // Load db object
        $db = PFdatabase::GetInstance();

        $query = "SELECT id FROM #__usergroups";
               $db->setQuery($query);
               $groups = $db->loadResultArray();

        if(!is_array($groups)) $groups = array();

        if($prefix) {
            $tmp_groups = array();
            foreach($groups AS $group)
            {
                $tmp_groups[] = 'group_'.intval($group);
            }
            $groups = $tmp_groups;
            unset($tmp_groups);
        }

        return $groups;
    }

    public function SyncJoomlaGroups()
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();

        $query = "SELECT id FROM #__usergroups";
               $db->setQuery($query);
               $groups = $db->loadResultArray();

        if(!is_array($groups)) $groups = array();

        // Add non-existant groups
        foreach($groups AS $group)
        {
            $pf_id     = 'group_'.intval($group);
            $pf_exists = (int) $config->Get($pf_id);

            if(!$pf_exists) {
                JRequest::setVar('title', 'GROUP_AUTO_NAME');
                JRequest::setVar('description', 'GROUP_AUTO_MEMBERS');
                $new_id = (int) $this->Save(true);
                if($new_id) $config->Set($pf_id, $new_id);
            }
        }

        // Delete deprecated groups
        $sys_config = $config->Get(NULL);
        $del_groups = array();

        foreach($sys_config AS $cfg => $value)
        {
            if(substr($cfg, 0, 6) == 'group_' && $cfg != 'group_pm' && $cfg != 'group_pa') {

                $group_id = (int) substr($cfg, 6);

                if(!in_array($group_id, $groups)) {
                    $del_groups[] = $group_id;
                    $config->Delete($cfg);
                }

            }
        }

        if(count($del_groups)) $this->Delete($del_groups);
    }

    public function MapGlobalGroups()
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();

        $sys_config = $config->Get(NULL);
        $groups     = array();

        foreach($sys_config AS $cfg => $value)
        {
            if(substr($cfg, 0, 6) == 'group_') {
                $groups[$value] = (int) substr($cfg, 6);
            }
        }

        return $groups;
    }

    public function GetJoomlaGroupName($id)
    {
        $db = PFdatabase::GetInstance();

        $query = "SELECT title FROM #__usergroups"
               . "\n WHERE id = '$id'";
               $db->setQuery($query);
               $name = $db->loadResult();

        return $name;
    }

	public function Save($return_id = false)
	{
        // Load objects
        $db   = PFdatabase::GetInstance();
        $user = PFuser::GetInstance();

        // Get user input
		$title       = $db->Quote( JRequest::getVar('title') );
		$desc        = $db->Quote( JRequest::getVar('description') );
		$users       = JRequest::getVar('user_id', array());
		$permissions = JRequest::getVar('p', array(), 'array');

		// Get workspace
		$project = $user->GetWorkspace();

		// Prepare permissions
		$new_permissions = array();

		if(!array_key_exists('sections', $permissions)) $permissions['sections'] = array();

		foreach($permissions['sections'] AS $section)
		{
            $new_permissions['sections'][] = $section;
            $new_permissions[$section] = array();

            if(!array_key_exists($section, $permissions)) $permissions[$section] = array();

            foreach($permissions[$section] AS $task)
            {
                $new_permissions[$section][] = $task;

                // Get child tasks
                $query = "SELECT name FROM #__pf_section_tasks"
		               . "\n WHERE parent = ".$db->Quote($task)
                       . "\n AND section = ".$db->Quote($section);
		               $db->setQuery($query);
		               $sub_tasks = $db->loadResultArray();

		        // Log any errors
		        if($db->getErrorMsg()) {
			        $this->AddError($db->getErrorMsg());
			        return false;
		        }

                if(!is_array($sub_tasks)) $sub_tasks = array();

                foreach($sub_tasks AS $sub_task)
                {
                    $new_permissions[$section][] = $sub_task;
                }
            }
        }

        // Serialize permissions
        $new_permissions = serialize($new_permissions);

		// Create the group record
		$query = "INSERT INTO #__pf_groups VALUES"
               . "\n (NULL, $title, $desc, '$project', '$new_permissions')";
		       $db->setQuery($query);
		       $db->query();
		       $id = $db->insertid();

		// Log any errors
        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return array();
        }

		// Assign users to group
		$assigned = array();
		$query    = "INSERT INTO #__pf_group_users VALUES";
		$parts    = array();
		$ucount   = 0;
		foreach ($users AS $i => $user)
		{
			$user = (int) $user;

            if(!$user) continue;

			if(!in_array($user, $assigned) && $user > 0) {
                $parts[] = "\n (NULL, '$id', '$user')";
				$assigned[] = $user;
				$ucount++;
			}
		}

        if($ucount != 0) {
            $query .= implode(',',$parts).';';
            $db->setQuery($query);
            $db->query();

		    // Log any errors
		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
        }

        $data = array($id);
        PFprocess::Event('save_group', $data);

		if($return_id) return $id;
		return true;
	}


	public function Update($id)
	{
		if(!$id) return false;

		// Load objects
		$db = PFdatabase::GetInstance();

		// Get user input
		$title       = $db->Quote(JRequest::getVar('title'));
		$desc        = $db->Quote(JRequest::getVar('description'));
		$users       = JRequest::getVar('user_id', array());
		$permissions = JRequest::getVar('p', array(), 'array');

		// Update basic group info
		$query = "UPDATE #__pf_groups SET"
               . "\n title = $title, description = $desc"
		       . "\n WHERE id = '$id'";
		       $db->setQuery($query);
		       $db->query();

        // Log any errors
		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		// Delete all group users
		$query = "DELETE FROM #__pf_group_users WHERE group_id = '$id'";
		       $db->setQuery($query);
		       $db->query();

		// Log any errors
		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		// Re-Assign users
		$assigned = array();
		$query    = "INSERT INTO #__pf_group_users VALUES";
		$parts    = array();
		$ucount   = 0;
		foreach ($users AS $i => $user)
		{
			$user = (int) $user;

            if(!$user) continue;

			if(!in_array($user, $assigned) && $user > 0) {
                $parts[] = "\n (NULL, '$id', '$user')";
				$assigned[] = $user;
				$ucount++;
			}
		}

        if($ucount != 0) {
            $query .= implode(',',$parts).';';
            $db->setQuery($query);
            $db->query();

		    // Log any errors
		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
        }

		// Update permissions
		$new_permissions = array();

		if(!array_key_exists('sections', $permissions)) $permissions['sections'] = array();

		foreach($permissions['sections'] AS $section)
		{
            $new_permissions['sections'][] = $section;
            $new_permissions[$section] = array();

            if(!array_key_exists($section, $permissions)) $permissions[$section] = array();

            foreach($permissions[$section] AS $task)
            {
                $new_permissions[$section][] = $task;

                // Get child tasks
                $query = "SELECT name FROM #__pf_section_tasks"
		               . "\n WHERE parent = ".$db->Quote($task)
                       . "\n AND section = ".$db->Quote($section);
		               $db->setQuery($query);
		               $sub_tasks = $db->loadResultArray();

		        // Log any errors
		        if($db->getErrorMsg()) {
			        $this->AddError($db->getErrorMsg());
			        return false;
		        }

                if(!is_array($sub_tasks)) $sub_tasks = array();

                foreach($sub_tasks AS $sub_task)
                {
                    $new_permissions[$section][] = $sub_task;
                }
            }
        }

        // Serialize permissions and then update db
        $new_permissions = serialize($new_permissions);

        $query = "UPDATE #__pf_groups SET permissions = '$new_permissions'"
               . "\n WHERE id = '$id'";
               $db->setQuery($query);
               $db->query();

        // Log any errors
		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        // Processes
        $data = array($id);
        PFprocess::Event('update_group', $data);

        return true;
	}

	function Delete($cid)
	{
		if(count($cid) == 0) return false;

		// Load db object
		$db = PFdatabase::GetInstance();

		$cid   = implode(',', $cid);
		$query = array();
		$query[] = "DELETE FROM #__pf_group_users WHERE group_id IN($cid)";
		$query[] = "DELETE FROM #__pf_groups WHERE id IN($cid)";

		foreach ($query AS $q)
		{
			$db->setQuery($q);
			$db->query();

			// Log any errors
		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
		}

        $data = array($cid);
        PFprocess::Event('delete_group', $data);

		return true;
	}

	public function Copy($cid)
	{
		if(count($cid) == 0) return false;

        // Load db object
        $db = PFdatabase::GetInstance();

		foreach ($cid AS $id)
		{
			// Select the group to copy
			$query = "SELECT * FROM #__pf_groups WHERE id = '$id'";
			       $db->setQuery($query);
			       $row = $db->loadObject();

			// Log any errors
		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

		    // Select the group users
		    $query = "SELECT user_id FROM #__pf_group_users"
                   . "\n WHERE group_id = '$id'";
                   $db->setQuery($query);
                   $users = $db->loadResultArray();

            // Log any errors
		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

		    if(!is_array($users)) $users = array();

			$row->title = $db->Quote( PFformat::Lang('COPY_OF').' '.$row->title );
            $row->description = $db->Quote( $row->description );

            // Duplicate the group
            $query = "INSERT INTO #__pf_groups VALUES"
                   . "\n (NULL, $row->title, $row->description, $row->project, '$row->permissions')";
                   $db->setQuery($query);
                   $db->query();
                   $new_id = $db->insertid();

            // Log any errors
		    if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

            // Copy group members
            $assigned = array();
		    $query    = "INSERT INTO #__pf_group_users VALUES";
		    $parts    = array();
		    $ucount   = 0;
		    foreach ($users AS $i => $user)
		    {
			    $user = (int) $user;

                if(!$user) continue;

			    if(!in_array($user, $assigned) && $user > 0) {
                    $parts[] = "\n (NULL, '$new_id', '$user')";
				    $assigned[] = $user;
				    $ucount++;
			    }
		    }

            if($ucount != 0) {
                $query .= implode(',',$parts).';';
                $db->setQuery($query);
                $db->query();

		        // Log any errors
		        if($db->getErrorMsg()) {
			        $this->AddError($db->getErrorMsg());
			        return false;
		        }
            }
		}

        $data = array($cid);
        PFprocess::Event('copy_group', $data);

		return true;
	}

	public function PermissionTable($permissions = '')
	{
        // Load objects
        $core = PFcore::GetInstance();
        $user = PFuser::GetInstance();
        $config = PFconfig::GetInstance();
		$showtips =  (int) $config->Get('tooltip_help');
        $use_score = (int) $config->Get('use_score');

        // Unserialize permissions
        $permissions = unserialize($permissions);

        if(!is_array($permissions) || count($permissions) == 0) {
            $permissions = array();
            $permissions['sections'] = array();
        }

        // Set current task
        $task = $core->GetTask();

        // Get sections
        $sections = $core->GetSections();

        // Get tasks
        $tasklist = $core->GetTasks();

        // Start html
		$html = '<script type="text/javascript">
		         function toggleSection(i, el)
		         {
		             var e = document.getElementById("s_"+i);

		             if(el.checked == true) {
		                 e.style.display = "";
		             }
		             else {
		                 e.style.display = "none";
		             }
		         }
		         </script>
		         <table width="100%">
		             <tr>
		                <td>';


        // Loop through each section
		foreach ($sections AS $i => $section)
		{
			$access       = $user->Access('', $section->name, 0, true);
			$can_edit     = $user->Access('form_edit_section', 'config');
			$desc_section = "";
			$e1 = '';
			$e2 = '';
			if(!$access) continue;

            // Get the tasks for this section
            if(!array_key_exists($section->name,$tasklist)) {
                $tasks = array();
            }
            else {
                $tasks = $tasklist[$section->name];
            }

			$checked = "";
			$style   = 'style="display:none"';

			if(in_array($section->name, $permissions['sections'])) {
				$checked = 'checked="checked"';
				$style   = 'style="display:"';
			}

			$desc_section = "<strong>".PFformat::Lang('REQUIREMENTS').":</strong><br/>";
			if($use_score) $desc_section .= "Score: $section->score<br/>";
			$desc_section .= "Special: ".PFformat::Tag($section->tags);
			if ($showtips){
				$e1 = '<span class="editlinktip hasTip" title="'.$desc_section.'">';
				$e2 = "</span>";
			}

			$html .= '<table class="pf_table adminlist" cellpadding="0" cellspacing="0">
			          <thead>
			          <tr>
			             <th width="5"><input type="checkbox" name="p[sections][]" value="'.$section->name.'" onclick="toggleSection(\''.$i.'\', this)" '.$checked.'/></th>
			             <th colspan="2" align="left" style="text-align:left !important">'.$e1.PFformat::Lang($section->title).$e2.'</th>
			          </tr>
			          </thead>
			          <tbody id="s_'.$i.'" '.$style.'>';

			          // Find out the total amount of tasks
                      $total_tasks = 0;
                      foreach($tasks AS $task)
                      {
                          // Dont show child tasks
                          if($task->parent != '') continue;
                          $access = $user->Access($task->name, $section->name, 0, true);

                          if(!$access) continue;

                          $total_tasks++;
                      }

			          // Loop through each task
                      $k  = 0;
                      $i2 = 0;
			          foreach ($tasks AS $task)
			          {
                           // Dont show child tasks
                           if($task->parent != '') continue;

                           $checked = "";
			               $access = $user->Access($task->name, $section->name, 0, true);

			               if(!$access) continue;

                           if(array_key_exists($section->name, $permissions)) {
                           	   if(in_array($task->name, $permissions[$section->name])) $checked = 'checked="checked"';
                           }

			               $desc_task = "<strong>".PFformat::Lang('REQUIREMENTS').":</strong><br/>";
			               if($use_score) $desc_task .= "Score: $task->score<br/>";
			               $desc_task .= "Special: ".PFformat::Tag($task->tags);
							if ($showtips){
								$e1 = '<span class="editlinktip hasTip" style="cursor:help" title="'.$desc_task.'">';
								$e2 = "</span>";
							}
 	  	 	               if($i2 == 0) {
 	  	 	               	   $html .= '<tr class="pf_row'.$k.' row'.$k.'">
			          	                 <td><input type="checkbox" onclick="pf_select_all(\''.$task->section.'\', '.$total_tasks.', this)" name="select_all"/></td>
			          	                 <td width="20%" nowrap="nowrap">'.PFformat::Lang('SELECT_ALL').'</td>
			          	                 <td width="80%"></td>
			          	                 </tr>';
 	  	 	               }
			          	   $html .= '<tr class="pf_row'.$k.' row'.$k.'">
			          	             <td><input type="checkbox" id="'.$task->section.'_'.$i2.'" name="p['.$task->section.'][]" value="'.$task->name.'" '.$checked.'/></td>
			          	             <td width="20%" nowrap="nowrap"><strong>'.$e1.PFformat::Lang($task->title).$e2.'</strong></td>
			          	             <td width="80%">'.PFformat::Lang($task->description).'</td>
			          	             </tr>';
			          	   $k = 1 - $k;
			          	   $i2++;
			          }

			          if(!count($tasks)) {
			          	 $html .= '<tr class="pf_row0 row0"><td colspan="3">'.PFformat::Lang('NO_PERMISSIONS').'</td></tr>';
			          }

			 $html .= '  </tbody>
			       </table>';
		}

		$html .= '     </td>
		         </tr>
		      </table>';

		$html .= '<script type="text/javascript">
		       function pf_select_all(cid, count, el)
		       {
		          for(var i = 0; i < count; i++)
		          {
		             var cbid = cid+"_"+i;
		             document.getElementById(cbid).checked= el.checked;
		          }
		       }
		       </script>';

        // Unset objects and data
        unset($user,$sections,$tasks,$tasklist,$core);

		return $html;
    }
}
?>
