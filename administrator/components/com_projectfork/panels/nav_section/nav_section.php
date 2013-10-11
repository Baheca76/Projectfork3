<?php
/**
* $Id: nav_section.php 837 2010-11-17 12:03:35Z eaxs $
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

$core = PFcore::GetInstance();
$user = Pfuser::GetInstance();
$cfg  = PFconfig::GetInstance();
$com  = PFcomponent::GetInstance();
$load = PFload::GetInstance();
$uri  = JFactory::getURI();

$this_section = $core->GetSection();

$sections = $core->GetSections();
$hide     = $cfg->Get('hide_template');
$location = $com->Get('location');
$uid      = $user->GetId();

$html = '';
// Build the navigation
$html .= '
<div class="pf_navigation">
    <div id="submenu-box">
        <div class="t">
            <div class="t">
                <div class="t"></div>
            </div>
        </div>
        <div class="m">
            <ul id="submenu">';

foreach($sections AS $section)
{
	$access = $user->Access(NULL, $section->name);
   	$link   = PFformat::Link("section=$section->name");
    $active = ($this_section == $section->name) ? "active_section" : "section";
    
	if($access && $section->enabled == '1') {
		$html .= "<li id=\"pf_nav_".$section->name."\" class=\"$active\">
                     <a href=\"$link\" class=\"pf_nav\"><span>".PFformat::Lang($section->title)."</span></a>
                  </li>";
	}
}

$html .= "</ul><div class=\"clr\"></div></div><div class=\"b\"><div class=\"b\"><div class=\"b\"></div></div></div></div></div>";

echo $html;
unset($core,$user,$cfg,$com,$uri);
?>