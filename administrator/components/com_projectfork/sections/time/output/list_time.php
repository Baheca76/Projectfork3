<?php
/**
* $Id: list_time.php 837 2010-11-17 12:03:35Z eaxs $
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
$total_time = 0;
echo $form->Start();
?>
<script type="text/javascript">
function task_save()
{
    document.adminForm.task.value = 'task_save';
    document.adminForm.submit();
}
function task_update()
{
    document.adminForm.task.value = 'task_update';
    document.adminForm.submit();
}
function form_new()
{
    document.getElementById('time_form_new').style.display = "block";
}
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
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('TIME_TRACKING');?>
        <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('time_nav');?>
        <!-- NAVIGATION END -->
        
        <!-- NEW/EDIT FORM START -->
        <?php
        if($can_create && $core->GetTask() != 'form_edit') {
            require_once($load->SectionOutput('form_new.php', 'time'));
        }
        if($can_edit && $core->GetTask() == 'form_edit' && $id) {
            require_once($load->SectionOutput('form_edit.php', 'time'));
        }
        ?>
        <!-- NEW/EDIT FORM END -->
        
        <!-- TABLE START -->
        <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th align="center" class="sectiontableheader title">#</th>
                    <?php if($can_delete && $core->GetTask() != 'form_edit') { ?>
                        <th align="center" class="sectiontableheader title"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $total; ?>);" /></th>
                    <?php } ?>
                   <th align="left" width="15%" class="sectiontableheader title"><?php echo $table->TH(0); // DATE ?></th>
                   <th align="left" width="10%" class="sectiontableheader title"><?php echo $table->TH(1); // TIME LOGGED ?></th>
                   <th align="left" width="25%" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(2); // TASK  ?></th>
                   <th align="left" width="20%" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(3); // USER ?></th>
                   <th align="left" width="30%" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(4); // DESCRIPTOIN ?></th>
                   <th align="left" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(5); // ID ?></th>
                </tr>
            </thead>
            <tbody id="table_body">
            <?php
            $k    = 0;
            $html = '';
            $this_task = $core->GetTask();
 	        foreach ($rows AS $i => $row)
 	        {
 	  	        JFilterOutput::objectHTMLSafe($row);

 	  	        $checkbox     = '<input id="cb'.$i.'" name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox"/>';
                $link_edit    = PFformat::Link("section=time&task=form_edit&id=$row->id");
                $link_details = PFformat::Link("section=tasks&task=display_details&id=$row->task_id");
                $time = (int) $row->timelog;
                $total_time = $total_time + $time;
                $h = floor($time / 60);
                $m = $time - floor(60 * $h);
                
                $html .= '<tr class="row'.$k.' sectiontableentry'.($k + 1).'">
  	                      <td>'.$pagination->getRowOffset( $i ).'</td>';
  	                
  	            if($can_delete && $this_task != 'form_edit') {
                    $html .= '<td align="center">'.$checkbox.'</td>';
                }
                
                $html .= '<td>'.PFformat::ToDate($row->cdate);
                $html .= $table->Menu();
                if($user->Access('form_edit', 'time', $row->user_id) && $this_task != 'form_edit') {
                    $html .= $table->MenuItem($link_edit,'EDIT','pf_edit');
                }
                $html .= $table->Menu(false).'</td>';
                
                $html .= '<td>'.$h.PFformat::Lang('PFL_H').' '.$m.PFformat::Lang('PFL_M').'</td>
  	                      <td class="pf_time_title item_title">';
  	                      
  	            if($user->Access('display_details', 'tasks', $row->user_id)) {
                    $html .= '<strong><a href="'.$link_details.'">'.$row->title.'</a></strong>';
                }
                else {
                    $html .= '<strong>'.$row->title.'</strong>';
                }
                
                $html .= '</td><td>';
                
                if($user->Access('display_details', 'profile')) {
                    $html .= '<a href="'.PFformat::Link("section=profile&task=display_details&id=$row->user_id").'">'.$row->name.'</a>';
                }
                else {
                    $html .= $row->name;
                }
                
                $html .= '</td>
                    <td>'.$row->content.'</td>
  	                <td>'.$row->id.'</td>
  	            </tr>';

  	            $k = 1 - $k;
 	        }
 	        echo $html;
 	        unset($html);
 	        
 	        if( !count($rows) ) {
      	        echo '<tr class="row0 sectiontableentry1">
      	                  <td colspan="10" align="center"><div class="pf_info">'.PFformat::Lang('NO_ENTRIES').'</div></td>
      	              </tr>';
 	        }
 	        else {
                if($this_task != 'form_edit') {
                    $h = floor($total_time / 60);
                    $m = $total_time - floor(60 * $h);
                
                    echo '<tr class="row0 sectiontableentry1">
      	                  <td colspan="3" align="center"><strong>'.PFformat::Lang('TOTAL_TIME_SPENT').'</strong></td>
      	                  <td>'.$h.PFformat::Lang('PFL_H').' '.$m.PFformat::Lang('PFL_M').'</td>
      	                  <td colspan="4">&nbsp;</td>
      	                  </tr>';
                }
 	        }
            ?>
            <tr>
                <td colspan="10" style="text-align:center"><?php echo $pagination->getListFooter(); ?></td>
            </tr>
            </tbody>
        </table>
        <!-- TABLE END -->

    </div>
</div>
<?php
echo $form->HiddenField("boxchecked", 0);
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("ob", $ob);
echo $form->HiddenField("od", $od);
echo $form->End();
?>