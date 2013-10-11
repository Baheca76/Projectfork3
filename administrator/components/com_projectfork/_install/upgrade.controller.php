<?php
/**
* $Id: upgrade.controller.php 911 2011-07-20 14:02:11Z eaxs $
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

class PFupgradeController
{
    public function DisplaySplash()
    {
        $html  = new PFupgradeHTML();
        $class = new PFupgradeClass();
        $com   = PFcomponent::GetInstance();

        // Prep content - replace akeeba placeholder
        $akeeba_txt = ($class->HasAkeebaBackup() == false) ? PFLS_UPD_AKEEBA_NOT_FOUND : PFLS_UPD_AKEEBA_FOUND;
        $akeeba_txt = str_replace('{logo}', $html->RenderLogo('akeeba_backup.png', 'style="float:left"'), $akeeba_txt);
        $akeeba_txt = str_replace('{backup_link}', $com->Get('url_root').'administrator/index.php?option=com_akeeba', $akeeba_txt);
        $content    = str_replace('{akeeba}', $akeeba_txt, PFLS_SPLASH_TXT);

        $header = $html->RenderLogo();
        $body   = PFLS_UPGRADE.$content."<br /><br />"
                . "<input class=\"button\" type=\"button\" onclick=\"start_upgrade();\" value=\"".PFLS_START_UPGRADE."\" />";
        $footer = $html->RenderFooter();

        $html->DisplayTemplate($header, $body, $footer);
    }

    public function DisplayProgress()
    {
        $html = new PFupgradeHTML();

        $header = $html->RenderLogo();
        $body = PFLS_UPGRADE
              . PFLS_UPGRADE_TXT."<br /><br />"
              . $html->RenderProgressBar()
              . $html->RenderUpgradeElements();

        $footer = $html->RenderFooter();
        $html->DisplayTemplate($header, $body, $footer);
    }

    public function DisplayError($errors)
    {
        $html = new PFupgradeHTML();

        $header = $html->RenderLogo();
        $body = PFLS_ERROR.PFLS_ERROR_TXT."<ul>";
        foreach($errors AS $error)
        {
            $body .= "<li>$error</li>";
        }
        $body .= "</ul>";
        $body .= $html->RenderUpgradeElements();
        $footer = $html->RenderFooter();
        $html->DisplayTemplate($header, $body, $footer, 1);
    }

    public function OptimizeOldTables()
    {
        $class = new PFupgradeClass();

        if(!$class->OptimizeOldTables()) {
            $errors = $class->GetErrors();
            JRequest::setVar('opt_old', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            JRequest::setVar('opt_old', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function AddFields()
    {
        $class = new PFupgradeClass();

        if(!$class->RunSQL('upgrade.addfields.sql')) {
            $errors = $class->GetErrors();
            JRequest::setVar('add_fields', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            JRequest::setVar('add_fields', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function DeleteFields()
    {
        $class = new PFupgradeClass();

        if(!$class->RunSQL('upgrade.delfields.sql')) {
            $errors = $class->GetErrors();
            JRequest::setVar('del_fields', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            JRequest::setVar('del_fields', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function RenameFields()
    {
        $class = new PFupgradeClass();

        if(!$class->RunSQL('upgrade.renfields.sql')) {
            $errors = $class->GetErrors();
            JRequest::setVar('ren_fields', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            JRequest::setVar('ren_fields', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function AddIndexes()
    {
        $class = new PFupgradeClass();

        if(!$class->RunSQL('upgrade.indexes.sql')) {
            $errors = $class->GetErrors();
            JRequest::setVar('indexes', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            JRequest::setVar('indexes', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function MigrateGroups()
    {
        $class = new PFupgradeClass();

        if(!$class->MigrateGroups()) {
            $errors = $class->GetErrors();
            JRequest::setVar('migrate_groups', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            JRequest::setVar('migrate_groups', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function UpdateExtensions()
    {
        $class = new PFupgradeClass();

        if(!$class->RunSQL('upgrade.extensions.sql')) {
            $errors = $class->GetErrors();
            JRequest::setVar('update_ext', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            $sqls = array('install.panels.sql', 'install.processes.sql',
                          'install.sections.sql', 'install.languages.sql',
                          'install.themes.sql');

            foreach($sqls AS $sql)
            {
                if(!$class->RunSQL($sql)) {
                    $errors = $class->GetErrors();
                    JRequest::setVar('update_ext', -1, 'post');
                    $this->DisplayError($errors);
                    return false;
                }
            }

            JRequest::setVar('update_ext', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function UpdateProfiles()
    {
        $class = new PFupgradeClass();

        if(!$class->UpdateProfiles()) {
            $errors = $class->GetErrors();
            JRequest::setVar('profiles', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            JRequest::setVar('profiles', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function UpdateTables()
    {
        $class = new PFupgradeClass();

        if(!$class->RunSQL('upgrade.tables.sql')) {
            $errors = $class->GetErrors();
            JRequest::setVar('tables', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            JRequest::setVar('tables', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function UpdateConfig()
    {
        $class = new PFupgradeClass();
        $db = JFactory::getDBO();

        if(!$class->RunSQL('upgrade.config.sql')) {
            $errors = $class->GetErrors();
            JRequest::setVar('config', -1, 'post');
            $this->DisplayError($errors);
        }
        else {
            if(defined('PF_VERSION_NUM')) {
                $query = "INSERT INTO #__pf_settings VALUES(NULL, 'version_num', '".PF_VERSION_NUM."', 'system')";
                       $db->setQuery($query);
                       $db->query();

                $query = "UPDATE #__pf_settings SET `content` = '".PF_VERSION_NUM."'"
                       . "\n WHERE `parameter` = 'version' AND scope = 'system'";
                       $db->setQuery($query);
                       $db->query();
            }
            if(defined('PF_VERSION_STATE')) {
                $query = "INSERT INTO #__pf_settings VALUES(NULL, 'version_state', '".PF_VERSION_STATE."', 'system')";
                       $db->setQuery($query);
                       $db->query();
            }

            $this->Finish();

            JRequest::setVar('config', 1, 'post');
            $this->DisplayProgress();
        }
    }

    public function Finish()
    {
        $db = JFactory::getDBO();

        $class    = new PFupgradeClass();
        $jversion = new JVersion();

        $class->OptimizeNewTables();

        $query = "INSERT INTO #__pf_settings VALUES(NULL, 'upgraded_2100', '1', 'system')";
               $db->setQuery($query);
               $db->query();

        if($jversion->RELEASE == '1.5') {
            global $mainframe;
            $mainframe->redirect('index.php?option=com_projectfork', 'Welcome to Projectfork 3.0!');
		    $mainframe->close();
        }
        else {
            $app = JFactory::getApplication();
			$app->redirect('index.php?option=com_projectfork', 'Welcome to Projectfork 3.0!');
        }
    }
}
?>