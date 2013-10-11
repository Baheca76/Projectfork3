<?php
/**
* $Id: list_tasks.php 863 2011-03-21 00:00:29Z angek $
* @package    Projectfork
* @subpackage Tasks
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
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo PFformat::WorkspaceTitle()." / ".PFformat::Lang('TASKS');?>
        <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('tasks_nav');?>
        <!-- NAVIGATION END -->
        
        <!--WIZARD START-->
        <?php if($wizard && ($can_createt || $can_createm) && ($p_tasks == 0 || $p_ms == 0)) { ?>
        <div id="pf_panel_pf_wizard">
        <div class="pf-panel-body">
        	<div class="pf-wizard pf-first-task">
        		<h3><?php echo PFformat::Lang('WIZ_TASKS_TITLE');?></h3>
        		<?php if($can_createt && $p_tasks == 0) { ?>
        		    <p><?php echo PFformat::Lang('WIZ_TASKS_DESC');?></p>
                    <a href="<?php echo PFformat::Link("section=tasks&task=form_new_task");?>" class="pf_button">
                        <?php echo PFformat::Lang('WIZ_TASKS_CTBTN');?>
                    </a>
        		    <div class="clr separator"></div>
        		<?php } ?>
                <?php if($can_createm && $p_ms == 0) { ?>  
        		<p><?php echo PFformat::Lang('WIZ_MILESTONES_DESC');?></p>
        		<a href="<?php echo PFformat::Link("section=tasks&task=form_new_milestone");?>" class="pf_button">
                    <?php echo PFformat::Lang('WIZ_TASKS_CMBTN');?>
                </a>
                <?php } ?>
        	</div>
        </div>
        </div>
        <?php } ?>
        <!--WIZARD END-->
        
        <!-- TABLE START -->
        <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                   <th align="center" class="sectiontableheader title pf_number_header">#</th>
                   <?php if($can_copy || $can_delete) { ?>
                   <th align="center" class="sectiontableheader title pf_check_header"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows[0]); ?>);" /></th>
                   <?php } ?>
                   <?php if($can_reorder) { ?>
                   <th align="left" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(0);  // ORDERING ?></th>
                   <?php } ?>
                   <th align="left" class="sectiontableheader title pf_title_header"><?php echo $table->TH(1); // TITLE ?></th>
                   <th align="left" class="sectiontableheader title pf_action_header"> </th>
                   <th align="center" class="sectiontableheader title pf_assign_header"><?php echo $table->TH(2); // ASSIGNED ?></th>
                   <?php if($use_progperc) {;?><th align="left" class="sectiontableheader title pf_progress_header"><?php echo $table->TH(3); // PROGRESS ?></th><?php } ?>
                   <th align="left" class="sectiontableheader title pf_priority_header"><?php echo $table->TH(4); // PRIORITY ?></th>
                   <th align="left" class="sectiontableheader title pf_deadline_header"><?php echo $table->TH(5); // DEADLINE ?></th>
                   <th align="left" nowrap="nowrap" class="sectiontableheader title idcol pf_id_header"><?php echo $table->TH(6); // ID ?></th>
                </tr>
            </thead>
            <tbody id="table_body">
            
            <?php
            $data = array();
            $data['uncat']  = array();
            $data['mstask'] = array();
            $data['ms']     = array();
            
            // Loop through tasks
            foreach ($rows[0] AS $i => $row)
            {
                $html  = "";
                JFilterOutput::objectHTMLSafe($row);
                
                if(!$use_milestones) $row->milestone = 0;
                
                // Make links
                $link_details = PFformat::Link("section=tasks&task=display_details&id=$row->id".$filter);
                $link_edit    = PFformat::Link("section=tasks&task=form_edit_task&id=$row->id&workspace=$row->project".$filter);
                $link_compete = PFformat::Link("section=tasks&task=task_update_progress&progress[$row->id]=100&id=".$row->id.$filter);
                $link_tt      = PFformat::Link("section=time&ftask=$row->id&fuser=0&keyword=");
                $link_profile = PFformat::Link("section=profile&task=display_details&id=$row->author");
                
                // Check permissions
                $t_can_edit = $user->Access('form_edit_task', 'tasks', $row->author);
                $t_new_time = $user->Access('form_new', 'time', $row->author);
                $t_comment  = $user->Access('task_save_comment', 'tasks', $row->author);
                $t_profile  = $user->Access('display_details', 'profile', $row->author);
                
                // Check and order box
                $checkbox = '<input id="cb'.$i.'" name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox" />';
                $ordering = '<input name="ordering['.$row->id.']" value="'.(int)$row->ordering.'" type="text" size="2" class="pf_order"/>';
   
                // Progress dropdown
                $progress = $row->progress." %";
                if($use_progperc && $t_can_edit) {
                    $progress = $form->SelectProgress("progress[$row->id]",
                                $row->progress,
                                'onchange="javascript:task_update_progress('.$row->id.', \''.PFformat::IdHash($row->id, 'tasks').'\');"');
                }
                
                // Project title
                $project_title = "";
                if($display_all) $project_title = "<span class='project_title'>(".$row->project_title.") </span>";
                
                // Deadline
                $edate = ($row->edate == 0) ? " " : PFformat::ToDate($row->edate);
                
                // Avatar
                $avatar = PFavatar::Display($row->author, false);
                
                // Render list of assigned ppl
                $assigned = "";
                $assigned_count = count($row->assigned);
                
                foreach($row->assigned AS $ia => $a)
                {
                    $assigned .= PFavatar::Display($a->id.':'.$a->name);
                    
                    if(!$display_avatar && ($ia + 1) != $assigned_count) $assigned .= ", ";
                }

                $html .= '<tr class="row'.$k.' sectiontableentry'.($k + 1).' priority_'.$row->priority.' progress_'.$row->progress.'">
                              <td class="pf_number_cell">'.$pagination->getRowOffset( $i ).'</td>';
                              
                if($can_copy || $can_delete) $html .= '<td align="center" class="pf_check_cell">'.$checkbox.'</td>';
                if($can_reorder) $html .= '<td align="center" style="text-align:center" class="pf_order_cell">'.$ordering.'</td>';
                
                $html .= '<td class="pf_task_title item_title"><div class="pf_title_wrap">'.$project_title;
                
                if($user->Access('display_details', 'tasks', $row->author)) {
                    $html .= '<a href="'.$link_details.'">'.$row->title.'</a>';
                }
                else {
                    $html .= '<strong>'.$row->title.'</strong>';
                }
                
                $html .= '</div></td><td class="pf_actions_cell">';                
                $html .= $table->Menu();
                if($t_can_edit) {
                    $html .= $table->MenuItem($link_edit,'TT_EDIT','pf_edit');
                }
                if($row->progress != '100' && $list_finish && $t_can_edit) {
                    $html .= $table->MenuItem($link_compete,'FINISHED','pf_complete');
                }
                /*if($t_new_time) {
                    $html .= $table->MenuItem($link_tt,'TI_FN','pf_timeadd');
                }*/
                if($use_comments && $list_comments && $t_comment) {
                    $html .= $table->MenuItem($link_details, $row->comments.' '.PFformat::Lang('COMMENTS'),'pf_comments');
                }
                $html .= $table->Menu(false);
                
                $html .= '</td><td class="pf_assign_cell">'.$assigned.'</td>';
                
                if($use_progperc) $html .= '<td class="pf_progress_cell"><span>'.$progress.'</span></td>';
                
                $html .= '<td class="pf_priority_cell"><span>'.PFtasksHelper::RenderPriority($row->priority).'</span></td>
                          <td class="pf_deadline_cell"><span>'.$edate.'</span></td>
                          <td class="idcol pf_id_cell"><span>'.$row->id.'</span></td>
                          </tr>';
                          
                
                
                if(!$use_milestones) {
                    $data['uncat'][] = $html;
                }
                else {
                    if($row->milestone != 0) {
                        if(!array_key_exists($row->milestone, $data['mstask'])) $data['mstask'][$row->milestone] = array();
                        $data['mstask'][$row->milestone][] = $html;
                    }
                    else {
                        $data['uncat'][] = $html;
                    }
                }
                unset($html);
                $k = 1 - $k;
            }
            
            // Loop through milestones
            foreach ($rows[1] AS $i => $ms)
            {
                JFilterOutput::objectHTMLSafe($ms);
                
                // Hide empty milestones
                if(!$use_milestones) continue;
                if($hide_ms_empty && !array_key_exists($ms->id, $data['mstask'])) continue;
                
                // Permissions
                $p_edit = $user->Access('form_edit_milestone', 'tasks', $ms->author);
                $p_newt = $user->Access('form_new_task', 'tasks');
                
                // Check and order box
                $checkbox  = '<input name="mid[]" value="'.$ms->id.'" onclick="isChecked(this.checked);" type="checkbox"/>';
  	            $ordering  = '<input name="mordering['.$ms->id.']" value="'.(int)$ms->ordering.'" type="text" size="2" class="pf_order"/>';
  	            
  	            // Content
  	            $content = "";
 	  	        if($ms->content) $content = "<br/><span class='milestone_content'>$ms->content</span>";
 	  	        
 	  	        // Links
 	  	        $edit_link = ($p_edit == true) ? PFformat::Link('section=tasks&task=form_edit_milestone&id='.$ms->id.$filter) : "";
 	  	        $add_link  = ($p_newt == true) ? PFformat::Link('section=tasks&task=form_new_task&milestone='.$ms->id.$filter) : "";
 	  	        $fms_link  = (!$fms) ? PFformat::Link('section=tasks&fms='.$ms->id.$filter) : PFformat::Link('section=tasks&fms=0'.$filter);
 	  	        
 	  	        // Buttons
 	  	        $btn_edit = "";

				if($edit_link) {
					$btn_edit = $table->MenuItem($edit_link, 'EDIT', 'pf_edit');
				}
                $btn_add = "";
 	  	        if($add_link) {
					$btn_add = $table->MenuItem($add_link, 'NEW_TASK', 'pf_new');
                }
                $btn_fms = "";
                if(!$fms) {
					$btn_fms = $table->MenuItem($fms_link, 'FILTER', 'pf_show');
                }
                else {
					$btn_fms = $table->MenuItem($fms_link, 'RESET_FILTER', 'pf_show');
                }

                // Project title
                $project_title = "";
 	  	        if($display_all) $project_title = "<span class='project_title'>(".$ms->project_title.") </span>";
 	  	        
 	  	        // Task avg percentage
 	  	        $avg = ($ms->tt > 0) ? round($ms->tp / $ms->tt)."%" : "0%";
 	  	        
 	  	        // Deadline
 	  	        $edate = ($ms->edate > 0) ? PFformat::ToDate($ms->edate) : " ";
 	  	        
 	  	        $html = '';
 	  	        $html .= '<tr class="milestone sectiontableheader priority_'.$ms->priority.'">
 	  	                  <td class="pf_number_cell">-</td>';
 	  	                  
 	  	        if($can_copy || $can_delete) $html .= '<td align="center" class="pf_check_cell">'.$checkbox.'</td>'; 
                if($can_reorder) $html .= '<td align="center" class="pf_order_cell">'.$ordering.'</td>';
                
                $html .= '<td class="pf_milestone_title item_title"><strong>
                          '.$project_title.$ms->title.'</strong>
                          <p>'.$content.'</p>
                          </td>';
                          
                $html .= '<td class="pf_action_cell">'.$table->Menu().$btn_edit.$btn_add.$btn_fms.$table->Menu(false).'</td>
                          <td></td>';
                          
                if($use_progperc) $html .= '<td class="pf_progress_cell"><span>'.$avg.'</td>';
                
                $html .= '<td class="pf_priority_cell"><span>'.PFtasksHelper::RenderPriority($ms->priority).'</span></td>
  	                      <td class="pf_deadline_cell"><span>'.$edate.'</span></td>
  	                      <td class="idcol pf_id_cell"><span>'.$ms->id.'</span></td></tr>';

                // Add the tasks
                if(array_key_exists($ms->id, $data['mstask'])) {
                    $html .= implode('', $data['mstask'][$ms->id]);
                }
                
                $data['ms'][] = $html;
                unset($html);
            }
            unset($rows);

            if(!$use_milestones) echo implode('', $data['uncat']);
            
            if($use_milestones && $uncat_tasks == 1 && $fms == 0) {
                echo '<tr class="milestone sectiontableheader title">
                      <td class="pf_number_cell">-</td><td></td><td></td>
                      <td colspan="6" class="item_title"><strong>'.PFformat::Lang('UNCATEGORIZED').'</strong></td>
                      <td class="pf_id_cell"></td>
                      </tr>'.implode('', $data['uncat']);
            }
            
            if($use_milestones) echo implode('', $data['ms']);
            
            if($use_milestones && $uncat_tasks == 2 && $fms == 0) {
                echo '<tr class="milestone sectiontableheader title">
                      <td class="pf_number_cell">-</td><td></td><td></td>
                      <td colspan="6" class="item_title"><strong>'.PFformat::Lang('UNCATEGORIZED').'</strong></td>
                      <td class="pf_id_cell"></td>
                      </tr>'.implode('', $data['uncat']);
            }
            
            unset($data);
            ?>
            <tr>
                <td colspan="9" style="text-align:center"><?php echo $pagination->getListFooter(); ?></td>
            </tr>
            </tbody>
        </table>
        <!-- TABLE END -->
        
    </div>
</div>
<?php
echo $form->HiddenField("boxchecked");
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("ob", $ob);
echo $form->HiddenField("od", $od);
echo $form->HiddenField("id");
echo $form->HiddenField("idlock", 1);
echo $form->End();
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
function task_copy()
{
	if(!document.adminForm.boxchecked.value) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('task_copy');
	}
}
function task_update_progress(tid, idl)
{
	document.adminForm.id.value = tid;
    document.adminForm.idlock.name = idl;
	submitbutton('task_update_progress');
}
</script>