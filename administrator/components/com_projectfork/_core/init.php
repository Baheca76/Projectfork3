<?php
/**
* $Id: init.php 838 2010-11-25 20:49:32Z eaxs $
* @package       Projectfork
* @subpackage    Framework
* @copyright     Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license       http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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

if(!class_exists('PFcomponent')) 
{
    /**
     * @package       Projectfork
     * @subpackage    Framework
     **/
	class PFcomponent
	{
	    /**
	     * Component name
	     * 
	     * @var    string
	     **/
		private $name;
		
		/**
	     * Component title
	     *
	     * @var    string
	     **/
		private $title;
		
		/**
	     * User location (frontend/backend)
	     *
	     * @var    string
	     **/
		private $location;
		
		/**
	     * Joomla root directory path
	     *
	     * @var    string
	     **/
		private $path_root;
		
		/**
	     * Component frontend directory path
	     *
	     * @var    string
	     **/
		private $path_frontend;
		
		/**
	     * Component backend directory path
	     *
	     * @var    string
	     **/
		private $path_backend;
		
		/**
	     * Joomla root URL path
	     *
	     * @var    string
	     **/
		private $url_root;
		
		/**
	     * Component frontend URL path
	     *
	     * @var    string
	     **/
		private $url_frontend;
		
		/**
	     * Component backend URL path
	     *
	     * @var    string
	     **/
		private $url_backend;
		
		/**
	     * Framework options
	     *
	     * @var    array
	     **/
		private $options;
		
		/**
	     * Joomla profiler instance
	     *
	     * @var    object
	     **/
		private $profiler;
		
		/**
	     * Framework error messages
	     *
	     * @var    array
	     **/
		private $errors;
		
		/**
		 * Constructor - Use PFcomponent::GetInstance() instead
		 * 
		 * @param    string    $name        Component name
		 * @param    string    $title       Component title
		 * @param    string    $location    User location (frontend/backend)
		 * @param    array     $options     Framework options
		 **/         		
		protected function __construct($name = NULL, $title = NULL, $location = NULL, $options = array())
		{
			$this->name     = $name;
			$this->title    = $title;
			$this->location = $location;
			$this->options  = $options;
			
			// Setup paths
			$this->path_root     = JPATH_ROOT;
			$this->path_frontend = JPATH_ROOT.DS.'components'.DS.$this->name;
			$this->path_backend  = JPATH_ADMINISTRATOR.DS.'components'.DS.$this->name;
			
			// Setup URLs
			$juri = &JFactory::getURI();
			$this->url_root     = str_replace('administrator/','',$juri->base(false));
			$this->url_frontend = $this->url_root.'components/'.$this->name;
		    $this->url_backend  = $this->url_root.'administrator/components/'.$this->name;
	    	
		    
		    // Setup profiler
		    jimport('joomla.error.profiler');
		    $this->profiler = new JProfiler();
		    
		    // Setup errors
		    $this->errors = array();

		    unset($juri);
		}
		
		/**
		 * Sets the value of a private class variable ($this->$var = $value)
		 * 
		 * @param    string    $var      The variable name
		 * @param    mixed     $value    The variable value                             		
		 **/
		public function Set($var, $value = NULL)
		{
            $vars   = get_class_vars(__CLASS__);
            $exists = array_key_exists($var, $vars);
            
            if($exists) $this->$var = $value;
            
            return $exists;
        }
		
		/**
		 * Returns the value of a private class variable
		 * 
		 * @param    string    $var    The variable name                             		
		 **/
		public function Get($var)
		{
            $vars   = get_class_vars(__CLASS__);
            $exists = array_key_exists($var, $vars);
            
            if($exists) return $this->$var;
            
            return false;
        }

        /**
		 * Returns an instance of the class
		 * 
		 * @param    string    $name        Component name
		 * @param    string    $title       Component title
		 * @param    string    $location    User location (frontend/backend)
		 * @param    array     $options     Framework options                                		
		 **/
        public function GetInstance($name = NULL, $title = NULL, $location = NULL, $options = array())
		{
            static $self = null;

            if(is_object($self)) return $self;
            $self = new PFcomponent($name, $title, $location, $options = array());
            return $self;
        }
        
        /**
		 * Loads the framework files
		 *
		 * @return    boolean    True on success, otherwise False
		 **/
        public function Load()
        {
            // Check for setup errors
            if(!$this->Check()) {
                $this->PrintErrors();
                return false;
            }
            
            // Component cold-start
            if(!defined('PF_INSTANCE')) {
                define('PF_INSTANCE', 1);
                
                // Include version file
                require_once($this->path_backend.DS.'version.php');
                
                // Include installer class
                require_once($this->path_backend.DS.'_core'.DS.'lib'.DS.'installer.php');
                
                // Check for valid install
                $ins = PFinstaller::GetInstance();
                
                if(!$ins->CheckInstalled()) {
                    if($this->location == 'frontend') {
                        $this->AddError($this->title." must be installed from the backend of your site!");
                        $this->PrintErrors();
                        return false;
                    }
                    $ins->InstallComponent();
                    return false;
                }
                
                // Check for upgrade
                if(!$ins->CheckUpgraded()) {
                    if($this->location == 'frontend') {
                        $this->AddError($this->title." must be upgraded from the backend of your site!");
                        $this->PrintErrors();
                        return false;
                    }
                    $ins->UpgradeComponent();
                    return false;
                }
                
                // Include Joomla mailer
                if(!class_exists('JMail')) jimport('joomla.mail.mail');

                // Load Joomla tooltips
	            if(!defined('PF_TOOLTIPS')) {
	        	    define('PF_TOOLTIPS', 1);
	        	    JHTML::_('behavior.tooltip');
	            }
	        
                // Include the debugging class
                require_once($this->path_backend.DS.'_core'.DS.'lib'.DS.'debug.php');
                $debug = PFdebug::GetInstance();
                $debug->_('n','PFcomponent::Load - Logging core...');
                
                // Include the database class
                require_once($this->path_backend.DS.'_core'.DS.'lib'.DS.'database.php');
                // Include the config class
                require_once($this->path_backend.DS.'_core'.DS.'lib'.DS.'config.php');
                // Include the mod class
                require_once($this->path_backend.DS.'_core'.DS.'lib'.DS.'mod.php');
                // Include the loader class
                require_once($this->path_backend.DS.'_core'.DS.'lib'.DS.'loader.php');
                $load = PFload::GetInstance();
                
                // Include the core class
                require_once( $load->Core('core.php') );
                // Include the user class
                require_once( $load->CoreLib('user.php') );
                // Include the language class
                require_once( $load->CoreLib('language.php') );
                // Include the utilities class
                require_once( $load->CoreLib('utilities.php') );
                
                $debug->_('n','PFcomponent::Load - Core is now loaded!');
            }
            
            unset($load, $debug, $ins);
            return true;
        }
        
        /**
		 * Runs the framework and the component                           		
		 **/
        public function Run()
        {
            // Component cold-start
            if(!defined('PF_INSTANCE')) {
                if(!$this->Load()) return false;
            }
            
            // Load main language file
            $lang = PFlanguage::GetInstance();
            $lang->Load($this->name, 'main');
            
            // Load section language files
            $core = PFcore::GetInstance();
            $sections = $core->GetSections();
            
            foreach($sections AS $section)
            {
                $lang->Load($section->name, 'section');
            }
            unset($lang);
            
            // Switch to SSL if enabled
            $ssl    = false;
            $config = PFconfig::GetInstance();
            $fe_ssl = (int) $config->Get('use_ssl_fe');
            $be_ssl = (int) $config->Get('use_ssl_be');
            
            if($this->location == 'frontend' && $fe_ssl == 1) $ssl = true;
            if($this->location == 'backend'  && $be_ssl == 1) $ssl = true;
            
            if($ssl) {
                $this->url_root     = str_replace('http://','https://',$this->url_root);
			    $this->url_frontend = str_replace('http://','https://',$this->url_frontend);
		        $this->url_backend  = str_replace('http://','https://',$this->url_backend);
            }
            
            // Add core JS to document header
            $load = PFload::GetInstance();
            $load->CoreJS('core.js');
            
            // Load startup processes
            PFprocess::Event('system_startup');
            
            // Load the compat file
            require_once($load->Core('compat.php'));
            
            // Include theme header file
            $load->Set404(false);
            $theme_header = $load->Theme('header.php');
            $load->Set404(true);
            
            if($theme_header) require_once($theme_header);
            
            // Force hide Joomla template?
		    if($this->Get('location') == 'frontend' && $config->Get('hide_template') == '1') {
			    JRequest::setVar('tmpl', 'component');
		    }
		    unset($config);
		
            // Check for render method
            $render = JRequest::getVar('render');
            
            // Ajax panel
            if($render == 'panel_ajax') {
                JRequest::setVar('tmpl', 'component');
                $pname = JRequest::getVar('render_name');
                $style = JRequest::getVar('render_style', 'panel_default');
                if($pname) echo PFpanel::Render($pname, $style);
                return true;
            }

            // Pre-Render Section
            $section = PFsection::GetInstance();
            $section->PreRender();
            unset($section);
            
            // Ajax section
            if($render == 'section_ajax') {
                JRequest::setVar('tmpl', 'component');
                require_once($load->Theme('index_ajax.php'));
                return true;
            }
            
            // Load after_section processes
            PFprocess::Event('after_section');
            
            // Redirect if set
            if($core->HasRedirect()) $core->Redirect();
            unset($core);
            
            // Include theme
            require_once($load->Theme('index.php'));
            unset($load);
        }
        
        /**
		 * Checks for framework setup errors
		 *
		 * @return    boolean    True if there are errors, otherwise false
		 **/
        private function Check()
        {
            $isvalid = true;
            
            if(!$this->name) {
                $isvalid = false;
                $this->AddError('Component Error: No name specified');
            }
            if(!$this->title) {
                $isvalid = false;
                $this->AddError('Component Error: No title specified');
            }
            if(!$this->location) {
                $isvalid = false;
                $this->AddError('Component Error: No location specified');
            }
            
            return $isvalid;
        }
        
        /**
		 * Adds an error message
		 * 
		 * @param    string    $msg    The message to add                               		
		 **/
        private function AddError($msg)
        {
            $this->errors[] = $msg;
        }
        
        /**
		 * Prints out all framework setup errors
		 **/
        private function PrintErrors()
        {
            $html = '<ul>';
            
            foreach($this->errors AS $e)
            {
                $html .= '<li>'.$e.'</li>';
            }
            
            $html .= '</ul>';
            
            echo $html;
        }
	}
}
?>