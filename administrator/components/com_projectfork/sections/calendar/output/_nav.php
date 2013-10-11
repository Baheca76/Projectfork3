<?php
/**
* $Id: _nav.php 837 2010-11-17 12:03:35Z eaxs $
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

// Get objects
$core = PFcore::GetInstance();
$form = new PFform('adminForm_subnav');

$form->SetBind(true, 'REQUEST');

// Get user input
$year  = (int) JRequest::getVar('year',date("Y"));
$month = (int) JRequest::getVar('month',date("n"));
$today = (int) JRequest::getVar('day', date("j"));

$task = $core->GetTask();
if(!$task) $task = 'display_month';

// Get section object
$sobj = $core->GetSectionObject();

$html = $form->Start();

switch( $task )
{
    case 'display_month':
        $prev_month = $month - 1;
        $next_month = $month + 1;
        $prev_year  = $year;
        $next_year  = $year;
        if($prev_month == 0) {
            $prev_month = 12;
            $prev_year--;
        }
        if($next_month == 13) {
            $next_month = 1;
            $next_year++;
        }
        $prev_link = PFformat::Link("section=calendar&task=display_month&year=".$prev_year.'&month='.$prev_month.'&day='.$today);
        $next_link = PFformat::Link("section=calendar&task=display_month&year=".$next_year.'&month='.$next_month.'&day='.$today);
        break;
        
    case 'display_week':
        // Prev dates
        $prev_year  = $year;
        $prev_month = $month;
        $prev_day   = $today - 7;
	    $dim = cal_days_in_month(CAL_GREGORIAN, $prev_month, $prev_year);
	    
	    if($prev_month-1 == 0) {
	         $dim1 = cal_days_in_month(CAL_GREGORIAN, 12, $prev_year-1);
	    } 
        else { 
             $dim1 = cal_days_in_month(CAL_GREGORIAN, $prev_month-1, $prev_year);
	    }
	    
	    if($prev_day <= 0 ) { 
	         $prev_month = $prev_month - 1;  
	         if($prev_month ==0 ) {$prev_year = $prev_year - 1; $prev_month = 12;}
	         $prev_day = $prev_day + $dim1; 
	    }
	    
	    // Next dates
	    $next_year  = $year;
	    $next_month = $month;
	    $next_day   = $today + 7;
	    $dim = cal_days_in_month(CAL_GREGORIAN, $next_month, $next_year);
	    
	    if ($next_day > $dim ) {
		     $next_month = $next_month+1;
		     if($next_month == 13) { $next_year = $next_year+1; $next_month=1; }
		     $next_day = 1; 
        }
        
        $prev_link = PFformat::Link("section=calendar&task=display_week&year=".$prev_year.'&month='.$prev_month.'&day='.$prev_day);
        $next_link = PFformat::Link("section=calendar&task=display_week&year=".$next_year.'&month='.$next_month.'&day='.$next_day);
        break;
        
    case 'display_day':
        // Prev dates
        $prev_year  = $year;
	    $prev_month = $month;
	    $prev_day   = $today-1;
	    
	    $dim = cal_days_in_month(CAL_GREGORIAN, $prev_month, $prev_year);
	    if($prev_day == 0) {
		    $prev_month = $prev_month-1;
		    if ($prev_month==0) {$prev_year = $prev_year-1; $prev_month = 12;}
		    $prev_day = cal_days_in_month(CAL_GREGORIAN, $prev_month, $prev_year); 
        }
        
        // Next dates
        $next_year  = $year;
	    $next_month = $month;
	    $next_day   = $today + 1;
	    
	    $dim = cal_days_in_month(CAL_GREGORIAN, $next_month, $next_year);
	    if($next_day > $dim) {
		    $next_month = $next_month+1;
		    if ($next_month==13) {$next_year = $next_year+1; $next_month = 1;}
		    $next_day = 1;
        }
        
        $prev_link = PFformat::Link("section=calendar&task=display_day&year=".$prev_year.'&month='.$prev_month.'&day='.$prev_day);
        $next_link = PFformat::Link("section=calendar&task=display_day&year=".$next_year.'&month='.$next_month.'&day='.$next_day);
        break;        
}

$year  = (int) JRequest::getVar('year',date("Y"));
$month = (int) JRequest::getVar('month',date("n"));
$today = (int) JRequest::getVar('day', date("j"));

switch( $task )
{
	case 'display_month':
	case 'display_week':
	case 'display_day':
	default:
	    $html .= '
        <div class="pf_navigation display_cal">
           <ul>
              <li class="btn pf_new">'.$form->NavButton('NEW', 'section=calendar&task=form_new&year='.$year.'&month='.$month.'&day='.$today, 'TT_NEW_EVENT', 'form_new').'</li>
              <li class="btn pf_delete">'.$form->NavButton('DELETE', 'javascript:task_delete();', 'TT_DELETE', 'task_delete').'</li>
              <li class="btn pf_month">'.$form->NavButton('DISPLAY_MONTH', 'section=calendar&task=display_month&year='.$year.'&month='.$month.'&day='.$today, 'TT_DPM', 'display_month').'</li>
              <li class="btn pf_week">'.$form->NavButton('DISPLAY_WEEK', 'section=calendar&task=display_week&year='.$year.'&month='.$month.'&day='.$today, 'TT_DPW', 'display_week').'</li>
              <li class="btn pf_day">'.$form->NavButton('DISPLAY_DAY', 'section=calendar&task=display_day&year='.$year.'&month='.$month.'&day='.$today, 'TT_DPD', 'display_day').'</li>
              <!--<li class="btn pf_config">'.$form->NavButton('CONFIG', "section=config&task=form_edit_section&&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section').'</li>-->
           </ul>
        </div>
        <div class="pf_navigation pf_nav_cal">
        	<span class="pf_nav_prev"><a class="pf_button" href="'.$prev_link.'">'.PFformat::Lang('PREV').'</a></span>
           <span class="pf_nav_day">'.$form->InputField('day', $today, 'size="5"').'</span>
           <span class="pf_nav_month">'.$form->SelectMonth('month').'</span>
           <span class="pf_nav_year">'.$form->InputField('year', $year, 'size="10"').'</span>
           <span class="btn">'.$form->NavButton('OK', "javascript:navsubmit('".$task."');").'</span>
           <span class="pf_nav_next"><a class="pf_button" href="'.$next_link.'">'.PFformat::Lang('NEXT').'</a></span>
        </div>
        <!--<div class="pfl_search">
              <span>'.PFformat::Lang('SEARCH').$form->InputField('keyword').'</span>
              <span class="btn">'.$form->NavButton('OK', "javascript:navsubmit('".$task."');").'</span>
        </div>-->';
		break;
		
	case 'form_new':
	    $html .= '
        <div class="pf_navigation form_new">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_save()", 'TT_CREATE_EVENT', 'task_save').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', 'section=calendar&task=display_month&year='.$year.'&month='.$month.'&day='.$today, 'TT_BACK', 'display_month').'</li>
           </ul>
        </div>';
		break;
		
	case 'form_edit':
	    $html .= '
        <div class="pf_navigation form_edit">
           <ul>
              <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update()", 'TT_UPDATE', 'task_update').'</li>
              <li class="btn pf_cancel">'.$form->NavButton('CANCEL', 'section=calendar&task=display_month&year='.$year.'&month='.$month.'&day='.$today, 'TT_BACK', 'display_month').'</li>
           </ul>
        </div>';
		break;	
}

$html .= $form->HiddenField("option");
$html .= $form->HiddenField("section");
$html .= $form->HiddenField("task");
$html .= $form->End();

echo $html;

unset($core,$form,$html,$sobj);
?>