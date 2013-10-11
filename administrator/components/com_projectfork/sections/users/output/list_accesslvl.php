<?php
/**
* $Id: list_accesslvl.php 911 2011-07-20 14:02:11Z eaxs $
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
<script type="text/javascript">
function task_delete_accesslvl()
{
	if(!document.adminForm.boxchecked.value) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('task_delete_accesslvl');
	}
}
</script>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('USERS');?> :: <?php echo PFformat::Lang('ACCESS_LVLS');?>
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
                <th align="center" class="sectiontableheader title">#</th>
                <th align="center" class="sectiontableheader title"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $total; ?>);" /></th>
                <th align="left" width="40%" class="sectiontableheader title"><?php echo $table->TH(0); // TITLE ?></th>
                <?php if($use_score) { ?>
                <th align="left" width="5%" class="sectiontableheader title"><?php echo $table->TH(1); // SCORE ?></th>
                <?php } ?>
                <th align="left" width="25%" class="sectiontableheader title"><?php echo $table->TH(2); // PROJECT ?></th>
                <th align="left" width="25%" class="sectiontableheader title"><?php echo $table->TH(3); // FLAG ?></th>
                <th align="left" width="5%" class="sectiontableheader title"><?php echo $table->TH(4);  // ID ?></th>
            </tr>
        </thead>
        <tbody>
        <?php
            $k = 0;
            $html = '';
			$config = PFconfig::GetInstance();
			$showtips =  (int) $config->Get('tooltip_help');
 	        foreach ($rows AS $i => $row)
 	        {
 	  	        JFilterOutput::objectHTMLSafe($row);
 	  	        $link_edit = PFformat::Link("section=users&task=form_edit_accesslvl&id=$row->id");
 	  	        $disabled  = "";
 	  	        $e1 = "";
 	  	        $e2 = "";

 	  	        // dont show public accesslevels to non system admins
 	  	        if($flag != 'system_administrator' && $row->project == 0) continue;

 	  	        if(in_array($row->id, $restricted)) {
 	  	 	        $disabled = "disabled='disabled' readonly='readonly'";
					if ($showtips){
						$e1 = '<span class="editlinktip hasTip" title="'.PFformat::Lang('RESTRICTED_ACL_DELETE').'">';
						$e2 = "</span>";
					}
 	  	        }

 	  	        $checkbox = $e1.'<input id="cb'.$i.'" name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox" '.$disabled.'/>'.$e2;

 	  	        if(!$row->flag_title) $row->flag_title = PFformat::Lang('PFL_NONE');
 	  	        if(!$row->project_title) $row->project_title = PFformat::Lang('GLOBAL');

 	  	        // Translate title
 	  	        $title = PFformat::Lang($row->title);
 	  	        if($jversion->RELEASE != '1.5' && $project == 0) {
 	  	            $title = str_replace('{group_name}', $row->jtitle, $title);
 	  	        }

 	  	        $html .= '
                <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
  	                <td align="center">'.$pagination->getRowOffset( $i ).'</td>
  	                <td align="center">'.$checkbox.'</td>
  	                <td class="pf_access_title item_title">'.$title;

  	            $html .= $table->Menu();
  	            if( $user->Access('form_edit_accesslvl', 'users') ) {
  	                $html .= $table->MenuItem($link_edit,'TT_EDIT','pf_edit');
  	            }
  	            $html .= $table->Menu(false);

  	            $html .= '</td>';

  	            if($use_score) $html .= '<td>'.$row->score.'</td>';

  	            $html .= '
  	            <td>'.$row->project_title.'</td>
  	            <td>'.PFformat::Lang($row->flag_title).'</td>
  	            <td>'.$row->id.'</td>
  	            </tr>';

  	            $k = 1 - $k;
 	        }

 	        echo $html;
 	        unset($html);
        ?>
        <tr>
            <td colspan="7" style="text-align:center"><?php echo $pagination->getListFooter(); ?></td>
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
$form->SetBind(true, 'REQUEST');
echo $form->HiddenField('section');
echo $form->HiddenField('task');
echo $form->HiddenField('boxchecked', 0);
echo $form->HiddenField('ob', $ob);
echo $form->HiddenField('od', $od);
echo $form->End();
?>