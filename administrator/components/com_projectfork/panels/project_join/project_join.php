<?php
/**
* $Id: project_join.php 837 2010-11-17 12:03:35Z eaxs $
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

$user   = PFuser::GetInstance();
$config = PFconfig::GetInstance();
$db     = PFdatabase::GetInstance();

$my_id = $user->GetId();
$form  = new PFform();
$id    = (int) JRequest::getVar('id');

if($id) {
	$query = "SELECT allow_register FROM #__pf_projects WHERE id = '$id'";
	       $db->setQuery($query);
	       $allow_register = (int) $db->loadResult();
	       
	if($allow_register) {
	
		$query = "SELECT user_id FROM #__pf_project_members WHERE project_id = '$id'";
		       $db->setQuery($query);
		       $members = $db->loadResultArray();
		       
		if(!is_array($members)) $members = array();

		if(!in_array($my_id, $members) && $user->Access('task_request_join', 'projects')) {
			$link = "section=projects&task=task_request_join&id=".$id;
			?>
			   <table class="pf_navigation" cellpadding="0" cellspacing="0">
                  <tr>
                     <td class="btn"><?php echo $form->NavButton('REQUEST_JOIN', $link);?></td>
                  </tr>
               </table>
			<?php
		}
	}
}
?>