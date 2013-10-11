<?php
/**
* $Id: form_edit_task.php 868 2011-03-22 11:56:57Z angek $
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
        <h1><?php echo $ws_title." / "; echo PFformat::Lang('TASKS');?> :: <?php echo PFformat::Lang('EDIT');?></h1>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('tasks_nav');?>
        <!-- NAVIGATION END -->
        
<?php
jimport('joomla.html.pane');
$tabs = JPane::getInstance('Tabs');
echo $tabs->startPane('paneID');
echo $tabs->startPanel(PFformat::Lang('GENERAL_INFORMATION'), 'pane1');
?>
        

                <table class="admintable">
                    <tr>
                        <td class="key required" width="150"><?php echo PFformat::Lang('TITLE');?></td>
                        <td><?php echo $form->InputField('title*', '', 'size="40" maxlength="124"');?></td>
                    </tr>
                    <?php if($use_milestones) { ?>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('MILESTONE');?></td>
                        <td><?php echo $form->SelectMilestone('milestone', -1);?></td>
                    </tr>
                    <?php } if($use_progperc) { ?>
                    <tr>
                        <td class="key"><?php echo PFformat::Lang('PROGRESS');?></td>
                        <td><?php echo $form->SelectProgress('progress');?></td>
                    </tr>
                    <?php } else { ?>
                    <tr>
                        <td class="key"><?php echo PFformat::Lang('TASK_COMPLETED');?></td>
                        <td><?php $form->SetBind(false); echo $form->SelectNY('progress', (($row->progress == 100) ? 1 : 0)); $form->SetBind(true, $row);?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('PRIORITY');?></td>
                        <td><?php echo $form->SelectPriority('prio', (($row->priority > 0) ? $row->priority : -1));?></td>
                    </tr>
                    <tr>
                        <td class="key"><?php echo PFformat::Lang('DEADLINE');?></td>
                        <td><input type="checkbox" name="has_deadline" value="1" onclick="switch_deadline(this.checked);" <?php if($row->edate) { echo 'checked="checked"'; }?>/>
                        <?php echo PFformat::Lang('TASK_HAS_DEADLINE'); ?></td>
                    </tr>
                    <tr <?php if(!$row->edate) { echo 'style="display:none"'; }?> id="dealine_table">
                        <td class="key" width="150"><?php echo PFformat::Lang('DATE');?></td>
                        <td>
                            <?php 
							if ($now == $date_format) {
								if ($date_exists) {
									$now = date('Y-m-d', $row->edate);
								}
								else {
									$now = JHTML::_('date','',PFformat::JhtmlCalendarDateFormat());
								}
								echo JHTML::calendar($now, 'edate', 'edate');
							}
							else {
								echo JHTML::calendar($now, 'edate', 'edate', $date_format);
							}
							?>
                            <?php echo PFformat::Lang('HOUR');?>
                            <?php echo $form->SelectHour('hour', $row->hour);?>
                            <?php echo PFformat::Lang('MINUTE');?>
                            <?php echo $form->SelectMinute('minute', $row->minute);?>
                            <?php echo $form->SelectAmPm('ampm', $row->ampm);?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                        <?php 
                            if($use_editor && !defined('PF_DEMO_MODE')) { 
                         	    echo $editor->display( 'text',  $row->content , '100%', '350', '75', '20' ) ;
                            } 
                            else {
                         	    echo $form->TextArea('text',$row->content,'75', '20');
                            }
                        ?>
                        </td>
                    </tr>
                </table>

<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('TASK_RESPONSIBLE'), 'pane2');
?>
            <table class="admintable">
               <tr>
                   <td class="key" width="150" valign="top"><a href="javascript:add_user();"><?php echo PFformat::Lang('ADD_MEMBER');?></a></td>
                   <td id="user_container">
                   <?php
                   foreach($row->assigned AS $assigned)
                   {
                       echo "<div style='padding:2px;'>";
                       echo $form->SelectUser('assigned[]', $assigned);
                       echo "</div>";
                   }
                   ?>
                   </td>
               </tr>
            </table>
<?php
echo $tabs->endPanel();
echo $tabs->endPane();
?>
    </div>
</div>
<?php
$form->SetBind(true, 'REQUEST');
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("limitstart");
echo $form->HiddenField("keyword");
echo $form->HiddenField("id");
echo $form->HiddenField("apply", 0);
echo $form->End();
?>
<script type="text/javascript">
function task_update_task(apply_value)
{
    if(document.adminForm.title.value == "") {
		alert("<?php echo PFformat::Lang('V_TITLE');?>");
	}
	else {
		<?php if($use_editor && !defined('PF_DEMO_MODE')) { echo $editor->save( 'text' ); } ?>
		document.adminForm.apply.value = apply_value;
        submitbutton( "task_update_task" );
	}
}
function switch_deadline(ch)
{
	var el = document.getElementById('dealine_table');
	if(ch) {
		el.style.display = "";
	}
	else {
		el.style.display = "none";
	}
}
function add_user()
{
	var template = document.getElementById('user_template').innerHTML;
	var dest     = document.getElementById('user_container');

	var div = document.createElement('div');
	    div.style.padding = '2px';
	    div.innerHTML = template;

	dest.appendChild(div);
}
</script>
<div id="user_template" style="display:none">
<?php echo $select_user; ?>
</div>