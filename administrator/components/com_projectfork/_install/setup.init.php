<?php
/**
* $Id: setup.init.php 837 2010-11-17 12:03:35Z eaxs $
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

require_once(dirname(__FILE__).DS.'setup.lang.php');
require_once(dirname(__FILE__).DS.'setup.class.php');
require_once(dirname(__FILE__).DS.'setup.controller.php');
require_once(dirname(__FILE__).DS.'setup.html.php');

$setup_task       = JRequest::getVar('setup_task', 'splash', 'post');
$setup_controller = new PFsetupController();

switch($setup_task)
{
    case 'splash':
        $setup_controller->DisplaySplash();
        break;

    case 'finish':
        $setup_controller->Finish();
        break;

    case 'sql_access_flags':
    case 'sql_access_levels':
    case 'sql_group_permissions':
    case 'sql_groups':
    case 'sql_languages':
    case 'sql_panels':
    case 'sql_processes':
    case 'sql_section_tasks':
    case 'sql_sections':
    case 'sql_settings':
    case 'sql_themes':
    case 'sql_example':
        $setup_controller->RunSQL($setup_task);
        break;
}
?>