<?php
/**
* $Id: form_edit.php 838 2010-11-25 20:49:32Z eaxs $
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

$load = PFload::GetInstance();

echo $form->Start();
?>
<div class="pf_container">
    <div class="pf_header componentheading">
        <h3><?php echo PFformat::Lang('PROFILE');?>
        <?php
        $core = PFcore::GetInstance();
        $sobj = $core->GetSectionObject();
        if($user->Access('form_edit_section', 'config')) {
           echo '<a href="'.PFformat::Link("section=config&task=form_edit_section&&rts=1&id=$sobj->id").'" class="section_edit">'
           .'<span>'.PFformat::Lang('QL_CONFIG_SECTION').'</span></a>';
        }
        unset($core);
        ?>
        </h3>
    </div>
    <div class="pf_body">
    
    <!-- NAVIGATION START -->
    <?php PFpanel::Position('profile_nav'); ?>
    <!-- NAVIGATION END -->
    
    <div class="pf_profile">
    
        <fieldset class="adminform">
            <legend><?php echo PFformat::Lang('PERSONAL_INFO');?></legend>
            <table class="admintable">
                <tr>
                    <td class="key required" width="150"><?php echo PFformat::Lang('NAME');?></td>
                    <td><?php echo $form->InputField('name*', '', 'size="25"');?></td>
                </tr>
                <tr>
                    <td class="key" width="150"><?php echo PFformat::Lang('LANGUAGE');?></td>
                    <td><?php echo $form->SelectLanguage('language', $row->profile->language);?></td>
                </tr>
                <?php if((int)$config->get('allow_upload', 'profile') && $use_avatar && !defined('PF_DEMO_MODE')) { ?>
                    <tr>
                        <td class="key" width="150" valign="top"><?php echo PFformat::Lang('AVATAR');?></td>
                        <td>
                            <div><?php echo $load->Avatar($row->id);?></div>
                            <input type="file" name="avatar" size="20"/>
                        </td>
                    </tr>
                    <?php if(isset($row->profile->avatar) && $use_avatar) { if($row->profile->avatar != '') { ?>
                        <tr>
                            <td class="key" width="150" valign="top"><?php echo PFformat::Lang('DELETE_AVATAR');?></td>
                            <td><input type="checkbox" name="delete_avatar" value="1"/></td>
                        </tr>
                    <?php } } ?>
                <?php } ?>
            </table>
        </fieldset>
        
        <fieldset class="adminform">
            <legend><?php echo PFformat::Lang('ACCESS_INFO');?></legend>
            <table class="admintable">
                <tr>
                    <td class="key" width="150"><?php echo PFformat::Lang('PASSWORD');?></td>
                    <td><input type="password" name="password" size="25" autocomplete="off"/></td>
                </tr>
                <tr>
                    <td class="key" width="150"><?php echo PFformat::Lang('PASSWORD2');?></td>
                    <td><input type="password" name="password2" size="25" autocomplete="off"/></td>
                </tr>
            </table>
        </fieldset>
        
        <fieldset class="adminform">
            <legend><?php echo PFformat::Lang('CONTACT_INFO');?></legend>
            <table class="admintable">
                <tr>
                    <td class="key required" width="150"><?php echo PFformat::Lang('EMAIL');?></td>
                    <td><?php echo $form->InputField('email*', '', 'size="40"');?></td>
                </tr>
                <?php $form->SetBind(true,$row->profile); ?>
                <?php if($use_phone) { ?>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('PHONE');?></td>
                        <td><?php echo $form->InputField('phone', '', 'size="30"');?></td>
                    </tr>
                <?php } if($use_mphone) { ?>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('PHONE_MOBILE');?></td>
                        <td><?php echo $form->InputField('mobile_phone', '', 'size="30"');?></td>
                    </tr>
                <?php } if($use_skype) { ?>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('SKYPE');?></td>
                        <td><?php echo $form->InputField('skype', '', 'size="30"');?></td>
                    </tr>
                <?php } if($use_msn) { ?>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('MSN');?></td>
                        <td><?php echo $form->InputField('msn', '', 'size="30"');?></td>
                    </tr>
                <?php } if($use_icq) { ?>
                    <tr>
                        <td class="key" width="150"><?php echo PFformat::Lang('ICQ');?></td>
                        <td><?php echo $form->InputField('icq', '', 'size="30"');?></td>
                    </tr>
                <?php } ?>
            </table>
        </fieldset>
        
        <?php if($use_street || $use_city || $use_zip) { ?> 
            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('LOCATION');?></legend>
                <table class="admintable">
                    <?php if($use_street) { ?>
                        <tr>
                            <td class="key" width="150"><?php echo PFformat::Lang('STREET');?></td>
                            <td><?php echo $form->InputField('street', '', 'size="40"');?></td>
                        </tr>
                    <?php } if($use_city) { ?>
                        <tr>
                            <td class="key" width="150"><?php echo PFformat::Lang('CITY');?></td>
                            <td><?php echo $form->InputField('city', '', 'size="30"');?></td>
                        </tr>
                    <?php } if($use_zip) { ?>
                        <tr>
                            <td class="key" width="150"><?php echo PFformat::Lang('ZIP');?></td>
                            <td><?php echo $form->InputField('zip', '', 'size="20"');?></td>
                        </tr>
                    <?php } ?>
                </table>   
            </fieldset>
        <?php } ?>
        
        <?php if($use_twitter || $use_friendf || $use_linkedin || $use_facebook || $use_youtube || $use_vimeo) { ?>
            <fieldset class="adminform">
                <legend><?php echo PFformat::Lang('NETWORKS');?></legend>
                <table class="admintable">
                    <?php if($use_twitter) { ?>
                        <tr>
                            <td class="key" width="150"><?php echo PFformat::Lang('TWITTER');?></td>
                            <td><?php echo $form->InputField('twitter', '', 'size="40"');?></td>
                        </tr>
                    <?php } if($use_friendf) { ?>
                        <tr>
                            <td class="key" width="150"><?php echo PFformat::Lang('FRIENDFEED');?></td>
                            <td><?php echo $form->InputField('friendfeed', '', 'size="40"');?></td>
                        </tr>
                    <?php } if($use_linkedin) { ?>
                        <tr>
                            <td class="key" width="150"><?php echo PFformat::Lang('LINKEDIN');?></td>
                            <td><?php echo $form->InputField('linkedin', '', 'size="40"');?></td>
                        </tr>
                    <?php } if($use_facebook) { ?>
                        <tr>
                            <td class="key" width="150"><?php echo PFformat::Lang('FACEBOOK');?></td>
                            <td><?php echo $form->InputField('facebook', '', 'size="40"');?></td>
                        </tr>
                    <?php } if($use_youtube) { ?>
                        <tr>
                            <td class="key" width="150"><?php echo PFformat::Lang('YOUTUBE');?></td>
                            <td><?php echo $form->InputField('youtube', '', 'size="40"');?></td>
                        </tr>
                    <?php } ?>
                </table>
            </fieldset>
        <?php } ?>
         
    </div>      

    </div>
</div>
<?php
$form->SetBind(true, 'REQUEST');
echo $form->HiddenField("option");
echo $form->HiddenField("section");
echo $form->HiddenField("task", 'task_update');
echo $form->End();
?>
<script type="text/javascript">
function task_update()
{
	var d = document.adminForm;
	var e = "";
	if(d.name.value == '') {e = "<?php echo PFformat::Lang('V_NAME');?>";}
	if(d.email.value == '') {e = "<?php echo PFformat::Lang('V_USERS_EMAIL');?>";}
	if(e) {alert(e);}
	else {submitbutton('task_update');}
}
</script>