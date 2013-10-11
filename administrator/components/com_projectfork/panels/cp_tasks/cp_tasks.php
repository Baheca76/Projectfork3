<?php
/**
* $Id: cp_tasks.php 837 2010-11-17 12:03:35Z eaxs $
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

$db   = PFdatabase::GetInstance();
$user = PFuser::GetInstance();
$cfg  = PFconfig::GetInstance();

$uid  = $user->GetId();
$ws   = $user->GetWorkspace();

// Don't show anything unless we are logged in
if(!$uid) return false;

// Get params
$ob       = $cfg->Get('ob', 'cp_tasks');
$od       = $cfg->Get('od', 'cp_tasks');
$limit    = (int) $cfg->Get('limit', 'cp_tasks');
$assigned = (int) $cfg->Get('assigned', 'cp_tasks');
$status   = (int) $cfg->Get('status', 'cp_tasks');
$priority = (int) $cfg->Get('priority', 'cp_tasks');

if(!$ob)    $ob = "t.cdate";
if(!$od)    $od = "ASC";
if(!$limit) $limit = 10;

if(!$ws) {
	$projects = $user->Permission('projects');
    $projects = implode(',',$projects);
    $all_projects = true;
}
else {
	$projects = $ws;
	$all_projects = false;
}

// Setup query filter
$filter = "";
if($assigned == 2) $filter .= "\n AND tu.user_id = ".$db->Quote($uid);
if($status == 1)   $filter .= "\n AND t.progress < 100";
if($status == 2)   $filter .= "\n AND t.progress = 100";
if($priority > 0)  $filter .= "\n AND t.priority = $priority";

// Include helper
require_once(PFobject::GetHelper('tasks'));

$html = '<table class="adminlist pf_table cp_tasks_table" width="100%" cellpadding="0" cellspacing="0">
         <tbody>';
         
if($projects != "" && $projects != 0) {

    $q_projects = ($all_projects == true) ? "\n WHERE p.id IN($projects)" : "\n WHERE p.id = $projects";
    
    $query = "SELECT t.*, u.name, p.title project_name, p.id pid, m.title ms_title FROM #__pf_tasks AS t"
	       . "\n LEFT JOIN #__pf_task_users AS tu ON tu.task_id = t.id"
           . "\n LEFT JOIN #__users AS u ON u.id = tu.user_id"
	       . "\n RIGHT JOIN #__pf_projects AS p ON p.id = t.project AND(p.approved = '1' AND p.archived = '0')"
	       . "\n LEFT JOIN #__pf_milestones AS m ON m.id = t.milestone"
	       . $q_projects
	       . "\n AND t.id = t.id"
	       . "\n AND p.id = t.project"
	       . $filter
           . "\n GROUP BY t.id"
           . "\n ORDER BY $ob $od"
           . "\n LIMIT $limit";
	       $db->setQuery($query);
	       $rows = $db->loadObjectList();

     if(!is_array($rows)) $rows = array();
     
     // Displaying all projects
     if($all_projects) {
         $pids = $user->Permission('projects');
         $looped = array();
         
         foreach($pids AS $pid)
         {
             if(in_array($pid, $looped)) continue;
             $looped[] = $pid;
             
             $query = "SELECT t.*, u.name, m.title ms_title FROM #__pf_tasks AS t"
        	        . "\n LEFT JOIN #__pf_task_users AS tu ON tu.task_id = t.id"
                    . "\n LEFT JOIN #__users AS u ON u.id = tu.user_id"
        	        . "\n RIGHT JOIN #__pf_projects AS p ON p.id = t.project AND(p.approved = '1' AND p.archived = '0')"
        	        . "\n LEFT JOIN #__pf_milestones AS m ON m.id = t.milestone"
        	        . "\n WHERE p.id = '$pid'"
        	        . "\n AND t.id = t.id"
        	        . "\n AND p.id = t.project"
        	        . $filter
                    . "\n GROUP BY t.id"
                    . "\n ORDER BY $ob $od"
                    . "\n LIMIT $limit";
        	        $db->setQuery($query);
        	        $rows = $db->loadObjectList();
        	        
             if(!is_array($rows)) $rows = array();
             if(!count($rows)) continue;
             
             $query = "SELECT title FROM #__pf_projects WHERE id = '$pid'";
                    $db->setQuery($query);
                    $ptitle = $db->loadResult();
                    
             $html .= '<tr>
             <td colspan="3" class="pf_project_cell"><h4>'.htmlspecialchars($ptitle).'</h4></td>
             </tr>';
             
             $k = 0;
             foreach($rows AS $i => $row)
             {
                 JFilterOutput::objectHTMLSafe($row);
                 
                 $tlink = PFformat::Link("section=tasks&task=display_details&id=$row->id&workspace=$row->project");
                 $prio  = PFtasksHelper::RenderPriority($row->priority);
                 $t_acc = $user->access('display_details', 'tasks', $row->author);
                 
                 $t_title = ($t_acc == true) ? '<a href="'.$tlink.'">'.$row->title.'</a>' : $row->title;
                 
                 $deadline = "";
		         if($row->edate) $deadline = PFformat::ToDate($row->edate);
		  
                 $html .= '<tr class="row'.$k.' sectiontableentry'.($k + 1).' priority_'.$row->priority.'">
                 <td class="pf_priority_cell"><span>'.$prio.'</span></td>
                 <td class="pf_deadline_cell">'.$deadline.'</td>
      	         <td class="pf_title_cell">'.$t_title.'</td>
                 </tr>';
             }
         }
         
         if(!count($rows)) {
              $html .= '<tr class="row0 sectiontableentry1 pf_no_tasks">
      	      <td align="center" style="text-align:center"><div class="pf_info">'.PFformat::Lang('PFL_NO_TASKS').'</div></td>
      	      </tr>';
          }
     }
     else {
          $query = "SELECT t.*, u.name, m.title ms_title FROM #__pf_tasks AS t"
 	             . "\n LEFT JOIN #__pf_task_users AS tu ON tu.task_id = t.id"
                 . "\n LEFT JOIN #__users AS u ON u.id = tu.user_id"
                 . "\n RIGHT JOIN #__pf_projects AS p ON p.id = t.project AND(p.approved = '1' AND p.archived = '0')"
        	     . "\n LEFT JOIN #__pf_milestones AS m ON m.id = t.milestone"
        	     . "\n WHERE p.id = '$ws'"
        	     . "\n AND t.id = t.id"
        	     . "\n AND p.id = t.project"
        	     . $filter
                 . "\n GROUP BY t.id"
                 . "\n ORDER BY $ob $od"
                 . "\n LIMIT $limit";
        	     $db->setQuery($query);
        	     $rows = $db->loadObjectList();
        	     
          if(!is_array($rows)) $rows = array();
          
          $k = 0;
          foreach($rows AS $i => $row)
          {
              JFilterOutput::objectHTMLSafe($row);
             
              $tlink = PFformat::Link("section=tasks&task=display_details&id=$row->id&workspace=$row->project");
              $prio  = PFtasksHelper::RenderPriority($row->priority);
              $t_acc = $user->access('display_details', 'tasks', $row->author);
             
              $t_title = ($t_acc == true) ? '<a href="'.$tlink.'">'.$row->title.'</a>' : $row->title;
             
              $deadline = "";
	          if($row->edate) $deadline = PFformat::ToDate($row->edate);
	  
              $html .= '<tr class="row'.$k.' sectiontableentry'.($k + 1).' priority_'.$row->priority.'">
              <td class="pf_priority_cell"><span>'.$prio.'</span></td>
              <td class="pf_deadline_cell">'.$deadline.'</td>
  	          <td class="pf_title_cell">'.$t_title.'</td>
              </tr>';
          }

          if(!count($rows)) {
              $html .= '<tr class="row0 sectiontableentry1 pf_no_tasks">
      	      <td align="center" style="text-align:center"><div class="pf_info">'.PFformat::Lang('PFL_NO_TASKS').'</div></td>
      	      </tr>';
          }
     }
}

$html .= '<tfoot>
     <tr class="pf_lastrow"><td colspan="7">&nbsp;</td></tr>
     </tfoot></table>';
     
echo $html;     
unset($db,$user,$cfg,$html);
?>