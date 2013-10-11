<?php
/**
* $Id: form_edit_accesslvl.php 911 2011-07-20 14:02:11Z eaxs $
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

echo $form->start();
?>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo $ws_title." / "; echo PFformat::Lang('USERS');?> :: <?php echo PFformat::Lang('ACCESS_LVLS');?> :: <?php echo PFformat::Lang('EDIT');?></h1>
    </div>
    <div class="pf_body">

        <!-- NAVIGATION START -->
        <?php PFpanel::Position('users_nav');?>
        <!-- NAVIGATION END -->

        <div class="col">

            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('GENERAL_INFORMATION');?></legend>
                <table class="admintable">
                    <tr>
                        <td class="key required" width="150"><?php echo PFformat::Lang('TITLE');?></td>
                        <td>
                            <?php
                            if(in_array($row->id, $restricted)) {
                                if($jversion->RELEASE != '1.5') {
                                    echo str_replace('{group_name}', $row->jtitle, PFformat::Lang($row->title));
                                }
                                else {
                                    echo PFformat::Lang($row->title);
                                }
                            }
                            else {
                                echo $form->InputField('title*', '', 'size="60" maxlength="124"');
                            }
                            ?>
                        </td>
                    </tr>
                    <tr <?php if(!$use_score) { echo 'style="display:none !important;"'; } ?>>
                        <td class="key" width="150"><?php echo PFformat::Lang('SCORE');?></td>
                        <td><?php echo $form->InputField('score', '0', 'size="10" maxlength="11"');?></td>
                    </tr>
                </table>
            </fieldset>

          <fieldset class="adminform">
              <legend><?php echo PFformat::Lang('FLAG');?></legend>
              <table class="admintable">
                  <tr>
                      <td class="key" width="150"><?php echo PFformat::Lang('PFL_NONE');?></td>
                      <td><input type="radio" name="f" value="" <?php if($row->flag == "") echo 'checked="checked"'; ?>/></td>
                  </tr>
                  <?php
                  foreach ($flags AS $f)
                  {
               	       if($f->name == 'project_administrator' && $flag == '') continue;
               	       if($f->name == 'system_administrator' && $flag == '') continue;
               	       if($f->name == 'system_administrator' && $flag == 'project_administrator') continue;

                       $checked = "";
               	       if($row->flag == $f->name) $checked = 'checked="checked"';
               	       ?>
               	       <tr>
               	           <td class="key" width="150"><?php echo PFformat::Lang($f->title);?></td>
               	           <td><input type="radio" name="f" value="<?php echo $f->name;?>" <?php echo $checked;?>/></td>
               	       </tr>
               	       <?php
                  }
                  ?>
              </table>
          </fieldset>

        </div>

        <?php if(!in_array($row->id, $restricted)) { ?>
        <div class="col">

            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('USERS');?></legend>
                <table class="admintable">
                    <tr>
                        <td class="key" width="150" valign="top">
                            <a href="javascript:addUserSelect('user_template', 'member_list');"><?php echo PFformat::Lang('ADD_MEMBER');?></a>
                        </td>
                        <td id="member_list">
                        <?php
                        if(!is_array($row->users )) $row->users = array();

                        foreach ($row->users AS $user)
                        {
                  	         echo "<div style='padding-bottom:2px;'>".$form->SelectUser('user_id[]', $user)."</div>";
                        }
                        ?>
                        </td>
                    </tr>
                </table>
            </fieldset>

        </div>
        <?php } ?>
        <div class="clr"></div>

    </div>
</div>
<?php
echo $form->HiddenField("option", "com_projectfork");
echo $form->HiddenField("section", "users");
echo $form->HiddenField("task", "task_update_accesslvl");
echo $form->HiddenField("id", $row->id);
if(in_array($row->id, $restricted)) {
    echo $form->HiddenField("title", $row->title);
}
echo $form->End();
?>
<div id="user_template" style="display:none">
<?php echo $select_user; ?>
</div>
<script type="text/javascript">
function addUserSelect(template_id, target_id)
{
	var template = document.getElementById(template_id).innerHTML;

	var div = document.createElement('div');
	    div.style.padding = '2px';
	    div.innerHTML = template;

	document.getElementById(target_id).appendChild(div);
}
function task_update_accesslvl()
{
	var d = document.adminForm;
	var e = "";
	if(d.title.value == '') {e = "<?php echo PFformat::Lang('V_TITLE');?>";}
	if(e) {alert(e);}
	else {submitbutton('task_update_accesslvl');}
}
</script>