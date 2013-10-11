<?php
/**
* $Id: profile.class.php 837 2010-11-17 12:03:35Z eaxs $
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


class PFprofileClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }
    
	public function Load($id = 0)
	{
	    // Load objects
	    $load = PFload::GetInstance();
	    
	    // Check ID
	    if(!$id) {
            $user = PFuser::GetInstance();
            $id = $user->GetId();
            unset($user);
        }
        
        // Include the class
		require_once($load->Section('users.class.php', 'users'));
		$class = new PFusersClass();
		
		// Load user record
		$row = $class->LoadUser($id);
		
		// Unset objects
		unset($load,$class);
		
		return $row;
	}
	
	public function Update()
	{
	    // Load objects
	    $load   = PFload::GetInstance();
	    $config = PFconfig::GetInstance();
	    $juser  = JFactory::getUser();
	    $user   = PFuser::GetInstance();
	    
	    // Include the class
		require_once($load->Section('users.class.php', 'users'));
		$class = new PFusersClass();

        $avatar = JRequest::getVar( 'avatar', '', 'files');
        $delete = (int) JRequest::getVar( 'delete_avatar');
     
        // Update basic Joomla account
		JRequest::setVar('username', $juser->username);
		if(!$class->UpdateJoomla($juser->id)) return false;
		
		// Load Joomla filesystem layer
		jimport('joomla.filesystem.file');
			
		// Delete current avatar?
        if($delete && $config->Get('allow_upload', 'profile') == '1' && !defined('PF_DEMO_MODE')) {
            $this->DeleteAvatar($juser->id);
        }
		
		// Upload new avatar?
		if($config->Get('allow_upload', 'profile') == '1' && $avatar['name'] != '' && !defined('PF_DEMO_MODE')) {
			$this->UploadAvatar('avatar', $juser->id);
		}
		
		// Save user profile
		$params = array('timezone','language','phone', 'mobile_phone',
                        'skype','msn','icq','street','city','zip',
                        'youtube','friendfeed','facebook','twitter','linkedin');
                        
		foreach ($params AS $param)
		{
			$user->SetProfile($param, JRequest::getVar($param), $user->GetId());
		}

        // Load update profile processes
        $data = array($user->GetId());
        PFprocess::Event('update_profile', $data);
		
		return true;
	}
	
	public function DeleteAvatar($uid)
	{
        // Load objects
        $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();
        
        $image = $user->GetProfile('avatar', NULL, false, $uid);
        if(is_null($image) || $image == '') {
            unset($user,$config);
            return false;
        }
        
        // Load Joomla filesystem layer
		jimport('joomla.filesystem.file');
		
        $upload_path = $config->Get('upload_path', 'profile');
        $full_path   = JPATH_ROOT.DS.$upload_path.DS.$image;
        
        if(file_exists($full_path)) {
		    if(!JFile::delete($full_path)) {
			    $this->AddError('AVATAR_E_DELETE');
			    return false;
			}
			$user->SetProfile('avatar', '', $uid);
			return true;
		}
		return false;
    }
	
	
	public function UploadAvatar($name, $uid)
	{
	    // Load objects
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();
	    
	    // Load Joomla filesystem layer
		jimport('joomla.filesystem.file');
		
		// Prepare file name
	    $avatar = JRequest::getVar( $name, '', 'files');
		$aname  = $avatar['name'];
		$asave  = str_replace(' ', '_', strtolower(JFile::makeSafe($aname)));
		$uid    = $user->GetId();
		
		$folder   = JPath::clean(JPATH_ROOT.DS.$config->Get('upload_path', 'profile'));
		$filepath = $folder.DS.$uid."_".$asave;
		
		// Get max width and height from settings
		$maxw     = (int) $config->Get('max_w', 'profile');
		$maxh     = (int) $config->Get('max_h', 'profile');
		
		// Resize image
		$imagesize = @getimagesize($avatar['tmp_name']); 
        $imgw      = (int) $imagesize[0]; 
        $imgh      = (int) $imagesize[1]; 
        $ratiow    = $maxw / $imgw; 
        $ratioh    = $maxh / $imgh;
        
        if(!$imgw || !$imgh) {
        	$this->AddError('NOT_AN_IMAGE');
        	return false;
        }

        // Calculate new size
        if($ratiow < $ratioh) { 
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
        
        // Create a new image
        $new_logo = imagecreatetruecolor($neww, $newh);
		$image    = false;
		
		// Check folder and set permissions
		if(!JFolder::exists($folder)) JFolder::create($folder, 0777);
        JPath::setPermissions($folder, '0644', '0777');
		
		// Remove the old logo if exists
		$this->DeleteAvatar($uid);

		// Upload image
		if(stristr($avatar['name'],'.jpg') || stristr($avatar['name'], '.jpeg')) {
			$source = ImageCreateFromJpeg($avatar['tmp_name']);
			imagecopyresized($new_logo, $source, 0, 0, 0, 0, $neww, $newh, $imgw, $imgh);
			$image = ImageJpeg($new_logo, $filepath, 100);
		}
		if(stristr($avatar['name'], '.png')) {
			$source = ImageCreateFromPng($avatar['tmp_name']);
			imagealphablending($source, true);  
            imagesavealpha($source, true);
			$background = imagecolorallocate($new_logo, 0, 0, 0);  
            ImageColorTransparent($new_logo, $background);
            imagealphablending($new_logo, false);  
            imagesavealpha($new_logo, true);
			imagecopyresized($new_logo, $source, 0, 0, 0, 0, $neww, $newh, $imgw, $imgh);
			$image = ImagePng($new_logo, $filepath, 0);
		}
		if(stristr($avatar['name'], 'gif')) {
			$source = ImageCreateFromGif($avatar['tmp_name']);
			imagecopyresized($new_logo, $source, 0, 0, 0, 0, $neww, $newh, $imgw, $imgh);
			$image = ImageGif($new_logo, $filepath, 100);
		}
		
		// something went wrong...
		if(!$image) return false;
		
		// Set directory permissions
		JPath::setPermissions($folder, '0644', '0755');
		
		// Update profile
		$user->SetProfile('avatar', $uid."_".$asave, $uid);
		
		return true;
	}
}
?>