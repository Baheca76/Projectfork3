<?php
/**
* $Id: project_tasks.php 878 2011-04-12 03:40:54Z angek $
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

$id = (int) JRequest::GetVar('id');

if($id) {
    // Load objects
    $db     = PFdatabase::GetInstance();
    $user   = PFuser::GetInstance();
    $config = PFconfig::GetInstance();
    
    // Load config params
    $ob       = $config->Get('ob', 'project_tasks');
    $od       = $config->Get('od', 'project_tasks');
    $limit    = (int) $config->Get('limit', 'project_tasks');
    $tlink    = (int) $config->Get('tlink', 'project_tasks');
    $assigned = (int) $config->Get('assigned', 'project_tasks');
    $status   = (int) $config->Get('status', 'project_tasks');
    $priority = (int) $config->Get('priority', 'project_tasks');
    
    if(!$ob) $ob = "t.cdate";
    if(!$od) $od = "ASC";
    if(!$limit) $limit = 10;
    
    // Setup query filter
    $filter = "";

    if($assigned == 2) $filter .= "\n AND tu.user_id = ".$db->Quote($user->GetId());
    if($status == 1) $filter .= "\n AND t.progress < 100";
    if($status == 2) $filter .= "\n AND t.progress = 100";
    if($priority > 0) $filter .= "\n AND t.priority = $priority";
    
    // Load tasks
    $query = "SELECT t.*, u.name, p.title project_name, p.id pid, m.title ms_title FROM #__pf_tasks AS t"
    	   . "\n LEFT JOIN #__pf_task_users AS tu ON tu.task_id = t.id"
           . "\n LEFT JOIN #__users AS u ON u.id = tu.user_id"
    	   . "\n LEFT JOIN #__pf_projects AS p ON p.id = t.project"
    	   . "\n LEFT JOIN #__pf_milestones AS m ON m.id = t.milestone"
    	   . "\n WHERE p.id = $id"
    	   . $filter
           . "\n GROUP BY t.id"
           . "\n ORDER BY $ob $od"
           . "\n LIMIT $limit";
    	   $db->setQuery($query);
    	   $rows = $db->loadObjectList();
	
    if(!is_array($rows)) $rows = array();
    
    if($user->Access('display_details', 'tasks')) {
        $html = '
        <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th align="center">#</th>
                    <th width="50%">'.PFformat::Lang('TITLE').'</th>
                    <th width="25%">'.PFformat::Lang('ASSIGNED_TO').'</th>
                    <th width="10%">'.PFformat::Lang('PRIORITY').'</th>
                    <th width="10%">'.PFformat::Lang('DEADLINE').'</th>
                    <th width="5%">'.PFformat::Lang('PROGRESS').'</th>
                </tr>
            </thead>
            <tbody>';
            
        $k = 0;
        foreach ($rows AS $i => $row)
        {
            JFilterOutput::objectHTMLSafe($row);
     	    $p_edit_s = "";
       	    $p_edit_e = "";
       	    $t_edit_s = "";
       	    $t_edit_e = "";
          	  
       	    switch ($row->priority)
    	    {
    	        case 0:$priority = PFformat::Lang('NOT_SET');break;
    		    case 1:$priority = PFformat::Lang('PRIO_VERY_LOW');break;
    		    case 2:$priority = PFformat::Lang('PRIO_LOW');break;
    		    case 3:$priority = PFformat::Lang('PRIO_MEDIUM');break;	
    		    case 4:$priority = PFformat::Lang('PRIO_HIGH');break;
    		    case 5:$priority = PFformat::Lang('PRIO_VERY_HIGH');break;			
    	    }
    		  
    		$deadline = PFformat::Lang('NOT_SET');
    		if($row->edate) $deadline = PFformat::ToDate($row->edate);
    		  
    		if($tlink == 1) {
    		    $t_edit_s = "<a href='".PFformat::Link("section=tasks&task=display_details&id=".$row->id)."'>";
    		  	$t_edit_e = "</a>";
    		}
          	
          	$html .= '
          	<tr class="pf_row'.$k.' row'.$k.' sectiontableentry'.($k+1).'">
          	    <td>'.($i + 1).'</td>
     	        <td>'.$t_edit_s.$row->title.$t_edit_e.'</td>
     	        <td>'.$row->name.'</td>
     	        <td>'.$priority.'</td>
     	        <td>'.$deadline.'</td>
     	        <td>'.$row->progress.'%</td>
          	</tr>';
          	$k = 1 - $k;
        }
        
        if(!count($rows)) {
            $html .= '
      	    <tr class="pf_row0">
      	        <td colspan="7" align="center" style="text-align:center"><div class="pf_info">'.PFformat::Lang('PFL_NO_TASKS').'</div></td>
  	        </tr>';
        }
        
        $html .= '</tbody></table>';

        echo $html;
        unset($html, $rows);
    }
}
?>