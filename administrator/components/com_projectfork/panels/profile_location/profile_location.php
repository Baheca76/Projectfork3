<?php
/**
* $Id: profile_location.php 837 2010-11-17 12:03:35Z eaxs $
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

// Get config settings
$use_street = (int) $config->Get('allow_street', 'profile');
$use_city   = (int) $config->Get('allow_city', 'profile');
$use_zip    = (int) $config->Get('allow_zip', 'profile');
	    
// Get user id
$id = (int) JRequest::getVar('id');

// Show nothing if everything is disabled
if(!$use_street && ! $use_city && !$use_zip) return false;

if($id) {
    $params = $user->LoadProfile($id);
    
    if(count($params)) {
        $html = '<table class="admintable">';
        
        $has_street = array_key_exists('street', $params);
        $has_city   = array_key_exists('city', $params);
        $has_zip    = array_key_exists('zip', $params);
        
        if($has_street) $has_street = trim($params['street']);
        if($has_city)   $has_city = trim($params['city']);
        if($has_zip)    $has_zip = trim($params['zip']);
        
        // Show nothing if no info is provided
        if(!$has_street && !$has_city && !$has_zip) return false;
        
        if($has_street && $has_city && $has_zip) {
            $html .= '
            <tr>
                <td class="key" width="150">Google Maps</td>
                <td><a href="http://maps.google.com/maps?q='.$params['street'].','.$params['city'].','.$params['zip'].'&oe=utf-8&ie=UTF8&split=0" target="_blank" title="'.PFformat::Lang('GOOGLE_TITLE').'">
                '.PFformat::Lang('MAPIT').'
                </a></td>
            </tr>';
        }
        if($use_street && $has_street) {
            $html .= '
            <tr>
                <td class="key" width="150">'.PFformat::Lang('STREET').'</td>
                <td>'.$params['street'].'</td>
            </tr>';
        }
        if($use_city && $has_city) {
            $html .= '
            <tr>
                <td class="key" width="150">'.PFformat::Lang('CITY').'</td>
                <td>'.$params['city'].'</td>
            </tr>';
        }
        if($use_zip && $has_zip) {
            $html .= '
            <tr>
                <td class="key" width="150">'.PFformat::Lang('ZIP').'</td>
                <td>'.$params['zip'].'</td>
            </tr>';
        }
        $html .= '</table>';
        echo $html;
        unset($html,$params);
    }
}
unset($user,$config);
?>