<?php
/**
* $Id: form_new.php 911 2011-07-20 14:02:11Z eaxs $
* @package    Projectfork
* @subpackage Users
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

$jversion = new JVersion();
$group_field = 'gid';
if($jversion->RELEASE != '1.5') $group_field = 'groups';

echo $form->Start();
?>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo $ws_title." / "; echo PFformat::Lang('USERS');?> :: <?php echo PFformat::Lang('NEW');?></h1>
    </div>
    <div class="pf_body">

        <!-- NAVIGATION START -->
        <?php PFpanel::Position('users_nav');?>
        <!-- NAVIGATION END -->

        <div class="col">

            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('PERSONAL_INFO');?></legend>
                <table class="admintable">
                	<tr>
                	    <td class="key required" width="150"><?php echo PFformat::Lang('NAME');?></td>
                	    <td><?php echo $form->InputField('name*', '', 'size="25"');?></td>
                	</tr>
                    <tr>
                        <td class="key required" width="150"><?php echo PFformat::Lang('USERNAME');?></td>
                        <td><?php echo $form->InputField('username*', '', 'size="30"');?></td>
                    </tr>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('LANGUAGE');?></td>
                        <td><?php echo $form->SelectLanguage('language');?></td>
                    </tr>
                </table>
            </fieldset>

            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('ACCESS_INFO');?></legend>
                <table class="admintable">
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('ACL');?></td>
                        <td><?php echo $form->SelectAccessLevel('accesslvl', -1);?></td>
                    </tr>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('JGROUP');?></td>
                        <td><?php echo $form->SelectJoomlaGroup($group_field, 18);?></td>
                    </tr>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('PASSWORD');?></td>
                        <td><?php echo $form->PasswordField('password', '', 'size="25"');?></td>
                    </tr>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('PASSWORD2');?></td>
                        <td><?php echo $form->PasswordField('password2', '', 'size="25"');?></td>
                    </tr>
                </table>
            </fieldset>

            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('GROUPS');?></legend>
                <table class="admintable">
                    <tr>
                        <td class="key" width="150" valign="top"><a href="javascript:addGroupSelect('group_template','group_target')"><?php echo PFformat::Lang('ADD');?></a></td>
                        <td id="group_target"></td>
                    </tr>
                </table>
            </fieldset>

        </div>
        <div class="col">

            <fieldset class="adminform">
            <legend><?php echo PFformat::Lang('CONTACT_INFO');?></legend>
            <table class="admintable">
               <tr>
                  <td class="key required" width="150"><?php echo PFformat::Lang('EMAIL');?></td>
                  <td><?php echo $form->InputField('email*', '', 'size="40"');?></td>
               </tr>

               <tr>
                  <td class="key" width="150"><?php echo PFformat::Lang('PHONE');?></td>
                  <td><?php echo $form->InputField('phone', '', 'size="30"');?></td>
               </tr>
               <tr>
                  <td class="key" width="150"><?php echo PFformat::Lang('PHONE_MOBILE');?></td>
                  <td><?php echo $form->InputField('mobile_phone', '', 'size="30"');?></td>
               </tr>
               <tr>
                  <td class="key" width="150"><?php echo PFformat::Lang('SKYPE');?></td>
                  <td><?php echo $form->InputField('skype', '', 'size="30"');?></td>
               </tr>
               <tr>
                  <td class="key" width="150"><?php echo PFformat::Lang('MSN');?></td>
                  <td><?php echo $form->InputField('msn', '', 'size="30"');?></td>
               </tr>
               <tr>
                  <td class="key" width="150"><?php echo PFformat::Lang('ICQ');?></td>
                  <td><?php echo $form->InputField('icq', '', 'size="30"');?></td>
               </tr>
            </table>
         </fieldset>
         <fieldset class="adminform">
            <legend><?php echo PFformat::Lang('LOCATION');?></legend>
            <table class="admintable">
               <tr>
                  <td class="key" width="150"><?php echo PFformat::Lang('STREET');?></td>
                  <td><?php echo $form->InputField('street', '', 'size="40"');?></td>
               </tr>
               <tr>
                  <td class="key" width="150"><?php echo PFformat::Lang('CITY');?></td>
                  <td><?php echo $form->InputField('city', '', 'size="30"');?></td>
               </tr>
               <tr>
                  <td class="key" width="150"><?php echo PFformat::Lang('ZIP');?></td>
                  <td><?php echo $form->InputField('zip', '', 'size="20"');?></td>
               </tr>
            </table>
         </fieldset>
</div>
<div class="clr"></div>

    </div>
</div>
<?php
echo $form->HiddenField("option", "com_projectfork");
echo $form->HiddenField("section", "users");
echo $form->HiddenField("task", "task_save");
echo $form->End();
?>
<div id="group_template" style="display:none">
<?php echo $select_group; ?>
</div>
<script type="text/javascript">
function addGroupSelect(template_id, target_id)
{
	var template = document.getElementById(template_id).innerHTML;

	var div = document.createElement('div');
	    div.style.padding = '2px';
	    div.innerHTML = template;

	document.getElementById(target_id).appendChild(div);
}
function task_save()
{
	var d = document.adminForm;
	var e = "";
	if(d.username.value == '') {e = "<?php echo PFformat::Lang('V_USERS_USERNAME');?>";}
	if(d.name.value == '') {e = "<?php echo PFformat::Lang('V_USERS_FNAME');?>";}
	if(d.email.value == '') {e = "<?php echo PFformat::Lang('V_USERS_EMAIL');?>";}
	if(e) {alert(e);}
	else {submitbutton('task_save');}
}
</script>