<?php
/**
* $Id: list_topics.php 863 2011-03-21 00:00:29Z angek $
* @package    Projectfork
* @subpackage Board
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
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('BOARD');?>
        <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('board_nav');?>
        <!-- NAVIGATION END -->
        
        <!--WIZARD START-->
        <?php if($wizard && $can_ctopic) { ?>
        <div id="pf_panel_pf_wizard">
        <div class="pf-panel-body">
        	<div class="pf-wizard pf-first-task">
        		<h3><?php echo PFformat::Lang('WIZ_BOARD_TITLE');?></h3>
        		<p><?php echo PFformat::Lang('WIZ_BOARD_DESC');?></p>
        		<a href="<?php echo PFformat::Link("section=board&task=form_new_topic");?>" class="pf_button"><?php echo PFformat::Lang('WIZ_BOARD_BTN_NT');?></a>
        	</div>
        </div>
        </div>
        <?php } ?>
        <!--WIZARD END-->
        
        <!-- TABLE START -->
        <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th align="center" class="sectiontableheader title pf_number_header">#</th>
                    <?php if($can_subscribe || $can_unsubscribe) { ?>
                        <th align="center" class="sectiontableheader title pf_check_header"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" /></th>
                        <th align="center" nowrap="nowrap" class="sectiontableheader title pf_subscribe_header"><?php echo $load->ThemeImg('letter.png');?></th>
                    <?php } ?>
                    <th align="left" class="sectiontableheader title pf_author_header"><?php echo $table->TH(0); // AUTHOR ?></th>
                    <th align="left" class="sectiontableheader title pf_title_header"><?php echo $table->TH(1); // TITLE ?></th>
                    <th align="left" class="sectiontableheader title pf_replies_header"><?php echo $table->TH(2); // CREATED BY ?></th>
                    <th align="left" class="sectiontableheader title pf_created_header"><?php echo $table->TH(3); // CREATED ON ?></th>
                    <th align="left" class="sectiontableheader title pf_activity_header"><?php echo $table->TH(4); // LAST REPLY ?></th>
                    <th align="left" nowrap="nowrap" class="sectiontableheader title pf_id_header"><?php echo $table->TH(5); // ID ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
                $k = 0;
                $html = '';
                $img_info  = $load->ThemeImg('letter.png');
                $img_info2 = $load->ThemeImg('letter_open.png');
				
				$config = PFconfig::GetInstance();
				$showtips =  (int) $config->Get('tooltip_help');
				$class = $showtips ? 'hasTip' : '';
				$title = null;
				
                foreach ($rows AS $i => $row)
                {
   	                $link_subs = "";
                    $id_lock   = PFformat::IdHash($row->id,'board');
       
   	                $checkbox     = '<input id="cb'.$i.'" name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox" />';
   	                $link_details = PFformat::Link("section=board&task=display_details&id=$row->id");
   	                $link_edit    = PFformat::Link("section=board&task=form_edit_topic&id=$row->id");
   	                $link_delete  = "javascript:task_delete_topic($row->id, '$id_lock')";
   	   
   	                if($user->Access('task_subscribe', 'board', $row->author)) {
						if ($showtips){
							$title = PFformat::Lang('TT_SUB_TOPIC');
						}
   	   	                $link_subs = "<a href='".PFformat::Link('section=board&task=task_subscribe&cid[]='.$row->id)."'>"
                                   . "<span class='".$class."' title='".$title."'>".$img_info2."</span></a>";
   	                }
       
   	                if(in_array($row->id,$subscriptions) && $user->Access('task_unsubscribe', 'board', $row->author)) {
						if ($showtips){
							$title = PFformat::Lang('TT_UNSUB_TOPIC');
						}
   	   	                $link_subs = "<a href='".PFformat::Link('section=board&task=task_unsubscribe&cid[]='.$row->id)."'>"
                                   . "<span class='".$class."' title='".$title."'>".$img_info. "</span></a>";
   	                }
                     
   	                if(!$row->last_active) { 
   	   	                $last_active = PFformat::ToDate($row->edate); 
   	                }
   	                else {
   	   	                $last_active = PFformat::ToDate($row->last_active);
   	                }
   	                
   	                $html .= '
                    <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
   	                    <td class="pf_number_cell">'.$pagination->getRowOffset( $i ).'</td>';
   	                    
   	                if($can_subscribe || $can_unsubscribe) {
                        $html .= '
                        <td class="pf_check_cell">'.$checkbox.'</td>
                        <td class="pf_subscribe_cell">'.$link_subs.'</td>';
                    }
                    
                    $html .= '
                    <td class="pf_author_cell">'.PFavatar::Display($row->author.':'.$row->name).'</td>
                    <td class="pf_board_title item_title">
   	                   <strong><a href="'.$link_details.'">'.htmlspecialchars($row->title).'</a></strong>
   	                   <span class="pf_post_date">'.PFformat::ToDate($row->cdate).'</span>';
   	                   
   	                                       
                    $html .= $table->Menu();
                    if($preview) {
                        $html .= $table->MenuItem("javascript:toggle_content('tc_$row->id');",'TOGGLE_CONTENT','pf_show');
                    }
                    if($user->Access('form_edit_topic', 'board', $row->author)) {
                        $html .= $table->MenuItem($link_edit,'EDIT','pf_edit');
                    }
                    if($user->Access('task_delete_topic', 'board', $row->author)) {
                        $html .= $table->MenuItem($link_delete,'DELETE','pf_delete');
                    }
                    $html .= $table->Menu(false);
                    
                    if($preview) {
                        $html .= '<div class="pf_inline_message"><div id="tc_'.$row->id.'" class="pf_board_preview">'.$row->content.'';
                        $html .= '<a href="'.$link_details.'" class="pf_button">'.PFformat::Lang('READMORE').'</a>';
                        $html .= '</div><div class="pf_replies">'.PFformat::Lang('REPLIES'). " " .$row->replies.'</div></div>';
                    }
                    
                    $html .= '
                    </td>
   	                <td class="pf_replies_cell">'.$row->replies.'</td>
                    <td class="pf_created_cell">'.PFformat::ToDate($row->cdate).'</td>
   	                <td class="pf_activity_cell">'.$last_active.'</td>
   	                <td class="pf_id_cell">'.$row->id.'</td>
   	                </tr>
                    ';
                    
   	                $k = 1 - $k;
                }
                echo $html;
                unset($html);
                if(!count($rows)) {
                    echo '<tr class="row0 sectiontableentry1">
   	                <td colspan="9"><div class="pf_info">'.PFformat::Lang('NO_TOPICS').'</div></td>
   	                </tr>';
                }
            ?>
            <tr>
               <td colspan="9" style="text-align:center"><?php echo $pagination->getListFooter(); ?></td>
            </tr>
            </tbody>
   
        </table>
        <!-- TABLE END -->

    </div>
</div>
<?php
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("boxchecked", 0);
echo $form->HiddenField("ob", $ob);
echo $form->HiddenField("od", $od);
echo $form->HiddenField("id");
echo $form->HiddenField("idlock", 1);
echo $form->End();
?>
<script type="text/javascript">
function task_delete_topic(item, idl)
{
	var d = document.adminForm;
	if(confirm("<?php echo PFformat::Lang('CONFIRM_ACTION');?>")) {
		d.id.value = item;
        d.idlock.name = idl;
	    submitbutton('task_delete_topic');
	}
}
function task_subscribe()
{
	var d = document.adminForm;
	
	if(d.boxchecked.value == 0) {
		alert("<?php echo PFformat::Lang('ALERT_LIST');?>");
	}
	else {
		submitbutton('task_subscribe');
	}
}
function task_unsubscribe()
{
	var d = document.adminForm;
	
	if(d.boxchecked.value == 0) {
		alert("<?php echo PFformat::Lang('ALERT_LIST');?>");
	}
	else {
		submitbutton('task_unsubscribe');
	}
}
function toggle_content(el)
{
	var cs = document.getElementById(el).style.display;
	
	if(cs == 'none') {
		document.getElementById(el).style.display = '';
	}
	else {
		document.getElementById(el).style.display = 'none';
	}
}
</script>