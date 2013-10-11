<?php
/**
* $Id: note_comments.php 842 2011-01-20 11:52:28Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2009 Tobias Kuhn. All rights reserved.
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
	$ls  = (int) JRequest::getVar('limitstart');
	$cid = (int) JRequest::getVar('cid');
	$dir = (int) JRequest::getVar('dir');
    $k   = urlencode(JRequest::getVar('keyword'));
    
	$config = PFconfig::GetInstance();
	$core   = PFcore::GetInstance();
	$user   = PFuser::GetInstance();
	$db     = PFdatabase::GetInstance();
	$task   = $core->GetTask();
	
	$section = $core->GetSection();
	
    $comments = new PFcomments();
    
	$query = "SELECT title FROM #__pf_notes WHERE id = '$id'";
	       $db->setQuery($query);
	       $title = $db->loadResult();

	$comments->Init('notes', $id);

	if($task == 'form_edit_comment' && $cid) {
		$pf_form = new PFform('adminform');
		$cform   = $comments->RenderEdit($cid);
		$task    = 'task_update_comment';
	}
	else {
		$pf_form = new PFform('adminform');
		$cform   = $comments->RenderNew($title);
		$task    = 'task_save_comment';
	}
	
	$list = $comments->RenderList("&dir=$dir");
	
	if((($section == 'filemanager_pro' && $config->Get('use_comments', $section) == '1') || $section == 'filemanager') && $id) {
		echo $pf_form->Start();
	    echo $cform;
	    echo $list;
        echo $pf_form->HiddenField('option', 'com_projectfork');
        echo $pf_form->HiddenField('section', $section);
        echo $pf_form->HiddenField('dir', $dir);
        echo $pf_form->HiddenField('task', $task);
        echo $pf_form->HiddenField('id', $id);
        echo $pf_form->HiddenField('cid', $cid);
	    echo $pf_form->End();
	}
}
?>