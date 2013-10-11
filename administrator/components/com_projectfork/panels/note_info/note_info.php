<?php
/**
* $Id: note_info.php 837 2010-11-17 12:03:35Z eaxs $
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

$config = PFconfig::GetInstance();
$class  = new PFfilemanagerClass();
$form   = new PFform();

$id  = (int) JRequest::getVar('id');
$row = $class->LoadNote($id);
       
if(!is_null($row)) {
	?>
    <table class="admintable">
        <tr>
            <td class="key" width="100"><?php echo PFformat::Lang('TITLE');?></td>
            <td><?php echo htmlspecialchars($row->title);?></td>
        </tr>
        <tr>
            <td class="key" width="100"><?php echo PFformat::Lang('DESC');?></td>
            <td><?php echo htmlspecialchars($row->description);?></td>
        </tr>
        <tr>
            <td class="key" width="100" valign="top"><?php echo PFformat::Lang('CONTENT');?></td>
            <td>
                <?php
                if($config->Get('use_editor', 'filemanager') == '0') {
                    echo nl2br($row->content);
                }
                else {
                    echo $row->content;
                }
                ?>
            </td>
        </tr>
    </table>
	<?php
}
?>