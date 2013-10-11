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

// Load objects
$core = PFcore::GetInstance();
$form = new PFform('adminForm_subnav');

// Get section object
$sobj = $core->GetSectionObject();

$html = $form->Start();

switch($core->GetTask())
{
	default:
	    $html .= '
        <div class="pf_navigation">
            <ul>
                <li class="btn pf_save">'.$form->NavButton('SAVE', "javascript:task_update()", 'TT_SAVE_PROFILE', 'task_update').'</li>
                <li class="btn pf_cancel">'.$form->NavButton('CANCEL', "", 'TT_CANCEL_PROFILE').'</li>
                <!--<li class="btn pf_config">'.$form->NavButton('CONFIG', "section=config&task=form_edit_section&&rts=1&id=$sobj->id", 'QL_CONFIG_SECTION', 'form_edit_section', 'config').'</li>-->
           </ul>
        </div>';
		break;
		
	case 'display_details':
	    $html .= '
        <div class="pf_navigation">
            <ul>
                <li class="btn pf_back">'.$form->NavButton('BACK', "javascript:window.history.back();").'</li>
            </ul>
        </div>';
		break;	
}

$html .= $form->HiddenField("option");
$html .= $form->HiddenField("section");
$html .= $form->HiddenField("task");
$html .= $form->End();

echo $html;

unset($core,$form,$sobj,$html);
?>