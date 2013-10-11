<?php
/**
* $Id: list_requests.php 837 2010-11-17 12:03:35Z eaxs $
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

echo $form->start();
?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
         <h3><?php echo $ws_title." / "; echo PFformat::Lang('USERS');?> :: <?php echo PFformat::Lang('JOIN_REQUESTS');?>
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
                    <th align="left" width="45%" class="sectiontableheader title"><?php echo $table->TH(0); // name ?></th>
                    <th align="left" width="30%" class="sectiontableheader title"><?php echo $table->TH(1); // User name ?></th>
                    <th align="left" width="25%" class="sectiontableheader title"><?php echo $table->TH(2); // email ?></th>
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
 	  	             $checkbox = '<input id="cb'.$i.'" name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" type="checkbox"/>';
 	  	             
 	  	             $html .= '
                     <tr class="row'.$k.' sectiontableentry'.($k + 1).'">
  	                     <td align="center">'.$pagination->getRowOffset($i).'</td>
  	                     <td align="center">'.$checkbox.'</td>
  	                     <td class="pf_requests_title item_title">'.$row->name.'</td>
  	                     <td>'.$row->username.'</td>
  	                     <td><a href="mailto:'.$row->email.'">'.$row->email.'</a></td>
  	                     <td>'.$row->id.'</td>
                     </tr>';

  	                 $k = 1 - $k;
 	            }
 	            echo $html;
 	            unset($html);
 	            if(!count($rows)) {
                ?>
 	  	        <tr>
 	  	            <td colspan="6" style="text-align:center"><div class="pf_info"><?php echo PFformat::Lang('NO_USERS');?></div></td>
 	  	        </tr>
                <?php } ?>
                <tr>
                    <td colspan="6" style="text-align:center"><?php echo $pagination->getListFooter(); ?></td>
                </tr>
            </tbody>
        </table> 	 
        <!-- TABLE END -->

    </div>
</div>
<?php
echo $form->HiddenField('task');
echo $form->HiddenField('boxchecked', 0);
echo $form->HiddenField('ob', 'u.id');
echo $form->HiddenField('od', 'ASC');
echo $form->End();
?>
<script type="text/javascript">
function form_accept_request()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		submitbutton('form_accept_request');
	}
}
function task_deny()
{
	if(document.adminForm.boxchecked.value == 0) {
		alert('<?php echo PFformat::Lang('ALERT_LIST');?>');
	}
	else {
		if(confirm("<?php echo PFformat::Lang('CONFIRM_ACTION');?>")) {
			submitbutton('task_deny');
		}
	}
}
</script>