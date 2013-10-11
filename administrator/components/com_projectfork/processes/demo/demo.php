<?php
/**
* $Id: demo.php 837 2010-11-17 12:03:35Z eaxs $
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

// SET THIS TO 1 WHEN IN DEMO MODE!
define('PF_DEMO_RESTRICT', 0);

// Do not change below this line!
if(!defined('PF_DEMO_MODE')) {
	define('PF_DEMO_MODE', 1);
	
	$pf_core   = PFcore::GetInstance();
	$pf_config = PFconfig::GetInstance();
	$pf_user   = PFuser::GetInstance();
	$pf_db     = PFdatabase::GetInstance();
	
	$section = $pf_core->GetSection();
	$task    = $pf_core->GetTask();
	$flag    = $pf_user->GetFlag();

	// Load params
	$use_reset = (int) $pf_config->Get('use_reset', 'demo');
	$reset_val = (int) $pf_config->Get('reset_intervall', 'demo');
	// if(!$reset_val) $reset_val = 1;
	
	$reset_val  = (int) $pf_config->Get('reset_intervall', 'demo') * 3600;
	$last_reset = (int) $pf_config->Get('last_reset', 'demo');
	$now        = time();
	
	if(!$last_reset) {
		$pf_config->Set('last_reset', $now, 'demo');
		$last_reset = $now;
	}
	
	// Get default section
	$query = "SELECT name FROM #__pf_sections WHERE is_default = '1'";
	       $pf_db->setQuery($query);
	       $default_section = $pf_db->loadResult();

	$pf_core->addMessage('PFL_DEMO_MSG');
	
    if(PF_DEMO_RESTRICT == 1) {
        switch ($section)
    	{	
    		case 'filemanager':
    		    switch ($task)
    		    {
    		    	case 'form_new_file':
    		    	case 'task_save_file':	
    		    	    $pf_core->addMessage('PFL_DEMO_NA');
                        $pf_core->Redirect('section=filemanager');
    		    	break;
    		    }
    			break;
    			
    		case 'users':
    		case 'config':
                    $pf_core->addMessage('PFL_DEMO_NA');
                    $pf_core->Redirect('section='.$default_section);
    			break;
    			
    		case 'profile':
                switch ($task)
                {
                	case 'task_update':
                		$pf_core->addMessage('PFL_DEMO_NA');
                        $pf_core->Redirect('section=profile');
                		break;
                }
    			break;
                
            case 'groups':
                if($task == 'task_update') {
                    $id = (int) JRequest::GetVar('id');
                    $query = "SELECT project FROM #__pf_groups WHERE id = '$id'";
                           $pf_db->setQuery($query);
                           $group_project = (int) $pf_db->loadResult();
                           
                    if(!$group_project) {
                        $pf_core->addMessage('PFL_DEMO_NA');
                        $pf_core->Redirect('section=groups');
                    }
                }
                break;    	
    	}
    }       
	
	// Reset System
	if((($last_reset + $reset_val) <= $now) && $use_reset) {
	    $load = PFload::GetInstance();
	    require_once($load->Process('demo.class.php', 'demo'));
	    unset($load);
		$pf_demo = new PFdemo();
		$pf_demo->ResetSystem();
		$pf_core->AddMessage('PFL_DEMO_RESET');
		unset($pf_demo);
	}
	
	unset($pf_core,$pf_config,$pf_user,$pf_db);
}
?>