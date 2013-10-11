<?php
/**
* $Id: controlpanel.init.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Controlpanel
* @copyright  Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license    http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License License as published by
* the Free Software Foundation, either version 3 of the License,
* or any later version.
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

$load = PFload::GetInstance();
$core = PFcore::GetInstance();

// Load the controller
require_once($load->Section('controlpanel.controller.php'));
$controller = new PFcontrolpanelController();

// Decide what to do
switch( $core->GetTask() )
{
	default:
		$controller->DisplayDefault();
		break;
}

// Unset objects
unset($load,$core);
?>