<?php
/**
* $Id: form_edit_panel.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Config
* @copyright  Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license    http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License License as published by
* the Free Software Foundation, either version 3 of the License,
* or any later version.
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
?>
<?php echo $form->Start();?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo PFformat::Lang('CONFIG');?> :: <?php echo PFformat::Lang('PANELS');?>:: <?php echo PFformat::Lang('EDIT');?></h1>
    </div>
    <div class="pf_body">
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('config_nav');?>
        <!-- NAVIGATION END -->
<?php
jimport('joomla.html.pane');
$tabs = JPane::getInstance('Tabs');
echo $tabs->startPane('paneID');
echo $tabs->startPanel(PFformat::Lang('OPTIONS'), 'pane1');
?>
        
		<table class="admintable">
		    <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('SHOW_TITLE');?></td>
		        <td><?php echo $form->SelectNY('show_title', $config->Get('show_title', $row->name));?></td>
		    </tr>
		    <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('OVERRIDE_TITLE');?></td>
		        <td><?php echo $form->InputField('override_title', $config->Get('override_title', $row->name), 'size="40"');?></td>
		    </tr>
		    <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('ENABLED');?></td>
		        <td><?php echo $form->SelectNY('published', $row->enabled);?></td>
		    </tr>
		    <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('POSITION');?></td>
		        <td><?php echo $form->InputField('pos', $row->position);?></td>
		    </tr>
		    <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('CACHE_PANEL');?></td>
		        <td><?php echo $form->SelectNY('cache', $row->caching);?></td>
		    </tr>
		</table>
		
		<?php echo $params_html;?>

<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('ACCESS_SETTINGS'), 'pane2');
?>

		<table class="admintable">
		    <tr <?php if(!$use_score) echo 'style="display:none"'; ?>>
		        <td class="key" width="150"><?php echo PFformat::Lang('SCORE');?></td>
		        <td><?php echo $form->InputField('score', $row->score);?></td>
		    </tr>
		    <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('FLAG');?></td>
		        <td><?php echo PFconfigHelper::SelectFlag('flag', $row->flag);?></td>
		    </tr>
		</table>      

                        
<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('GENERAL_INFORMATION'), 'pane3');
?>
		<table class="admintable">
		    <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('NAME');?></td>
		        <td><?php echo $row->name;?></td>
		     </tr>
		     <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('TITLE');?></td>
		        <td><?php echo PFformat::Lang($row->title);?></td>
		     </tr>
		     <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('VERSION');?></td>
		        <td><?php echo $row->version;?></td>
		     </tr>
		     <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('AUTHOR');?></td>
		        <td><?php echo $row->author;?></td>
		     </tr>
		     <?php if($row->website) { ?>
		         <tr>
		             <td class="key" width="150"><?php echo PFformat::Lang('VISIT_WEBSITE');?></td>
		             <td><a href="<?php echo $row->website;?>" target="_blank"><?php echo $row->website;?></a></td>
		         </tr>
		     <?php } ?>
		     <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('COPYRIGHT');?></td>
		        <td><?php echo $row->copyright;?></td>
		     </tr>
		     <tr>
		        <td class="key" width="150"><?php echo PFformat::Lang('LICENSE');?></td>
		        <td><?php echo $row->license;?></td>
		     </tr>
		</table>
                


<?php
echo $tabs->endPanel();
?>
<?php
echo $tabs->endPane();
?>
    	<div class="clr"></div>
    </div>
</div>
<?php
$form->SetBind(true, 'REQUEST');
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task");
echo $form->HiddenField("limitstart");
echo $form->HiddenField("id", $id);
echo $form->End();
?>