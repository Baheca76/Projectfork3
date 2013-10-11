<?php
/**
* $Id: upgrade.html.php 837 2010-11-17 12:03:35Z eaxs $
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

class PFupgradeHTML
{
    private $elements;
    
    public function __construct()
    {
        $this->elements = array(PFLS_OPT => 'opt_old',
                                PFLS_ADD_FIELDS => 'add_fields',
                                PFLS_DEL_FIELDS => 'del_fields',
                                PFLS_REN_FIELDS => 'ren_fields',
                                PFLS_ADD_INDEXES => 'indexes',
                                PFLS_MIG_GROUPS => 'migrate_groups',
                                PFLS_UPD_EXT => 'update_ext',
                                PFLS_UPD_PROFILES => 'profiles',
                                PFLS_UPD_TABLES => 'tables',
                                PFLS_UPD_CFG => 'config');
    }
    
    public function RenderLogo($logo = 'projectfork_install.png', $params = '')
    {
        $com = PFcomponent::GetInstance();
        
        $html = '<img src="'.$com->Get('url_backend').'/_core/images/'.$logo.'" alt="Projectfork" '.$params.'/>';
        return $html;
    }
    
    public function RenderFooter()
    {
        $html = '<div style="float:left;width:275px;">
                 Copyright (C) 2006-'.date('Y').' Tobias Kuhn. All rights reserved.<br />
                 Projectfork is released under the <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GNU/GPL</a>.
                 </div>
                 <div style="float:right;width:220px;">
                 </div><div style="clear:both"></div>';

        return $html;
    }
    
    public function RenderProgressBar()
    {
        $html  = "";
        $count = count($this->elements);

        $piece = round(100/$count);
        $progress = 0;

        foreach($this->elements AS $str => $field)
        {
            $status = (int) JRequest::getVar($field, 0, 'post');
            if($status != 0 && $status != 2) $progress++;
        }

        $progress = round($progress*$piece);
        $html = "<div class='progress_bar_outer'><div class='progress_bar' style='width:$progress%'></div></div><br /><br />";

        return $html;
    }
    
    public function RenderUpgradeElements()
    {
        $com = PFcomponent::GetInstance();
        
        $html         = "<ul class='install_elements'>";
        $img_pending  = '<img src="'.$com->Get('url_backend').'/_core/images/install_pending.png" alt="'.PFLS_PENDING.'" />';
        $img_ok       = '<img src="'.$com->Get('url_backend').'/_core/images/install_ok.png" alt="'.PFLS_DONE.'" />';
        $img_failed   = '<img src="'.$com->Get('url_backend').'/_core/images/install_failed.png" alt="'.PFLS_FAILED.'" />';
        $e_found      = false;

        foreach($this->elements AS $str => $field)
        {
            $status      = (int) JRequest::getVar($field, 0, 'post');
            $in_progress = JRequest::getVar('in_progress', '', 'post');
            $img         = "";
            $class       = "";
            
            switch($status)
            {
                case -1:
                    $img = $img_failed;
                    $class = "el_failed";
                    $e_found = true;
                    break;

                case 0:
                    $img = $img_pending;
                    $class = "el_pending";
                    break;

                case 1:
                    $img = $img_ok;
                    $class = "el_ok";
                    break;
            }

            if($in_progress == $field && $e_found == false) {
                $class = "el_progress";
                $img = $img_pending;
            }

            $html .= "<li class='$class'>$img <span>$str</span></li>";
        }

        $html .= "</ul><div style='clear:both'></div>";

        return $html;
    }
    
    public function DisplayTemplate($header, $body, $footer, $error = 0)
    {
        $setup_task = JRequest::getVar('task', 'splash', 'post');
        $com  = PFcomponent::GetInstance();
        $juri = JFactory::getURI();
        
        if($setup_task == 'splash') {
            ?>
            <script type="text/javascript">
            function start_upgrade()
            {
                document.adminForm.in_progress.value = "opt_old";
                document.adminForm.task.value = "opt_old";
                document.adminForm.submit();
            }
            </script>
            <?php
        }
        ?>
        <link href="<?php echo $com->Get('url_backend');?>/_core/css/setup.css" rel="stylesheet" type="text/css" />
        <form name="adminForm" action="<?php echo $juri->tostring();?>" method="post">
            <div class="pf_setup_wrapper">
                <div class="pf_setup_header">
                    <?php echo $header;?>
                </div>
                <div class="pf_setup_body">
                    <?php echo $body;?>
                </div>
                <div class="pf_setup_footer">
                    <?php echo $footer;?>
                </div>
            </div>
        <?php
        foreach($this->elements AS $str => $field)
        {
            $status = (int) JRequest::getVar($field, 0, 'post');
            echo "<input type='hidden' name='$field' value='$status' />";
        }
        ?>
        <input type="hidden" name="option" value="com_projectfork" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="in_progress" value="<?php echo JRequest::getVar('in_progress', 0, 'post');?>" />
        </form>
        <?php
        if($setup_task != null && $setup_task != 'splash' && $setup_task != 'finish' && $error == 0) {
            ?>
            <script type="text/javascript">
            <?php
            switch($setup_task)
            {
                case 'opt_old':
                    ?>
                    document.adminForm.in_progress.value = "add_fields";
                    document.adminForm.task.value = "add_fields";
                    <?php
                    break;
                    
                case 'add_fields':
                    ?>
                    document.adminForm.in_progress.value = "del_fields";
                    document.adminForm.task.value = "del_fields";
                    <?php
                    break;
                    
                case 'del_fields':
                    ?>
                    document.adminForm.in_progress.value = "ren_fields";
                    document.adminForm.task.value = "ren_fields";
                    <?php
                    break;
                    
                case 'ren_fields':
                    ?>
                    document.adminForm.in_progress.value = "indexes";
                    document.adminForm.task.value = "indexes";
                    <?php
                    break;
                    
                case 'indexes':
                    ?>
                    document.adminForm.in_progress.value = "migrate_groups";
                    document.adminForm.task.value = "migrate_groups";
                    <?php
                    break;
                    
                case 'migrate_groups':
                    ?>
                    document.adminForm.in_progress.value = "update_ext";
                    document.adminForm.task.value = "update_ext";  
                    <?php
                    break;
                    
                case 'update_ext':
                    ?>
                    document.adminForm.in_progress.value = "profiles";
                    document.adminForm.task.value = "profiles";  
                    <?php
                    break;
                    
                case 'profiles':
                    ?>
                    document.adminForm.in_progress.value = "tables";
                    document.adminForm.task.value = "tables";  
                    <?php
                    break;
                    
                case 'tables':
                    ?>
                    document.adminForm.in_progress.value = "config";
                    document.adminForm.task.value = "config";  
                    <?php
                    break;
            }
            ?>
            setTimeout("document.adminForm.submit()",1000);
            </script>
            <?php
        }
    }
}
?>