<?php
/**
* $Id: form_accept_requests.php 837 2010-11-17 12:03:35Z eaxs $
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
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo $ws_title." / "; echo PFformat::Lang('USERS');?> :: <?php echo PFformat::Lang('JOIN_REQUESTS');?> :: <?php echo PFformat::Lang('ACCEPT');?></h1>
    </div>
    <div class="pf_body">
        <!-- NAVIGATION START -->
        <?php PFpanel::Position('users_nav');?>
        <!-- NAVIGATION END -->
        
        <!-- TABLE START -->
        <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th align="center" class="sectiontableheader title">#</th>
                    <th align="left" width="30%" class="sectiontableheader title"><?php echo PFformat::Lang('NAME');?></th>
                    <th align="left" width="30%" class="sectiontableheader title"><?php echo PFformat::Lang('USERNAME');?></th>
                    <th align="left" width="20%" class="sectiontableheader title"><?php echo PFformat::Lang('ACL');?></th>
                    <th align="left" width="20%" class="sectiontableheader title"><?php echo PFformat::Lang('GROUP');?></th>
                </tr>
            </thead>
            <tbody> 
            <?php
                $k = 0;
 	            foreach ($rows AS $i => $row)
 	            { 
 	  	            JFilterOutput::objectHTMLSafe($row);
  	                ?>
  	                <tr class="row<?php echo $k;?> sectiontableentry<?php echo $k + 1;?>">
  	                    <td><?php echo $i +1;echo $form->HiddenField("accept_data[$row->id][id]", $row->id); ?></td>
  	                    <td><?php echo $row->name;?></td>
  	                    <td><?php echo $row->username;?></td>
  	                    <td><?php echo $form->SelectAccessLevel("accept_data[$row->id][acl]", -1);?></td>
  	                    <td><?php echo $form->SelectGroup("accept_data[$row->id][group]", -1);?></td>
  	                </tr>
  	                <?php
  	                $k = 1 - $k;
 	            }
            ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" align="center">&nbsp;</td>
                </tr>
            </tfoot>  
        </table>
        <!-- TABLE END -->

    </div>
</div>
<?php
echo $form->HiddenField('option');
echo $form->HiddenField('section');
echo $form->HiddenField('task', 'task_accept_requests');
echo $form->End();
?>