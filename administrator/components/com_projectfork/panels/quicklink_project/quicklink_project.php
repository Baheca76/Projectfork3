<?php
/**
* $Id: quicklink_project.php 872 2011-03-23 13:07:19Z eaxs $
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

$db   = PFdatabase::GetInstance();
$core = PFcore::GetInstance();
$user = PFuser::GetInstance();
$lang = PFlanguage::GetInstance();
$juri = &JFactory::getURI();

$ws     = $user->GetWorkspace();
$uid    = $user->GetId();
$itemid = $core->GetItemid();
$task   = $core->GetTask();
$in_ws  = false;

if($uid) {
    $projects = $user->Permission('projects');
    $projects = implode(',',$projects);
	$disabled = "";
	
	if( stristr($task, 'edit') || stristr($task, 'new') || stristr($task, 'details') ) {
	    $disabled = 'disabled="disabled"';
	}
	    
	if($projects != '') {
	    $query = "SELECT id, title FROM #__pf_projects WHERE id IN($projects)"
	           . "\n AND approved = '1' AND archived = '0'"
	           . "\n GROUP BY id ORDER BY title ASC";
	           $db->setQuery($query);
	           $projects = $db->loadObjectList();
    }

    if(!is_array($projects)) $projects = array();
	    
    if(count($projects) || $ws) {
	    echo '<form action="'.$juri->toString().'" method="get">
	    <input type="hidden" name="option" value="com_projectfork" />
		<select name="workspace" class="comboBoo" onchange="this.form.submit();" '.$disabled.'>
        <option value="0">- '.$lang->_('PFL_SELECT_WORKSPACE').' -</option>';
		
		foreach ($projects AS $project)
		{
			$ps = "";
			if($ws == $project->id) $ps = 'selected="selected"'; $in_ws = true;
			echo '<option value="'.$project->id.'" '.$ps.'>'.htmlspecialchars($project->title).'</option>';
		}
		
		if(!$in_ws && $ws != 0) {
		    $query = "SELECT id, title FROM #__pf_projects WHERE id = '$ws'";
				   $db->setQuery($query);
				   $object = $db->loadObject();

			if(is_object($object)) {
			    echo '<option value="'.$object->id.'" selected="selected">'.htmlspecialchars($object->title).'</option>';
			}
		}

		echo '</select><input type="hidden" name="section" value="'.$core->GetSection().'" />';
		if($core->GetTask()) echo '<input type="hidden" name="task" value="'.$core->GetTask().'" />';
		echo '</form>';
	}
}
unset($db,$core,$user,$lang);
?>