<?php
/**
* $Id: form_invite.php 837 2010-11-17 12:03:35Z eaxs $
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
?>
<script type="text/javascript">
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
<?php
echo $form->Start();
?>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo $ws_title." / "; echo PFformat::Lang('USERS');?> :: <?php echo PFformat::Lang('IMPORT_USER'); ?></h1>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START -->
        <?php PFpanel::Position('users_nav');?>
        <!-- NAVIGATION END -->
        
        <fieldset class="adminform">
             <legend><?php echo $load->ThemeImg("add.png").PFformat::Lang('IMPORT_USER');?></legend>
             <table class="admintable" width="100%">
                 <?php if(!$invite_select) { ?>
                 <tr>
                     <td class="key" width="100%" style="text-align:left !important" colspan="3"><?php echo PFformat::Lang('INVITE_PEOPLE_DESC');?></td>
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
        </fieldset>

    </div>
</div>
<?php
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->End();
if($invite_select) {
?>
<div id="user_template" style="display:none">
<?php echo $select_user; ?>
</div>
<?php } ?>