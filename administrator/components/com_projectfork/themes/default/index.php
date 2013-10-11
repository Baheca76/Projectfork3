<?php
/**
* $Id: index.php 890 2011-06-29 15:59:09Z pixelpraise $
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

// Hide the joomla admin toolbar in the backend, we're not using it
if($location == 'backend') {
    echo '<style type="text/css">#toolbar-box { display:none; }</style>';
}

// Load theme for location
if($location == 'frontend' && $hide_tpl == '1') {
	$load->ThemeCSS('notpl.css');
} else if($location == 'frontend' && $hide_tpl == '0') {
	$load->ThemeCSS('frontend.css');
} else if($location == 'backend') {
	$load->ThemeCSS('backend.css');
}

//Check for active workspace
$user = PFuser::GetInstance();
$workspace = $user->GetWorkspace();

//Change the active Joomla template if using Projectfork on the frontend and hiding Joomla
$user = &JFactory::getUser();
if($location == 'frontend' && $hide_tpl == '1') {
$app =& JFactory::getApplication();
$app->setTemplate('rhuk_milkyway'); 
}
// Load Khepri css if Hide Joomla template is chosen in global config
//if($location != 'backend' && $hide_tpl == '1') {
//    $doc = JFactory::getDocument();
//    $doc->addStyleSheet( 'administrator/templates/khepri/css/template.css' );
//    $doc->addStyleSheet( 'administrator/templates/khepri/css/rounded.css' );
//    unset($doc);
//}
?>

<?php if($location == 'frontend' && $hide_tpl == '1') { ?>
<div id="pf-frontend-notemplate">
<?php } ?>
<div id="pf-wrapper" class="pf-<?php echo $section;?> pf-<?php echo $location;?> pf-color-<?php echo PFtheme::ProjectColor(); ?> <?php if(!$workspace){echo"pf-noworkspace";}?>">
	<div id="pf-infopanel">
	<?php 
	/* optionally clear messages
	$core->ClearMessages();
	*/
	?>
	<?php PFpanel::Position('theme_pos2'); ?></div>
	<div id="pf-header">
		<?php echo PFpanel::Render("theme_logo", "panel_simple"); ?>
		<?php echo PFpanel::Render("quicklink_project", "panel_simple"); ?>
		<?php echo PFpanel::Render("cp_welcome", "panel_simple"); ?>
		<div class="clr"></div>
		<?php PFpanel::Position("theme_header", "panel_simple"); ?>
	</div>
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
	            <div id="pf-bottom"><?php PFpanel::Position('theme_bottom'); ?></div>
	         </div>
	         <div class="clr"></div>
         </div>
         <div class="clr"></div>
      </div>
      <div class="b"><div class="b"><div class="b"></div></div></div>
      <div class="clr"></div>
   </div>
   <div id="pf-footer">
      <?php PFpanel::Position("theme_footer", "panel_simple"); ?>
      <?php PFpanel::Position("theme_debug", "panel_simple"); ?>
      <?php if($location == 'frontend' && $hide_tpl == '1') { ?>
      	<div class="pf-return">
      		<a href="<?php echo $uri->base();?>index.php"><span>Return to site</span></a>
      	</div>
      <?php } ?>
   </div>
   <!-- FOOTER NOTICE - YOU CAN REMOVE THIS IF YOU LIKE! -->
   <div style="text-align:center !important" id="theme_link">
      <a href="http://www.projectfork.net" target="_blank" title="Free Joomla! Project Manager">Projectfork</a> is Free Software released under the <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU/GPL License</a>.
   </div>
   <!-- FOOTER NOTICE - YOU CAN REMOVE THIS IF YOU LIKE! -->
</div>
<?php if($location != 'backend' && $hide_tpl) { ?>
</div>
<?php } ?>
<?php
unset($com,$load,$core,$cfg);
?>


