<?php
/**
* $Id: form_new_topic.php 837 2010-11-17 12:03:35Z eaxs $
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
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('BOARD')." :: ".PFformat::Lang('NEW_TOPIC');?></h3>
    </div>
    <div class="pf_body">
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('board_nav');?>
        <!-- NAVIGATION END -->
        <div class="col">
            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('GENERAL_INFORMATION');?></legend>
                <table class="admintable">
                    <tr>
                        <td class="key required" width="150"><?php echo PFformat::Lang('TITLE');?></td>
                        <td><?php echo $form->InputField('title*', '', 'size="40" maxlength="124"');?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                        <?php 
                        if($use_editor && !defined('PF_DEMO_MODE')) { 
                     	    echo $editor->display( 'text',  "" , '100%', '350', '75', '20' ) ;
                        }
                        else {
                     	    echo $form->TextArea('text','','75', '20');
                        }
                        ?>
                        </td>
                    </tr>
                </table>
            </fieldset>      
        </div>
        <div class="clr"></div>  

</div>
</div>
<?php
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("limitstart");
echo $form->End();
?>
<script type="text/javascript">
function task_save_topic()
{
	var d  = document.adminForm;
	var e  = false;
	
	if(d.title.value == "") {
        alert("<?php echo PFformat::Lang('V_TITLE');?>");
        e = true;
    }
    
    if(e == false){
    	<?php if($use_editor && !defined('PF_DEMO_MODE')) { echo $editor->save( 'text' ); } ?>
	    submitbutton('task_save_topic');
    }
}
</script>