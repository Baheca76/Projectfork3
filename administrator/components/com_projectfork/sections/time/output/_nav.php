<?php
/**
* $Id: _nav.php 837 2010-11-17 12:03:35Z eaxs $
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

// Load objects
$core = PFcore::GetInstance();
$user = PFuser::GetInstance();

$form = new PFform('adminForm_subnav');

// Get user input
$ftask   = (int) JRequest::getVar('ftask', $user->GetProfile("timelist_task"));
$fuser   = (int) JRequest::getVar('fuser', $user->GetProfile("timelist_user"));
$keyword = htmlspecialchars(trim(JRequest::getVar('keyword')), ENT_QUOTES);

$sobj = $core->GetSectionObject();
echo $form->Start();
?>
<div class="pf_navigation tasks_navigation">
   <ul>
      <li class="btn pf_new"><?php echo $form->NavButton('ADD', "javascript:form_new();", 'PFL_TI_FN', 'form_new');?></li>
      <li class="btn pf_delete"><?php echo $form->NavButton('DELETE', 'javascript:task_delete();', 'TT_DELETE', 'task_delete');?></li>
      <!--<li class="btn pf_config"><?php echo $form->NavButton('CONFIG', "section=config&task=form_edit_section&&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section', 'config');?></li>-->
   </ul>
</div>
<?php if($user->GetWorkspace() != 0) { ?>
    <div class="pfl_search">
      <span>
         <?php echo PFformat::Lang('SEARCH');?>
         <?php echo $form->InputField('keyword', $keyword);?>
         <?php echo $form->SelectTask('ftask', $ftask);?>
         <?php echo $form->SelectUser('fuser', $fuser);?>
       </span>
      <span class="btn"><?php echo $form->NavButton('OK', "javascript:navsubmit('');");?></span>
    </div>
<?php 
}
$form->setBind(true, 'REQUEST');
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("limit");
echo $form->End();

unset($core,$user,$form,$sobj);
?>