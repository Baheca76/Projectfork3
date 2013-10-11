<?php
/**
* $Id: display_month.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Calendar
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

echo $form->Start();
?>
<script type="text/javascript">
function task_delete()
{
	if(!document.adminForm.boxchecked.value) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('task_delete');
	}
}
</script>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('CALENDAR');?> :: <?php echo PFformat::Lang('DISPLAY_MONTH');?>
        <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    <div class="pf_body">
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('calendar_nav'); ?>
        <!-- NAVIGATION END -->
        
        <!-- LEGEND START-->
        <div class="pf_legend">
            <span class="event project_event" <?php echo $project_bg;?>><?php echo PFformat::Lang('PROJECTS'); ?></span>
            <span class="event milestone_event" <?php echo $milestone_bg;?>><?php echo PFformat::Lang('MILESTONES'); ?></span>
            <span class="event task_event" <?php echo $task_bg;?>><?php echo PFformat::Lang('TASKS'); ?></span>
            <span class="event"><?php echo PFformat::Lang('EVENTS'); ?></span>
        </div>
        <!-- LEGEND END -->
        
        <!-- TABLE START -->
        <table class="pf_table adminlist pf_calendar" width="100%" cellpadding="1" cellspacing="1">
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
            <?php
                $html = '';
                for ($i = 0; $i < $days + $month_start; $i++)
                {
  	                $we         = '';
  	                $events     = $rows[$current_day]['events'];
  	                $milestones = $rows[$current_day]['milestones'];
  	                $tasks      = $rows[$current_day]['tasks'];
  	                $projects   = $rows[$current_day]['projects'];

  	                if ($counter == 0) $html .= "\n <tr class='row0 sectiontableentry1'>";
  	                if ($counter >= 5) $we = '_we';
  	 
  	                if ($i < $month_start) {
  	 	                $html .= "\n <td class=\"pf_monthstart\">&nbsp;</td>";
  	                }
  	                else {
  	                    $link_add        = 'section=calendar&task=form_new&year='.$year.'&month='.$month.'&day='.$current_day;
  	                    $event_link_edit = 'section=calendar&task=form_edit&year='.$year.'&month='.$month.'&day='.$current_day.'&id=';
  	                    $project_link    = 'section=projects&task=display_details&id=';
  	                    $task_link       = 'section=tasks&task=display_details&id=';
  	                    
  	                    $today_class = "";
  	                    if($this_day == $current_day && $this_month == $month && $this_year == $year) {
  	                        $today_class = ' today';
  	                    }
  	                    
  	                    if($can_add) {
                            $link_add = '<a href="'.PFformat::Link($link_add).'" class="pf_addevent"><span>+</span></a>';
                        }
                        else {
                            $link_add = '';
                        }
  	                    
  	                    $html .= '
                        <td align="left" valign="top" class="day'.$we.$today_class.'">
                        <table width="100%">
                            <tr>
                                <td valign="top" width="20">
                                    <div class="pf_daywrap">
                                        <span class="pf_daynumber">'.$current_day.'</span>
                                        '.$link_add.'
                                    </div>
                                </td>
                                <td valign="top">';
                    
                        foreach ($events AS $x => $event)
                        {
                            JFilterOutput::objectHTMLSafe($event);
                        	$checkbox = '<input id="cb'.$x.'" name="cid[]" value="'.$event->id.'" onclick="isChecked(this.checked);" type="checkbox">';
                        	$html .= '
                            <div class="event">'.$checkbox.'
                               <a href="'.PFformat::Link($event_link_edit.$event->id).'">'.$event->title.'</a>
                            </div>';
                            unset($event);
                        }
                        unset($events);
                        
                        foreach ($projects AS $x => $project)
                        {
                            JFilterOutput::objectHTMLSafe($project);
                            $html .= '
                            <div class="event project_event" '.$project_bg.'>
                               <a href="'.PFformat::Link($project_link.$project->id).'">'.$project->title.'</a>
                            </div>';
                            unset($project);
                        }
                        unset($projects);
                        
                        foreach ($milestones AS $x => $milestone)
                        {
                            JFilterOutput::objectHTMLSafe($milestone);
                            $html .= '
                            <div class="event milestone_event" '.$milestone_bg.'>'.$milestone->title.'</div>';
                            unset($milestone);
                        }
                        unset($milestones);
                        
                        foreach ($tasks AS $x => $task)
                        {
                            JFilterOutput::objectHTMLSafe($task);
                            $html .= '
                            <div class="event task_event" '.$task_bg.'>
                               <a href="'.PFformat::Link($task_link.$task->id).'">'.htmlspecialchars($task->title).'</a>
                            </div>';
                            unset($task);
                        }
                        unset($tasks);
                        
                        $html .= '</td></tr></table></td>';
                        $current_day++;
  	                }
  	 
  	                if ($counter == 6) { 
                        $counter = -1;
     	                $html .= "\n </tr>"; 
  	                }
  	                $counter++;
                }
            echo $html;
            unset($html);    
            ?>
            </tr>
            </tbody>
        </table>
       <!-- TABLE END -->
 
    </div>
</div>
<?php 
echo $form->HiddenField('option');
echo $form->HiddenField('section');
echo $form->HiddenField('task', 'display_month');
echo $form->HiddenField('boxchecked');
echo $form->End();
?>