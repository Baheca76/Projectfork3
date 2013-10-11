<?php
/**
* $Id: profile_networks.php 837 2010-11-17 12:03:35Z eaxs $
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
$config = PFconfig::GetInstance();
$user   = PFuser::GetInstance();

// Get user id 
$id = (int) JRequest::getVar('id');

// Get params
$a_twitter    = (int) $config->Get('allow_twitter', 'profile');
$a_friendfeed = (int) $config->Get('allow_friendfeed', 'profile');
$a_linkedin   = (int) $config->Get('allow_linkedin', 'profile');
$a_facebook   = (int) $config->Get('allow_facebook', 'profile');
$a_youtube    = (int) $config->Get('allow_youtube', 'profile');

if($id) {
    $params = $user->LoadProfile($id);
    
    if(count($params) && ($a_twitter || $a_friendfeed || $a_linkedin || $a_facebook || $a_youtube)) {
        $html = '<table class="admintable">';
        
        if($a_twitter)    $a_twitter    = (array_key_exists('twitter', $params) ? trim($params['twitter']) : '');
        if($a_friendfeed) $a_friendfeed = (array_key_exists('friendfeed', $params) ? trim($params['friendfeed']) : '');
        if($a_linkedin)   $a_linkedin   = (array_key_exists('linkedin', $params) ? trim($params['linkedin']) : '');
        if($a_facebook)   $a_facebook   = (array_key_exists('facebook', $params) ? trim($params['facebook']) : '');
        if($a_youtube)    $a_youtube    = (array_key_exists('youtube', $params) ? trim($params['youtube']) : '');
        
        if($a_twitter) {
            $html .= '
            <tr class="twitter">
                <td class="key" width="100">'.PFformat::Lang('TWITTER').'</td>
                <td><a href="http://twitter.com/'.$params['twitter'].'" target="_blank">'.$params['twitter'].'</a></td>
            </tr>';
        }
        if($a_friendfeed) {
            $html .= '
            <tr class="twitter">
                <td class="key" width="100">'.PFformat::Lang('FRIENDFEED').'</td>
                <td><a href="http://friendfeed.com/'.$params['friendfeed'].'" target="_blank">'.$params['friendfeed'].'</a></td>
            </tr>';
        }
        if($a_linkedin) {
            $html .= '
            <tr class="twitter">
                <td class="key" width="100">'.PFformat::Lang('LINKEDIN').'</td>
                <td><a href="http://www.linkedin.com/'.$params['linkedin'].'" target="_blank">'.PFformat::Lang('LINKEDIN').'</a></td>
            </tr>';
        }
        if($a_facebook) {
            $html .= '
            <tr class="twitter">
                <td class="key" width="100">'.PFformat::Lang('FACEBOOK').'</td>
                <td><a href="http://facebook.com/'.$params['facebook'].'" target="_blank">'.$params['facebook'].'</a></td>
            </tr>';
        }
        if($a_youtube) {
            $html .= '
            <tr class="twitter">
                <td class="key" width="100">'.PFformat::Lang('YOUTUBE').'</td>
                <td><a href="http://youtube.com/'.$params['youtube'].'" target="_blank">'.$params['youtube'].'</a></td>
            </tr>';
        }
        $html .= '</table>';
        echo $html;
        unset($html);
    }
    unset($params);
}
unset($user,$config);
?>