<?php
/**
* $Id: list_projects.php 888 2011-06-25 21:12:06Z eaxs $
* @package    Projectfork
* @subpackage Projects
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
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('PROJECTS');?><?php echo PFformat::SectionEditButton();?></h3>
    </div>
    <div class="pf_body">

        <!-- NAVIGATION START-->
        <?php PFpanel::Position('projects_nav');?>
        <!-- NAVIGATION END -->

        <!--WIZARD START-->
        <?php if($wizard && (count($my_projects) == 0) && $can_create) {;?>
        <div id="pf_panel_pf_wizard">
        <div class="pf-panel-body">
        	<div class="pf-wizard pf-first-project">
        		<h3><?php echo PFformat::Lang('WIZ_CP_NEWP_INTRO_TITLE');?></h3>
        		<p><?php echo PFformat::Lang('WIZ_CP_NEWP_INTRO_BODY');?></p>
        		<a href="<?php echo PFformat::Link('section=projects&task=form_new');?>" class="pf_button"><?php echo PFformat::Lang('WIZ_CP_NEWP_INTRO_BTN');?></a>
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
                <?php if($can_copy || $can_delete || $can_archive) { ?>
                    <th align="center" class="sectiontableheader title pf_check_header"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $total; ?>);" /></th>
                <?php } ?>
                <th align="left" class="sectiontableheader title pf_title_header"><?php echo $table->TH(0); // TITLE ?></th>
                <th align="left" class="sectiontableheader title pf_action_header"><span></span></th>
                <th align="left" class="sectiontableheader title pf_founder_header"><?php echo $table->TH(1); // FOUNDER ?></th>
                <th align="left" nowrap="nowrap" class="sectiontableheader title pf_created_header"><?php echo $table->TH(2);// CREATED ON ?></th>
                <th align="left" nowrap="nowrap" class="sectiontableheader title pf_deadline_header"><?php echo $table->TH(3);// DEADLINE ?></th>
                <th align="left" nowrap="nowrap" class="sectiontableheader title pf_id_header"><?php echo $table->TH(4); // ID ?></th>
            </tr>
        </thead>
        <tbody id="table_body">
        <?php
            $k    = 0;
            $html = "";
            $current_cat = "";
 	        foreach ($rows AS $i => $row)
 	        {
                // Sanitize data
 	  	        JFilterOutput::objectHTMLSafe($row);

                // Set deadline
 	  	        $row->edate = ($row->edate != 0) ? PFformat::ToDate($row->edate) : $row->edate = PFformat::Lang('NOT_SET');

 	  	        // Set custom color
                // $color = ($row->color != '') ? "style='color:#$row->color !important'" : NULL;
                $color = NULL;



                // Set selected workspace
 	  	        $sel_ws = ($status != 0) ? "" : "&workspace=$row->id";

                // Set links
 	  	        $l_details   = PFformat::Link("section=projects$sel_ws&task=display_details&id=$row->id".$filter);
 	  	        $l_edit      = PFformat::Link("section=projects&task=form_edit&id=$row->id&".$filter);
 	  	        $l_tasks     = PFformat::Link("workspace=$row->id&section=tasks");
 	  	        $l_activate  = PFformat::Link("section=projects&task=task_activate&cid[]=$row->id".$filter);
 	  	        $l_approve   = PFformat::Link("section=projects&task=task_approve&cid[]=$row->id".$filter);
                $l_tt        = PFformat::Link("section=time&workspace=$row->id");

                // Set checkbox
 	  	        $checkbox   = '<input id="cb'.$i.'" name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox" />';

 	  	        // Display project category?
 	  	        if($row->category == '') $row->category = PFformat::Lang('UNCATEGORIZED');

 	  	        if($use_cats && $row->category != $current_cat) {
 	  	            $current_cat = trim($row->category);

 	  	            // Set category color
                    $cat_color = (array_key_exists($row->category, $cats) ? $cats[$row->category] : NULL);
                    if($cat_color) $cat_color = ' style="background-color:'.$cat_color.' !important;"';

                    $cname = (array_key_exists($current_cat, $cat_names) ? $cat_names[$current_cat] : $current_cat);

                    $html .= '
                    <tr class="pf_pcat"'.$cat_color.'>
                        <td></td>
                        <td colspan="5">'.$cname.'</td>
                    </tr>';
                }

 	  	        // Create HTML
 	  	        $html .= '<tr class="row'.$k.' sectiontableentry'.($k + 1).'">'
                      .  '<td class="pf_number_cell">'.$pagination->getRowOffset($i).'</td>';

                if($can_copy || $can_delete || $can_archive) $html .= '<td align="center" class="pf_check_cell">'.$checkbox.'</td>';

                $html .= '<td class="pf_project_title item_title">';

                if($user->Access('display_details', 'projects', $row->author)) {
                    $html .= '<strong><a href="'.$l_details.'" '.$color.'>'.$row->title.'</a></strong>';
                }
                else {
                    $html .= '<strong '.$color.'>'.$row->title.'</strong>';
                }

                $html .= '</td>';

                $html .= '<td class="pf_action_cell">';

                // Start Item options menu
                $html .= $table->Menu();
                if($user->Access('form_edit', 'projects', $row->author) && $status != 1) {
                    $html .= $table->MenuItem($l_edit,'EDIT','pf_edit',$color);
                }
                if($status == 1 && $user->Access('task_activate', 'projects', $row->author)) {
                    $html .= $table->MenuItem($l_activate,'ACTIVATE','pf_activate',$color);
                }
                if($status == 2 && $user->Access('task_approve', 'projects', $row->author)) {
                    $html .= $table->MenuItem($l_approve,'APPROVE','pf_approve',$color);
                }
                if($status == 0 && $user->Access('', 'tasks')) {
                    $html .= $table->MenuItem($l_tasks,'GO_TO_TASKS','pf_tasks',$color);
                }
                if($user->Access('form_new', 'time', $row->author) && $status == 0) {
                    $html .= $table->MenuItem($l_tt,'TI_FN','pf_timeadd',$color);
                }
                $html .= $table->Menu(false);

                $html .= '</td><td class="pf_founder_cell">';

                if($user->Access('display_details', 'profile')) {
                    $html .= '<a href="'.PFformat::Link("section=profile&task=display_details&id=$row->author").'">'.$row->name.'</a>';
                }
                else {
                    $html .= $row->name;
                }

                $html .= '</td>'
                      .  '<td class="pf_created_cell">'.PFformat::ToDate($row->cdate).'</td>'
                      .  '<td class="pf_deadline_cell">'.$row->edate.'</td>'
                      .  '<td class="pf_id_cell">'.$row->id.'</td>'
                      .  '</tr>';

                $k = 1 - $k;
            }
            echo $html;
            unset($html);
            if(!count($rows)) {
                ?>
                <tr class="row0 sectiontableentry1">
      	            <td colspan="6" align="center" style="text-align:center"><div class="pf_info"><?php echo PFformat::Lang('NO_PROJECTS');?></div></td>
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
<?php
echo $form->HiddenField("boxchecked", 0);
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("ob", $ob);
echo $form->HiddenField("od", $od);
echo $form->End();
?>
<script type="text/javascript">
function task_delete()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		if(confirm("<?php echo PFformat::Lang('CONFIRM_ACTION');?>")) {
			submitbutton('task_delete');
		}
	}
}
function task_copy()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('task_copy');
	}
}
function task_archivate()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('task_archive');
	}
}
function task_activate()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('task_activate');
	}
}
function task_approve()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('task_approve');
	}
}
</script>