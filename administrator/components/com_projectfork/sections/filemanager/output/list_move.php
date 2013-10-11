<?php
/**
* $Id: list_move.php 837 2010-11-17 12:03:35Z eaxs $
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
echo $form->Start();
?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('FILEMANAGER');?> :: <?php echo PFformat::Lang('MOVE');?></h3>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('filemanager_nav');?>
        <!-- NAVIGATION END -->
        
        <!-- TABLE START -->
        <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th align="center" class="sectiontableheader title">#</th>
                    <th align="left" width="25%" class="sectiontableheader title"><?php echo $table->TH(0); // TITLE ?></th>
                    <th align="left" width="35%" class="sectiontableheader title"><?php echo $table->TH(1); // DESC ?></th>
                    <th align="left" width="15%" class="sectiontableheader title"><?php echo $table->TH(2); // CDATE ?></th>
                    <th align="left" width="25%" class="sectiontableheader title"><?php echo $table->TH(3); // AUTHOR ?></th>
                    <th align="left" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(4);  // ID ?></th>
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
 	                JFilterOutput::objectHTMLSafe($row);
 	  	            if(!$row->id) continue;
 	  	            if(in_array($row->id, $mfolders) && count($mfolders) != 0) continue;
 	  	 
 	  	            $link_open = "javascript:list_move($row->id);";
 	  	            
 	  	            $html .= '
                    <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
  	                    <td>'.($x+1).'</td>
  	                    <td class="pf_move_title item_title"><strong><a href="'.$link_open.'" class="pf_fm_folder">'.$row->title.'</a></strong></td>
  	                    <td>'.$row->description.'</td>
  	                    <td>'.PFformat::ToDate($row->cdate).'</td>
  	                    <td>'.$row->name.'</td>
  	                    <td>'.$row->id.'</td>
                    </tr>';
                    
                    $k = 1 - $k;
  	                $x++;
 	            }
 	            /* END LIST FOLDERS */
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
foreach ($mfolders AS $id)
{
	$id = (int) $id;
	
	echo $form->HiddenField('folder[]', $id);
}
foreach ($mfiles AS $id)
{
	$id = (int) $id;
	
	echo $form->HiddenField('file[]', $id);
}
foreach ($mnotes AS $id)
{
	$id = (int) $id;
	
	echo $form->HiddenField('note[]', $id);
}
echo $form->End();
?>
<script type="text/javascript">
function list_move(d)
{
	document.adminForm.dir.value = d;
	submitbutton('list_move');
}
</script>