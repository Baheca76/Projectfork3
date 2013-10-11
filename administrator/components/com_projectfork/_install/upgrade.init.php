<?php
/**
* $Id: upgrade.init.php 837 2010-11-17 12:03:35Z eaxs $
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

require_once(dirname(__FILE__).DS.'upgrade.lang.php');
require_once(dirname(__FILE__).DS.'upgrade.class.php');
require_once(dirname(__FILE__).DS.'upgrade.controller.php');
require_once(dirname(__FILE__).DS.'upgrade.html.php');

$task = JRequest::getVar('task', NULL, 'post');
$controller = new PFupgradeController();

switch($task)
{
    default:
        $controller->DisplaySplash();
        break;
        
    case 'opt_old':
        $controller->OptimizeOldTables();
        break;
        
    case 'add_fields':
        $controller->AddFields();
        break; 
        
    case 'del_fields':
        $controller->DeleteFields();
        break;
        
    case 'ren_fields':
        $controller->RenameFields();
        break;
        
    case 'indexes':
        $controller->AddIndexes();
        break;
        
    case 'migrate_groups':
        $controller->MigrateGroups();
        break;
        
    case 'update_ext':
        $controller->UpdateExtensions();
        break;
        
    case 'profiles':
        $controller->UpdateProfiles();
        break;
        
    case 'tables':
        $controller->UpdateTables();
        break;  
        
    case 'config':
        $controller->UpdateConfig();
        break;        
}
?>