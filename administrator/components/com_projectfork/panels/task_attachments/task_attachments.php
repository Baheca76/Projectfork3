<?php
/**
* $Id: task_attachments.php 923 2012-03-28 17:50:44Z eaxs $
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

$id = (int) JRequest::getVar('id');
$db = PFdatabase::GetInstance();
$core = PFcore::GetInstance();
$sections = $core->GetSections();

if($id) {
	// get connected folders
	$query = "SELECT f.* FROM #__pf_task_attachments AS a"
	       . "\n RIGHT JOIN #__pf_folders AS f ON f.id = a.attach_id"
	       . "\n WHERE a.task_id = '$id' AND a.attach_type = 'folder'"
	       . "\n GROUP BY a.attach_id ORDER BY f.title";
	       $db->setQuery($query);
	       $folders = $db->loadObjectList();

	if(!is_array($folders)) $folders = array();

	// get connected notes
	$query = "SELECT n.* FROM #__pf_task_attachments AS a"
	       . "\n RIGHT JOIN #__pf_notes AS n ON n.id = a.attach_id"
	       . "\n WHERE a.task_id = '$id' AND a.attach_type = 'note'"
	       . "\n GROUP BY a.attach_id ORDER BY n.title";
	       $db->setQuery($query);
	       $notes = $db->loadObjectList();

	if(!is_array($notes)) $notes = array();

	// get connected files
	$query = "SELECT f.* FROM #__pf_task_attachments AS a"
	       . "\n RIGHT JOIN #__pf_files AS f ON f.id = a.attach_id"
	       . "\n WHERE a.task_id = '$id' AND a.attach_type = 'file'"
	       . "\n GROUP BY a.attach_id ORDER BY f.name";
	       $db->setQuery($query);
	       $files = $db->loadObjectList();

	if(!is_array($files)) $files = array();

    $designs = array();
    if(array_key_exists('design_review', $sections)) {
        if($sections['design_review']->enabled == 1) {
            $query = "SELECT d.id, d.title, d.description FROM #__pf_designs AS d"
                   . "\n RIGHT JOIN #__pf_design_tasks AS t ON t.design_id = d.id"
                   . "\n WHERE t.task_id = '$id'"
                   . "\n GROUP BY d.id ORDER BY d.title ASC";
                   $db->setQuery($query);
                   $designs = (array) $db->loadObjectList();
        }
    }

	// display
	if(count($folders) || count($notes) || count($files) || count($designs)) {
		$k = 0;
		?>
		   <table class="pf_table" width="100%" cellpadding="0" cellspacing="0">
		      <tbody>
		      <?php foreach ($folders AS $row)
		      {
		      	  JFilterOutput::objectHTMLSafe($row);
		      	  $link_open = PFformat::Link("section=filemanager&dir=$row->id");
		      	  ?>
		      	  <tr class="pf_row<?php echo $k;?>">
		      	     <td width="40%" valign="top"><a href="<?php echo $link_open;?>" class="pf_fm_folder"><?php echo $row->title; ?></a></td>
		      	     <td><?php echo $row->description;?></td>
		      	  </tr>
		      	  <?php
		      	  $k = 1 - $k;
		      }
		      ?>
		      <?php foreach ($notes AS $row)
		      {
		      	  JFilterOutput::objectHTMLSafe($row);
		      	  $link_open = PFformat::Link("section=filemanager&dir=$row->dir&task=display_note&id=$row->id");
		      	  ?>
		      	  <tr class="pf_row<?php echo $k;?>">
		      	     <td width="40%" valign="top"><a href="<?php echo $link_open;?>" class="pf_fm_doc"><?php echo $row->title; ?></a></td>
		      	     <td><?php echo $row->description;?></td>
		      	  </tr>
		      	  <?php
		      	  $k = 1 - $k;
		      }
		      ?>
		      <?php foreach ($files AS $row)
		      {
		      	  JFilterOutput::objectHTMLSafe($row);
		      	  $link_open = PFformat::Link("section=filemanager&dir=$row->dir&task=task_download&id=$row->id");
		      	  ?>
		      	  <tr class="pf_row<?php echo $k;?>">
		      	     <td width="40%" valign="top"><a href="<?php echo $link_open;?>" class="pf_fm_file"><?php echo $row->name; ?></a></td>
		      	     <td><?php echo $row->description;?></td>
		      	  </tr>
		      	  <?php
		      	  $k = 1 - $k;
		      }
		      ?>
              <?php foreach ($designs AS $row)
		      {
		      	  JFilterOutput::objectHTMLSafe($row);
		      	  $link_open = PFformat::Link("section=design_review&task=display_details&id=$row->id");
		      	  ?>
		      	  <tr class="pf_row<?php echo $k;?>">
		      	     <td width="40%" valign="top"><a href="<?php echo $link_open;?>" class="pf_fm_file"><?php echo $row->title; ?></a></td>
		      	     <td><?php echo $row->description;?></td>
		      	  </tr>
		      	  <?php
		      	  $k = 1 - $k;
		      }
		      ?>
		      </tbody>
		   </table>
		<?php
	}
}
?>