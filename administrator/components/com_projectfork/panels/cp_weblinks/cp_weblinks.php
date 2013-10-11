<?php
/**
* $Id: cp_weblinks.php 837 2010-11-17 12:03:35Z eaxs $
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

$config = PFconfig::GetInstance();

$pf_links = (int) $config->Get('pflinks', 'cp_weblinks');
$c_links  = $config->Get('clinks', 'cp_weblinks');

$html = '';

if($pf_links) {
    $html .= '<ul class="cp_weblinks">
    <li class="pfweb_site"><a href="http://www.projectfork.net" target="_blank"><span>'.PFformat::Lang('PFWEBSITE').'</span></a></li>
    <li class="pfweb_jed"><a href="http://extensions.joomla.org/extensions/clients-a-communities/project-a-task-management/1389" target="_blank"><span>'.PFformat::Lang('PFJED').'</span></a></li>
    <li class="pfweb_twitter"><a href="http://twitter.com/projectfork" target="_blank"><span>'.PFformat::Lang('PFTWITTER').'</span></a></li>
    <li class="pfweb_fb"><a href="http://www.facebook.com/pages/Projectfork/147185965309518" target="_blank"><span>'.PFformat::Lang('PFFACEBOOK').'</span></a></li>
    </ul>';
}
if($c_links) {
    $c_links = explode("\n", $c_links);

    if($pf_links) $html .= '<hr class="separator"/>';
    $html .= '<ul class="cp_weblinks">';
    
    foreach($c_links AS $str)
    {
        $str   = explode('|', $str);
        $count = count($str);

        if($count == 1) {
            $html .= '<li class="cp_weblink"><a href="'.urlencode($str[0]).'" target="_blank"><span>'.htmlspecialchars($str[0], ENT_QUOTES).'</span></a></li>';
        }
        if($count == 2) {
            $html .= '<li class="cp_weblink"><a href="'.trim($str[1]).'" target="_blank"><span>'.htmlspecialchars(trim($str[0]), ENT_QUOTES).'</span></a></li>';
        }
    }
    
    $html .= '</ul>';
}
echo $html;
?>