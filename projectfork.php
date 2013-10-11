<?php
/**
* $Id: projectfork.php 326 2009-04-25 21:18:23Z eaxs $
* @package   Project Fork
* @copyright Copyright (C) 2006-2008 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
*
* This file is part of ProjectFork.
*
* ProjectFork is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License,
* or any later version.
* 
* ProjectFork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Lesser General Public License for more details.
* 
* You should have received a copy of the GNU Lesser General Public License
* along with ProjectFork.  If not, see <http://www.gnu.org/licenses/lgpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the framework launcher
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'_core'.DS.'init.php');

// Create a new component instance
$pf_component = PFcomponent::GetInstance('com_projectfork', 'Projectfork', 'frontend');

// Run the component
$pf_component->Run();
?>