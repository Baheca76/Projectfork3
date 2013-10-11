<?php
/**
* $Id: display_details.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Tasks
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

$print = (int) JRequest::getVar('print');
?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo $ws_title." / "; echo PFformat::Lang('PROJECTS');?> :: <?php echo $row->title;?>
        <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    <div class="pf_body">
    
    <!-- NAVIGATION START-->
    <?php PFpanel::Position('projects_nav'); ?>
    <!-- NAVIGATION END -->
    
    <div class="col left">
    	<?php PFpanel::Position('project_details_left'); ?>
    </div>
    <div class="col right">
    	<?php PFpanel::Position('project_details_right'); ?>
    </div>
    <div class="col">
    	<?php PFpanel::Position('project_details_bottom'); ?>
    </div>

    </div>
</div>
<script type="text/javascript">
function task_print()
{
    window.open('<?php echo PFformat::Link("section=projects&task=display_details&id=$id&print=1&render=section_ajax", false, false, false);?>',
    'win2',
    'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
}
<?php if($print) { ?>
window.print();
<?php } ?>
</script>