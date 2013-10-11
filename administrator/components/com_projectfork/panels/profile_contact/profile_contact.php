<?php
/**
* $Id: profile_contact.php 837 2010-11-17 12:03:35Z eaxs $
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

// Load objects
$user   = PFuser::GetInstance();
$config = PFconfig::GetInstance();
$db     = PFdatabase::GetInstance();

// Load config settings
$use_phone  = (int) $config->Get('allow_phone', 'profile');
$use_mphone = (int) $config->Get('allow_mphone', 'profile');
$use_skype  = (int) $config->Get('allow_skype', 'profile');
$use_msn    = (int) $config->Get('allow_msn', 'profile');
$use_icq    = (int) $config->Get('allow_icq', 'profile');

// Get user id
$id = (int) JRequest::getVar('id');

if($id) {
    // Load the user email
    $query = "SELECT email FROM #__users"
           . "\n WHERE id = '$id'";
           $db->setQuery($query);
           $email = $db->loadResult();
           
    $params = $user->LoadProfile($id);
    
    if($use_phone)  $use_phone  = ((array_key_exists('phone', $params)) ? trim($params['phone']) : '');
    if($use_mphone) $use_mphone = ((array_key_exists('mobile_phone', $params)) ? trim($params['mobile_phone']) : '');
    if($use_skype)  $use_skype  = ((array_key_exists('skype', $params)) ? trim($params['skype']) : '');
    if($use_msn)    $use_msn    = ((array_key_exists('msn', $params)) ? trim($params['msn']) : '');
    if($use_icq)    $use_icq    = ((array_key_exists('icq', $params)) ? trim($params['icq']) : '');
    
    if(count($params)) {
        $html = '
        <table class="admintable">
            <tr>
                <td class="key" width="100">'.PFformat::Lang('EMAIL').'</td>
                <td><a href="mailto:'.$email.'">'.$email.'</a></td>
            </tr>';
            
        if($use_phone) {
            $html .= '
            <tr>
                <td class="key" width="100">'.PFformat::Lang('PHONE').'</td>
                <td>'.$params['phone'].'</td>
            </tr>';
        }
        if($use_mphone) {
            $html .= '
            <tr>
                <td class="key" width="100">'.PFformat::Lang('PHONE_MOBILE').'</td>
                <td>'.$params['mobile_phone'].'</td>
            </tr>';
        } 
        if($use_skype) {
            $html .= '
            <tr>
                <td class="key" width="150">'.PFformat::Lang('SKYPE').'</td>
                <td><a href="skype:'.$params['skype'].'">'.$params['skype'].'</a></td>
            </tr>';
        }   
        if($use_msn) {
            $html .= '
            <tr>
                <td class="key" width="150">'.PFformat::Lang('MSN').'</td>
                <td>'.$params['msn'].'</td>
            </tr>';
        }
        if($use_icq) {
            $html .= '
            <tr>
                <td class="key" width="150">'.PFformat::Lang('ICQ').'</td>
                <td>'.$params['icq'].'</td>
            </tr>';
          
        }
        $html .= '</table>';
        echo $html;
        unset($params,$html);
    }
}
unset($user,$db,$config);
?>