<?php
/**
* $Id: display_details.php 837 2010-11-17 12:03:35Z eaxs $
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

?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
       <h3><?php echo $ws_title." / "; echo PFformat::Lang('PROFILE');?> :: <?php echo htmlspecialchars($row->name);?>
       <?php echo PFformat::SectionEditButton();?>
        </h3>
    </div>
    
    <div class="pf_body">
        <!-- NAVIGATION START-->
        <?php echo PFpanel::Position('profile_nav');?>
        <!-- NAVIGATION END -->
        <?php echo PFpanel::Render("cp_mystatus"); ?>
        <div class="pf_profile_inner">
	        <div class="pf_profile_wrap">
	        <?php PFpanel::Position('profile_details_top'); ?>
	        <div class="col left">
	            <?php PFpanel::Position('profile_details_left'); ?>
	        </div>
	        
	        <div class="col right">
	            <?php PFpanel::Position('profile_details_right'); ?>
	        </div>
	        
	        <div class="clr"></div>
	        </div>
        </div>
        <?php PFpanel::Position('profile_details_bottom'); ?>
    </div>
</div>