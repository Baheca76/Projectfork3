<?php
/**
* $Id: list_users.php 837 2010-11-17 12:03:35Z eaxs $
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

echo $form->Start();
?>
<a id="pf_top"></a>
<div class="pf_container">
     <div class="pf_header componentheading">
         <h3><?php echo $ws_title." / "; echo PFformat::Lang('USERS');?>
         <?php echo PFformat::SectionEditButton();?>
        </h3>
     </div>
     <div class="pf_body">
     
         <!-- NAVIGATION START-->
         <?php PFpanel::Position('users_nav');?>
         <!-- NAVIGATION END -->
         
         <!-- TABLE START -->
         <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
             <thead>
                 <tr>
                     <th align="center" class="sectiontableheader title pf_number_header">#</th>
                     <th align="center" class="sectiontableheader title pf_check_header"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" /></th>
                     <th class="sectiontableheader title pf_avatar_header">&nbsp;</th>
                     <th align="left" class="sectiontableheader title pf_name_header"><?php echo $table->TH(0); // Name ?></th>
                     <th align="left" class="sectiontableheader title pf_username_header"><?php echo $table->TH(1); // User name ?></th>
                     <th align="left" class="sectiontableheader title pf_email_header"><?php echo $table->TH(2); // email ?></th>
                     <th align="left" nowrap="nowrap" class="sectiontableheader title pf_id_header"><?php echo $table->TH(3);  // ID ?></th>
                 </tr>
             </thead>
             <tbody>
             <?php
                 $k = 0;
                 $html = '';
 	             foreach ($rows AS $i => $row)
 	             {	
 	  	             JFilterOutput::objectHTMLSafe($row);
 	  	             if(!$row->id) continue;
 	  	              
 	  	             $link_edit = PFformat::Link("section=users&task=form_edit&id=$row->id");
 	  	             $checkbox = '<input id="cb'.$i.'" name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox">';
 	  	 
 	  	             if($row->id == $user->GetId()) $checkbox = "&nbsp;";
 	  	             
 	  	             $html .= '
                     <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
  	                     <td align="center" class="pf_number_cell">'.$pagination->getRowOffset( $i ).'</td>
  	                     <td align="center" class="pf_check_cell">'.$checkbox.'</td>
  	                     <td class="pf_avatar_cell">'.PFavatar::Display($row->id.':'.$row->name).'</td>
  	                     <td class="pf_users_title item_title pf_name_cell"><strong>';
  	                     
  	                 if($user->Access('display_details', 'profile')) {
  	                     $html .= '<a href="'.PFformat::Link("section=profile&task=display_details&id=$row->id").'">'.$row->name.'</a>';
  	                 }
  	                 else {
                         $html .= $row->name;
                     }
                     
                     $html .= '</strong>';
                     
                     // Start Item options menu
                     $html .= $table->Menu();
                     if($user->Access('form_edit', 'users')) $html .= $table->MenuItem($link_edit,'EDIT','pf_edit');
                     $html .= $table->Menu(false);
                     
                     $html .= '
                     </td>
  	                 <td class="pf_username_cell">'.$row->username.'</td>
  	                 <td class="pf_email_cell"><a href="mailto:'.$row->email.'">'.$row->email.'</a></td>
  	                 <td class="pf_id_cell">'.$row->id.'</td></tr>';
  	                 
  	                 $k = 1 - $k;
 	             }
 	             echo $html;
 	             unset($html);
                 if(!count($rows)) { ?>
      	         <tr>
      	             <td colspan="6"><div class="pf_info"><?php echo PFformat::Lang('NO_USERS');?></div></td>
      	         </tr>
                 <?php
                 }
             ?>
             <tr>
                 <td colspan="6" style="text-align:center"><?php echo $pagination->getListFooter(); ?></td>
             </tr>
             </tbody>   
         </table>
         <!-- TABLE END -->

    </div>
</div>
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
<?php
echo $form->HiddenField('task');
echo $form->HiddenField('boxchecked', 0);
echo $form->HiddenField('ob', 'u.id');
echo $form->HiddenField('od', 'ASC');
echo $form->End();
?>