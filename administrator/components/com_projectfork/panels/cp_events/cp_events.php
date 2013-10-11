<?php
/**
* $Id: cp_events.php 866 2011-03-21 07:58:08Z angek $
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

// Include the calendar class
require_once(PFobject::GetClass('calendar'));

$config = PFconfig::GetInstance();
$user   = PFuser::GetInstance();

$project_bg   = $config->Get('project_bg', 'calendar');
$milestone_bg = $config->Get('milestone_bg', 'calendar');
$task_bg      = $config->Get('task_bg', 'calendar');
        
$project_bg   = ($project_bg == '') ? '' : "style='background-color:#$project_bg'";
$milestone_bg = ($milestone_bg == '') ? '' : "style='background-color:#$milestone_bg'";
$task_bg      = ($task_bg == '') ? '' : "style='background-color:#$task_bg'";
        
$year  = date("Y");
$month = date("n");
$today = date("j");
$hours = array('00','01','02','03','04','05','06','07','08','09','10','11','12',
		       '13','14','15','16','17','18','19','20','21','22','23');
		         
$week            = date("W", mktime(0,0,0,$month,$today,$year));
$day_of_week     = strftime( '%w', mktime(0,0,0,$month,$today,$year)) - 1;
$start_of_week   = date("d", mktime(0,0,0,$month,$today - $day_of_week,$year));
$start_of_week_y = date("Y", mktime(0,0,0,$month,$today - $day_of_week,$year));
$start_of_week_m = date("m", mktime(0,0,0,$month,$today - $day_of_week,$year));
$end_of_week     = date("d", mktime(0,0,0,$month,$today + (6 - $day_of_week),$year));
$end_of_week_y   = date("Y", mktime(0,0,0,$month,$today + (6 - $day_of_week),$year));
$end_of_week_m   = date("m", mktime(0,0,0,$month,$today + (6 - $day_of_week),$year));
$last_day_of_m   = date("t", mktime(0,0,0,$month-1,$start_of_week,$year));

$class = new PFcalendarClass();
$rows  = $class->LoadWeek($start_of_week_y, $start_of_week_m, $start_of_week, $end_of_week_y, $end_of_week_m, $end_of_week);
?>
<table cellspacing="1" cellpadding="1" width="100%" class="pf_table adminlist pf_calendar">
    <thead>
	    <tr>
	        <th align="left" width="14%" class="sectiontableheader title"><?php echo PFformat::Lang('MONDAY');?></th>
	        <th align="left" width="14%" class="sectiontableheader title"><?php echo PFformat::Lang('TUESDAY');?></th>
	        <th align="left" width="14%" class="sectiontableheader title"><?php echo PFformat::Lang('WEDNESDAY');?></th>
            <th align="left" width="14%" class="sectiontableheader title"><?php echo PFformat::Lang('THURSDAY');?></th>
            <th align="left" width="14%" class="sectiontableheader title"><?php echo PFformat::Lang('FRIDAY');?></th>
            <th align="left" width="14%" class="sectiontableheader title"><?php echo PFformat::Lang('SATURDAY');?></th>
            <th align="left" width="14%" class="sectiontableheader title"><?php echo PFformat::Lang('SUNDAY');?></th>
	    </tr>
    </thead>
    <tbody>
    <tr class="row0 sectiontableentry1">
        <td align="left" valign="top" class="day">
            <span class="pf_daynumber"><?php echo (int) $start_of_week;?></span>
            <?php
            foreach($hours AS $hour)
            {
                $hour = (int) $hour;
                
                // Events
                if($user->Access(NULL, 'calendar')) {
                    foreach($rows[1][$hour]['events'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event">'.$row->title.'</div>';
                    }
                }
                
                // Projects
                if($user->Access(NULL, 'projects')) {
                    foreach($rows[1][$hour]['projects'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("section=projects&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'projects', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'projects', $row->author)) ? '</a>' : '';
                        echo '<div class="event project_event" '.$project_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
                
                if($user->Access(NULL, 'tasks')) {
                    // Milestones
                    foreach($rows[1][$hour]['milestones'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event milestone_event" '.$milestone_bg.'>'.$row->title.'</div>';
                    }
                    // Tasks
                    foreach($rows[1][$hour]['tasks'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("workspace=".$row->project."&section=tasks&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'tasks', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'tasks', $row->author)) ? '</a>' : '';
                        echo '<div class="event task_event" '.$task_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
            }
            ?>
        </td>
        <td align="left" valign="top" class="day">
            <span class="pf_daynumber">
                <?php
                $start_of_week++;
                if($start_of_week > $last_day_of_m) $start_of_week = 1;
                echo $start_of_week;
                ?>
            </span>
            <?php
            foreach($hours AS $hour) 
            {
                $hour = (int) $hour;
                
                // Events
                if($user->Access(NULL, 'calendar')) {
                    foreach($rows[2][$hour]['events'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event">'.$row->title.'</div>';
                    }
                }
                
                // Projects
                if($user->Access(NULL, 'projects')) {
                    foreach($rows[2][$hour]['projects'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("section=projects&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'projects', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'projects', $row->author)) ? '</a>' : '';
                        echo '<div class="event project_event" '.$project_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
                
                if($user->Access(NULL, 'tasks')) {
                    // Milestones
                    foreach($rows[2][$hour]['milestones'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event milestone_event" '.$milestone_bg.'>'.$row->title.'</div>';
                    }
                    // Tasks
                    foreach($rows[2][$hour]['tasks'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("workspace=".$row->project."&section=tasks&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'tasks', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'tasks', $row->author)) ? '</a>' : '';
                        echo '<div class="event task_event" '.$task_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
            }
            ?>
        </td>
        <td align="left" valign="top" class="day">
            <span class="pf_daynumber">
                <?php 
                $start_of_week++;
                if($start_of_week > $last_day_of_m) $start_of_week = 1;
                echo $start_of_week;
                ?>
            </span>
            <?php
            foreach($hours AS $hour) 
            {
                $hour = (int) $hour;
                
                // Events
                if($user->Access(NULL, 'calendar')) {
                    foreach($rows[3][$hour]['events'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event">'.$row->title.'</div>';
                    }
                }
                
                // Projects
                if($user->Access(NULL, 'projects')) {
                    foreach($rows[3][$hour]['projects'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("section=projects&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'projects', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'projects', $row->author)) ? '</a>' : '';
                        echo '<div class="event project_event" '.$project_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
                
                if($user->Access(NULL, 'tasks')) {
                    // Milestones
                    foreach($rows[3][$hour]['milestones'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event milestone_event" '.$milestone_bg.'>'.$row->title.'</div>';
                    }
                    // Tasks
                    foreach($rows[3][$hour]['tasks'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("workspace=".$row->project."&section=tasks&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'tasks', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'tasks', $row->author)) ? '</a>' : '';
                        echo '<div class="event task_event" '.$task_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
            }
            ?>
        </td>
        <td align="left" valign="top" class="day">
            <span class="pf_daynumber">
                <?php 
                $start_of_week++;
                if($start_of_week > $last_day_of_m) $start_of_week = 1;
                echo $start_of_week;
                ?>
            </span>
            <?php
            foreach($hours AS $hour) 
            {
                $hour = (int) $hour;
                
                // Events
                if($user->Access(NULL, 'calendar')) {
                    foreach($rows[4][$hour]['events'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event">'.$row->title.'</div>';
                    }
                }
                
                // Projects
                if($user->Access(NULL, 'projects')) {
                    foreach($rows[4][$hour]['projects'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("section=projects&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'projects', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'projects', $row->author)) ? '</a>' : '';
                        echo '<div class="event project_event" '.$project_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
                
                if($user->Access(NULL, 'tasks')) {
                    // Milestones
                    foreach($rows[4][$hour]['milestones'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event milestone_event" '.$milestone_bg.'>'.$row->title.'</div>';
                    }
                    // Tasks
                    foreach($rows[4][$hour]['tasks'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("workspace=".$row->project."&section=tasks&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'tasks', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'tasks', $row->author)) ? '</a>' : '';
                        echo '<div class="event task_event" '.$task_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
            }
            ?>
        </td>
        <td align="left" valign="top" class="day">
            <span class="pf_daynumber">
                <?php 
                $start_of_week++;
                if($start_of_week > $last_day_of_m) $start_of_week = 1;
                echo $start_of_week;
                ?>
            </span>
            <?php
            foreach($hours AS $hour) 
            {
                $hour = (int) $hour;
                
                // Events
                if($user->Access(NULL, 'calendar')) {
                    foreach($rows[5][$hour]['events'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event">'.$row->title.'</div>';
                    }
                }
                
                // Projects
                if($user->Access(NULL, 'projects')) {
                    foreach($rows[5][$hour]['projects'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("section=projects&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'projects', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'projects', $row->author)) ? '</a>' : '';
                        echo '<div class="event project_event" '.$project_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
                
                if($user->Access(NULL, 'tasks')) {
                    // Milestones
                    foreach($rows[5][$hour]['milestones'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event milestone_event" '.$milestone_bg.'>'.$row->title.'</div>';
                    }
                    // Tasks
                    foreach($rows[5][$hour]['tasks'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("workspace=".$row->project."&section=tasks&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'tasks', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'tasks', $row->author)) ? '</a>' : '';
                        echo '<div class="event task_event" '.$task_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
            }
            ?>
        </td>
        <td align="left" valign="top" class="day">
            <span class="pf_daynumber">
                <?php 
                $start_of_week++;
                if($start_of_week > $last_day_of_m) $start_of_week = 1;
                echo $start_of_week;
                ?>
            </span>
            <?php
            foreach($hours AS $hour) 
            {
                $hour = (int) $hour;
                
                // Events
                if($user->Access(NULL, 'calendar')) {
                    foreach($rows[6][$hour]['events'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event">'.$row->title.'</div>';
                    }
                }
                
                // Projects
                if($user->Access(NULL, 'projects')) {
                    foreach($rows[6][$hour]['projects'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("section=projects&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'projects', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'projects', $row->author)) ? '</a>' : '';
                        echo '<div class="event project_event" '.$project_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
                
                if($user->Access(NULL, 'tasks')) {
                    // Milestones
                    foreach($rows[6][$hour]['milestones'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event milestone_event" '.$milestone_bg.'>'.$row->title.'</div>';
                    }
                    // Tasks
                    foreach($rows[6][$hour]['tasks'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("workspace=".$row->project."&section=tasks&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'tasks', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'tasks', $row->author)) ? '</a>' : '';
                        echo '<div class="event task_event" '.$task_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
            }
            ?>
        </td>
        <td align="left" valign="top" class="day">
            <span class="pf_daynumber">
                <?php 
                $start_of_week++;
                if($start_of_week > $last_day_of_m) $start_of_week = 1;
                echo $start_of_week;
                ?>
            </span>
            <?php
            foreach($hours AS $hour) 
            {
                $hour = (int) $hour;
                
                // Events
                if($user->Access(NULL, 'calendar')) {
                    foreach($rows[7][$hour]['events'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event">'.$row->title.'</div>';
                    }
                }
                
                // Projects
                if($user->Access(NULL, 'projects')) {
                    foreach($rows[7][$hour]['projects'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("section=projects&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'projects', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'projects', $row->author)) ? '</a>' : '';
                        echo '<div class="event project_event" '.$project_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
                
                if($user->Access(NULL, 'tasks')) {
                    // Milestones
                    foreach($rows[7][$hour]['milestones'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        echo '<div class="event milestone_event" '.$milestone_bg.'>'.$row->title.'</div>';
                    }
                    // Tasks
                    foreach($rows[7][$hour]['tasks'] AS $x => $row)
                    {
                        JFilterOutput::objectHTMLSafe($row);
                        $link = PFformat::Link("workspace=".$row->project."&section=tasks&task=display_details&id=".$row->id);
                        $as   = ($user->Access('display_details', 'tasks', $row->author)) ? '<a href="'.$link.'">' : '';
                        $ae   = ($user->Access('display_details', 'tasks', $row->author)) ? '</a>' : '';
                        echo '<div class="event task_event" '.$task_bg.'>'.$as.$row->title.$ae.'</div>';
                    }
                }
            }
            ?>
        </td>
    </tr>
</table>
<?php
unset($rows,$config,$user);
?>