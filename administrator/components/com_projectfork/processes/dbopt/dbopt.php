<?php
/**
* $Id: dbopt.php 837 2010-11-17 12:03:35Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2009 Tobias Kuhn. All rights reserved.
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

$config = PFconfig::GetInstance();

$interval = (int) $config->Get('interval', 'dbopt');
$optdate  = (int) $config->Get('optdate', 'dbopt');
$tables   = array("#__pf_groups", "#__pf_group_users", "#__pf_languages", "#__pf_mods",
                  "#__pf_mod_files", "#__pf_panels", "#__pf_sections", "#__pf_section_tasks",
                  "#__pf_settings", "#__pf_tasks", "#__pf_themes", "#__pf_time_tracking",
                  "#__pf_user_profile", "#__pf_projects", "#__pf_notes", "#__pf_files",
                  "#__pf_events", "#__pf_processes", "#__pf_access_levels", "#__pf_access_flags",
                  "#__pf_milestones", "#__pf_comments", "#__pf_task_attachments", "#__pf_topics",
                  "#__pf_topic_replies", "#__pf_project_members", "#__pf_project_invitations",
                  "#__pf_user_access_level", "#__pf_topic_subscriptions");

if(!$interval) $interval = 1;
if(!$optdate) {
    $optdate  = time();
    $config->Set('optdate', $optdate, 'dbopt');
}

$now = time();
$next_opt = $now + ($interval * 3600);

if($next_opt <= $optdate) {
    $db = PFdatabase::GetInstance();
    $tables = implode(',', $tables);
    
    $query = "ANALYZE TABLE $tables";
           $db->setQuery($query);
           $db->query();
           
    $query = "OPTIMIZE TABLE $tables";
           $db->setQuery($query);
           $db->query();
           
    $next_opt = $now + ($interval * 3600);       
    $config->Set('optdate', $next_opt, 'dbopt');
    unset($db);
}
unset($config);
?>