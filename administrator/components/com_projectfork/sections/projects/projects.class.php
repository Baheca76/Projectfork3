<?php
/**
* $Id: projects.class.php 930 2013-04-06 02:09:33Z eaxs $
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

class PFprojectsClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

    public function GetAuthor($id)
    {
        $db = PFdatabase::GetInstance();

        $query = "SELECT author FROM #__pf_projects"
               . "\n WHERE id = '$id'";
               $db->setQuery($query);
               $result = (int) $db->loadResult();

        return $result;
    }


	function SaveLogo($name, $task = 'save')
	{
	    $logo   = JRequest::getVar( $name, '', 'files');
	    $config = PFconfig::GetInstance();

	    jimport('joomla.filesystem.file');

		$folder   = JPath::clean(JPATH_ROOT.DS.$config->Get('logo_save_path', 'projects'));
		$aname    = $logo['name'];
		$asafe    = str_replace(' ', '_', strtolower(JFile::makeSafe($aname)));
		$filepath = $folder .DS.$asafe;

		// Delete previous image when updating
		if($task == 'update') {
			$id = (int) JRequest::getVar('id');
			$this->DeleteLogo($id);
		}

		// Get max width and height from settings
		$maxw = (int) $config->Get('max_w', 'projects');
		$maxh = (int) $config->Get('max_h', 'projects');

		// resize image
		$imagesize = getimagesize($logo['tmp_name']);
        $imgw      = $imagesize[0];
        $imgh      = $imagesize[1];
        $ratiow    = $maxw / $imgw;
        $ratioh    = $maxh / $imgh;

        if(!$imgw || !$imgh) {
            return false;
        }
        // calculate new size
        if($ratiow < $ratioh)  {
            $newh = $imgh * $ratiow;
            $neww = $maxw;
        }
        elseif($ratioh < $ratiow) {
            $neww = $imgw * $ratioh;
            $newh = $maxh;
        }
        elseif($ratiow == $ratioh) {
            $neww = $maxw;
            $newh = $maxh;
        }

        // create a new image
        $new_logo = imagecreatetruecolor($neww, $newh);
		$image    = false;

		// check folder and set permissions
		if(!JFolder::exists($folder)) {
			JFolder::create($folder, 0777);
		}
		else {
			JPath::setPermissions($folder, '0644', '0777');
		}

		// upload image
		if(stristr($logo['name'],'.jpg') || stristr($logo['name'], '.jpeg')) {
			$source = ImageCreateFromJpeg($logo['tmp_name']);
			imagecopyresized($new_logo, $source, 0, 0, 0, 0, $neww, $newh, $imgw, $imgh);
			$image = ImageJpeg($new_logo, $filepath, 100);
		}
		if(stristr($logo['name'], '.png')) {
			$source   = ImageCreateFromPng($logo['tmp_name']);
			imagealphablending($source, true);
            imagesavealpha($source, true);
			$background = imagecolorallocate($new_logo, 0, 0, 0);
            ImageColorTransparent($new_logo, $background);
            imagealphablending($new_logo, false);
            imagesavealpha($new_logo, true);
			imagecopyresized($new_logo, $source, 0, 0, 0, 0, $neww, $newh, $imgw, $imgh);
			$image = ImagePng($new_logo, $filepath, 0);
		}
		if(stristr($logo['name'], 'gif')) {
			$source = ImageCreateFromGif($logo['tmp_name']);
			imagecopyresized($new_logo, $source, 0, 0, 0, 0, $neww, $newh, $imgw, $imgh);
			$image = ImageGif($new_logo, $filepath, 100);
		}

		// something went wrong...
		if(!$image) {
			return false;
		}

		// set directory permissions
		JPath::setPermissions($folder, '0644', '0755');

		return true;
	}

	public function DeleteLogo($id)
	{
	    $db     = PFdatabase::GetInstance();
	    $config = PFconfig::GetInstance();

		$query = "SELECT logo FROM #__pf_projects WHERE id = '$id'";
		       $db->setQuery($query);
		       $logo = $db->loadResult();

        $query = "UPDATE #__pf_projects SET logo = '' WHERE id = '$id'";
		       $db->setQuery($query);
		       $db->query();

		if($logo) {
			jimport('joomla.filesystem.file');
			$upath = $config->Get('logo_save_path', 'projects');
			$upath = str_replace('\\', DS, $upath);
			$upath = str_replace('/', DS, $upath);
			if(substr($upath, 0, 1) == DS) $upath = substr($upath, 0);
			if(substr($upath, -1, 0) == DS) $upath = substr($upath, 0);
			$p = JPath::clean(JPATH_ROOT.DS.$config->Get('logo_save_path', 'projects'));
			$f = $p.DS.$logo;

			if(!file_exists($f)) return false;
			if(!JFile::delete($f)) return false;
		}

		return true;
	}

	/**
	 * Counts the total amount of projects
	 *
	 * @param    string    $keyword    Search keyword
	 * @param    int       $status     Project status code
	 * @param    string    $cat        Project category
	 * @return   int       Number of projects found
	 **/
    public function Count($keyword = '', $status = 0, $cat = '')
    {
        $db = PFdatabase::GetInstance();
    	$filter = "";
    	$syntax = "WHERE";

    	// Filter - Keyword
    	if($keyword) {
            $filter .= "\n $syntax title LIKE ".$db->Quote('%'.$db->getEscaped($keyword, true).'%', false);
            $syntax = "AND";
        }
        // Filter - Category
        if($cat) {
            $filter .= "\n $syntax category = ".$db->Quote($cat);
            $syntax = "AND";
        }
    	// Filter - Project status code
    	switch ($status)
    	{
    		case 0: $filter .= "\n $syntax approved = '1' AND archived = '0'"; break;
    		case 1: $filter .= "\n $syntax approved = '1' AND archived = '1'"; break;
    		case 2: $filter .= "\n $syntax approved = '0'"; break;
    	}

    	// Do the query
    	$query = "SELECT COUNT(id) FROM #__pf_projects"
    	       . $filter;
    	       $db->setQuery($query);
    	       $result = (int) $db->loadResult();

    	if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		unset($db);
    		return NULL;
    	}

    	unset($db);
    	return $result;
    }

    /**
	 * Load a single project row
	 *
	 * @param    int    $id    Project id
	 * @return   object    Project object
	 **/
    public function Load($id)
    {
        $db = PFdatabase::GetInstance();

    	// Load project record
    	$query = "SELECT * FROM #__pf_projects"
               . "\n WHERE id = '$id'";
    	       $db->setQuery($query);
    	       $row = $db->loadObject();

        // Log any errors
    	if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		unset($db);
    		return NULL;
    	}

    	// Load project groups
    	if(!is_null($row)) {
    		$query = "SELECT id FROM #__pf_groups"
                   . "\n WHERE project = '$id'";
    		       $db->setQuery($query);
    		       $groups = $db->loadResultArray();

    		// Log any errors
        	if($db->getErrorMsg()) {
        		$this->AddError($db->getErrorMsg());
        		unset($db);
        		return NULL;
        	}

    	    if(!is_array($groups)) $groups = array();
    		$row->groups = $groups;
    		unset($groups);

    		// Load project members
        	$query = "SELECT u.id, u.name, u.username FROM #__users AS u"
        	       . "\n RIGHT JOIN #__pf_project_members AS pm ON (pm.user_id = u.id AND pm.project_id = '$id')"
        	       . "\n WHERE pm.project_id = '$id'"
        	       . "\n AND pm.approved = '1'"
        	       . "\n GROUP BY u.id ORDER BY u.name ASC";
        	       $db->setQuery($query);
        		   $row->users = $db->loadObjectList();

        	if(!is_array($row->users)) $row->users = array();

        	// Log any errors
        	if($db->getErrorMsg()) {
        		$this->AddError($db->getErrorMsg());
        		unset($db);
        		return NULL;
        	}

        	// Load project founder
        	$query = "SELECT id, name, username FROM #__users"
        		   . "\n WHERE id = '$row->author'";
        		   $db->setQuery($query);
        		   $row->founder = $db->loadObject();
    	}
    	else {
    		$row->groups  = array();
    		$row->users   = array();
    		$row->founder = NULL;
    	}

    	return $row;
    }

    /**
	 * Loads a list of projects
	 *
	 * @param    int       $ls          Query limit start
	 * @param    int       $l           Query limit
	 * @param    string    $keyword     Search keyword
	 * @param    string    $ob          Query sort-by
	 * @param    string    $od          Query sort-direction
	 * @param    int       $status      Project status code
	 * @param    string    $cat         Project category
	 * @return   array     Project list
	 **/
    public function LoadList($ls = 0, $l = 50, $keyword = '', $ob = 'id', $od = 'ASC', $status = 0, $cat = '')
    {
        $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();

        $db   = PFdatabase::GetInstance();
        $uid  = $user->GetId();
        $flag = $user->GetFlag();
    	$filter = "";
    	$syntax = "WHERE";

    	// Filter - Keyword
    	if($keyword) {
            $filter .= "\n $syntax p.title LIKE ".$db->Quote('%'.$db->getEscaped($keyword, true).'%', false);
    		$limitstart = 0;
    		$syntax = "AND";
    	}
    	// Filter - Public if not logged in
    	if(!$uid) {
            $filter .= "\n $syntax p.is_public = '1'";
            $syntax = "AND";
        }
        // Filter - Category
    	if($cat) {
            $filter .= "\n $syntax p.category = ".$db->Quote($cat);
            $syntax = "AND";
        }
    	// Filter - Project status code
    	switch ($status)
    	{
    		case 0:
    		default:
    			$filter .= "\n $syntax p.approved = '1' AND p.archived = '0'";
    			// Filter out projects
    			if($flag != 'system_administrator') {
    				$projects     = $user->Permission('projects');
    				$imp_projects = implode(',',$projects);

    				if($imp_projects != '') {
    				    if(strlen($imp_projects) > 1) {
                            $filter .= "\n AND (p.id IN(".$imp_projects.") OR p.is_public = '1')";
                        }
    					else {
                            $filter .= "\n AND (p.id = '$imp_projects' OR p.is_public = '1')";
                        }
    				}
    				else {
    					$filter .= "\n AND p.is_public = '1'";
    				}
    			}
    			break;

    		case 1:
    			if($user->Access('task_approve', 'projects')) {
    				$filter .= "\n $syntax p.approved = '1' AND p.archived = '1'";
    			}
    			else {
    				$filter .= "\n $syntax p.approved = '1' AND p.archived = '0'";
    			}
    			break;

    		case 2:
    			if($user->Access('task_approve', 'projects')) {
    			    $filter .= "\n $syntax p.approved = '0'";
    			}
    			else {
    				$filter .= "\n $syntax p.approved = '1' AND p.archived = '0'";
    			}
    			break;
    	}

    	// Set order by category?
    	if($config->Get('use_cats', 'projects')) $ob = "p.category,".$ob;

    	// Do the query
    	$query = "SELECT p.*, u.name"
               . "\n FROM #__pf_projects AS p"
    	       . "\n RIGHT JOIN #__users AS u ON u.id = p.author"
    	       . $filter
    	       . "\n GROUP BY p.id"
    	       . "\n ORDER BY $ob $od"
    	       . (($l > 0) ? "\n LIMIT $ls, $l" : "\n");
    	       $db->setQuery($query);
    	       $rows = $db->loadObjectList();

        // Check for db error
    	if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            unset($user,$db);
    		return array();
    	}

    	// Make sure we return an array
    	if(!is_array($rows)) $rows = array();

        // Unset objects
        unset($user,$db);

    	return $rows;
    }

	/**
	 * Validates form input when creating/updating a project
	 *
	 * @param    string     $title      Project title
	 * @return   boolean    $success    True when no error, otherwise False
	 **/
	public function Validate($title = NULL, $id = 0)
	{
	    $success = true;
	    $db      = PFdatabase::GetInstance();
	    $title   = $db->Quote(trim($title));

	    if(!$title) {
            $success = false;
            $this->AddError('V_TITLE');
        }

        // Check for existing project title
    	$query = "SELECT id FROM #__pf_projects WHERE LOWER(title) = ".strtolower($title)
    	       . (($id > 0) ? "\n AND id != '$id'" : "\n");
    	       $db->setQuery($query);
    	       $tmp_id = $db->loadResult();

    	if($tmp_id) {
    	    $success = false;
    		$this->AddError('PROJECT_EXISTS');
    	}

        unset($db);
		return $success;
	}

    public function Save()
    {
        // Setup objects
        $db     = PFdatabase::GetInstance();
        $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();

        // Other vars
    	$success = true;
    	$now     = time();

    	// Capture user input
    	$title = JRequest::getVar('title');
    	$desc  = JRequest::getVar('text');
    	$color = JRequest::getVar('color');
    	$pub   = JRequest::getVar('is_public');
    	$reg   = JRequest::getVar('allow_register');
    	$web   = JRequest::getVar('website');
    	$email = JRequest::getVar('email');
    	$edate = JRequest::getVar('edate');
    	$hour  = JRequest::getVar('hour');
    	$min   = JRequest::getVar('min');
    	$cat   = JRequest::getVar('cat');

    	$title   = $db->Quote(trim(JRequest::getVar('title')));
    	$title2  = trim(JRequest::getVar('title'));
    	$color   = $db->Quote(JRequest::getVar('color'));
    	$logo    = JRequest::getVar('logo', '', 'files');
    	$has_end = (int) JRequest::getVar('has_deadline');
    	$public  = $db->Quote((int) JRequest::getVar('is_public'));
    	$reg     = $db->Quote((int) JRequest::getVar('allow_register'));
    	$web     = JRequest::getVar('website');
    	$email   = $db->Quote(JRequest::getVar('email'));
    	$edate   = JRequest::getVar('edate');
    	$hour    = (int) JRequest::getVar('hour');
		$min     = (int) JRequest::getVar('minute');
		$ampm    = (int) JRequest::getVar('ampm');
		$edate   = PFformat::ToTime($edate, $hour, $min, $ampm);

    	$flag    = $user->GetFlag('flag');
    	$invite  = JRequest::getVar('invite');
        $member  = JRequest::getVar('member', array());

        // Get config settings
        $invite_select = (int) $config->Get('invite_select', 'projects');
    	$approve       = (int) $config->Get('approve_new', 'projects');
    	$allow_c       = (int) $config->Get('allow_color', 'projects');
    	$allow_l       = (int) $config->Get('allow_logo', 'projects');
    	$use_cats      = (int) $config->Get('use_cats', 'projects');

    	// Set approved status
    	$approved = 1;
    	if($approve && $flag != 'system_administrator') $approved = 0;

    	// Get Project description
    	$content = $db->Quote(JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW));
    	if(defined('PF_DEMO_MODE')) $content = $db->Quote(JRequest::getVar('text'));
		$content2 = JRequest::getVar('text');

        // Set end date
    	if(!$has_end) $edate = "0";
    	$edate = $db->Quote($edate);

    	// Set color
    	if(!$allow_c) $color = "''";

    	// Set project website
    	if(!stristr($web, 'http://') && $web) $web = 'http://'.$web;
    	$web = $db->Quote($web);

    	// Set category
    	if(!$use_cats) $cat = "";
        $cat = $db->Quote($cat);

    	// Upload logo
    	$qlogo = "''";
    	if(!empty($logo['name']) && $allow_l && !defined('PF_DEMO_MODE')) {
    		if($this->SaveLogo('logo')) {
    		    $aname = $logo['name'];
		        $qlogo = $db->Quote(str_replace(' ', '_', strtolower(JFile::makeSafe($aname))));
    		}
    	}

        // Save the project
    	$query = "INSERT INTO #__pf_projects VALUES(NULL, $title, $content, '".$user->GetId()."', $color, $qlogo,"
    	       . "\n $web, $email, $cat, $public, $reg, '0', '$approved', '$now', $edate)";
    	       $db->setQuery($query);
    	       $db->query();
    	       $id = $db->insertid();

    	if(!$id) {
    		$this->AddError($db->getErrorMsg());
    		return false;
    	}

    	// add author to project member list
    	$query = "INSERT INTO #__pf_project_members VALUES(NULL, '$id', '".$user->GetId()."', '1')";
    	       $db->setQuery($query);
    	       $db->query();

    	if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		return false;
    	}

        $data = array($id);
        PFprocess::Event('save_project', $data);

        // Invite users - From drop down list?
        if($invite_select) {
            $looped = array();
            $invite = array();
            foreach($member AS $m)
            {
                if(!in_array($m, $looped)) {
                    if($m == "0") {
                        continue;
                    }
                    $invite[] = $m;
                    $looped[] = $m;
                }
            }
            $invite = implode(',',$invite);
        }

        // Invite users to the project
        $fj = (int) JRequest::getVar('force_join');

    	if($invite) $this->Invite($invite, $id, $fj);

        // Send approval notification
        if($approve && ($flag != 'system_administrator')) {
            $this->SendNotification($id, 'approve');
            $core = PFcore::GetInstance();
            $core->AddMessage('MSG_PROJECT_PENDING');
        }

    	return $success;
    }

    public function Update($id)
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();
        $user   = PFuser::GetInstance();

    	$success = true;
    	$title   = $db->Quote(JRequest::getVar('title'));
    	$color   = $db->Quote(JRequest::getVar('color'));
    	$public  = $db->Quote((int) JRequest::getVar('is_public'));
    	$reg     = $db->Quote((int) JRequest::getVar('allow_register'));
    	$email   = $db->Quote(JRequest::getVar('email'));
    	$logo    = JRequest::getVar( 'logo', '', 'files');
    	$web     = JRequest::getVar('website');
    	$edate   = JRequest::getVar('edate');
    	$invite  = JRequest::getVar('invite');
    	$member  = JRequest::getVar('member', array());
    	$delete  = (int) JRequest::getVar('delete_logo');
    	$has_end = (int) JRequest::getVar('has_deadline');
    	$hour    = (int) JRequest::getVar('hour');
		$min     = (int) JRequest::getVar('minute');
		$ampm    = (int) JRequest::getVar('ampm');
		$edate   = PFformat::ToTime($edate, $hour, $min, $ampm);
		$cat     = JRequest::getVar('cat');
		$author  = (int) JRequest::getVar('author');

        // Get config settings
        $invite_select = (int) $config->Get('invite_select', 'projects');
        $allow_c = (int) $config->Get('allow_color', 'projects');
    	$allow_l = (int) $config->Get('allow_logo', 'projects');
    	$use_cat = (int) $config->Get('use_cats', 'projects');

    	if(!$id) return false;
    	if(!$allow_c) $color = "''";

    	// search for existing title
    	$query = "SELECT id FROM #__pf_projects WHERE LOWER(title) = ".strtolower($title)
    	       . "\n AND id != '$id'";
    	       $db->setQuery($query);
    	       $tmp_id = $db->loadResult();

    	if($tmp_id) {
    		$this->AddError('PFL_PROJECT_EXISTS');
    		return false;
    	}

    	$content = $db->Quote(JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW));
    	if(defined('PF_DEMO_MODE')) $content = $db->Quote(JRequest::getVar('text'));

    	if(!$has_end) $edate = "0";
    	$edate = $db->Quote($edate);

    	if($delete) $this->DeleteLogo($id);

    	if(!stristr($web, 'http://') && $web) $web = 'http://'.$web;
    	$web = $db->Quote($web);

    	$qlogo = "";
    	if($logo['name'] != '' && $allow_l && !defined('PF_DEMO_MODE')) {
    		if($this->SaveLogo('logo', 'update')) {
    			$aname = $logo['name'];
		        $qlogo = "\n ,logo = ".$db->Quote(str_replace(' ', '_', strtolower(JFile::makeSafe($aname))));
    		}
    	}

    	// Set cat
    	if(!$use_cat) $cat = "";
    	$cat = $db->Quote($cat);

    	// Set project founder
    	$q_author = "";
    	if($user->Permission('flag') == 'system_administrator' || $user->Permission('flag') == 'project_administrator') {
    	    $q_author = ", author = '$author'";
    	}

    	$query = "UPDATE #__pf_projects SET title = $title, content = $content, edate = $edate,"
    	       . "\n color = $color, is_public = $public, allow_register = $reg,"
    	       . "\n website = $web, email = $email, category = $cat".$q_author
    	       . $qlogo
    	       . "\n WHERE id = '$id'";
    	       $db->setQuery($query);
    	       $db->query();

        if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		return false;
    	}

        $data = array($id);
        PFprocess::Event('update_project');

        if($invite_select) {
            $looped = array();

            $invite = array();
            foreach($member AS $m)
            {
                if(!in_array($m, $looped)) {
                    if($m == "0") {
                        continue;
                    }
                    $invite[] = $m;
                    $looped[] = $m;
                }
            }
            $invite = implode(',',$invite);
        }

        $force_join = (int) JRequest::getVar('force_join');
    	if($invite && !defined('PF_DEMO_MODE')) $this->Invite($invite, $id, $force_join);

    	return true;
    }

    public function Delete($cid)
    {
    	jimport('joomla.filesystem.file');

    	$db     = PFdatabase::GetInstance();
    	$config = PFconfig::GetInstance();
    	$cid    = implode(',', $cid);

    	$file_upload_path = $config->Get('upload_path', 'filemanager');

    	// Delete logo files
    	$query = "SELECT id FROM #__pf_projects WHERE id IN($cid)";
    	       $db->setQuery($query);
    	       $ids = $db->loadResultArray();

    	if(is_array($ids)) {
    		foreach ($ids AS $id)
    		{
    			$this->DeleteLogo($id);
    		}
    	}

    	$queries = array("DELETE FROM #__pf_groups WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_tasks WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_projects WHERE id IN($cid)",
    	                 "DELETE FROM #__pf_project_members WHERE project_id IN($cid)",
    	                 "DELETE FROM #__pf_folders WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_notes WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_files WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_topics WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_topic_replies WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_events WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_milestones WHERE project IN($cid)",
    	                 "DELETE FROM #__pf_time_tracking WHERE project_id IN($cid)",
    	                 "DELETE FROM #__pf_user_access_level WHERE project_id IN($cid)");

    	foreach ($queries AS $i => $query)
    	{
    	    // Group queries
    	    if($i == 0) {
                $query2 = "SELECT id FROM #__pf_groups"
                        . "\n WHERE project IN($cid)";
                        $db->setQuery($query2);
                        $gids = $db->loadResultArray();

                if(is_array($gids) && count($gids)) {
                    $gids = implode(',', $gids);

                    $query2 = "DELETE FROM #__pf_group_users"
                            . "\n WHERE group_id IN($gids)";
                            $db->setQuery($query2);
                            $db->query();

                    if($db->getErrorMsg()) {
    		            $this->AddError($db->getErrorMsg());
    		            return false;
    	            }
                }
            }

    		// Task queries
    		if($i == 1) {
    			$query2 = "SELECT id FROM #__pf_tasks WHERE project IN($cid)";
    			       $db->setQuery($query2);
    			       $tasks = $db->loadResultArray();

    			if($db->getErrorMsg()) {
    		        $this->AddError($db->getErrorMsg());
    		        return false;
    	        }

    	        if(!is_array($tasks)) $tasks = array();
    			if(count($tasks) >= 1) {
    				$tasks = implode(',', $tasks);
    				// Delete task attachments
    				$query2 = "DELETE FROM #__pf_task_attachments WHERE task_id IN($tasks)";
    				       $db->setQuery($query2);
    				       $db->query();

    				if($db->getErrorMsg()) {
    		            $this->AddError($db->getErrorMsg());
    		            return false;
    	            }

    				// Delete task comments
    				$query2 = "DELETE FROM #__pf_comments WHERE scope = 'tasks' AND item_id IN($tasks)";
    				       $db->setQuery($query2);
    				       $db->query();

    				if($db->getErrorMsg()) {
    		            $this->AddError($db->getErrorMsg());
    		            return false;
    	            }
    			}
    		}

    		// Folder queries
    		if($i == 4) {
    			$query2 = "SELECT id FROM #__pf_folders WHERE project IN($cid)";
    			        $db->setQuery($query2);
    			        $folders = $db->loadResultArray();

    			if($db->getErrorMsg()) {
    		       $this->AddError($db->getErrorMsg());
    		       return false;
    	        }

    			if(is_array($folders) && @count($folders) > 0) {
    				$folders = implode(',', $folders);
    				// Delete folder references
    				$query2 = "DELETE FROM #__pf_folder_tree WHERE folder_id IN($folders)";
    				       $db->setQuery($query2);
    				       $db->query();

    				if($db->getErrorMsg()) {
    		            $this->AddError($db->getErrorMsg());
    		            return false;
    	            }
    			}
    		}

    		// File queries
    		if($i == 6) {
    			$query2 = "SELECT name, project, prefix FROM #__pf_files WHERE project IN($cid)";
		                $db->setQuery($query2);
		                $files = $db->loadObjectList();

		        if($db->getErrorMsg()) {
    		        $this->AddError($db->getErrorMsg());
    		        return false;
    	        }

		        if(!is_array($files)) $files = array();

		        foreach ($files AS $f)
		        {
		        	$prefix1 = "project_".$f->project;
			        $prefix2 = $f->prefix;

			        $path = JPATH_ROOT.DS.$file_upload_path.DS.$prefix1.DS.$prefix2.strtolower($f->name);
			        if(file_exists($path)) JFile::delete($path);
		        }
    		}

    		// Main queries
    		$db->setQuery($query);
    	    $db->query();

    	    if($db->getErrorMsg()) {
    		    $this->AddError($db->getErrorMsg());
    		    return false;
    	    }
    	}

    	return true;
    }

    public function Copy($cid)
    {
        $db     = PFdatabase::GetInstance();
    	$config = PFconfig::GetInstance();

    	// Get copy settings
		$copy_tasks = (int) $config->Get('copy_tasks', 'projects');
		$copy_ms    = (int) $config->Get('copy_milestones', 'projects');
		$copy_group = (int) $config->get('copy_groups', 'projects');
		$copy_users = (int) $config->Get('copy_users', 'projects');

    	$project_to_copy = $cid;
    	foreach ($cid AS $id)
    	{
    		$id  = (int) $id;
    		$now = time();

    		// Get original project data
    		$query = "SELECT * FROM #__pf_projects"
                   . "\n WHERE id = '$id'";
    		       $db->setQuery($query);
    		       $row = $db->loadObject();

    		if($db->getErrorMsg()) {
    		    $this->AddError($db->getErrorMsg());
    		    return false;
    	    }

    		if(is_null($row)) continue;

    		// Rename project title
            $row->title = PFformat::Lang('COPY_OF')." ".$row->title;

            // Get project - group relations
            $query = "SELECT id FROM #__pf_groups"
                   . "\n WHERE project = '$id'";
                   $db->setQuery($query);
                   $groups = $db->loadResultArray();

            if($db->getErrorMsg()) {
    		    $this->AddError($db->getErrorMsg());
    		    return false;
    	    }

    	    // Create a copy
            $aid = $row->author;

    		$query = "INSERT INTO #__pf_projects VALUES (NULL, ".$db->Quote($row->title).","
    		       . "\n ".$db->Quote($row->content).", ".$db->Quote($row->author).","
    		       . "\n ".$db->Quote($row->color).",".$db->Quote($row->logo).","
    		       . "\n ".$db->Quote($row->website).",".$db->Quote($row->email).",".$db->Quote($row->category).","
    		       . "\n ".$db->Quote($row->is_public).",".$db->Quote($row->allow_register).","
    		       . "\n ".$db->Quote($row->archived).",".$db->Quote($row->approved).","
    		       . "\n ".$db->Quote($now).", ".$db->Quote($row->edate).")";
    		       $db->setQuery($query);
    	           $db->query();
    	           $new_id = $db->insertid();

    	    if($db->getErrorMsg()) {
    		    $this->AddError($db->getErrorMsg());
    		    return false;
    	    }

    	    if(!$new_id) continue;

    	    // Copy tasks?
    	    if($copy_tasks == "1") {
    	    	$query = "SELECT * FROM #__pf_tasks"
                       . "\n WHERE project = '$id'";
    	    	       $db->setQuery($query);
    	    	       $tasks = $db->loadObjectList();

    	    	if($db->getErrorMsg()) {
    		       $this->AddError($db->getErrorMsg());
    		       return false;
    	        }

    	        if(!is_array($tasks)) $tasks = array();
    	        $task_rel = array();

    	        foreach ($tasks AS $row)
    	        {
    	        	if(!$copy_ms) $row->milestone = "0";
    	            $author = ($copy_users == 1) ? $row->author : $aid;

    	        	$query = "INSERT INTO #__pf_tasks VALUES (NULL, ".$db->quote($row->title)
                           . "\n, ".$db->quote($row->content).",".$db->quote($author)
                           . "\n, ".$db->quote($new_id).", ".$db->quote($now).", '0', "
		                   . "\n ".$db->quote($now).", ".$db->quote($row->edate).", ".$db->quote($row->progress)
                           . "\n, ". "\n ".$db->quote($row->priority).",'$row->milestone', '$row->ordering'"
		                   . "\n )";
		                   $db->setQuery($query);
		                   $db->query();
		                   $tid = $db->insertid();

		            if($db->getErrorMsg()) {
    		            $this->AddError($db->getErrorMsg());
    		            return false;
    	            }

		            $task_rel[] = $tid;

                    if($copy_users) {
                        // Assign users back to new tasks
                        $query = "SELECT task_id, user_id FROM #__pf_task_users"
                               . "\n WHERE task_id = ".$db->quote($row->id);
                               $db->SetQuery($query);
                               $tus = $db->loadObjectList();

                        if(is_array($tus)) {
                            foreach($tus AS $tu)
                            {
                                $query = "INSERT INTO #__pf_task_users"
                                       . "\n VALUES(NULL,$tid,$tu->user_id,0,0)";
                                       $db->setQuery($query);
                                       $db->query();
                            }
                        }
                    }
    	        }

                $tmp_tasks = implode(',',$task_rel);
    	    }

    	    // Copy milestones?
    	    if($copy_ms) {
    	    	$query = "SELECT * FROM #__pf_milestones"
                       . "\n WHERE project = $id";
    	    	       $db->setQuery($query);
    	    	       $milestones = $db->loadObjectList();

    	    	if($db->getErrorMsg()) $this->AddError($db->getErrorMsg());
    	        if(!is_array($milestones)) $milestones = array();

    	        foreach ($milestones AS $row)
    	        {
    	            $author = ($copy_users == 1) ? $row->author : $aid;

    	        	$query = "INSERT INTO #__pf_milestones VALUES (NULL,".$db->Quote($row->title).","
    	        	       . "\n ".$db->Quote($row->content).",".$db->Quote($new_id).","
    	        	       . "\n ".$db->Quote($row->priority).",".$db->Quote($author).","
    	        	       . "\n ".$db->Quote($now).", ".$db->Quote($row->edate).", ".$db->Quote($row->ordering).")";
		                   $db->setQuery($query);
		                   $db->query();
		                   $mid = $db->insertid();

		            if($db->getErrorMsg()) {
    		            $this->AddError($db->getErrorMsg());
    		            return false;
    	            }

    	            // Sync with tasks
    	            if(count($task_rel)) {
    	            	$query = "UPDATE #__pf_tasks SET milestone = '$mid'"
                               . "\n WHERE id IN(".implode(',',$task_rel).")"
    	            	       . "\n AND milestone = '$row->id'";
    	            	       $db->setQuery($query);
    	            	       $db->query();

    	            	if($db->getErrorMsg()) {
    		                $this->AddError($db->getErrorMsg());
    		                return false;
    	                }
    	            }
    	        }
    	    }

    	    // Copy groups?
    	    $group_rel = array();
    	    $acl_rel   = array();

    	    if($copy_group) {
    	    	$query = "SELECT * FROM #__pf_groups"
                       . "\n WHERE project = '$id'";
    	    	       $db->setQuery($query);
    	    	       $groups = $db->loadObjectList();

    	    	if($db->getErrorMsg()) {
    		        $this->AddError($db->getErrorMsg());
    		        return false;
    	        }

    	        if(!is_array($groups)) $groups = array();

    	        foreach ($groups AS $row)
    	        {
    	        	$query = "INSERT INTO #__pf_groups VALUES"
                           . "\n (NULL, ".$db->Quote($row->title)
                           . "\n ,".$db->Quote($row->description).", '$new_id', "
                           . "\n ".$db->Quote($row->permissions).")";
    	        	       $db->setQuery($query);
    	        	       $db->query();
    	        	       $gid = $db->insertid();

    	        	if($db->getErrorMsg()) {
    		            $this->AddError($db->getErrorMsg());
    		            return false;
    	            }

    	            $group_rel[$row->id] = $gid;
    	        }

    	        // Copy project access levels
    	        $query = "SELECT id, title, score, flag FROM #__pf_access_levels"
                       . "\n WHERE project = '$id'";
                       $db->setQuery($query);
                       $acls = $db->loadObjectList();

                if(!is_array($acls)) $acls = array();

                foreach($acls AS $acl)
                {
                    $query = "INSERT INTO #__pf_access_levels VALUES("
                           . "\n NULL, ".$db->Quote($acl->title).","
                           . "\n ".$db->Quote($acl->score).","
                           . "\n ".$db->Quote($new_id).", ".$db->Quote($acl->flag)
                           . "\n )";
                           $db->setQuery($query);
                           $db->query();

                     $acl_id = $db->insertid();

                     if($db->getErrorMsg()) {
                         $this->AddError($db->getErrorMsg());
		                 return false;
                     }

                     $acl_rel[$acl->id] = $acl_id;
                }
    	    }

    	    // Copy group members?
    	    if($copy_users) {
    	    	$query = "SELECT * FROM #__pf_project_members"
                       . "\n WHERE project_id = '$id'";
    	    	       $db->setQuery($query);
    	    	       $users = $db->loadObjectList();

    	    	if($db->getErrorMsg()) {
    		        $this->AddError($db->getErrorMsg());
    		        return false;
    	        }

    	        $looped = array();

    	        foreach ($users AS $row)
    	        {
    	            if(in_array($row->user_id, $looped)) continue;
    	            $looped[] = $row->user_id;

    	        	$query = "INSERT INTO #__pf_project_members VALUES"
                           . "\n (NULL, '$new_id', '$row->user_id', '$row->approved')";
    	        	       $db->setQuery($query);
    	        	       $db->query();

    	        	if($db->getErrorMsg()) {
    		            $this->AddError($db->getErrorMsg());
    		            return false;
    	            }
    	        }

    	        // Copy user group assignment
    	        if(count($group_rel)) {
    	        	foreach ($group_rel AS $old_gid => $new_gid)
    	        	{
    	        		$query = "SELECT user_id FROM #__pf_group_users"
                               . "\n WHERE group_id = '$old_gid'";
    	        		       $db->setQuery($query);
    	        		       $users = $db->loadResultArray();

    	        		if($db->getErrorMsg()) {
    		                $this->AddError($db->getErrorMsg());
    		                return false;
    	                }

    	        		if(is_array($users)) {
    	        		    $looped = array();
    	        			foreach ($users AS $uid)
    	        			{
    	        			    if(in_array($uid, $looped)) continue;
    	                        $looped[] = $uid;

    	        				$query = "INSERT INTO #__pf_group_users"
                                       . "\n VALUES(NULL, '$new_gid', '$uid')";
    	        				       $db->setQuery($query);
    	        				       $db->query();

    	        				if($db->getErrorMsg()) {
    		                        $this->AddError($db->getErrorMsg());
    		                        return false;
    	                        }
    	        			}
    	        		}
    	        	}
    	        }

    	        // Copy user access levels
    	        if(count($acl_rel)) {
                    foreach ($acl_rel AS $old_aid => $new_aid)
    	        	{
    	        		$query = "SELECT user_id FROM #__pf_user_access_level"
                               . "\n WHERE accesslvl = '$old_aid'";
    	        		       $db->setQuery($query);
    	        		       $users = $db->loadResultArray();

    	        		if($db->getErrorMsg()) {
    		                $this->AddError($db->getErrorMsg());
    		                return false;
    	                }

    	        		if(is_array($users)) {
    	        		    $looped = array();
    	        			foreach ($users AS $uid)
    	        			{
    	        			    if(in_array($uid, $looped)) continue;
    	                        $looped[] = $uid;

    	        				$query = "INSERT INTO #__pf_user_access_level"
                                       . "\n VALUES(NULL, '$new_aid', '$uid', '$id')";
    	        				       $db->setQuery($query);
    	        				       $db->query();

    	        				if($db->getErrorMsg()) {
    		                        $this->AddError($db->getErrorMsg());
    		                        return false;
    	                        }
    	        			}
    	        		}
    	        	}
                }
                // End copy users
    	    }
    	}

        // Call process event
        PFprocess::Event('copy_projects', $project_to_copy);

        return true;
    }

    public function SendJoinRequest($project, $user_id)
    {
        $db = PFdatabase::GetInstance();

        // Add to project as unapproved user
    	$query = "INSERT INTO #__pf_project_members VALUES(NULL, $project, $user_id, 0)";
    	       $db->setQuery($query);
    	       $db->query();

    	if($db->getErrorMsg()) {
    	    $this->AddError($db->getErrorMsg());
    		return false;
    	}

        $this->SendNotification($project, 'join_request', $user_id);
        return true;
    }

    public function Invite($users, $project, $force_join = 0)
    {
    	global $mainframe;

    	$my      = PFuser::GetInstance();
    	$db      = PFdatabase::GetInstance();
    	$config  = PFconfig::GetInstance();
    	$jconfig = JFactory::getConfig();
    	$flag    = $my->GetFlag();

        $ehtml  = (int) $config->Get('html_emails');

        // Do not allow force join as non-admin
    	if($flag != 'system_administrator') $force_join = 0;

    	// Get project title
    	$query = "SELECT title FROM #__pf_projects WHERE id = '$project'";
    	       $db->setQuery($query);
    	       $project_title = $db->loadResult();

    	$users = explode(',', $users);

    	foreach ($users AS $uid)
    	{
    	    // Create invitation ID
    		$uid  = strtolower(trim($uid));
    		$hash = md5($uid.$project.$project_title.time());

    		if(!$uid) continue;

    		// Setup links
    		$com = PFcomponent::GetInstance();
            $location = $com->Get('location');
            $com->Set('location', 'frontend');
    	    $accept_link  = PFformat::Link("workspace=0&section=projects&task=accept_invite&iid=".$hash, true, false, true);
    	    $decline_link = PFformat::Link("workspace=0&section=projects&task=decline_invite&iid=".$hash, true, false, true);
    		$com->Set('location', $location);

    		// We have an email address
    		if(strstr($uid, '@')) {
    			$query = "SELECT id, name, email FROM #__users"
    			       . "\n WHERE LOWER(email) = ".$db->Quote($uid)
    			       . "\n AND block = '0'";
    			       $db->setQuery($query);
    			       $user = $db->loadObject();
    		}
    		else {
    			// We have a username
    			$query = "SELECT id, name, email FROM #__users"
    			       . "\n WHERE LOWER(username) = ".$db->Quote($uid)
    			       . "\n AND block = '0'";
    			       $db->setQuery($query);
    			       $user = $db->loadObject();

                // Nothing found, try name
    			if(!is_object($user)) {
    				$query = "SELECT id, name, email FROM #__users"
    			           . "\n WHERE LOWER(name) = ".$db->Quote($uid)
    			           . "\n AND block = '0'";
    			           $db->setQuery($query);
    			           $user = $db->loadObject();
    			}

    			// Still not found
    			if(!is_object($user)) {
    				$this->AddError( str_replace('{username}', $uid, PFformat::Lang('MSG_INVITE_E_USERNAME') ) );
    				continue;
    			}
    		}

            // Add to project if force join
	        if($force_join && is_object($user)) {
	        	$query = "INSERT INTO #__pf_project_members VALUES(NULL, '$project', '$user->id', '1')";
	        	       $db->setQuery($query);
	        	       $db->query();
	        }

            // No force join? Send invitation
            if(!$force_join) {
                // Save invitation
    		    $query = "INSERT INTO #__pf_project_invitations VALUES(NULL, '$project', '$hash')";
    		           $db->setQuery($query);
    		           $db->query();
            }

            // Append user email/user id to decline link
            if(is_object($user) && !$force_join) $decline_link .= "&email=$user->email";
            if(!is_object($user)) $decline_link .= "&email=$uid";


    	    // Send out notification email
            if(!defined('PF_DEMO_MODE') && class_exists('PFprojectsmailer') == true) {
                $mail = new PFprojectsmailer();
                $mail->SetId($project);
                $mail->SetType('invite');

                // Set subject
                if(is_object($user) && $force_join) {
                    $subject = str_replace('{project}', $project_title, PFformat::Lang('EM_PROJECT_SUBJECT_FJ'));
                }
                else {
                    $subject = str_replace('{project}', $project_title, PFformat::Lang('EM_PROJECT_SUBJECT_INV'));
                }

                // Setup body
                if(is_object($user) && $force_join) {
                    $message = ($ehtml == 1) ? 'EM_PROJECT_BODY_FJHTML' : 'EM_PROJECT_BODY_FJPLAIN';
                    $message = PFformat::Lang($message);
                }
                else {
                    $message = ($ehtml == 1) ? 'EM_PROJECT_BODY_INVHTML' : 'EM_PROJECT_BODY_INVPLAIN';
                    $message = PFformat::Lang($message);
                }

                // Replace body vars
                if(is_object($user)) {
                    $message = str_replace('{name}', $user->name, $message);
                }
                else {
                    $message = str_replace('{name}', '', $message);
                }

                $message = str_replace('{author}', $my->GetName(), $message);
                $message = str_replace('{project}', $project_title, $message);
                $message = str_replace('{acceptlink}', $accept_link, $message);
                $message = str_replace('{declinelink}', $decline_link, $message);

                // Send the invitation
                $mail->SetSubject( $subject );
                $mail->SetBody( $message );

                if(is_object($user)) {
                    $mail->SetRecipients( $user->email );
                }
                else {
                    $mail->SetRecipients( $uid );
                }

                $mail->Send();
                unset($mail);
		    }
    	}
    }

    public function Archive($cid)
    {
        $db   = PFdatabase::GetInstance();
        $user = PFuser::GetInstance();

        $workspace = $user->GetWorkspace();

    	// Reset workspace if needed
    	if(in_array($ws, $cid)) {
    	    $user->SetProfile('workspace', 0);
    	    JRequest::setVar('workspace', 0);
    	}

    	$cid = implode(',', $cid);

    	// Reset workspace for all users
    	$query = "UPDATE #__pf_user_profile"
               . "\n SET content = '0'"
               . "\n WHERE parameter = 'workspace'"
               . "\n AND content IN($cid)";
    	       $db->setQuery($query);
    	       $db->query();

    	if($db->getErrorMsg()) {
    	    $this->AddError($db->getErrorMsg());
    		return false;
    	}

    	// Set projects to archived
    	$query = "UPDATE #__pf_projects SET archived = '1'"
               . "\n WHERE id IN($cid)";
    	       $db->setQuery($query);
    	       $db->query();

    	if($db->getErrorMsg()) {
    	    $this->AddError($db->getErrorMsg());
    		return false;
    	}

        // Call archive process event
        $data = array($cid);
        PFprocess::Event('archive_projects', $data);

    	return true;
    }

    public function Activate($cid)
    {
    	$db  = PFdatabase::GetInstance();
    	$cid = implode(',', $cid);

    	$query = "UPDATE #__pf_projects SET archived = '0'"
               . "\n WHERE id IN($cid)";
    	       $db->setQuery($query);
    	       $db->query();

    	if($db->getErrorMsg()) {
    	    $this->AddError($db->getErrorMsg());
    		return false;
    	}

        // Call activate process event
        $data = array($cid);
        PFprocess::Event('activate_projects', $data);

    	return true;
    }

    public function Approve($cid)
    {
    	global $mainframe;

    	$db  = PFdatabase::GetInstance();
    	$cid = implode(',', $cid);

    	$query = "UPDATE #__pf_projects SET approved = '1'"
               . "\n WHERE id IN($cid)";
    	       $db->setQuery($query);
    	       $db->query();

    	if($db->getErrorMsg()) {
    	    $this->AddError($db->getErrorMsg());
    		return false;
    	}

    	// Send approval notification
    	$query = "SELECT u.*, p.title FROM #__users AS u"
    	       . "\n RIGHT JOIN #__pf_projects AS p ON p.author = u.id"
    	       . "\n WHERE p.id IN($cid)"
    	       . "\n GROUP BY p.id";
    	       $db->setQuery($query);
    	       $rows = $db->loadObjectList();

    	if(!is_array($rows)) $rows = array();

    	if($db->getErrorMsg()) {
    	    $this->AdError($db->getErrorMsg());
    		return false;
    	}

        $cid = explode(',', $cid);

        foreach($cid AS $id)
        {
            $this->SendNotification($id, 'admin_approved');
        }

        $data = array($cid);
        PFprocess::Event('approve_projects', $data);

    	return true;
    }

    public function AcceptInvitation($hash)
    {
    	$my = PFuser::GetInstance();
    	$db = PFdatabase::GetInstance();
    	$jconfig = JFactory::getConfig();

    	if(!$hash || strlen($hash) != 32) {
    		$this->AddError('MSG_INVALID_INV');
    		return false;
    	}

    	if(!$my->GetId()) return false;

    	$query = "SELECT project_id FROM #__pf_project_invitations WHERE inv_id = ".$db->Quote($hash);
    	       $db->setQuery($query);
    	       $project_id = (int) $db->loadResult();

    	if(!$project_id) {
    		$this->AddError('MSG_INVALID_PROJECT');
    		return false;
    	}

    	$row = $this->load($project_id);

    	if(!$row) {
    		$this->AddError('MSG_INVALID_PROJECT');
    		return false;
    	}

    	$query = "INSERT INTO #__pf_project_members VALUES(NULL, '$project_id', '".$my->GetId()."', '1')";
    	       $db->setQuery($query);
    	       $db->query();

    	if($db->getErrorMsg()) {
    		$this->AddError($db->getErrorMsg());
    		return false;
    	}

    	$query = "DELETE FROM #__pf_project_invitations WHERE inv_id = ".$db->Quote($hash);
    	       $db->setQuery($query);
    	       $db->query();

    	// Notify project author
    	$query = "SELECT name, email FROM #__users WHERE id = '$row->author'";
    	       $db->setQuery($query);
    	       $user = $db->loadObject();

        $data = array($row);
        PFprocess::Event('accept_invitation', $data);

    	if(is_object($row) && !defined('PF_DEMO_MODE')) {
    		$subject = str_replace('{project}', $row->title, PFformat::Lang('EM_PROJECT_SUBJECT_PIA'));
    		$message = str_replace('{author}', $user->name, PFformat::Lang('EM_PROJECT_BODY_PIA'));
    		$message = str_replace('{name}', $my->GetName(), $message);
    		$message = str_replace('{project}', $row->title, $message);

    		$mail = &JFactory::getMailer();

		    $mail->IsHTML(false);
            $message = str_replace('\n', "\n", $message);

		    // $mail->setSender( array( $jconfig->get('mailfrom'), $jconfig->get('fromname') ) );
		    $mail->setSubject( $subject );
	        $mail->setBody( $message );
	        $mail->addRecipient( $user->email );
	        $mail->Send();
    	}
    	return true;
    }

    public function DeclineInvitation($hash, $email)
    {
        $db = PFdatabase::GetInstance();
        $my = PFuser::GetInstance();
        $config  = PFconfig::GetInstance();
        $jconfig = JFactory::getConfig();

    	if(!$hash || strlen($hash) != 32) {
    		$this->AddError('MSG_INVALID_INV');
    		return false;
    	}

    	$query = "SELECT project_id FROM #__pf_project_invitations WHERE inv_id = ".$db->Quote($hash);
    	       $db->setQuery($query);
    	       $project_id = (int) $db->loadResult();

    	if(!$project_id) {
    		$this->AddError('MSG_INVALID_PROJECT');
    		return false;
    	}

    	$row = $this->Load($project_id);

    	if(!$row) {
    		$this->AddError('MSG_INVALID_PROJECT');
    		return false;
    	}

    	$query = "DELETE FROM #__pf_project_invitations WHERE inv_id = ".$db->Quote($hash);
    	       $db->setQuery($query);
    	       $db->query();

    	// Notify project author
    	$query = "SELECT name, email FROM #__users WHERE id = '$row->author'";
    	       $db->setQuery($query);
    	       $user = $db->loadObject();

        $data = array($row);
        PFprocess::Event('decline_invitation', $data);

        // User is not logged in, find username
        if(!$my->GetId()) {
            $query = "SELECT name FROM #__users WHERE email = ".$db->Quote($email)
                   . "\n LIMIT 1";
                   $db->setQuery($query);
                   $uname = $db->loadResult();

            if(!$uname) $uname = $email;
        }
        else {
            $uname = $my->GetName();
        }

        // Prepare and send Email
    	if(is_object($row) && !defined('PF_DEMO_MODE')) {
    		$subject = str_replace('{project}', $row->title, PFformat::Lang('EM_PROJECT_SUBJECT_PID'));
    		$message = str_replace('{author}', $user->name, PFformat::Lang('EM_PROJECT_BODY_PID'));
    		$message = str_replace('{name}', $uname, $message);
    		$message = str_replace('{project}', $row->title, $message);

    		$mail = &JFactory::getMailer();

            $mail->IsHTML(false);
            $message = str_replace('\n', "\n", $message);

		    // $mail->setSender( array( $jconfig->get('mailfrom'), $jconfig->get('fromname') ) );
		    $mail->setSubject( $subject );
	        $mail->setBody( $message );
	        $mail->addRecipient( $user->email );
	        $mail->Send();
    	}

    	return true;
    }

    public function SendNotification($project_id, $type, $user_id = null)
	{
		global $mainframe;

		if(defined('PF_DEMO_MODE') || !class_exists('PFprojectsmailer')) {
			return true;
		}

        $mail = new PFprojectsmailer();
        $mail->SetId($project_id);
        $mail->SetUserId($user_id);
        $mail->SetType($type);
        $mail->SetRecipients();
		$mail->Send();
	}
}
?>