<?php
/**
* $Id: note_details.php 837 2010-11-17 12:03:35Z eaxs $
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

$db    = PFdatabase::GetInstance();
$class = new PFfilemanagerClass();
$form  = new PFform();

$id  = (int) JRequest::getVar('id');
$row = $class->LoadNote($id);
    
 
if(!is_null($row)) {
    $avatar = PFavatar::Display($row->author);
     
    $query = "SELECT name FROM #__users WHERE id = '$row->author'";
           $db->setQuery($query);
           $name = $db->loadResult();
	?>
    <table class="admintable">
        <tr>
            <td class="key" width="100" valign="top"><?php echo PFformat::Lang('CREATED_BY');?></td>
            <td>
               <div class="pf_avatar"><?php echo $avatar;?></div>
               <strong><?php echo $name;?></strong>
            </td>
        </tr>
        <tr>
            <td class="key" width="100"><?php echo PFformat::Lang('CREATED_ON');?></td>
            <td><?php echo PFformat::ToDate($row->cdate);?></td>
        </tr>
        <tr>
            <td class="key" width="100" valign="top"><?php echo PFformat::Lang('EDITED_ON');?></td>
            <td><?php echo PFformat::ToDate($row->edate);?></td>
        </tr>
    </table>
	<?php
}
?>