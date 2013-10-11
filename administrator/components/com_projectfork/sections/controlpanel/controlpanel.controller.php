<?php
/**
* $Id: controlpanel.controller.php 837 2010-11-17 12:03:35Z eaxs $
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

class PFcontrolpanelController
{
	public function __construct()
	{
        // Nothing going on here
    }
	
	public function DisplayDefault()
	{
        $load   = PFload::GetInstance();
        $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();
        
        $wizard  = (int) $config->Get('use_wizard');
        $user_id = $user->GetId();
        $can_cp  = $user->Access('form_new', 'projects');

        $p_tasks = 0;
        $p_ms    = 0;
        $p_users = 0;
        
        if($wizard && $user_id && $can_cp) {
            $my_projects = count($user->Permission('author'));
            $my_ws       = $user->GetWorkspace();
            
            if($my_ws) {
                $class = new PFcontrolpanelClass();
                $p_tasks = $class->CountTasks($my_ws);
                $p_ms    = $class->CountMilestones($my_ws);
                $p_users = $class->CountUsers($my_ws);
            }
        }
        
        require_once($load->SectionOutput('overview.php'));
    }
}
?>