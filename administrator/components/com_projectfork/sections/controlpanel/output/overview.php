<?php
/**
* $Id: overview.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Controlpanel
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
?>
<div id="pf-cpanel-top"><?php PFpanel::Position('controlpanel_top', "panel_simple"); ?>

<!--begin wizard-->
<?php if($wizard && $user_id && $can_cp && ($p_tasks == 0 || $p_ms == 0 || $p_users <= 1)) { ?>
<div id="pf_panel_pf_wizard">
<div class="pf-panel-body">
	<?php if($my_projects == 0) { ?>
    <div class="pf-wizard pf-start">
		<h3><?php echo PFformat::Lang('WIZ_CP_NEWP_INTRO_TITLE');?></h3>
		<p><?php echo PFformat::Lang('WIZ_CP_NEWP_INTRO_BODY');?></p>
		<a href="<?php echo PFformat::Link('section=projects&task=form_new');?>" class="pf_button"><?php echo PFformat::Lang('WIZ_CP_NEWP_INTRO_BTN');?></a>
	</div>
	<div class="clr separator"></div>
	<?php } else { ?>
	<div class="pf-wizard pf-overview">
		<h3><?php echo PFformat::Lang('WIZ_CP_TITLE_COOL');?></h3>
		<p><?php echo PFformat::Lang('WIZ_CP_STEPS');?></p>
		<ol>
			<li class="strike"><?php echo PFformat::Lang('WIZ_CP_TITLE_CNP');?></li>
			<li <?php if($my_ws){?>class="strike"<?php } ?>><?php echo PFformat::Lang('WIZ_CP_TITLE_SPWS');?></li>
			<?php if($my_ws) { ?>
			    <?php if($user->Access('form_invite', 'users')) { ?>
                    <li><a href="<?php echo PFformat::Link("section=users&task=form_invite");?>">
                    <?php echo PFformat::Lang('WIZ_CP_TITLE_AUP');?></a>
                    <?php if($user->Access('form_new', 'groups')) { ?>
                        (<a href="<?php echo PFformat::Link("section=groups&task=form_new");?>"><?php echo PFformat::Lang('WIZ_CP_TITLE_CCG');?></a>)
                    <?php } ?>    
                    </li>
                <?php } ?> 
                <?php if($user->Access('form_new_milestone', 'tasks')) { ?>   
			        <li><a href="<?php echo PFformat::Link("section=tasks&task=form_new_milestone");?>"><?php echo PFformat::Lang('WIZ_CP_TITLE_CMS');?></a></li>
                <?php } if($user->Access('form_new_task', 'tasks')) { ?>
			    <li><a href="<?php echo PFformat::Link("section=tasks&task=form_new_task");?>"><?php echo PFformat::Lang('WIZ_CP_TITLE_CT');?></a></li>
			    <?php } ?>
			<?php } else { ?>
                <li><?php echo PFformat::Lang('WIZ_CP_TITLE_AUP');?> (<?php echo PFformat::Lang('WIZ_CP_TITLE_CCG');?>)</li>
			    <li><?php echo PFformat::Lang('WIZ_CP_TITLE_CMS');?></li>
			    <li><?php echo PFformat::Lang('WIZ_CP_TITLE_CT');?></li>
            <?php } ?>    
		</ol>
	</div>
	<?php } ?>
</div>
</div>
<?php } ?>
<!--end wizard-->

</div>
<div id="pf-cpanel-main"><?php echo PFpanel::Position('controlpanel_main'); ?>

</div>
<div id="pf-cpanel-left"><?php echo PFpanel::Position('controlpanel_left'); ?>

</div>
<div id="pf-cpanel-right"><?php PFpanel::Position('controlpanel_right'); ?>

</div>
<div id="pf-cpanel-bottom"><?php echo PFpanel::Position('controlpanel_bottom'); ?></div>