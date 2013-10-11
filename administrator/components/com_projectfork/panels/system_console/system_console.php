<?php
/**
* $Id: system_console.php 837 2010-11-17 12:03:35Z eaxs $
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

class PFconsole
{
    private $notices;
    private $warnings;
    private $errors;
    private $violations;
    private $queries;
    private $includes;
    private $form;

    public function Row($row, $k, $type)
    {
        $clear_row = $row;
        $row2 = explode(' - ', $row);

        if(count($row2) == 3) {
            $row = "<tr class='pf_row$k row$k sectiontableentry".($k + 1)."'>"
                 . "<td class='mono'>".$row2[0]."</td>"
                 . "<td class='mono'>".$row2[1]."</td>"
                 . "<td class='mono'>".nl2br($row2[2]).$this->form->HiddenField($type.'[]', $clear_row)."</td>"
                 . "</tr>";
        }
        if(count($row2) == 2) {
            $row = "<tr class='pf_row$k row$k sectiontableentry".($k + 1)."'>"
                 . "<td class='mono'>".$row2[0]."</td>"
                 . "<td class='mono'></td>"
                 . "<td class='mono'>".nl2br($row2[1]).$this->form->HiddenField($type.'[]', $clear_row)."</td>"
                 . "</tr>";
        }

        return $row;
    }

    public function AddCSS()
    {
        $j_doc = &JFactory::getDocument();
        $j_doc->addStyleDeclaration(
        ".pf_debug_wrapper {height:400px;overflow:auto;text-align:left;}
         .mono{font-family:Courier,monospace;}
        .pf_debug_getparameter {color:#339933;cursor:help;text-decoration:underline;}
        .pf_debug_setparameter {color:#006600;cursor:help;text-decoration:underline;}
        .pf_debug_loadpanels {color:#CC9900;cursor:help;text-decoration:underline;}
        .pf_debug_access {color:#990000;cursor:help;text-decoration:underline;}
        .pf_debug_loadprocesses {color:#009999;cursor:help;text-decoration:underline;}
        .pf_debug_loadprofile {color:#0000FF;cursor:help;text-decoration:underline;}
        .pf_debug_pathlib {color:#009933;cursor:help;text-decoration:underline;}
        .pf_debug_pathpanels {color:#FF9900;cursor:help;text-decoration:underline;}
        .pf_debug_pathsections {color:#990099;cursor:help;text-decoration:underline;}
        .pf_debug_pathprocesses {color:#00CCCC;cursor:help;text-decoration:underline;}
        .pf_debug_paththemes {color:#3300FF;cursor:help;text-decoration:underline;}
        .pf_debug_sqlselect {color:#009900;cursor:help;text-decoration:underline;}
        .pf_debug_sqlupdate {color:#CC9900;cursor:help;text-decoration:underline;}
        .pf_debug_sqldelete {color:#990000;cursor:help;text-decoration:underline;}
        ");
    }
    
    public function FetchLog()
    {
        $debug     = PFdebug::GetInstance();
        $n_notices = array();
        $full_log  = $debug->GetLog();
        
        // get debug messages
        $this->notices    = $full_log['n'];
        $this->warnings   = $full_log['w'];
        $this->errors     = $full_log['e'];
        $this->violations = $full_log['v'];
        $this->includes   = array();
        $this->queries    = array();
        
        if( !is_array($this->notices) )    $this->notices    = array();
        if( !is_array($this->warnings) )   $this->warnings   = array();
        if( !is_array($this->errors) )     $this->errors     = array();
        if( !is_array($this->violations) ) $this->violations = array();

        // extract includes and queries from notices
        foreach ($this->notices AS $notice)
        {
            if(strstr($notice, 'PFload::FilePath') || strstr($notice, 'PFload::URLpath') ) {
                $this->includes[] = $notice;
                continue;
            }
            if( strstr($notice, 'PFdatabase::setQuery') ) {
                $this->queries[] = $notice;
                continue;
            }
            $n_notices[] = $notice;
        }
        $this->notices = $n_notices;
        
        unset($n_notices,$debug,$full_log);
        
        // new form
        $this->form = new PFform();
    }
    
    public function Get($var)
    {
        return $this->$var;
    }
}

// Include HTML tabs class
if(!class_exists('JPane')) jimport('joomla.html.pane');

// Get objects
$core = PFcore::GetInstance();
$com  = PFcomponent::GetInstance();
$db   = JFactory::getDBO();
$user = PFuser::GetInstance();
$pane = JPane::getInstance('tabs');
$form = new PFform('pf_debug_form');
$con  = new PFconsole();

// Setup vars
$uid = $user->GetId();
$user_env = $user->Permission();
$k = 0;

// Setup console stuff
$con->FetchLog();
$con->AddCSS();

$notices    = $con->Get('notices');
$warnings   = $con->Get('warnings');
$errors     = $con->Get('errors');
$violations = $con->Get('violations');
$includes   = $con->Get('includes');
$queries    = $con->Get('queries');

$notices_c    = count($notices);
$warnings_c   = count($warnings);
$errors_c     = count($errors);
$violations_c = count($violations);
$includes_c   = count($includes);
$queries_c    = count($queries);

// Load settings
$query = "SELECT * FROM #__pf_settings ORDER BY scope,parameter ASC";
       $db->setQuery($query);
       $settings = $db->loadObjectList();

if(!is_array($settings)) $settings = array();

// Load user profile
$query = "SELECT * FROM #__pf_user_profile WHERE user_id = '$uid'"
       . "\n ORDER BY `parameter` ASC";
       $db->setQuery($query);
       $uprofile = $db->loadObjectList();
      
if(!is_array($uprofile)) $uprofile = array();
       

// Start output
echo $form->Start();
echo $pane->startPane("pf_debug-pane");

// PRINT SYSTEM INFO
echo $pane->startPanel('System Info', 'pf_debug_sys');
?>
<div class="pf_debug_wrapper">
    <table width="99%">
       <tr>
          <td style="width:50%" valign="top" width="50%">
             <h3>General Information</h3>
                <table class="admintable" width="100%">
                    <tbody>
                       <tr>
                          <td class="key"><strong>Joomla Version</strong></td>
                          <td><?php echo JVERSION; ?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Projectfork Version</strong></td>
                          <td><?php echo PF_VERSION_STRING;?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>PHP Built On</strong></td>
                          <td><?php echo php_uname();?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>PHP Version</strong></td>
                          <td><?php echo phpversion();?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Database Version</strong></td>
                          <td><?php echo $db->getVersion();?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Database Collation</strong></td>
                          <td><?php echo $db->getCollation();?></td>
                       </tr>
                    </tbody>
                </table>
                <h3>Variables</h3>
                <table class="admintable" width="100%">
                    <tbody>
                       <tr>
                          <td class="key"><strong>Query String</strong></td>
                          <td><?php echo htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES);?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Current Section</strong></td>
                          <td><?php echo $core->GetSection();?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Current Task</strong></td>
                          <td><?php echo $core->GetTask();?></td>
                       </tr>
                    </tbody>
                </table>
                <h3>User</h3>
                <table class="admintable" width="100%">
                    <tbody>
                       <tr>
                          <td class="key"><strong>ID</strong></td>
                          <td><?php echo $uid;?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>GID</strong></td>
                          <td><?php echo $user->GetGid();?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Type</strong></td>
                          <td><?php echo $user->GetType();?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Access level</strong></td>
                          <td><?php echo $user->Permission('accesslevel');?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Score</strong></td>
                          <td><?php echo $user->Permission('score');?></td>
                       </tr>
                       <tr>
                          <td class="key"><strong>Flag</strong></td>
                          <td><?php echo $user->Permission('flag');?></td>
                       </tr>
                    </tbody>
                </table>
                <h3>User Profile</h3>
                <table width="100%" class="pf_table adminlist">
                 <thead>
                 <tr>
                    <th>Parameter</th>
                    <th>Value</th>
                 </tr>
                 </thead>
                 <tbody>
                 <?php
                 $k = 0;
                 foreach($uprofile AS $profile)
                 {
                     ?>
                     <tr class="pf_row<?php echo $k;?> row<?php echo $k;?> sectiontableentry<?php echo $k+1;?>">
                        <td><?php echo htmlspecialchars($profile->parameter, ENT_QUOTES);?></td>
                        <td><?php echo htmlspecialchars($profile->content, ENT_QUOTES);?></td>
                     </tr>
                     <?php
                     $k = 1 - $k;
                 }
                 ?>
                 </tbody>
              </table>
          </td>
          <td style="width:50%" valign="top" width="50%">
              <h3>System Settings</h3>
              <table width="100%" class="pf_table adminlist">
                 <thead>
                 <tr>
                    <th>Scope</th>
                    <th>Parameter</th>
                    <th>Value</th>
                 </tr>
                 </thead>
                 <tbody>
                 <?php
                 $k = 0;
                 foreach($settings AS $setting)
                 {
                     ?>
                     <tr class="pf_row<?php echo $k;?> row<?php echo $k;?> sectiontableentry<?php echo $k+1;?>">
                        <td><?php echo $setting->scope;?></td>
                        <td><?php echo htmlspecialchars($setting->parameter, ENT_QUOTES);?></td>
                        <td><?php echo htmlspecialchars($setting->content, ENT_QUOTES);?></td>
                     </tr>
                     <?php
                     $k = 1 - $k;
                 }
                 ?>
                 </tbody>
              </table>
          </td>
       </tr>
    </table>
</div>
<?php
echo $pane->endPanel();

// PRINT NOTICES
if($notices_c) {
	echo $pane->startPanel($notices_c." Notices", 'pf_debug_notice');
    ?>
	<div class="pf_debug_wrapper">
        <table width='99%' class='pf_table adminlist'>
        <thead>
           <tr>
              <th style="width:80px;" nowrap="nowrap">Time</th>
              <th style="width:10%" nowrap="nowrap">Method</th>
              <th>Message</th>
           </tr>
        </thead>
        <tbody>
        <?php
	    foreach ($notices AS $row)
	    {
            echo $con->Row($row, $k, 'notices');
		    $k = 1 - $k;
	    }
        ?>
	    </tbody>
        </table>
    </div>
    <?php
	echo $pane->endPanel();
}
// PRINT WARNINGS
if($warnings_c) {
	echo $pane->startPanel($warnings_c." Warnings", 'pf_debug_warnings');
    ?>
	<div class="pf_debug_wrapper">
        <table width='99%' class='pf_table adminlist'>
        <thead>
           <tr>
              <th style="width:80px;" nowrap="nowrap">Time</th>
              <th style="width:10%" nowrap="nowrap">Method</th>
              <th>Message</th>
           </tr>
        </thead>
        <tbody>
        <?php
        foreach ($warnings AS $row)
        {
            echo $con->Row($row, $k, 'warnings');
            $k = 1 - $k;
        }
        ?>
        </tbody>
        </table>
    </div>
    <?php
	echo $pane->endPanel();
}
// PRINT ERRORS
if($errors_c) {
	echo $pane->startPanel($errors_c." Errors", 'pf_debug_errors');
    ?>
	<div class="pf_debug_wrapper">
        <table width='99%' class='pf_table adminlist'>
        <thead>
           <tr>
              <th style="width:80px;" nowrap="nowrap">Time</th>
              <th style="width:10%" nowrap="nowrap">Method</th>
              <th>Message</th>
           </tr>
        </thead>
        <tbody>
        <?php
        foreach ($errors AS $row)
        {
            echo $con->Row($row, $k, 'errors');
            $k = 1 - $k;
        }
        ?>
        </tbody>
        </table>
    </div>
    <?php
	echo $pane->endPanel();
}
// PRINT VIOLATIONS
if($violations_c) {
	echo $pane->startPanel($violations_c." Access violations", 'pf_debug_violations');
    ?>
	<div class="pf_debug_wrapper">
        <table width='99%' class='pf_table adminlist'>
        <thead>
           <tr>
              <th style="width:80px;" nowrap="nowrap">Time</th>
              <th style="width:10%" nowrap="nowrap">Method</th>
              <th>Message</th>
           </tr>
        </thead>
        <tbody>
        <?php
        foreach ($violations AS $row)
        {
            echo $con->Row($row, $k, 'violations');
            $k = 1 - $k;
        }
        ?>
        </tbody>
        </table>
    </div>
    <?php
	echo $pane->endPanel();
}

// PRINT INCLUDES
if($includes_c) {
	echo $pane->startPanel($includes_c." Included files", 'pf_debug_includes');
    ?>
	<div class="pf_debug_wrapper">
    <table width='99%' class='pf_table adminlist'>
    <thead>
       <tr>
          <th style="width:80px;" nowrap="nowrap">Time</th>
          <th style="width:10%" nowrap="nowrap">Method</th>
          <th>Message</th>
       </tr>
    </thead>
    <tbody>
    <?php
	foreach ($includes AS $row)
	{
		echo $con->Row($row, $k, 'includes');
		$k = 1 - $k;
	}
    ?>
	</tbody></table>
    </div>
    <?php
	echo $pane->endPanel();
}

// PRINT QUERIES
if($queries_c) {
	echo $pane->startPanel($queries_c." Database queries", 'pf_debug_dbq');
    ?>
	<div class="pf_debug_wrapper">
    <table width='99%' class='pf_table adminlist'>
    <thead>
       <tr>
          <th style="width:80px;" nowrap="nowrap">Time</th>
          <th style="width:10%" nowrap="nowrap">Method</th>
          <th>Message</th>
       </tr>
    </thead>
    <tbody>
    <?php
	foreach ($queries AS $row)
	{
		echo $con->Row($row, $k, 'queries');
		$k = 1 - $k;
	}
    ?>
	</tbody></table>
    </div>
    <?php
	echo $pane->endPanel();
}

// PRINT OPTIONS
echo $pane->startPanel('Options', 'pf_debug_options');
?>
<div class="pf_debug_wrapper">
   <table width='99%' class="admintable">
   <tbody>
      <tr>
         <td class="key">Download logfile</td>
         <td align='left'><?php echo $form->NavButton('Download', "javascript:document.pf_debug_form.submit();");?></td>
      </tr>
   </tbody>
   </table>
</div>
<?php
echo $pane->endPanel();
echo $pane->endPane();

// user environment
echo $form->HiddenField('user_env[accesslevel]', $user_env['accesslevel']);
echo $form->HiddenField('user_env[score]', $user_env['score']);
echo $form->HiddenField('user_env[flag]', $user_env['flag']);
echo $form->HiddenField('user_env[workspace]', $user_env['workspace']);

foreach($user_env['groups'] AS $row)
{
    echo $form->HiddenField('user_env[groups][]', $row);
}

foreach($user_env['projects'] AS $row)
{
    echo $form->HiddenField('user_env[projects][]', $row);
}

foreach($user_env['author'] AS $row)
{
    echo $form->HiddenField('user_env[author][]', $row);
}

foreach($user_env['userspace'] AS $row)
{
    echo $form->HiddenField('user_env[userspace][]', $row);
}

foreach($user_env['sections'] AS $k => $row)
{
    if($k == '__objects') continue;
    echo $form->HiddenField('user_env[sections][]', $row);
}

foreach($user_env['tasks'] AS $section => $tasks)
{
    foreach($tasks AS $task)
    {
        if(is_array($task)) continue;
        echo $form->HiddenField('user_env[tasks]['.$section.'][]', $task);
    }
}

// end form
echo $form->HiddenField('option', $com->Get('name'));
echo $form->HiddenField('section', JRequest::getVar('section'));
echo $form->HiddenField('task', 'debug_save');
echo $form->HiddenField('section_task', JRequest::getVar('task'));
echo $form->HiddenField('qstring', $_SERVER['QUERY_STRING']);
echo $form->HiddenField('save_method', '0');
echo $form->End();
?>