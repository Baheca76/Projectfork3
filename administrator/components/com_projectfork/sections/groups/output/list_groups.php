<?php
/**
* $Id: list_groups.php 837 2010-11-17 12:03:35Z eaxs $
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
function task_delete()
{
	if(!document.adminForm.boxchecked.value) {
		alert('<?php echo htmlspecialchars(PFformat::Lang('ALERT_LIST'));?>');
	}
	else {
		submitbutton('task_delete');
	}
}
function task_copy()
{
	if(!document.adminForm.boxchecked.value) {
		alert('<?php echo htmlspecialchars(PFformat::Lang('ALERT_LIST'));?>');
	}
	else {
		submitbutton('task_copy');
	}
}
</script>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('GROUPS');?>
        <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    <div class="pf_body">
    
    <!-- NAVIGATION START -->
    <?php PFpanel::Position('groups_nav') ;?>
    <!-- NAVIGATION END -->
    
    <!-- TABLE START -->
    <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th align="center" class="sectiontableheader title">#</th>
            <?php if($can_delete || $can_copy) { ?>
                <th align="center" class="sectiontableheader title"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" /></th>
            <?php } ?>
            <th align="left" width="30%" class="sectiontableheader title"><?php echo $table->TH(0); // Title ?></th>
            <th align="left" width="50%" class="sectiontableheader title"><?php echo $table->TH(1); // Desc ?></th>
            <th align="left" width="20%" class="sectiontableheader title"><?php echo $table->TH(2); // Users ?></th>
            <th align="left" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(3);  // ID ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
             $k = 0;
             $html = '';
 	         foreach ($rows AS $i => $row)
 	         {
 	  	         JFilterOutput::objectHTMLSafe($row);
 	  	 
 	  	         if($flag != 'system_administrator') {
 	  	 	         if(in_array($row->id, $restricted)) continue;
 	  	         }
 	  	         
 	  	         // Translate title
 	  	         $title = PFformat::Lang($row->title);
 	  	         if($jversion->RELEASE == '1.6' && $project == 0) {
 	  	             $title = str_replace('{group_name}', $row->jtitle, $title);
 	  	         }
 	  	 
 	  	         $disabled   = "";
 	  	         $user_count = $row->user_count;
 	  	 
 	  	         if(in_array($row->id, $restricted)) {
 	  	 	         $disabled = 'disabled="disabled" readonly="readonly"';
 	  	 	         $user_count = PFformat::Lang('GROUP_AUTO');
 	  	         }
 	  	 
 	  	         $checkbox  = "<input type='checkbox' id='cb$i' value='$row->id' name='cid[]' onclick='isChecked(this.checked);' $disabled/>";
 	  	         $edit_link = PFformat::Link("section=groups&task=form_edit&id=$row->id");
 	  	         
 	  	         $html .= '<tr class="row'.$k.' sectiontableentry'.($k + 1).'">
                               <td align="center">'.$pagination->getRowOffset( $i ).'</td>';
                               
                 if($can_delete || $can_copy) {
                     $html .= '<td align="center">'.$checkbox.'</td>';
                 }
                 
                 $html .= '<td class="pf_groups_title item_title"><strong>'.$title.'</strong>';
                 $html .= $table->Menu();
                 $html .= $table->MenuItem($edit_link,'TT_EDIT','pf_edit');
                 $html .= $table->Menu(false);
                 $html .= '</td>';

                 $html .= '<td>'.PFformat::Lang($row->description).'</td>'
  	                    . '<td>'.$user_count.'</td>
  	                       <td>'.$row->id.'</td>
  	                       </tr>';
  	                       
  	             $k = 1 - $k;
  	         }
  	         echo $html;
  	         unset($html);
  	         if( count($rows) == 0 ) {
                 ?>
                 <tr>
      	             <td colspan="6"><div class="pf_info"><?php echo PFformat::Lang('NO_GROUPS');?></div></td>
      	         </tr>
                 <?php
  	         }
  	     ?>
         <tr>
             <td colspan="8" style="text-align:center"><?php echo $pagination->getListFooter(); ?></td>
         </tr>
    </tbody>
    </table>
    <!-- TABLE END -->

    </div>
</div>
<?php
echo $form->HiddenField('option');
echo $form->HiddenField('section');
echo $form->HiddenField('task');
echo $form->HiddenField('ob', $order_by);
echo $form->HiddenField('od', $order_dir);
echo $form->HiddenField('boxchecked');
echo $form->End();
?>