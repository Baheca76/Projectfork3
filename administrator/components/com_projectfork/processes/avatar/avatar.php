<?php
/**
* $Id: avatar.php 863 2011-03-21 00:00:29Z angek $
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

if(!class_exists('PFavatar')) {
    class PFavatar
    {
        public function Display($id, $link = true)
        {
			$config = PFconfig::GetInstance();
			$showtips =  (int) $config->Get('tooltip_help');
			unset($config);
			
            static $display = NULL;
            
            $user = PFuser::GetInstance();
            
            $tooltip     = "";
            $tt_class    = "";

            if(is_null($display)) {
                $config  = PFconfig::GetInstance();
                $display = (int) $config->Get('display_avatar');
                unset($config);
            }
            
            $id_parts = explode(':', $id);
            $pcount   = count($id_parts);
            
            $id = (int) $id_parts[0];
            
            // Show name only?
            if(!$display && $link && $pcount == 2) {
                return '<a href="'.PFformat::Link("section=profile&task=display_details&id=$id").'">'
                       .htmlspecialchars($id_parts[1]).'</a>';
            }
            
            // Display nothing?
            if(!$display) return "";
            
            if($pcount == 2) {
				if ($showtips) {
					$tooltip = ' title="::'.htmlspecialchars($id_parts[1]).'"';
					$tt_class = 'class="hasTip"';
				}
            }

            // Load the image
            $load = PFload::GetInstance();
            $img  = $load->Avatar($id);
            unset($load);
            
            if($link && $user->Access('display_details', 'profile')) {
                $img = '<a href="'.PFformat::Link("section=profile&task=display_details&id=$id").'" '.$tt_class.$tooltip.'>'
                     . $img
                     . '</a>';
            }
            
            unset($user);
            return $img;
        }
    }
}
?>