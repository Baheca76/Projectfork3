<?php
/**
* $Id: list_languages.php 863 2011-03-21 00:00:29Z angek $
* @package    Projectfork
* @subpackage Config
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

$load = PFload::GetInstance();
$user = PFuser::GetInstance();

$img_default1 = $load->ThemeImg('star_color.png');
$img_default2 = $load->ThemeImg('star_gray.png');
$img_pub1 = $load->ThemeImg('action_check.png');
$img_pub2 = $load->ThemeImg('action_delete.png');

echo $form->Start();
?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo PFformat::Lang('CONFIG');?> :: <?php echo PFformat::Lang('LANGUAGES');?></h1>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('config_nav'); ?>
        <!-- NAVIGATION END -->
        
        <!-- TABLE START -->
        <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th align="center" class="sectiontableheader title">#</th>
                    <th align="center" class="sectiontableheader title">&nbsp;</th>
                    <th align="left" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(0); // PUBLISHED ?></th>
                    <th align="left" width="50%" class="sectiontableheader title"><?php echo $table->TH(1); // TITLE ?></th>
                    <th align="left" width="10%" class="sectiontableheader title"><?php echo $table->TH(2); // DEFAULT ?></th>
                    <th align="left" width="10%" class="sectiontableheader title"><?php echo $table->TH(3); // VERSION ?></th>
                    <th align="left" width="30%" class="sectiontableheader title"><?php echo $table->TH(4); // AUTHOR ?></th>
                    <th align="left" nowrap="nowrap" class="sectiontableheader title"><?php echo $table->TH(5);  // ID ?></th>
                </tr>
            </thead>
            <tbody id="table_body">
                <?php
                    $k = 0;
                    $html = '';
					$config = PFconfig::GetInstance();
					$showtips =  (int) $config->Get('tooltip_help');
 	                foreach ($rows AS $i => $row)
 	                {	
 	  	                JFilterOutput::objectHTMLSafe($row);

 	  	                $is_default = "<a href='".PFformat::Link("section=config&task=task_default_language&id=$row->id")."'>"
 	  	                            . $img_default2
 	  	                            . "</a>";
 	  	             
 	  	                if($row->is_default == '1') $is_default = $img_default1;
 	  	                if($row->published != '1')  $is_default = $img_default2;
 	  	          
 	  	                $published = "<a href='".PFformat::Link("section=config&task=task_publish&type=language&id=$row->id")."'>"
 	  	                           . $img_pub2
 	  	                           . "</a>";            
 	  	 
 	  	                if($row->published  == '1') { 
 	  	                    $published = "<a href='".PFformat::Link("section=config&task=task_unpublish&type=language&id=$row->id")."'>"
 	  	                               . $img_pub1
 	  	                               . "</a>";
 	  	                }
 	  	 
 	  	                if($row->is_default == '1' && $row->published == '1') $published = $img_pub1;
 	  	                if($row->is_default == '1' && $row->published == '0') $published = $img_pub2;
 	  	 
 	  	                $website   = "";
 	  	 
 	  	                if($row->website) {
 	  	 	                $website = "<li class='pf_website'><a href='$row->website'><span>".PFformat::Lang('VISIT_WEBSITE')."</span></a></li>";
 	  	                }
 	  	                
 	  	                $disabled = '';
 	  	                $is_core  = '';
 	  	                if(in_array($row->name, $core_data)) {
                            $disabled = ' disabled="disabled"';
							if (!$showtips) {
								$is_core  = '<span class="core-ext">'.PFformat::Lang('CORE_EXT').'</span>'; 
							}
							else {
								$is_core  = '<span class="core-ext hasTip" title="'.PFformat::Lang('CORE_EXT_DESC').'">'.PFformat::Lang('CORE_EXT').'</span>'; 
							}
                        }
                        
                        if($row->is_default == '1') $disabled = ' disabled="disabled"';

 	  	                $checkbox = '<input name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="radio"'.$disabled.'/>';
 	  	                
 	  	                $html .= '
 	  	                <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
  	                        <td>'.$pagination->getRowOffset( $i ).'</td>
  	                        <td align="center">'.$checkbox.'</td>
                            <td align="center" style="text-align:center">'.$published.'</td>
  	                        <td class="pf_language_title item_title">
  	                            <strong>'.PFformat::Lang($row->title).'</strong>'.$is_core;
  	                            
 	  	                $html .= $table->Menu();
  	                    $html .= $website;
  	                    $html .= $table->Menu(false);
  	                    
  	                    $html .= '
                        </td>
                        <td align="center" style="text-align:center;">'.$is_default.'</td>
  	                    <td>'.$row->version.'</td>
  	                    <td>'.$row->author.'</td>
  	                    <td>'.$row->id.'</td>
  	                    </tr>';

  	                    $k = 1 - $k;
 	                }
 	                echo $html;
 	                unset($html);
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
echo $form->HiddenField("boxchecked");
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("type", 'language');
echo $form->HiddenField("ob", 'ordering');
echo $form->HiddenField("od", 'ASC');
echo $form->End();
?>
<script type="text/javascript">
function task_uninstall()
{
    if(!document.adminForm.boxchecked.value) {
		alert('<?php echo PFformat::Lang('PFL_ALERT_LIST');?>');
	}
	else {
		submitbutton('task_uninstall');
	}
}
</script>