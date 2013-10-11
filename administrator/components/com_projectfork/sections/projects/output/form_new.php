<?php
/**
* $Id: form_new.php 864 2011-03-21 06:02:52Z angek $
* @package   Projectfork
* @copyright Copyright (C) 2006-2009 Tobias Kuhn. All rights reserved.
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

echo $form->Start();
?>

<script type="text/javascript">
function submitbutton(pressbutton)
{
	var ok = 1;
	var d  = document.adminForm;
    switch(pressbutton)
    {
    	case 'task_save':
    	    if(d.title.value == "") {
    	    	ok = 0;
    	    }
    	    <?php if($use_editor) { echo $editor->save( 'text' ); } ?>
    	    break;
    }

    if(ok == 0) {
    	alert("<?php echo PFformat::Lang('V_TITLE');?>");
    }
    else {
    	submitform( pressbutton );
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
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo $ws_title." / "; echo PFformat::Lang('PROJECTS');?> :: <?php echo PFformat::Lang('NEW');?></h1>
    </div>
    <div class="pf_body">
    
    <!-- NAVIGATION START-->
    <?php PFpanel::Position('projects_nav'); ?>
    <!-- NAVIGATION END -->
    
<?php
jimport('joomla.html.pane');
$tabs = JPane::getInstance('Tabs');
echo $tabs->startPane('paneID');
echo $tabs->startPanel(PFformat::Lang('GENERAL_INFORMATION'), 'pane1');
?>
    
    <!-- START PANES -->
    		<table class="admintable" width="100%">
    		    <tr>
    		        <td class="key required" width="150"><?php echo PFformat::Lang('TITLE');?></td>
    		        <td><?php echo $form->InputField('title*', '', 'size="50" maxlength="124"');?></td>
    		    </tr>
    		    <?php if($config->Get('use_cats', 'projects')) { ?>
    		    <tr>
    		        <td class="key required" width="150"><?php echo PFformat::Lang('CATEGORY');?></td>
    		        <td><?php echo PFprojectsHelper::SelectCategory('cat');?></td>
    		    </tr>
    		    <?php } ?>
    		    <?php if($user->Permission('flag') == 'system_administrator' || $user->Permission('flag') == 'project_administrator') { ?>
    		    <tr>
    		        <td class="key required" width="150"><?php echo PFformat::Lang('FOUNDER');?></td>
    		        <td><?php echo PFformat::Lang('FOUNDER_ONSAVE_DESC');?></td>
    		    </tr>
    		    <?php } ?>
    		    <tr>
    		        <td class="key"><?php echo PFformat::Lang('DEADLINE');?></td>
    		        <td><input type="checkbox" name="has_deadline" value="1" onclick="switch_deadline(this.checked);"/>
    		        <?php echo PFformat::Lang('PROJECT_HAS_DEADLINE'); ?></td>
    		    </tr>
		        <tr style="display:none" id="dealine_table">
		            <td class="key" width="150"><?php echo PFformat::Lang('DATE');?></td>
		            <td>
		                <?php 
						if ($now == $date_format) {
							echo JHTML::calendar(JHTML::_('date', '', PFformat::JhtmlCalendarDateFormat()), 'edate', 'edate');
						}
						else {
							echo JHTML::calendar($now, 'edate', 'edate', $date_format);
						}
						?>
						<?php echo PFformat::Lang('HOUR');?>
						<?php echo $form->SelectHour('hour', $hour);?>
						<?php echo PFformat::Lang('MINUTE');?>
						<?php echo $form->SelectMinute('minute', $minute);?>
						<?php echo $form->SelectAmPm('ampm', $ampm);?>
		            </td>
		        </tr>
    		    <tr>
    		        <td colspan="2">
    		        <?php 
    		        if($use_editor && !defined('PF_DEMO_MODE')) { 
    		            echo $editor->display( 'text',  "" , '100%', '350', '75', '20' ) ;
    		        }
    		        else {
    		            echo $form->TextArea('text','','75', '20');
    		        }
    		        ?>
    		        </td>
    		    </tr>
    		</table>	
<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('INVITE_PEOPLE'), 'pane2');
?>
    		<table class="admintable" width="100%">
    		
                 <?php if(!$invite_select) { ?>
                 <tr>
                     <td class="key" width="100%" style="text-align:left !important" colspan="3">
                         <?php echo PFformat::Lang('INVITE_PEOPLE_DESC');?>
                     </td>
                 </tr>
                 <?php } ?>
                 
                 <?php if($flag == 'system_administrator') { ?>
                 <tr>
                     <td class="key" width="100"><?php echo PFformat::Lang('FORCE_JOIN');?></td>
                     <td><input type="checkbox" name="force_join" value="1"/></td>
                     <td><?php echo PFformat::Lang('FORCE_JOIN_DESC');?></td>
                 </tr>
                 <?php } ?>
               
                 <?php if(!$invite_select) { ?>
                 <tr>
                     <td width="100%" colspan="3"><textarea name="invite" rows="5" cols="50" style="width:100%"></textarea></td>
                 </tr>
                 <?php } else { ?>
                 <tr>
                     <td class="key" width="150" valign="top"><a href="javascript:add_user();"><?php echo PFformat::Lang('ADD_MEMBER');?></a></td>
                     <td id="user_container" colspan="2"></td>
                 </tr>
                 <?php } ?>
             </table>
<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('PROJECT_SETTINGS'), 'pane3');
?>
    		<table class="admintable">
    		    <tr>
    		        <td class="key" width="150" valign="top"><?php echo PFformat::Lang('IS_PUBLIC');?></td>
    		        <td><?php echo $form->SelectNY('is_public');?></td>
    		        <td><?php echo PFformat::Lang('IS_PUBLIC_DESC');?></td>
    		    </tr>
    		    <tr>
    		        <td class="key" width="150" valign="top"><?php echo PFformat::Lang('ALLOW_JREQ');?></td>
    		        <td><?php echo $form->SelectNY('allow_register');?></td>
    		        <td><?php echo PFformat::Lang('ALLOW_JREQ_DESC');?></td>
    		    </tr>
    		    <?php if($allow_color) { ?>
    		        <tr>
    		            <td class="key" width="150" valign="top"><?php echo PFformat::Lang('COLOR'); ?></td>
    		            <td><?php echo $form->SelectColor('color');?></td>
    		            <td><?php echo PFformat::Lang('PROJECT_COLOR_DESC');?></td>
    		        </tr>
    		    <?php } if($allow_logo && !defined('PF_DEMO_MODE')) { ?>
    		        <tr>
    		          <td class="key" width="150" valign="top"><?php echo PFformat::Lang('LOGO'); ?></td>
    		          <td><?php echo $form->FileField('logo');?></td>
    		          <td><?php echo PFformat::Lang('PROJECT_LOGO_DESC');?></td>
    		        </tr>
    		    <?php } ?>
    		    <tr>
    		        <td class="key" width="150"><?php echo PFformat::Lang('WEBSITE');?></td>
    		        <td><?php echo $form->InputField('website', '', 'size="40" maxlength="255"');?></td>
    		        <td><?php echo PFformat::Lang('PROJECT_WEBSITE_DESC');?></td>
    		    </tr>
    		    <tr>
    		        <td class="key" width="150"><?php echo PFformat::Lang('EMAIL');?></td>
    		        <td><?php echo $form->InputField('email', '', 'size="40" maxlength="124"');?></td>
    		        <td><?php echo PFformat::Lang('PROJECT_EMAIL_DESC');?></td>
    		    </tr>
    		</table>
<?php
echo $tabs->endPanel();
echo $tabs->endPane();
?>
    	<div class="clr"></div>
    </div>
</div>
<?php
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("limit");
echo $form->HiddenField("limitstart");
echo $form->HiddenField("keyword");
echo $form->End();
if($invite_select) {
?>
<div id="user_template" style="display:none">
    <?php echo $select_user; ?>
</div>
<?php } ?>
