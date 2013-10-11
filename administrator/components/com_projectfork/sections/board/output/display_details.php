<?php
/**
* $Id: display_details.php 837 2010-11-17 12:03:35Z eaxs $
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

$avc = class_exists('PFavatar');
$avatar = PFavatar::Display($row->author);

echo $form->Start();
?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('BOARD')." :: ".htmlspecialchars($row->title);?>
        <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    <div class="pf_body">
    
         <!-- NAVIGATION START-->
         <?php PFpanel::Position('board_nav');?>
         <!-- NAVIGATION END -->
         
         <table class="pf_board" width="100%">
             <tr class="row1 sectiontableentry2">
                 <td class='date'><?php echo PFformat::ToDate($row->cdate);?></td>
                 <td class='title' valign='top' align='left'><?php echo htmlspecialchars($row->title);?></td>
             </tr>
             <tr class='row1 sectiontableentry2'>
                 <td class='author' valign='top' align='center' width='10%' nowrap='nowrap' style="text-align:center">
                     <div class="pf_avatar"><?php echo $avatar;?>
                     </div>
                     <strong>
                         <a href="<?php echo PFformat::Link("section=profile&task=display_details&id=$row->author");?>">
                             <?php echo htmlspecialchars($row->name);?>
                         </a>
                     </strong>
                 </td>
                 <td class='content' valign='top' align='left'><?php echo nl2br($row->content);?></td>   
             </tr>
             <?php
                 // List replies
                 $k = 0;
                 $html = '';
                 $img_edit   = $load->ThemeImg('reply.png');
                 $img_delete = $load->ThemeImg('action_delete.png');
                 foreach ($rows AS $row)
                 {
   	                 $edit_link   = "";
   	                 $delete_link = "";
   	   
                     $avatar = PFavatar::Display($row->author);

   	                 if($user->Access('form_edit_reply', 'board', $row->author)) {
   	   	                 $edit_link = "<a href='".PFformat::Link("section=board&task=form_edit_reply&rid=$row->id&id=$id")."#pf_reply' class='pf_button'>"
                                    . $img_edit
                                    . "</a>";
                     }
   	   
   	                 if($user->Access('task_delete_reply', 'board', $row->author)) {
   	   	                 $delete_link = "<a href='javascript:task_delete_reply($row->id);' class='pf_button'>".$img_delete."</a>";
   	                 }
   	                 
   	                 $html .= '
                     <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
                         <td class="date" valign="top" align="left">'.PFformat::ToDate($row->cdate).'
                         </td>
                         <td class="title" valign="top" align="left" nowrap="nowrap">
                             <a href="#r_'.$row->id.'" id="r_'.$row->id.'">'.htmlspecialchars($row->title).'</a></td>
   	                 </tr>
   	                 <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
                         <td class="author" valign="top" align="center" nowrap="nowrap" style="text-align:center">
                             <div class="pf_avatar">'.$avatar.'</div>
                             <strong>
                                 <a href="'.PFformat::Link("section=profile&task=display_details&id=$row->author").'">
                                     '.htmlspecialchars($row->name).'
                                 </a>
                             </strong>
                         </td>
                        <td class="content" valign="top" align="left">'.nl2br($row->content).'</td>
   	                 </tr>
   	                 <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
   	                     <td class="edit" colspan="2" align="right">'.$edit_link.$delete_link.'</td>
   	                    </tr>';
   	                 $k = 1 - $k;
                 }
                 echo $html;
                 $html = '';
             ?>
         </table>
         
         <a id="pf_reply"></a>
         
         <?php if($user->Access($task, 'board')) { 
             $avatar = PFavatar::Display($user->GetId());
         ?>
         
                 <h3><?php echo PFformat::Lang('REPLY'); if($task == 'form_edit_reply') echo " :: ".PFformat::Lang('EDIT'); ?></h3>
                 
                 <?php if($task == 'display_details') { ?>
                     <table class="pf_board" width="100%">
                         <tr class="row0 sectiontableentry1">
                             <td class='date'><?php echo PFformat::ToDate(time());?></td>
                             <td class='title' valign='top' align='left'>
                                 <input type='text' class='inputbox' name='title' size='40' value='<?php echo htmlspecialchars($row->title);?>'/>                                 
                             </td>
                         </tr>
                         <tr class='row0 sectiontableentry1'>
                             <td class='author' valign='top' align='center' nowrap='nowrap' style="text-align:center">
                                 <div class="pf_avatar"><?php echo $avatar;?>
                                 </div>
                                 <strong>
                                     <?php echo htmlspecialchars($user->GetName());?>
                                 </strong>
                             </td>
                             <td class='content' valign='top' align='left'>
                                 <?php 
                                 if($use_editor && !defined('PF_DEMO_MODE')) { 
                                     echo $editor->display( 'text',  "" , '100%', '350', '75', '20' ) ;
                                 }
                                 else {
                                     echo "<textarea name='text' class='text' rows='5' cols='40' style='width:100%'></textarea>";
                                 } 
                                 ?>       
                             </td>   
                         </tr>
                         <tr class="row0 sectiontableentry1">
                         	<td class='save' colspan='2'>
                         		<input type='button' class='pf_button' value='<?php echo PFformat::Lang('SAVE');?>' onclick="task_save_reply()"/>
                         	</td>
                         </tr>
                     </table>
                 <?php } ?>
                 
                 <?php if($task == 'form_edit_reply') { 
                     $avatar = PFavatar::Display($row->author);
                 ?>
                     <table class="pf_board" width="100%">
                         <tr class="row1 sectiontableentry2">
                             <td class='date'><?php echo PFformat::ToDate($edit->cdate);?></td>
                             <td class='title' valign='top' align='left'>
                                 <input type='text' name='title' size='40' value='<?php echo htmlspecialchars($edit->title);?>'/>
                             </td>
                         </tr>
                         <tr class='row1 sectiontableentry2'>
                             <td class='author' valign='top' align='center' width='10%' nowrap='nowrap' style="text-align:center">
                                 <div class="pf_avatar"><?php echo $avatar;?></div>
                                 <strong><?php echo htmlspecialchars($row->name);?></strong>
                             </td>
                             <td class='content' valign='top' align='left'>
                                 <?php
                                 if($use_editor && !defined('PF_DEMO_MODE')) { 
                                     echo $editor->display( 'text',  $edit->content , '100%', '350', '75', '20' ) ;
                                 }
                                 else {
                                     echo "<textarea name='text' class='text' rows='5' cols='40' style='width:100%'>".htmlspecialchars($edit->content)."</textarea>";
                                 }
                                 ?>
                             </td>   
                         </tr>
                         <tr class="row1 sectiontableentry2">
                         	<td class='save' colspan='2' align='right'>
                         		<input type='button' class='pf_button' value='<?php echo PFformat::Lang('SAVE');?>' onclick="task_update_reply()"/>
                         		<input type='button' class='pf_button' value='<?php echo PFformat::Lang('CANCEL');?>' onclick="task_save_cancel()"/>
                         	</td>
                         </tr>
                     </table>
                 <?php } ?>
         <?php } ?> 
    </div>
</div>
<?php
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task", 'task_save_reply');
echo $form->HiddenField("id");
echo $form->HiddenField("rid");
echo $form->HiddenField("limitstart");
echo $form->End();
?>
<script type="text/javascript">
function task_save_reply()
{
	<?php if($use_editor && !defined('PF_DEMO_MODE')) echo $editor->save( 'text' );?>
	document.adminForm.task.value = 'task_save_reply';
	document.adminForm.submit();
}
function task_update_reply()
{
	<?php if($use_editor && !defined('PF_DEMO_MODE')) echo $editor->save( 'text' );?>
	document.adminForm.task.value = 'task_update_reply';
	document.adminForm.submit();
}
function task_delete_reply(el)
{
	if(confirm("<?php echo PFformat::Lang('CONFIRM_ACTION');?>")) {
		document.adminForm.task.value = 'task_delete_reply';
		document.adminForm.rid.value = el;
	    document.adminForm.submit();
	}
}
function task_save_cancel()
{
	document.location = "<?php echo PFformat::Link("section=board&task=display_details&id=".$id."&limitstart=".$limitstart, true);?>";
}
</script>