<?php
/**
* $Id: cp_news.php 837 2010-11-17 12:03:35Z eaxs $
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

$cfg  = PFconfig::GetInstance();
$lang = PFlanguage::GetInstance();

// Load panel params
$feed_url = $cfg->Get('feed_url', 'cp_news');
$limit    = (int) $cfg->Get('total', 'cp_news');

if(!$feed_url) $feed_url = "http://feeds.feedburner.com/joomlapraise";
if(!$limit)    $limit = 4;

$options   = array();

$options['rssUrl'] 		= $feed_url;
$options['cache_time']  = 15 * 60;

$rss =& JFactory::getXMLparser('RSS', $options);

if($rss != false) {
	$items = $rss->get_items();
	$items = array_slice($items, 0, $limit);
	
	echo "<ul class=\"cp_news\">";
	foreach ($items AS $row)
	{
		JFilterOutput::objectHTMLSafe($row);
		echo "<li><a href='".$row->get_link()."' target='_blank'>".$row->get_title()."</a></li>";
	}
	echo "</ul>";
	if($cfg->Get('read_more', 'cp_news')) {
		echo "<a href='".$rss->get_link()."' class=\"pf_button\" target='_blank'>".$lang->_('PFL_READMORE')."</a>";
	}
}
unset($cfg,$lang,$rss);
?>