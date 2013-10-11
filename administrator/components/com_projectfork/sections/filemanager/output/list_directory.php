<?php
/**
* $Id: list_directory.php 932 2013-04-06 04:00:39Z eaxs $
* @package    Projectfork
* @subpackage Filemanager
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

$user   = PFuser::GetInstance();
$search = trim(JRequest::getVar('keyword'));

echo $form->Start();
?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('FILEMANAGER');?>
        <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    <div class="pf_body">

        <!-- NAVIGATION START-->
        <?php PFpanel::Position('filemanager_nav');?>
        <!-- NAVIGATION END -->

        <!--WIZARD START-->
        <?php if($wizard && ($can_cfolder || $can_cnote || $can_cfile)) { ?>
        <div id="pf_panel_pf_wizard">
        <div class="pf-panel-body">
        	<div class="pf-wizard pf-first-task">
        		<h3><?php echo PFformat::Lang('WIZ_FM_TITLE');?></h3>
        		<p><?php echo PFformat::Lang('WIZ_FM_DESC');?></p>
        		<?php if($can_cfolder) { ?>
        		<a href="<?php echo PFformat::Link("section=filemanager&task=form_new_folder");?>" class="pf_button"><?php echo PFformat::Lang('WIZ_FM_BTN_CF');?></a>
                <?php } if($can_cfile) { ?>
                <a href="<?php echo PFformat::Link("section=filemanager&task=form_new_file");?>" class="pf_button"><?php echo PFformat::Lang('WIZ_FM_BTN_UF');?></a>
                <?php } if($can_cnote) { ?>
                <a href="<?php echo PFformat::Link("section=filemanager&task=form_new_note");?>" class="pf_button"><?php echo PFformat::Lang('WIZ_FM_BTN_CN');?></a>
                <?php } ?>
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
                    <?php if($can_move || $can_delete) { ?>
                        <th align="center" class="sectiontableheader title pf_check_header"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $total; ?>);" /></th>
                    <?php } ?>
                    <th align="left" width="25%" class="sectiontableheader title"><?php echo $table->TH(0); // TITLE ?></th>
                    <th align="left" width="35%" class="sectiontableheader title"><?php echo $table->TH(1); // DESC ?></th>
                    <th align="left" width="15%" class="sectiontableheader title"><?php echo $table->TH(2); // CDATE ?></th>
                    <th align="left" width="25%" class="sectiontableheader title"><?php echo $table->TH(3); // AUTHOR ?></th>
                    <th align="left" nowrap="nowrap" class="sectiontableheader title idcol pf_id_header"><?php echo $table->TH(4);  // ID ?></th>
                </tr>
            </thead>
            <tbody id="table_body">
                <?php
                    $k = 0;
                    $x = 0;
                    $html = '';
                    /* START LIST FOLDERS */
 	                foreach ($folders AS $i => $row)
 	                {
 	  	                if(!$row->id) continue;

                        if ($search) {
                            $row->title = implode('/', $class->LoadElementPath($row->id));
                        }

 	  	                JFilterOutput::objectHTMLSafe($row);

 	  	                // Check permission
 	  	                $can_move2   = $user->Access('list_move', 'filemanager', $row->author);
 	  	                $can_delete2 = $user->Access('task_delete', 'filemanager', $row->author);

 	  	                $link_open = PFformat::Link("section=filemanager&dir=$row->id");
 	  	                $link_edit = PFformat::Link("section=filemanager&dir=$dir&task=form_edit_folder&id=$row->id");
 	  	                $checkbox = '<input id="cb'.$x.'" name="folder[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox"/>';

 	  	                $html .= '<tr class="row'.$k.' sectiontableentry'.($k+1).'">
  	                    <td class="pf_number_cell">'.($x+1).'</td>';

  	                    if($can_move || $can_delete) {
                            if($can_move2 || $can_delete2) {
                                $html .= '<td align="center" class="pf_check_cell">'.$checkbox.'</td>';
                            }
                            else {
                                $html .= '<td align="center" class="pf_check_cell"></td>';
                            }
                        }

  	                    $html .= '<td class="pf_directory_title item_title">
  	                    <strong><a href="'.$link_open.'" class="pf_fm_folder"><span>'.$row->title.'</span></a></strong>';

  	                    if($user->Access('form_edit_folder', 'filemanager', $row->author)) {
  	                        $html .= $table->Menu();
  	                        $html .= $table->MenuItem($link_edit,'TT_EDIT','pf_edit');
  	                        $html .= $table->Menu(false);
  	                    }

  	                    $html .= '</td>
  	                    <td>'.$row->description.'</td>
  	                    <td>'.PFformat::ToDate($row->cdate).'</td>
                        <td>';

                        if($user->Access('display_details', 'profile')) {
                            $html .= "<a href='".PFformat::Link("section=profile&task=display_details&id=$row->author")."'>".$row->name."</a>";
                        }
                        else {
                            $html .= $row->name;
                        }

                        $html .= '</td>
  	                    <td class="idcol pf_id_cell">'.$row->id.'</td>
  	                    </tr>';

  	                    $k = 1 - $k;
  	                    $x++;
 	                }
 	                /* END LIST FOLDERS */
 	                /* START LIST DOCUMENTS */

                    foreach ($notes AS $i => $row)
 	                {
 	  	                if(!$row->id) continue;

                        if ($search) {
                            $row->title = implode('/', $class->LoadElementPath($row->dir)) . '/' . $row->title;
                        }

 	  	                JFilterOutput::objectHTMLSafe($row);

 	  	                // Check permission
 	  	                $can_move2   = $user->Access('list_move', 'filemanager', $row->author);
 	  	                $can_delete2 = $user->Access('task_delete', 'filemanager', $row->author);

 	  	                $link_open = PFformat::Link("section=filemanager&dir=$dir&task=display_note&id=$row->id");
 	  	                $link_edit = PFformat::Link("section=filemanager&dir=$dir&task=form_edit_note&id=$row->id");
 	  	                $checkbox = '<input id="cb'.$x.'" name="note[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox"/>';

 	  	                $html .= '<tr class="row'.$k.' sectiontableentry'.($k+1).'">
  	                    <td class="pf_number_cell">'.($x+1).'</td>';

  	                    if($can_move || $can_delete) {
                            if($can_move2 || $can_delete2) {
                                $html .= '<td align="center" class="pf_check_cell">'.$checkbox.'</td>';
                            }
                            else {
                                $html .= '<td align="center" class="pf_check_cell"></td>';
                            }
                        }

  	                    $html .= '<td class="pf_directory_title item_title"><strong>';

  	                    if($user->Access('display_note', 'filemanager', $row->author)) {
                            $html .= '<a href="'.$link_open.'" class="pf_fm_doc"><span>'.$row->title.'</span></a>';
                        }

                        $html .= '</strong>';

                        if($user->Access('form_edit_note', 'filemanager', $row->author)) {
                            $html .= $table->Menu();
  	                        $html .= $table->MenuItem($link_edit,'TT_EDIT','pf_edit');
  	                        $html .= $table->Menu(false);
  	                    }

                        $html .= '</td>
                        <td>'.$row->description.'</td>
  	                    <td>'.PFformat::ToDate($row->cdate).'</td>
                        <td>';

                        if($user->Access('display_details', 'profile')) {
                            $html .= '<a href="'.PFformat::Link("section=profile&task=display_details&id=$row->author").'">'.$row->name.'</a>';
                        }
                        else {
                            $html .= $row->name;
                        }

                        $html .= '</td><td class="idcol pf_id_cell">'.$row->id.'</td></tr>';

  	                    $k = 1 - $k;
  	                    $x++;
 	                }
 	                /* END LIST DOCUMENTS */
 	                /* START LIST FILES */
                    foreach ($files AS $i => $row)
 	                {
 	  	                if(!$row->id) continue;

                        if ($search) {
                            $row->name = implode('/', $class->LoadElementPath($row->dir)) . '/' . $row->name;
                        }

 	  	                JFilterOutput::objectHTMLSafe($row);

 	  	                // Check permission
 	  	                $can_move2   = $user->Access('list_move', 'filemanager', $row->author);
 	  	                $can_delete2 = $user->Access('task_delete', 'filemanager', $row->author);

 	  	                $link_open = PFformat::Link("section=filemanager&dir=$dir&task=task_download&id=$row->id");
 	  	                $link_edit = PFformat::Link("section=filemanager&dir=$dir&task=form_edit_file&id=$row->id");
 	  	                $checkbox = '<input id="cb'.$x.'" name="file[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox"/>';

 	  	                $html .= '
                        <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
  	                    <td class="pf_number_cell">'.($x+1.).'</td>';

  	                    if($can_move || $can_delete) {
                            if($can_move2 || $can_delete2) {
                                $html .= '<td align="center" class="pf_check_cell">'.$checkbox.'</td>';
                            }
                            else {
                                $html .= '<td align="center" class="pf_check_cell"></td>';
                            }
                        }

  	                    $html .= '<td><strong>';

  	                    if($user->Access('task_download', 'filemanager', $row->author)) {
  	                    $file_type = substr($row->name,-3);
  	                        $html .= '<a href="'.$link_open.'" class="pf_fm_file '.$file_type.'"><span>'.$row->name.'</span></a>';
  	                    }
  	                    else {
                            $html .= $row->name;
                        }

                        $html .= '</strong>';

                        if($user->Access('form_edit_file', 'filemanager', $row->author)) {
                           $html .= $table->Menu();
  	                       $html .= $table->MenuItem($link_edit,'TT_EDIT','pf_edit');
  	                       $html .= $table->Menu(false);
  	                    }


                        $html .= '</td>
                        <td>'.$row->description.'</td>
  	                    <td>'.PFformat::ToDate($row->cdate).'</td>
                        <td>';

                        if($user->Access('display_details', 'profile')) {
                            $html .= '<a href="'.PFformat::Link("section=profile&task=display_details&id=$row->author").'">'.$row->uname.'</a>';
                        }
                        else {
                            $html .= $row->uname;
                        }

                        $html .= '</td><td class="idcol pf_id_cell">'.$row->id.'</td></tr>';

                        $k = 1 - $k;
  	                    $x++;
 	                }
 	                /* END LIST FILES */
 	                echo $html;
 	                unset($html);
                ?>
                <tr>
                    <td colspan="7">&nbsp;</td>
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
echo $form->HiddenField('dir');
echo $form->HiddenField('boxchecked', 0);
echo $form->HiddenField('ob', $ob);
echo $form->HiddenField('od', $od);
echo $form->End();
?>
<script type="text/javascript">
function task_delete()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('task_delete');
	}
}
function list_move()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('list_move');
	}
}
</script>