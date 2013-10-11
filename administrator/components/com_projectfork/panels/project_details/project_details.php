<?php
/**
* $Id: project_details.php 837 2010-11-17 12:03:35Z eaxs $
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

$config = PFdatabase::GetInstance();
$user   = PFuser::GetInstance();
$class  = new PFprojectsClass();
$form   = new PFform();

$id  = (int) JRequest::getVar('id');
$row = $class->load($id);

$avatar = PFavatar::Display($row->author);
$view_profile = $user->Access('display_details', 'profile');
?>
<table class="admintable">
   <tr>
      <td class="key" width="100"><?php echo PFformat::Lang('CREATED_ON');?></td>
      <td><?php echo PFformat::ToDate($row->cdate);?></td>
   </tr>
   <tr>
      <td class="key" width="100"><?php echo PFformat::Lang('DEADLINE');?></td>
      <td><?php echo ($row->edate ? PFformat::ToDate($row->edate) : PFformat::Lang('NOT_SET'));?></td>
   </tr>
   <?php if($row->website) { ?>
   <tr>
      <td class="key" width="100"><?php echo PFformat::Lang('WEBSITE');?></td>
      <td><a href="<?php echo $row->website;?>" target="_blank"><?php echo $row->website;?></a></td>
   </tr>
   <?php } ?>
   <?php if($row->email) { ?>
   <tr>
      <td class="key" width="100"><?php echo PFformat::Lang('EMAIL');?></td>
      <td><a href="mailto:<?php echo $row->email;?>"><?php echo $row->email;?></a></td>
   </tr>
   <?php } ?>
   <tr>
      <td class="key" width="100" valign="top"><?php echo PFformat::Lang('PROJECT_FOUNDER');?></td>
      <td>
         <div><?php echo $avatar;?></div>
         <strong><?php echo @$row->founder->name;?></strong>  
      </td>
   </tr>
   <?php if(count($row->users)) { ?>
   <tr>
      <td class="key" width="100" valign="top"><?php echo PFformat::Lang('MEMBERS');?></td>
         <td>
               <?php 
               foreach ($row->users AS $users)
               {
               	   $avatar = PFavatar::Display($users->id);
               	   
               	   echo "<div style='float:left; margin:5px;text-align:center'>
               	             <div>".$avatar."</div>
                             <strong>".htmlspecialchars($users->name)."</strong><br/>
                         </div>";
               }
               ?>
         </td>
      </tr>
   <?php } ?>
</table>
<?php
unset($config,$user,$class,$form);
?>