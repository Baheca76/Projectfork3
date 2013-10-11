<?php
/**
* $Id: task_comments.php 842 2011-01-20 11:52:28Z eaxs $
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

if(defined('PF_COMMENTS_PROCESS')) {

	$id  = (int) JRequest::getVar('id');
	$cid = (int) JRequest::getVar('cid');
    
	$config = PFconfig::GetInstance();
	$db     = PFdatabase::GetInstance();
	$core   = PFcore::GetInstance();
	$com    = new PFcomments();
	$task   = $core->GetTask();

	$query = "SELECT title FROM #__pf_tasks WHERE id = '$id'";
	       $db->setQuery($query);
	       $title = $db->loadResult();
	       
	$com->Init('tasks', $id);

	if($task == 'form_edit_comment' && $cid) {
		$pf_form = new PFform('adminform');
		$cform   = $com->RenderEdit($cid);
		$task    = 'task_update_comment';
	}
	else {
		$pf_form = new PFform('adminform');
		$cform   = $com->RenderNew($title);
		$task    = 'task_save_comment';
	}
	
	$list = $com->RenderList();
	
	if((int)$config->Get('use_comments', 'tasks') && $id) {
		echo $pf_form->Start();
	    echo $cform;
	    echo $list;
        echo $pf_form->HiddenField('option', 'com_projectfork');
        echo $pf_form->HiddenField('section', 'tasks');
        echo $pf_form->HiddenField('task', $task);
        echo $pf_form->HiddenField('id', $id);
        echo $pf_form->HiddenField('cid', $cid);
	    echo $pf_form->End();
	}
}
?>