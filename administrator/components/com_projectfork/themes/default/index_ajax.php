<?php
/**
* $Id: index_ajax.php 837 2010-11-17 12:03:35Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
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

$com  = PFcomponent::GetInstance();
$load = PFload::GetInstance();
$cfg  = PFconfig::GetInstance();
$core = PFcore::GetInstance();

$location = $com->Get('location');
$hide_tpl = $cfg->Get('hide_template');
$section  = $core->GetSection();

$uri  = JFactory::getURI();

// Load theme header processes
PFprocess::Event('theme_header');

// Load theme CSS
$load->ThemeCSS('general.css');
$load->ThemeCSS('theme.css');
$load->ThemeCSS('icons.css');
$load->ThemeCSS('mootabs.css');

// Load theme for location
if($location == 'frontend' && $hide_tpl == '1') {
	$load->ThemeCSS('frontend.css');
} else if($location == 'backend') {
	$load->ThemeCSS('backend.css');
}
?>
<?php if($location == 'frontend' && $hide_tpl == '1') { ?>
<div id="pf-frontend-notemplate">
<?php } ?>
<div id="pf-wrapper" class="pf-<?php echo $section;?> pf-<?php echo $location;?>">

	<div id="pf-top">
      <div id="pf-mainpanel"><?php PFpanel::Position('theme_pos1'); ?>
      <div class="clr"></div>
   </div>
   <div class="clr"></div>
      <div id="pf-section-subpanel"><?php PFpanel::Position($section."_sub"); ?></div>
   </div>
   <div id="pf-body">
      <div class="t"><div class="t"><div class="t"></div></div></div>
      <div class="m">
      	<div class="pf-inner">
	         <div id="pf-content">
	            <div><?php PFpanel::Position('theme_top'); ?></div>
	            <div id="pf-main"><?php PFsection::Render(); ?></div>
	            
	         </div>
	         <div class="clr"></div>
         </div>
         <div class="clr"></div>
      </div>
      <div class="b"><div class="b"><div class="b"></div></div></div>
      <div class="clr"></div>
   </div>
</div>
<?php if($location != 'backend' && $hide_tpl) { ?>
</div>
<?php } ?>
<?php
unset($com,$load,$core,$cfg);
?>