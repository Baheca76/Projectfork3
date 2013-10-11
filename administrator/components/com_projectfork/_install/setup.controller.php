<?php
/**
* $Id: setup.controller.php 911 2011-07-20 14:02:11Z eaxs $
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

class PFsetupController
{
    public function DisplaySplash()
    {
        $html = new PFsetupHTML();

        $header = $html->RenderLogo();
        $body = PFLS_INSTALLATION
              . PFLS_SPLASH_TXT."<br /><br />"
              . "<input class=\"button\" type=\"button\" onclick=\"start_install(1);\" value=\"".PFLS_INSTALL_WITH_EXAMPLE."\" />"
              . "&nbsp;&nbsp;"
              . "<input class=\"button\" type=\"button\" onclick=\"start_install(0);\" value=\"".PFLS_INSTALL_WITHOUT_EXAMPLE."\" />";
        $footer = $html->RenderFooter();
        $html->DisplayTemplate($header, $body, $footer);
    }

    public function DisplayInstall()
    {
        $html = new PFsetupHTML();

        $header = $html->RenderLogo();
        $body = PFLS_INSTALLATION
              . PFLS_INSTALL_TXT."<br /><br />"
              . $html->RenderProgressBar()
              . $html->RenderInstallElements();

        $footer = $html->RenderFooter();
        $html->DisplayTemplate($header, $body, $footer);
    }

    public function DisplayError($e)
    {
        $html = new PFsetupHTML();

        $header = $html->RenderLogo();
        $body = PFLS_ERROR.PFLS_ERROR_TXT."<ul>";
        foreach($e AS $err)
        {
            $body .= "<li>$err</li>";
        }
        $body .= "</ul>";
        $body .= $html->RenderInstallElements();
        $footer = $html->RenderFooter();
        $html->DisplayTemplate($header, $body, $footer, 1);
    }

    public function RunSQL($setup_task)
    {
         $sql_file = 'install.'.str_replace('sql_', '', $setup_task).'.sql';
         $class    = new PFsetupClass();
         $success  = $class->RunSQL($sql_file);

         if(!$success) {
             $e = $class->GetErrors();
             $this->DisplayError($e);
         }
         else {
             JRequest::setVar($setup_task, 1, 'post');
             $this->DisplayInstall();
         }
    }

    public function Finish()
    {
        // run custom install sql
        $class    = new PFsetupClass();
        $jversion = new JVersion();
        $class->RunSQL('install.custom.sql');

        $j_db = &JFactory::getDBO();

        if(defined('PF_VERSION_NUM')) {
            $query = "INSERT INTO #__pf_settings VALUES(NULL, 'version_num', '".PF_VERSION_NUM."', 'system')";
                   $j_db->setQuery($query);
                   $j_db->query();
        }

        if(defined('PF_VERSION_STATE')) {
            $query = "INSERT INTO #__pf_settings VALUES(NULL, 'version_state', '".PF_VERSION_STATE."', 'system')";
                   $j_db->setQuery($query);
                   $j_db->query();
        }

        $query = "INSERT INTO #__pf_settings VALUES(NULL, 'secret_number', '".rand(99,999)."', 'system')";
               $j_db->setQuery($query);
               $j_db->query();

        $query = "INSERT INTO #__pf_settings VALUES(NULL, 'installed', '1', 'system')";
               $j_db->setQuery($query);
               $j_db->query();

        if($jversion->RELEASE == '1.5') {
            global $mainframe;
            $mainframe->redirect('index.php?option=com_projectfork', 'Welcome to Projectfork!');
		    $mainframe->close();
        }
        else {
            // Update global groups
            $class->J16AclUpdate();
            // Redirect
            $app = JFactory::getApplication();
			$app->redirect('index.php?option=com_projectfork', 'Welcome to Projectfork!');
        }
    }
}
?>