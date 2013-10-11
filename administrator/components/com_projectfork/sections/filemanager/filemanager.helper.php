<?php
/**
* $Id: filemanager.helper.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Filemanager
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

class PFfilemanagerHelper
{
    public function GetUploadLimit()
    {
        $max_upload   = (int)ini_get('upload_max_filesize');
        $max_post     = (int)ini_get('post_max_size');
        $memory_limit = (int)ini_get('memory_limit');
        
        return min($max_upload, $max_post, $memory_limit);
    }
}
?>