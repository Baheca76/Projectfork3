<?php
/**
* $Id: form_edit_milestone.php 868 2011-03-22 11:56:57Z angek $
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

?>
<script type="text/javascript">
function task_update_milestone(apply_value)
{
	if(document.adminForm.title.value == "") {
		alert("<?php echo PFformat::Lang('V_TITLE');?>");
	}
	else {
		document.adminForm.apply.value = apply_value;
        submitform( "task_update_milestone" );
	}
}
function switch_deadline(ch)
{
	var el = document.getElementById('deadline_table');
	if(ch) {
		el.style.display = "";
	}
	else {
		el.style.display = "none";
	}
}
</script>
<?php echo $form->Start();?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo $ws_title." / "; echo PFformat::Lang('TASKS');?> :: <?php echo PFformat::Lang('EDIT_MILESTONE');?></h1>
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
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('DESC');?></td>
                        <td colspan="2"><?php echo $form->InputField('content', '', 'size="70" maxlength="255"');?></td>
                    </tr>
                </table>
            
<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('DEADLINE_AND_PRIORITY'), 'pane2');
?>
        
                <table class="admintable">
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('MILESTONE_HAS_DEADLINE'); ?></td>
                        <td><input type="checkbox" name="has_deadline" value="1" onclick="switch_deadline(this.checked);" <?php if($row->edate) echo 'checked="checked"';?>/></td>
                    </tr>
                    <tr <?php if(!$row->edate) {?>style="display:none"<?php }?> id="deadline_table">
                        <td class="key"><?php echo PFformat::Lang('DATE');?></td>
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
                  <td class="key"><?php echo PFformat::Lang('PRIORITY');?></td>
                  <td><?php echo $form->SelectPriority('prio', $row->priority);?></td>
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
echo $form->HiddenField("id", $row->id);
echo $form->HiddenField("apply");
echo $form->End();
?>