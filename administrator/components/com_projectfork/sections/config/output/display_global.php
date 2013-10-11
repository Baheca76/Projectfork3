<?php
/**
* $Id: display_global.php 855 2011-02-24 04:28:38Z angek $
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

echo $form->Start();
?>
<a id="pf_top"></a>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h1><?php echo PFformat::Lang('CONFIG');?> :: <?php echo PFformat::Lang('GLOBAL');?></h1>
    </div>
    <div class="pf_body">
    
        <!-- NAVIGATION START-->
        <?php PFpanel::Position('config_nav'); ?>
        <!-- NAVIGATION END -->
<?php
jimport('joomla.html.pane');
$tabs = JPane::getInstance('Tabs');
echo $tabs->startPane('paneID');
echo $tabs->startPanel(PFformat::Lang('DISPLAY_SETTINGS'), 'pane1');
?>

                        <table class="admintable">
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('DATE_FORMAT');?></td>
                                <td><?php echo $form->InputField('date_format', ($config->Get('date_format') ? $config->Get('date_format') : '%m/%d/%Y'));?></td>
                                <td><?php echo PFformat::Lang('DATE_FORMAT_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('USE_12HCLOCK');?></td>
                                <td><input type="checkbox" name="12hclock" value="1" <?php if($config->Get('12hclock') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('USE_12HCLOCK_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('DISPLAY_AVATAR');?></td>
                                <td><input type="checkbox" name="display_avatar" value="1" <?php if($config->Get('display_avatar') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('DISPLAY_AVATAR_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('USE_WIZARD');?></td>
                                <td><input type="checkbox" name="use_wizard" value="1" <?php if($config->Get('use_wizard') == '1') echo $html_checked; ?>/></td>
                                <td><?php echo PFformat::Lang('USE_WIZARD_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('TOOLTIP_HELP');?></td>
                                <td><input type="checkbox" name="tooltip_help" value="1" <?php if($config->Get('tooltip_help') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('TOOLTIP_HELP_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('CFG_SEND_EMAIL_HTML');?></td>
                                <td><input type="checkbox" name="html_emails" value="1" <?php if($config->Get('html_emails') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('CFG_SEND_EMAIL_HTML_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('CFG_PANEL_EDIT');?></td>
                                <td><input type="checkbox" name="panel_edit" value="1" <?php if($config->Get('panel_edit') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('CFG_PANEL_EDIT_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('CFG_EDIT_LIGHTBOX');?></td>
                                <td><input type="checkbox" name="edit_lightbox" value="1" <?php if($config->Get('edit_lightbox') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('CFG_EDIT_LIGHTBOX_DESC');?></td>
                            </tr>
                        </table>
<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('FRONTEND_SETTINGS'), 'pane2');
?>
                        <table class="admintable">
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('HIDE_TEMPLATE');?></td>
                                <td><input type="checkbox" name="hide_template" value="1" <?php if($config->Get('hide_template') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('HIDE_TEMPLATE_DESC');?></td>
                            </tr>
                        </table>
<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('PERMISSION_SETTINGS'), 'pane3');
?>
                        <table class="admintable">
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('USE_SSL_FE');?></td>
                                <td><input type="checkbox" name="use_ssl_fe" value="1" <?php if($config->Get('use_ssl_fe') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('USE_SSL_FE_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('USE_SSL_BE');?></td>
                                <td><input type="checkbox" name="use_ssl_be" value="1" <?php if($config->Get('use_ssl_be') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('USE_SSL_BE_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('USE_SCORE');?></td>
                                <td><input type="checkbox" name="use_score" value="1" <?php if($config->Get('use_score') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('USE_SCORE_DESC');?></td>
                            </tr>
                        </table>
<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('CACHE_SETTINGS'), 'pane4');
?>
                        <table class="admintable">
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('CACHE_PANELS');?></td>
                                <td><?php echo $form->SelectNY('cache_panels', $config->Get('cache_panels'));?></td>
                                <td><?php echo PFformat::Lang('CACHE_PANELS_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('CACHE_USER');?></td>
                                <td><?php echo $form->SelectNY('cache_user', $config->Get('cache_user'));?></td>
                                <td><?php echo PFformat::Lang('CACHE_USER_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('CACHE_FRAMEWORK');?></td>
                                <td><?php echo $form->SelectNY('cache_core', $config->Get('cache_core'));?></td>
                                <td><?php echo PFformat::Lang('CACHE_FRAMEWORK_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('CACHE_MODS');?></td>
                                <td><?php echo $form->SelectNY('cache_mods', $config->Get('cache_mods'));?></td>
                                <td><?php echo PFformat::Lang('CACHE_MODS_DESC');?></td>
                            </tr>
                        </table>
<?php
echo $tabs->endPanel();
echo $tabs->startPanel(PFformat::Lang('DEBUG_SETTINGS'), 'pane5');
?>
                        <table class="admintable">
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('ENABLE_DEBUG');?></td>
                                <td><input type="checkbox" name="debug" value="1" <?php if($config->Get('debug') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('ENABLE_DEBUG_DESC');?></td>
                            </tr>
                            <tr>
                                <td class="key" width="150"><?php echo PFformat::Lang('SHOW_PANEL_POS');?></td>
                                <td><input type="checkbox" name="debug_panels" value="1" <?php if($config->Get('debug_panels') == '1') echo $html_checked;?>/></td>
                                <td><?php echo PFformat::Lang('SHOW_PANEL_POS_DESC');?></td>
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
echo $form->HiddenField('option');
echo $form->HiddenField('section');
echo $form->HiddenField('task');
echo $form->End();
?>
<script type="text/javascript">
function task_install()
{
    if(document.adminForm_subnav.value == '') {
    	alert("Please select a package");
    }
    else {
    	navsubmit( 'task_install' );
    }
}
</script>