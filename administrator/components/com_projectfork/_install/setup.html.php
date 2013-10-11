<?php
/**
* $Id: setup.html.php 837 2010-11-17 12:03:35Z eaxs $
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

class PFsetupHTML
{
    private $elements;

    public function __construct()
    {
        $this->elements = array(PFLS_ACCESS_FLAGS => 'sql_access_flags',
                                PFLS_ACCESS_LEVELS => 'sql_access_levels',
                                PFLS_GROUPS => 'sql_groups',
                                PFLS_LANGUAGES => 'sql_languages',
                                PFLS_PANELS => 'sql_panels',
                                PFLS_PROCESSES => 'sql_processes',
                                PFLS_SECTION_TASKS => 'sql_section_tasks',
                                PFLS_SECTIONS => 'sql_sections',
                                PFLS_SETTINGS => 'sql_settings',
                                PFLS_THEMES => 'sql_themes',
                                PFLS_EXAMPLE => 'sql_example');
    }

    public function RenderLogo()
    {
        $com  = PFcomponent::GetInstance();
        $html = '<img src="'.$com->Get('url_backend').'/_core/images/projectfork_install.png" alt="Projectfork" />';

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

    public function RenderInstallElements()
    {
        $com = PFcomponent::GetInstance();
        
        $html         = "<ul class='install_elements'>";
        $img_pending  = '<img src="'.$com->Get('url_backend').'/_core/images/install_pending.png" alt="'.PFLS_PENDING.'" />';
        $img_ok       = '<img src="'.$com->Get('url_backend').'/_core/images/install_ok.png" alt="'.PFLS_INSTALLED.'" />';
        $img_failed   = '<img src="'.$com->Get('url_backend').'/_core/images/install_failed.png" alt="'.PFLS_INSTALL_FAILED.'" />';
        $example_data = JRequest::getVar('example_data', 0, 'post');
        $e_found      = false;

        foreach($this->elements AS $str => $field)
        {
            $status      = (int) JRequest::getVar($field, 0, 'post');
            $in_progress = JRequest::getVar('in_progress', '', 'post');
            $img         = "";
            $class       = "";

            if($example_data == 0 && $field == 'sql_example') continue;
            
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

    public function RenderProgressBar()
    {
        $html  = "";
        $count = count($this->elements);
        $example_data = JRequest::getVar('example_data', 0, 'post');

        if($example_data == 0) $count--;

        $piece    = round(100/$count);
        $progress = 0;

        foreach($this->elements AS $str => $field)
        {
            $status = (int) JRequest::getVar($field, 0, 'post');

            if($status != 0 && $status != 2) {
                $progress++;
            }
        }

        $progress = round($progress*$piece);
        $html = "<div class='progress_bar_outer'><div class='progress_bar' style='width:$progress%'></div></div><br /><br />";

        return $html;
    }

    public function DisplayTemplate($header, $body, $footer, $error = 0)
    {
        $setup_task   = JRequest::getVar('setup_task', 'splash', 'post');
        $example_data = JRequest::getVar('example_data', 0, 'post');
        $com  = PFcomponent::GetInstance();
        $juri = JFactory::getURI();
        
        if($setup_task == 'splash') {
            ?>
            <script type="text/javascript">
            function start_install(exdata)
            {
                document.adminForm.in_progress.value = "sql_access_levels";
                document.adminForm.setup_task.value = "sql_access_flags";
                document.adminForm.example_data.value = exdata;
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
        <input type="hidden" name="setup_task" value="" />
        <input type="hidden" name="in_progress" value="<?php echo JRequest::getVar('in_progress', 0, 'post');?>" />
        <input type="hidden" name="example_data" value="<?php echo JRequest::getVar('example_data', 0, 'post');?>" />
        </form>
        <?php
        if($setup_task != null && $setup_task != 'splash' && $setup_task != 'finish' && $error == 0) {
            ?>
            <script type="text/javascript">
            <?php
            switch($setup_task)
            {
                case 'sql_access_flags':
                    ?>
                    document.adminForm.in_progress.value = "sql_group_permissions";
                    document.adminForm.setup_task.value = "sql_access_levels";
                    <?php
                    break;

                case 'sql_access_levels':
                    ?>
                    document.adminForm.in_progress.value = "sql_groups";
                    document.adminForm.setup_task.value = "sql_groups";
                    <?php
                    break;

                case 'sql_group_permissions':
                    ?>
                    document.adminForm.in_progress.value = "sql_languages";
                    document.adminForm.setup_task.value = "sql_groups";
                    <?php
                    break;

                case 'sql_groups':
                    ?>
                    document.adminForm.in_progress.value = "sql_panels";
                    document.adminForm.setup_task.value = "sql_languages";
                    <?php
                    break;

                case 'sql_languages':
                    ?>
                    document.adminForm.in_progress.value = "sql_processes";
                    document.adminForm.setup_task.value = "sql_panels";
                    <?php
                    break;

                case 'sql_panels':
                    ?>
                    document.adminForm.in_progress.value = "sql_section_tasks";
                    document.adminForm.setup_task.value = "sql_processes";
                    <?php
                    break;

                case 'sql_processes':
                    ?>
                    document.adminForm.in_progress.value = "sql_sections";
                    document.adminForm.setup_task.value = "sql_section_tasks";
                    <?php
                    break;

                case 'sql_section_tasks':
                    ?>
                    document.adminForm.in_progress.value = "sql_settings";
                    document.adminForm.setup_task.value = "sql_sections";
                    <?php
                    break;

                case 'sql_sections':
                    ?>
                    document.adminForm.in_progress.value = "sql_themes";
                    document.adminForm.setup_task.value = "sql_settings";
                    <?php
                    break;

                case 'sql_settings':
                    if($example_data == 0) {
                        ?>
                        document.adminForm.in_progress.value = "sql_themes";
                        document.adminForm.setup_task.value = "sql_themes";
                        <?php
                    }
                    else {
                        ?>
                        document.adminForm.in_progress.value = "sql_example";
                        document.adminForm.setup_task.value = "sql_themes";
                        <?php
                    }
                    break;

                case 'sql_themes':
                    if($example_data == 0) {
                        ?>
                        document.adminForm.in_progress.value = "finish";
                        document.adminForm.setup_task.value = "finish";
                        <?php
                    }
                    else {
                        ?>
                        document.adminForm.in_progress.value = "finish";
                        document.adminForm.setup_task.value = "sql_example";
                        <?php
                    }
                    break;

                case 'sql_example':
                    ?>
                    document.adminForm.in_progress.value = "finish";
                    document.adminForm.setup_task.value = "finish";
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