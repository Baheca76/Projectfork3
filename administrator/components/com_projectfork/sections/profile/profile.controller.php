<?php
/**
* $Id: profile.controller.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Profile
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


class PFprofileController extends PFobject 
{
    public function __construct()
    {
        parent::__construct();
    }
    
	public function DisplayEdit()
	{
	    // Load objects
	    $load   = PFload::GetInstance();
	    $user   = PFuser::GetInstance();
	    $config = PFconfig::GetInstance();
	    
	    $uid = $user->GetId();
	    
	    // Config settings
	    $use_avatar = (int) $config->Get('display_avatar');
	    
	    $use_phone  = (int) $config->Get('allow_phone', 'profile');
	    $use_mphone = (int) $config->Get('allow_mphone', 'profile');
	    $use_skype  = (int) $config->Get('allow_skype', 'profile');
	    $use_msn    = (int) $config->Get('allow_msn', 'profile');
	    $use_icq    = (int) $config->Get('allow_icq', 'profile');
	    
	    $use_street = (int) $config->Get('allow_street', 'profile');
	    $use_city   = (int) $config->Get('allow_city', 'profile');
	    $use_zip    = (int) $config->Get('allow_zip', 'profile');
	    
	    $use_twitter  = (int) $config->Get('allow_twitter', 'profile');
	    $use_friendf  = (int) $config->Get('allow_friendfeed', 'profile');
	    $use_linkedin = (int) $config->Get('allow_linkedin', 'profile');
	    $use_facebook = (int) $config->Get('allow_facebook', 'profile');
	    $use_youtube  = (int) $config->Get('allow_youtube', 'profile');
	    $use_vimeo    = (int) $config->Get('allow_vimeo', 'profile');
	    
	    // Include the class
		require_once($load->Section('profile.class.php', 'profile'));
		$class = new PFprofileClass();
		
		// Load user record
		$row   = $class->Load($uid);
		
		// Setup form
		$form = new PFform('adminForm', NULL, 'post', 'enctype="multipart/form-data"');
		$form->SetBind(true, $row);
		
		// Include the output file
		require_once($load->SectionOutput('form_edit.php', 'profile'));
		
		// Unset data
		unset($load,$user,$row,$form);
	}
	
	public function DisplayDetails($id)
	{
	    // Load objects
	    $load = PFload::GetInstance();
	    
	    // Include the class
		require_once($load->Section('profile.class.php', 'profile'));
		$class = new PFprofileClass();
		
		// Load user record
		$row = $class->Load($id);
		
		$ws_title = PFformat::WorkspaceTitle();
		
		// Include the output file
		require_once($load->SectionOutput('display_details.php', 'profile'));
		
		// Unset objects
		unset($load,$class,$row);
	}
	
	public function Update()
	{
	    // Load objects
	    $load = PFload::GetInstance();
	    
	    // Include the class
		require_once($load->Section('profile.class.php', 'profile'));
		$class = new PFprofileClass();
		
		// Update, then redirect
		if(!$class->Update()) { // Error
			$this->SetRedirect("section=profile", 'PROFILE_E_UPDATE');
			return false;
		}
		else { // Success
			$this->SetRedirect("section=profile", 'PROFILE_S_UPDATE');
			return true;
		}
	}
}
?>