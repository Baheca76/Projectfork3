<?php
/**
* $Id: cp_welcome.php 881 2011-05-26 10:59:50Z eaxs $
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

$user = PFuser::GetInstance();
$lang = PFlanguage::GetInstance();
$com  = PFcomponent::GetInstance();
$name = $user->GetName();
$uid  = $user->GetId();
$uri  = JFactory::getURI();
$jv   = new JVersion();
$comu = "com_users&task=user.logout";
$tokn = "&".JUtility::getToken()."=1";

if($jv->RELEASE == '1.5') {
    $comu = "com_user&task=logout";
    $tokn = '';
}

if($name == 'PFL_GUEST') {
    $name = $lang->_('PFL_GUEST');
?>
<span class="pf_welcome">
   <?php echo $lang->_('PFL_WELCOME').", ".$name; ?>
</span>
<?php } else { ?>
<span class="pf_welcome">
   <?php echo PFavatar::Display($uid). " " .$name; ?>
   <ul>
        <?php if($user->Access('display_details', 'profile', $user->GetId())) { ?>
    	   	<li>
               <a href="<?php echo PFformat::Link("section=profile&task=display_details&id=".$user->GetId());?>">
                   <span><?php echo $lang->_('VIEW_PROFILE'); ?></span>
               </a>
            </li>
        <?php } ?>
        <?php if($user->Access('task_update', 'profile', $user->GetId())) { ?>
    	   	<li>
               <a href="<?php echo PFformat::Link("section=profile");?>">
                   <span><?php echo $lang->_('EDIT_PROFILE'); ?></span>
               </a>
            </li>
        <?php } ?>
        <?php if($user->Access('', 'tasks')) { ?>
    	   	<li>
               <a href="<?php echo PFformat::Link("section=tasks&assigned=2");?>">
                   <span><?php echo $lang->_('MY_TASKS'); ?></span>
               </a>
            </li>
        <?php } ?>
	   	<?php if($com->Get('location') == 'frontend') { ?>
           <li>
               <a href="index.php?option=<?php echo $comu;?>&return=<?php echo base64_encode($uri->base()."index.php").$tokn; ?>">
                   <span><?php echo $lang->_('LOGOUT'); ?></span>
               </a>
           </li>
        <?php } ?>
   </ul>
</span>
<?php
}
unset($user,$lang);
?>