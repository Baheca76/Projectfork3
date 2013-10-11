<?php
/**
* $Id: user.php 925 2012-06-22 11:46:01Z eaxs $
* @package       Projectfork
* @subpackage    Framework
* @copyright     Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license       http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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

class PFuser
{
    private $id;
    private $gid;
    private $name;
    private $username;
    private $usertype;
    private $email;
    private $profile;
    private $permissions;
    private $permission_objects;
    private $cache_enabled;

	protected function __construct($id = 0)
	{
	    $user = ($id == 0) ?  JFactory::getUser() : JFactory::getUser($id);

		$jversion = new JVersion();

		$this->id          = $user->id;
		$this->name        = $user->name;
		$this->username    = $user->username;
		$this->email       = $user->email;
		$this->usertype    = $user->usertype;
		$this->profile     = array();
		$this->permissions = array();

		if($jversion->RELEASE != '1.5') {
		    $this->gid = $this->LoadGid(0, $user->id);
		}
		else {
            $this->gid = $this->LoadGid($user->gid);
        }

        if(!$this->name) $this->name = 'PFL_GUEST';

		if($this->id) {
		    $config = PFconfig::GetInstance();
		    $debug  = PFdebug::GetInstance();

		    $cache_user = (int) $config->Get('cache_user');

		    // Do not cache profile
		    $this->profile = $this->LoadProfile($this->id);
		    $workspace = JRequest::getVar('workspace');

		    // Get Workspace
		    if(is_null($workspace)) {
                $workspace = (int) $this->GetProfile('workspace');
            }
            else {
                // $workspace = explode(':', $workspace);
                // $workspace = (int) $workspace[0];
                $workspace = (int) $workspace;
            }

		    if($cache_user) {
		        $cache = JFactory::getCache('com_projectfork.user');
		        $cache->setCaching(true);
		        $debug->_('n', 'PFuser::__construct - Loading user data from cache');
                $this->cache_enabled = true;

                $tmp_permissions = $cache->call(array('PFuser', 'LoadPermissions'), $this->id, $this->gid, $workspace);
                $this->permissions = $tmp_permissions[0];
                $this->permission_objects = $tmp_permissions[1];

                unset($cache, $tmp_permissions);
            }
            else {
                $debug->_('n', 'PFuser::__construct - User cache is disabled');
                $this->cache_enabled = false;

                $tmp_permissions = $this->LoadPermissions($this->id, $this->gid, $workspace);
                $this->permissions = $tmp_permissions[0];
                $this->permission_objects = $tmp_permissions[1];

                unset($tmp_permissions);
            }

            // Set workspace in user profile
            $this->SetProfile('workspace', $this->Permission('workspace'));

            unset($debug);
        }
        else {
            $workspace = (int) JRequest::getVar('workspace', 0);
            $tmp_permissions = $this->LoadPermissions(0, $this->gid, $workspace);
            $this->permissions = $tmp_permissions[0];
            $this->permission_objects = $tmp_permissions[1];

            unset($tmp_permissions);
        }
	}

	public function GetInstance($id = 0)
	{
        static $self = array();

        if(array_key_exists($id, $self)) return $self[$id];

        $self[$id] = new PFuser($id);

        return $self[$id];
    }

	public function LoadGid($gid, $user_id = 0)
	{
        $db = PFdatabase::GetInstance();

        if(!$user_id) {
            $gids = array(18,19,20,21,23,24,25);

		    if(!in_array($gid, $gids)) {
			    $query = "SELECT parent_id FROM #__core_acl_aro_groups"
                       . "\n WHERE id = '$gid'";
			           $db->setQuery($query);
			           $gid = (int) $db->loadResult();
		    }
        }
        else {
            // Joomla 1.6 GID
            $user_id = (int) $user_id;

            if($user_id) {
                // Logged in
                $query = "SELECT group_id FROM #__user_usergroup_map"
                       . "\n WHERE user_id = '$user_id'";
                       $db->setQuery($query);
                       $gid = $db->loadResultArray();

                if(!is_array($gid)) $gid = array();
            }
            else {
                // Not logged in - Find all public groups (parent id = 0?)
                $query = "SELECT id FROM #__usergroups WHERE parent_id = '0'";
                       $db->setQuery($query);
                       $gid = $db->loadResultArray();

                if(!is_array($gid)) $gid = array();
            }
        }

		unset($db);
		return $gid;
    }

	public function LoadProfile($id)
	{
		$profile_array = array();
		$db = PFdatabase::GetInstance();

		$query = "SELECT `parameter`, `content` FROM #__pf_user_profile"
		       . "\n WHERE user_id = '$id'";
		       $db->setQuery($query);
		       $profile = $db->loadObjectList();

		if(!$profile) $profile = array();

        foreach($profile AS $data)
        {
            $profile_array[$data->parameter] = $data->content;
        }

        unset($db);
        return $profile_array;
	}

	public function LoadPermissions($id = 0, $gid = NULL, $workspace = 0)
	{
	    $db     = PFdatabase::GetInstance();
	    $config = PFconfig::GetInstance();
	    $debug  = PFdebug::GetInstance();
	    $core   = PFcore::GetInstance();

        $jversion  = new JVersion();
        $use_score = (int) $config->Get('use_score');

        // J 1.6 detection
        $is_16 = false;
        if($jversion->RELEASE != '1.5') $is_16 = true;
        unset($jversion);

	    $permissions = array();
	    $permissions['score']       = 0;
	    $permissions['workspace']   = $workspace;
	    $permissions['flag']        = "";
	    $permissions['groups']      = array();
	    $permissions['projects']    = array();
	    $permissions['author']      = array();
	    $permissions['userspace']   = array();
	    $permissions['sections']    = array();
	    $permissions['tasks']       = array();

        $permission_objects = array();

        // Load access level(s)
        if(!$is_16) {
            // Joomla 1.5
            $permissions['accesslevel'] = (int) $config->Get('accesslevel_'.$gid);
        }
        else {
            // Joomla 1.6
            $permissions['accesslevel'] = array();
            if(!is_array($gid)) $gid = array();
            foreach($gid AS $gid2)
            {
                $tmp_acl = (int) $config->Get('accesslevel_'.$gid2);
                if($tmp_acl) $permissions['accesslevel'][] = $tmp_acl;
            }
        }

        // Load score and flag
        if(!$is_16) {
            // Joomla 1.5
            if($permissions['accesslevel']) {
    			$acl = $permissions['accesslevel'];
    			$query = "SELECT `score`, `flag` FROM #__pf_access_levels"
                       . "\n WHERE id = '$acl'";
    				   $db->setQuery($query);
    				   $object = $db->loadObject($query);

    			if(is_object($object)) {
    				$permissions['score'] = (int) $object->score;
    				$permissions['flag']  = $object->flag;
    			}
    		}
        }
        else {
            // Joomla 1.6
            if(count($permissions['accesslevel'])) {
                $tmp_acl = implode(',', $permissions['accesslevel']);

                $query = "SELECT `score`, `flag` FROM #__pf_access_levels"
                       . "\n WHERE id IN($tmp_acl)";
                       $db->setQuery($query);
                       $objects = $db->loadObjectList();

                if(!is_array($objects)) {
                    $permissions['score'] = 0;
    			    $permissions['flag']  = '';
                }
                else {
                    $highest_score = 0;
                    $highest_flag = '';

                    foreach($objects AS $object)
                    {
                        if($object->score >= $highest_score) $highest_score = (int) $object->score;
                        if($object->flag == 'system_administrator') $highest_flag = 'system_administrator';
                        if($object->flag == 'project_administrator' && $highest_flag != 'system_administrator') {
                            $highest_flag = 'project_administrator';
                        }
                    }

                    $permissions['score'] = $highest_score;
    			    $permissions['flag']  = $highest_flag;
                }
            }
            else {
                $permissions['score'] = 0;
    			$permissions['flag']  = '';
            }
        }


		// LOAD PROJECT GROUPS
		if($id && $workspace) {
			$query = "SELECT gu.group_id FROM #__pf_group_users AS gu"
			       . "\n RIGHT JOIN #__pf_groups AS g ON (g.id = gu.group_id AND(g.project = '$workspace'))"
			       . "\n WHERE gu.user_id = '".$id."'"
			       . "\n GROUP BY gu.group_id";
			       $db->setQuery($query);
			       $groups = $db->loadResultArray();

			if(!is_array($groups)) {
				$debug->_("w", "PFuser::LoadPermissions - You are not a member of a PROJECT group!");
				$groups = array();
			}

			$debug->_("n", "PFuser::LoadPermissions - You are a member if the current project = ".$workspace);

            // LOAD GLOBAL PROJECT MEMBER GROUP
            $query = "SELECT user_id FROM #__pf_project_members"
                   . "\n WHERE project_id = '$workspace'"
                   . "\n AND user_id = '".$id."'"
                   . "\n AND approved = '1'";
                   $db->setQuery($query);
                   $is_project_member = (int) $db->loadResult();

            if($is_project_member) {
                $result = (int) $config->Get('group_pm');
                $debug->_("n", "PFuser::LoadPermissions - Adding you to global project member group = ".$result."!");
                if($result) $groups[] = $result;
            }
			$permissions['groups'] = $groups;
		}
		else {
			$debug->_("w", "PFuser::LoadPermissions - You are not a member of a PROJECT group!");
			$is_project_member = 0;
			$permissions['groups'] = array();
		}

		// LOAD GLOBAL GROUP
		if(!$is_16) {
		    // Joomla 1.5
		    $global_group = (int) $config->Get('group_'.$gid);

    		if($global_group) {
    			$permissions['groups'][] = $global_group;
    			$debug->_("n", "PFuser::LoadPermissions - Your GLOBAL group is = $global_group");
    		}
    		else {
    			$debug->_("w", "PFuser::LoadPermissions - You are not a member of a GLOBAL group!");
    		}
		}
		else {
            // Joomla 1.6
            foreach($gid AS $gid2)
            {
                $global_group = (int) $config->Get('group_'.$gid2);
                if($global_group) {
        			$permissions['groups'][] = $global_group;
        			$debug->_("n", "PFuser::LoadPermissions - You're member of the GLOBAL group '$global_group'");
        		}
            }
        }


		// LOAD AUTHOR GROUP
		$author_group = 0;
		if($workspace && $id) {
			$query = "SELECT author FROM #__pf_projects WHERE id = '$workspace'";
			       $db->setQuery($query);
			       $author_id = (int) $db->loadResult();

			if($author_id == $id) {
			    $author_group = (int) $config->Get('group_pa');

				if($author_group) {
					$debug->_("n", "PFuser::LoadPermissions - You are the founder of this project. You have been added to the group = $author_group");
					$permissions['groups'][] = $author_group;
				}
				else {
                    $debug->_("n", "PFuser::LoadPermissions - You are not the founder of this project.");
                }
			}
		}

		// LOAD AUTHOR ACCESSLEVEL
        $pa_level = 0;
		if($author_group && $permissions['flag'] != 'system_administrator') {
			$pa_level = (int) $config->Get('accesslevel_pa');

			if($pa_level) {
				$debug->_("n","PFuser::LoadPermissions - You are the founder of this project. Setting your accesslevel id to = $pa_level");
				$permissions['accesslevel'] = $pa_level;
				$query = "SELECT score, flag FROM #__pf_access_levels WHERE id = '$pa_level'";
				       $db->setQuery($query);
				       $object = $db->loadObject();

				if(is_object($object)) {
					$debug->_("n", "You are the founder of this project. Setting your score to = $object->score");
					$debug->_("n", "You are the founder of this project. Setting your flag to = $object->flag");
					$permissions['score'] = (int) $object->score;
					$permissions['flag']  = $object->flag;
				}
			}
		}

		// LOAD PROJECT MEMBER ACCESSLEVEL
        if($is_project_member && $permissions['flag'] != 'system_administrator' && $pa_level == 0) {
            $pm_level = (int) $config->Get('accesslevel_pm');

			if($pm_level) {
				$debug->_("n","PFuser::LoadPermissions - You are a member of this project. Setting your accesslevel id to = $pm_level");
				$permissions['accesslevel'] = $pm_level;
				$query = "SELECT score, flag FROM #__pf_access_levels WHERE id = '$pm_level'";
				       $db->setQuery($query);
				       $object = $db->loadObject();

				if(is_object($object)) {
                    if($permissions['score'] < $object->score) {
                        $debug->_("n", "PFuser::LoadPermissions - You are a member of this project. Setting your score to = $object->score");
                        $permissions['score'] = (int) $object->score;
                    }
                    if(!$permissions['flag'] || ($permissions['flag'] == 'project_administrator' && $object->flag == 'system_administrator')) {
                        $debug->_("n", "PFuser::LoadPermissions - You are a member of this project. Setting your flag to = '$object->flag'");
                        $permissions['flag'] = $object->flag;
                    }
				}
				else {
                    $debug->_("e", "PFuser::LoadPermissions - Global accesslevel for project members not found = $pm_level");
                }
			}
        }

        // LOAD GROUP PERMISSIONS
		if(count($permissions['groups']) >= 1) {
			$tmp_groups   = implode(',', $permissions['groups']);
			$tmp_sections = array();
			$tmp_tasks    = array();

            $debug->_("n", "PFuser::LoadPermissions - Loading permissions from the following groups: $tmp_groups");

			$query = "SELECT permissions FROM #__pf_groups"
                   . "\n WHERE id IN($tmp_groups)";
                   $db->setQuery($query);
                   $group_perms = $db->LoadResultArray();

            if(!is_array($group_perms)) {
                $group_perms = array();
                $debug->_("e", "PFuser::LoadPermissions - No permissions found for the groups: $tmp_groups");
            }

            // Unserialize permissions
            $unserialized = array();
            foreach($group_perms AS $gp)
            {
                $unserialized[] = unserialize($gp);
            }
            $group_perms = $unserialized;
            unset($unserialized);

            // Filter out permissions
            foreach($group_perms AS $gp)
            {
                if(!is_array($gp)) $gp = array();
                if(!array_key_exists('sections',$gp)) $gp['sections'] = array();

                // Find all sections
                foreach($gp['sections'] AS $s)
                {
                    if(!in_array($s, $tmp_sections)) $tmp_sections[] = $s;

                    // Find all tasks
                    if(!array_key_exists($s,$gp)) continue;
                    if(!array_key_exists($s,$tmp_tasks)) $tmp_tasks[$s] = array();

                    foreach($gp[$s] AS $t)
                    {
                        if(in_array($t,$tmp_tasks[$s])) continue;
                        $tmp_tasks[$s][] = $t;
                    }
                }
            }
            unset($group_perms);
		}
		else {
		    $debug->_("w", "PFuser::LoadPermissions - You are not a member of any group!");
			$tmp_sections = array();
			$tmp_tasks    = array();
		}

		// LOAD PROJECTS
		if($permissions['flag'] == 'system_administrator') {
			$query = "SELECT id FROM #__pf_projects ORDER BY id ASC";
			       $db->setQuery($query);
			       $projects = $db->loadResultArray();

			if(!is_array($projects)) {
				$projects = array();
				$debug->_("n", "PFuser::LoadPermissions - You are not member of any project!");
			}
		}
		else {
			if($id) {
				$query = "SELECT project_id FROM #__pf_project_members"
                       . "\n WHERE user_id = '".$id."'"
                       . "\n GROUP BY project_id"
                       . "\n ORDER BY id ASC";
			           $db->setQuery($query);
			           $projects = $db->loadResultArray();

			    if(!is_array($projects)) {
				    $projects = array();
				    $debug->_("w", "PFuser::LoadPermissions - You are not member of any projects!");
			    }
			}
			else {
				$projects = array();
				$debug->_("w", "PFuser::LoadPermissions - You are not member of any projects!");
			}
		}

		$permissions['projects'] = $projects;
		unset($projects);

		// LOAD PROJECTS WHERE I AM THE AUTHOR
		if($id) {
			$query = "SELECT id FROM #__pf_projects"
                   . "\n WHERE author = '".$id."'"
                   . "\n ORDER BY id ASC";
			       $db->setQuery($query);
			       $permissions['author'] = $db->loadResultArray();

			if(!is_array($permissions['author'])) $permissions['author'] = array();
		}

		// MERGE PROJECTS
		$pdiff = array_diff($permissions['projects'], $permissions['author']);
		$permissions['projects'] = array_merge($permissions['projects'], $pdiff);
		unset($pdiff);

		// SECURE WORKSPACE
		if($workspace) {
			if(!in_array($workspace, $permissions['projects'])) {
                $query = "SELECT is_public FROM #__pf_projects"
                       . "\n WHERE id = '$workspace'";
                       $db->setQuery($query);
                       $is_public = (int) $db->loadResult();

                if(!$is_public) {
                    $debug->_("v", "PFuser::LoadPermissions - You are not allowed to access the workspace id $workspace");
				    $permissions['workspace'] = 0;
				    $workspace = 0;
                }
                else {
                    $debug->_("w", "PFuser::LoadPermissions - Project $workspace not within range! Project set to public - Access granted.");
                }
			}
		}

		// ASSIGN PROJECT ACCESS LEVEL
		if($workspace != 0 && $id != 0) {
			$query = "SELECT accesslvl FROM #__pf_user_access_level"
			       . "\n WHERE project_id = '".$workspace."'"
			       . "\n AND user_id = '".$id."'";
			       $db->setQuery($query);
			       $tmp_acl = (int) $db->loadResult();

			if($tmp_acl) {
				$permissions['accesslevel'] = $tmp_acl;

				$query = "SELECT score, flag FROM #__pf_access_levels"
                       . "\n WHERE id = '$tmp_acl'";
				       $db->setQuery($query);
				       $tmp_acl = $db->loadObject();

				if(is_object($tmp_acl)) {
					if($permissions['flag'] == '' || ($permissions['flag'] == 'project_administrator' && $tmp_acl->flag == 'system_administrator')) {
						$permissions['flag'] = $tmp_acl->flag;
					}

					if($permissions['score'] < $tmp_acl->score) {
						$permissions['score'] = $tmp_acl->score;
					}
				}
			}
		}

		// LOAD USERSPACE
		if(count($permissions['projects']) > 0) {
			$tmp = implode(',', $permissions['projects']);
			$query = "SELECT user_id FROM #__pf_project_members"
                   . "\n WHERE project_id IN($tmp)";
			       $db->setQuery($query);
			       $userspace = $db->loadResultArray();

			if(!is_array($userspace)) {
				$debug->_("w", "PFuser::LoadPermissions - Your userspace is empty!");
				$userspace = array();
			}
			$permissions['userspace'] = $userspace;
		}
		else {
			$debug->_("w", "PFuser::LoadPermissions - Your userspace is empty!");
			$permissions['userspace'] = array();
		}

		// LOAD SECTIONS
		$this_sections = $core->GetSections();
		$permission_objects['sections'] = array();
		foreach ($this_sections AS $section)
		{
		    $permission_objects['sections'][$section->name] = $section;

			$score_ok = false;
			$flag_ok  = false;
			$group_ok = false;
			$tag_ok   = false;
			$ws_ok    = false;

			// Check score
			if($section->score <= $permissions['score']) $score_ok = true;
			// Override score?
			if($use_score == 0) $score_ok = true;
			// Check flag
			if($section->flag == '' || $section->flag == $permissions['flag']) $flag_ok = true;
			// Check group
			if(in_array($section->name, $tmp_sections)) $group_ok = true;
			// Check tags
			if($section->tags == '') {
				$tag_ok = true;
				$ws_ok  = true;
			}
			else {
				if($section->tags == '-l' && $id > 0) $tag_ok = true;
				if($section->tags == '-p') {
					$tag_ok   = true;
                    $group_ok = true;
				}
				if($section->tags == '-ws') {
					$ws_ok  = true;
				    $tag_ok = true;
				}
				else {
					$ws_ok  = true;
				}
			}

			if($permissions['flag'] == 'system_administrator' && $section->enabled == '1') {
				$permissions['sections'][] = $section->name;
			}
            else {
                if($score_ok && $flag_ok && $group_ok && $tag_ok && $ws_ok && $section->enabled == '1') {
				    $permissions['sections'][] = $section->name;
			    }
            }
		}

		// LOAD TASKS
		$this_tasks = $core->GetTasks();
		if(count($permissions['sections']) > 0) {
			foreach ($permissions['sections'] AS $i => $section)
			{
				$permissions['tasks'][$section] = array();
				$permission_objects['tasks'][$section] = array();

				foreach ($this_tasks[$section] AS $i => $task)
				{
				    $permission_objects['tasks'][$section][$task->name] = $task;
					$score_ok = false;
					$flag_ok  = false;
					$tag_ok   = false;
                    $group_ok = false;
                    $ws_ok    = false;

                    // Check score
					if($task->score <= $permissions['score']) $score_ok = true;

                    // Override score?
			        if($use_score == 0) $score_ok = true;

					// check flag
					if($task->flag == '') {
						$flag_ok = true;
					}
					else {
						switch (strval($task->flag))
						{
							case 'system_administrator':
								if($permissions['flag'] == 'system_administrator') $flag_ok = true;
								break;

							case 'project_administrator':
								if($permissions['flag'] == 'project_administrator') $flag_ok = true;
								if($permissions['flag'] == 'system_administrator') $flag_ok = true;
								break;

                            default:
                                if($permissions['flag'] == $task->flag) $flag_ok = true;
                                break;
						}
					}

					// check tags
					if($task->tags == '') {
						$tag_ok = true;
						$ws_ok  = true;
					}
					else {
						if($task->tags == '-l' && $id > 0) $tag_ok = true;

						if($task->tags == '-p') {
					        $tag_ok   = true;
                            $flag_ok  = true;
                            $group_ok = true;
				        }
				        if($task->tags == '-a') {
                            $tag_ok   = true;
                            $flag_ok  = true;
                        }
						if($task->tags == '-ws') {
							$tag_ok = true;
							$ws_ok  = true;
						}
						else {
							$ws_ok  = true;
						}
					}

					if(array_key_exists($task->section, $tmp_tasks)) {
						if(in_array($task->name, $tmp_tasks[$task->section])) $group_ok = true;
					}

					if($permissions['flag'] == 'system_administrator') {
						$permissions['tasks'][$section][] = $task->name;
						$debug->_("n", "PFuser::LoadPermissions - Permission granted: $section => ".$task->name);
					}
                    else {
                        if($score_ok === true && $flag_ok === true && $tag_ok === true && $group_ok === true && $ws_ok === true) {
						    $permissions['tasks'][$section][] = $task->name;
						    $debug->_("n", "PFuser::LoadPermissions - Permission granted: $section => ".$task->name);
					    }
					    else {
					        $debug_msg = "PFuser::LoadPermissions - Permission denied (";
					        $reasons = array();
					        if(!$score_ok) $reasons[] = 'score';
					        if(!$flag_ok)  $reasons[] = 'flag';
					        if(!$tag_ok)   $reasons[] = 'tag';
					        if(!$group_ok) $reasons[] = 'group';
					        if(!$ws_ok)    $reasons[] = 'ws';
                            $reasons = implode(',',$reasons);
                            $debug_msg .= $reasons."): $section => ".$task->name;
                            $debug->_("v", $debug_msg);
                        }
                    }
				}
			}
		}

		unset($db,$config,$debug,$core,$this_sections,$this_tasks);
        return array($permissions, $permission_objects);
    }

    public function SetProfile($param, $value, $user_id = 0)
    {
        if(!$user_id) $user_id = $this->id;

        if(!$user_id) {
            // Not logged in, fall back to session
            $app = &JFactory::getApplication();
            $app->setUserState('com_projectfork.profile.'.$param, $value);

            return true;
        }

        $db  = PFdatabase::GetInstance();
        $update = false;

        if($user_id != $this->id) {
            $query = "SELECT COUNT(id) FROM #__pf_user_profile"
                   . "\n WHERE parameter = ".$db->Quote($param)
				   . "\n AND user_id = ".$db->Quote($user_id);
				   $db->setQuery($query);
			       $count = (int) $db->loadResult();

            $update = ($count == 0) ? false : true;
        }
        else {
            $update = array_key_exists($param, $this->profile);
        }

        if(!$update) {
            $query = "INSERT INTO #__pf_user_profile"
                   . "\n VALUES (NULL, ".$db->Quote($user_id)
                   . "\n , ".$db->Quote($param)
                   . "\n , ".$db->Quote($value).")";
	               $db->setQuery($query);
	               $db->query();
        }
        else {
            $query = "UPDATE #__pf_user_profile"
                   . "\n SET `content` = ".$db->Quote($value)
                   . "\n WHERE user_id = ".$db->Quote($user_id)
                   . "\n AND parameter = ".$db->Quote($param);
	               $db->setQuery($query);
	               $db->query();
        }

        if($user_id == $this->id) $this->profile[$param] = $value;

        unset($debug, $db);
        return true;
    }

	public function GetProfile($param, $alt = false, $set = false, $uid = 0)
	{
	    if($uid != 0 && $uid != $this->id) {
            $db = PFdatabase::GetInstance();

            $query = "SELECT `content` FROM #__pf_user_profile"
                   . "\n WHERE user_id = '$uid'"
                   . "\n AND `parameter` = ".$db->Quote($param);
                   $db->setQuery($query);
                   $result = $db->loadResult();

            unset($db);
            if(is_null($result) && $set == true) $this->SetProfile($param, $alt, $uid);
            return (is_null($result)) ? $alt : $result;
        }
        else {
            if($this->id) {
                if(array_key_exists($param, $this->profile)) return $this->profile[$param];
            }
            else {
                // Not logged in, fall back to user session
                $app = &JFactory::getApplication();
                $sess_value = $app->getUserState('com_projectfork.profile.'.$param);

                if(is_null($sess_value)) {
                    $sess_value = $alt;
                }
                else {
                    return $sess_value;
                }
            }
            if($set) $this->SetProfile($param, $alt);
        }
		return $alt;
	}

	public function Permission($key = NULL)
	{
        if(is_null($key)) return $this->permissions;
		if(array_key_exists($key, $this->permissions)) return $this->permissions[$key];

		return NULL;
    }

	public function Access($task = NULL, $section = NULL, $id = 0, $ignore_ws = false)
	{
	    static $cache = array();
	    static $current_section = NULL;
        static $project_tasks   = NULL;

 	    $sa = 'system_administrator';
 	    $pa = 'project_administrator';

	    if(is_null($current_section)) {
            $core  = PFcore::GetInstance();
            $tasks = $core->GetTasks();

            $current_section = $core->GetSection();
            $project_tasks   = $tasks['projects'];

            unset($core, $tasks);
        }

		if(!$section) $section = $current_section;

        $key = $task.$section;
		if($id) $key = $task.$section.$id;

		$debug = PFdebug::GetInstance();

		// Check cache
		if(array_key_exists($key, $cache)) {
			$debug->_("n", "PFuser::Access - Returning result from access cache '$key' = ".strval($cache[$key]));
			unset($debug);
			return $cache[$key];
		}

		// Check section
		$sections = $this->Permission('sections');
		if(!in_array($section, $sections)) {
			$object = $this->permission_objects['sections'][$section];
			if($object->tags != '-p') {
				$debug->_("v", "PFuser::Access - Invalid section '$section'");
				$cache[$key] = false;
				$debug->_("n", "PFuser::Access - Adding to access cache: '$key' = false");
				unset($debug);
			    return false;
			}
		}

		// Check task
		$tasks = $this->Permission('tasks');
		if($task) {
			if(!array_key_exists($section, $tasks)) $tasks[$section] = array();
			if(!is_array($tasks[$section])) $tasks[$section] = array();

			if(!in_array($task, $tasks[$section])) {
				$debug->_("v", "PFuser::Access - Invalid task: $section => $task");
			    $cache[$key] = false;
			    unset($debug);
			    return false;
			}
			else {
			    $object = $this->permission_objects['tasks'][$section][$task];
				if($object->tags != '') {
					if($object->tags == '-ws' && $this->permissions['workspace'] == 0 && !$ignore_ws) {
			            $debug->_("v", "PFuser::Access - Invalid task: $section => $task");
			            $cache[$key] = false;
			            unset($debug);
			            return false;
		            }
		            if($object->tags == '-a') {
                        if($id != $this->id && $id != 0) {
                            if($this->permissions['flag'] != $pa ||
                                ($section == 'projects' && array_key_exists($task, $project_tasks) && $this->permissions['flag'] == $pa)
                            ) {
                                if($this->permissions['flag'] != $sa) {
                                    $debug->_("v", "PFuser::Access - Invalid task: $section => $task");
			                        $cache[$key] = false;
			                        unset($debug);
			                        return false;
                                } // System admin
                            } // Project admin
                        } // Is author
                    } // Must be author
				} // Has special condition
			} // Has group permission
		} // Task exists

		$cache[$key] = true;
		$debug->_("n", "PFuser::Access - Adding to cache '$key' = 1");
		unset($debug);
		return true;
    }

    public function GetWorkspace()
    {
        return $this->permissions['workspace'];
    }

    public function GetAccessLvl()
    {
        return $this->permissions['accesslevel'];
    }

    public function GetFlag()
    {
        return $this->permissions['flag'];
    }

    public function GetScore()
    {
        return $this->permissions['score'];
    }

    public function GetId()
    {
        return $this->id;
    }

    public function GetGid()
    {
        return $this->gid;
    }

    public function GetName()
    {
        return $this->name;
    }

    public function GetUsername()
    {
        return $this->username;
    }

    public function GetType()
    {
        return $this->usertype;
    }

    public function GetEmail()
    {
        return $this->email;
    }
}
?>