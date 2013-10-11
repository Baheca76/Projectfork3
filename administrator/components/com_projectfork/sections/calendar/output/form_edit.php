<?php
/**
* $Id: form_edit.php 856 2011-02-24 15:07:15Z angek $
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
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo PFformat::Lang('CALENDAR');?> :: <?php echo PFformat::Lang('EDIT');?></h1>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('calendar_nav'); ?>
        <!-- NAVIGATION END -->
        <div class="col">
        
            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('DATE');?></legend>
                <table class="admintable">
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('START');?></td>
                        <td>
                            <?php 
							if ($start_date == $date_format) {
								$start_date = date('Y-m-d', $row->sdate);
								echo JHTML::calendar($start_date, 'sdate', 'sdate');
							}
							else {
								echo JHTML::calendar($start_date, 'sdate', 'sdate', $date_format);
							}
							?>
                            <?php echo PFformat::Lang('HOUR');?>
                            <?php echo $form->SelectHour('s_hour', $s_hour);?>
                            <?php echo PFformat::Lang('MINUTE');?>
                            <?php echo $form->SelectMinute('s_minute', $s_min);?>
                            <?php echo $form->SelectAmPm('s_ampm', $s_ampm);?>
                        </td>
                    </tr>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('END');?></td>
                        <td>
                            <?php 
							if ($end_date == $date_format) {
								$end_date = date('Y-m-d', $row->edate);
								echo JHTML::calendar($end_date, 'edate', 'edate');
							}
							else {
								echo JHTML::calendar($end_date, 'edate', 'edate', $date_format);
							}
							?>
                            <?php echo PFformat::Lang('HOUR');?>
                            <?php echo $form->SelectHour('e_hour', $e_hour);?>
                            <?php echo PFformat::Lang('MINUTE');?>
                            <?php echo $form->SelectMinute('e_minute', $e_min);?>
                            <?php echo $form->SelectAmPm('e_ampm', $e_ampm);?>
                        </td>
                    </tr>
                </table>
            </fieldset>
            
        </div>
        <div class="col">
        
            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('GENERAL_INFORMATION');?></legend>
                <table class="admintable">
                    <tr>
                        <td class="key required" width="150"><?php echo PFformat::Lang('TITLE');?></td>
                        <td><?php echo $form->InputField('title*', '', 'size="40" maxlength="56"');?></td>
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
            </fieldset>
            
        </div>
        
        <div class="clr"></div>
        
    </div>
</div>
<?php
$form->SetBind(true, 'REQUEST');
echo $form->HiddenField('option');
echo $form->HiddenField('section');
echo $form->HiddenField('task', 'task_update');
echo $form->HiddenField('year');
echo $form->HiddenField('month');
echo $form->HiddenField('day');
echo $form->HiddenField('id', $row->id);
echo $form->End();
?>
<script type="text/javascript">
function task_update()
{
	var d = document.adminForm;
	var e = "";
	if(d.title.value == '') {e = "<?php echo PFformat::Lang('V_TITLE');?>";}
	if(e) {alert(e);}
	else { <?php if($use_editor && !defined('PF_DEMO_MODE')) { echo $editor->save( 'text' ); } ?> submitbutton('task_update');}
}
</script>