<?php
/**
* $Id: upgrade.lang.php 837 2010-11-17 12:03:35Z eaxs $
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

define('PFLS_UPD_AKEEBA_NOT_FOUND', '{logo} <span style="color:red;font-weight:bold">It appears that Akeeba Backup 3.1 or higher is not installed on your site</span>. For your convenience, <a href="http://www.akeebabackup.com/" target="_blank">download and use Akeeba Backup to manage your backups</a>.');
define('PFLS_UPD_AKEEBA_FOUND', '{logo} <span style="color:#70AF2C;font-weight:bold">You have Akeeba Backup 3.1 or higher installed</span>. <a href="{backup_link}" target="_blank">Use it now to backup your site</a>!');
define('PFLS_UPD_EXT', 'Updating extensions');
define('PFLS_UPD_TABLES', 'Updating tables');
define('PFLS_UPD_CFG', 'Updating config');
define('PFLS_UPD_PROFILES', 'Updating profiles');
define('PFLS_ADD_FIELDS', 'Adding fields');
define('PFLS_DEL_FIELDS', 'Deleting fields');
define('PFLS_REN_FIELDS', 'Renaming fields');
define('PFLS_ADD_INDEXES', 'Adding Indexes');
define('PFLS_MIG_GROUPS', 'Migrating groups');
define('PFLS_ERROR', '<h3>An error has occured!</h3>');
define('PFLS_ERROR_TXT', "Please copy the error message(s) below and post them in the official forum at <a href='http://forum.projectfork.net' target='_blank'>forum.projectfork.net</a>.");
define('PFLS_PENDING', 'Pending');
define('PFLS_UPGRADE_TXT', 'Upgrade in progress. Please do not navigate away from this page!');
define('PFLS_DONE', 'Done');
define('PFLS_FAILED', 'Failed');
define('PFLS_OPT', 'Optimizing db');
define('PFLS_START_UPGRADE', 'I\'ve backed up my database and files, let\'s go!');
define("PFLS_UPGRADE", "<h3>Upgrade from 2.1 to 3.0</h3>");
define("PFLS_SPLASH_TXT", "You are about to upgrade from Projectfork 2.1 to 3.0. 
                           <span style=\"color:#70AF2C;font-weight:bold\">Please read the following information carefully before you proceed!</span><br /><br/>
                           The upgrade includes several database and file structure changes. Depending on the amount of data you have in your system (such as projects, groups, etc.), it may take a while to perform.<br/><br/>
                           <strong>Please make sure you have a full backup of your Projectfork database and files before you continue!</strong><br /><br /><hr /><p>{akeeba}</p><p> Extensions and mods you may have already installed for 2.1 will no longer be functional as they are not compatible with 3.0.
                           If you rely on third-party extensions, please check the corresponding author/vendor for available updates before continuing. You can install/update those extensions after this procedure.</p><hr /><br/>
                           When you are ready to begin, click the button below. To cancel or revert back to 2.1, you'll have to copy over the original files from your current 2.1 version or use the Akeeba Backup Manager.");
?>