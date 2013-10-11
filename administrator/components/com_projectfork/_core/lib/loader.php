<?php
/**
* $Id: loader.php 917 2011-09-19 17:08:49Z eaxs $
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

/**
 * @package       Projectfork
 * @subpackage    Framework
 **/
class PFload
{
    /**
	 * Mod object
	 *
	 * @var    Object
	 **/
	private $mod;

	/**
	 * Debug object
	 *
	 * @var    Object
	 **/
	private $debug;

	/**
	 * Component object
	 *
	 * @var    Object
	 **/
	private $com;

	/**
	 * Theme name
	 *
	 * @var    String
	 **/
	private $theme;

	/**
	 * Path to the error file
	 *
	 * @var    String
	 **/
	private $error_file;

	/**
	 * Sets whether to return an error file or bool true/false when a requested file is missing
	 *
	 * @var    Boolean
	 **/
	private $return_404;

	/**
	 * Sets the default section
	 *
	 * @var    String
	 **/
	private $default_section;

	/**
	 * Constructor
	 *
	 **/
	protected function __construct()
	{
		$this->mod   			= PFmod::GetInstance();
		$this->debug 			= PFdebug::GetInstance();
		$this->com   			= PFcomponent::GetInstance();
        $this->theme 			= $this->GetTheme();

		$this->error_file 		= $this->com->Get('path_backend').DS.'_core'.DS.'output'.DS.'404.php';
		$this->return_404 		= true;
		$this->default_section 	= $this->GetDefaultSection();
	}

	/**
	 * Finds and returns the name of the default theme
	 *
	 * @return    string    The theme name
	 **/
	private function GetTheme()
    {
        $db = JFactory::getDBO();

        $query = "SELECT name FROM #__pf_themes"
               . "\n WHERE enabled = '1'"
               . "\n AND is_default = '1'";
               $db->setQuery($query);
               $name = $db->loadResult();

        if(!$name) $name = 'default';

        return $name;
    }

	/**
	 * Finds and returns the name of the default section
	 *
	 * @return    string    The theme name
	 **/
	private function GetDefaultSection()
	{
		$db = JFactory::getDBO();

		$query = "SELECT name FROM #__pf_sections"
				. "\n WHERE enabled = '1'"
				. "\n AND is_default = '1'";
				$db->setQuery($query);
				$name = $db->loadResult();

        if(!$name) $name = 'controlpanel';
        
		return $name;
	}

	/**
	 * Returns an instance of the class
	 *
	 * @return     object     $self    Class object
	 **/
	public function GetInstance()
	{
        static $self;

        if(is_object($self)) return $self;

        $self = new PFload();

        return $self;
    }

    /**
	 * Sets whether to return a 404 error file or bool true/false when a requested file is missing
	 *
	 * @param    boolean    $state    True enables the 404 file. False will return bool true/false
	 **/
    public function Set404($state)
    {
        $this->return_404 = $state;
    }

    /**
	 * Returns the absolute path to a file. Also checks for modded files or theme overrides
	 *
	 * @param    string     $file    The name of the file to find. Example: tasks.controller.php
	 * @param    string     $path    The path to the file. Example: sections.tasks
	 * @param    boolean    $search_theme    If true, search active theme for override
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function FilePath($file, $path, $search_theme = false)
    {
        $base  = $this->com->Get('path_backend');
        $path  = str_replace('.',DS,$path);
        $file = $path.DS.$file;

        if($this->mod->Exists($file)) {
            $file = $this->mod->GetPointer();
            $this->debug->_('n','PFload::FilePath - Found modded file "'.$file.'"');
        }
        else {
            // Search replacement file in theme
            if($search_theme) {
                $tmp_file = $base.DS.'themes'.DS.$this->theme.DS.'html_custom'.DS.$file;
                if(file_exists($tmp_file)) {
                    $this->debug->_('n','PFload::FilePath - Found a theme override for file "'.$file.'"');
                    $file = 'themes'.DS.$this->theme.DS.'html_custom'.DS.$file;
                }
                else {
                    $this->debug->_('n','PFload::FilePath - Searching original file "'.$file.'"');
                }
            }
            else {
                $this->debug->_('n','PFload::FilePath - Searching original file "'.$file.'"');
            }
        }

        $file = $base.DS.$file;
        if(file_exists($file)) return $file;

        $this->debug->_('e','PFload::FilePath - File "'.$file.'" not found');
        if($this->return_404) return $this->error_file;
        return false;
    }

    /**
	 * Returns the URL path to a file. Also checks for modded files or theme overrides
	 *
	 * @param    string     $file    The name of the file to find. Example: setup.css
	 * @param    string     $path    The path to the file. Example: _core.css
	 * @param    boolean    $search_theme    If true, search active theme for override
	 * @return   mixed      Returns the full URL to the requested file.
	 **/
    public function URLpath($file, $path, $jbase = false, $search_theme = false)
    {
        if($jbase == true) {
            $base_path = $this->com->Get('path_root');
            $base_url  = $this->com->Get('url_root');

        }
        else {
            $base_path = $this->com->Get('path_backend');
            $base_url  = $this->com->Get('url_backend');
        }

        $url  = str_replace('.','/',$path);
        $path = str_replace('.',DS,$path);

        $mfile = $path.DS.$file;

        if($this->mod->Exists($mfile) && $jbase == false) {
            $file = $this->mod->GetPointer();
            $file = $base_url.'/'.str_replace(DS,'/',$mfile);
            $this->debug->_('n','PFload::URLpath - Found modded file "'.$mfile.'"');
            return $file;
        }
        else {
            if($search_theme) {
                $tmp_file = $base_path.DS.'themes'.DS.$this->theme.DS.'html_custom'.DS.$mfile;
                if(file_exists($tmp_file)) {
                    $this->debug->_('n','PFload::URLpath - Found theme override for file "'.$mfile.'"');
                    $file = 'themes/'.$this->theme.'/html_custom/'.str_replace(DS,'/',$mfile);

                    return $base_url.'/'.$file;
                }
                else {
                    $this->debug->_('n','PFload::URLpath - Returning original file "'.$mfile.'"');
                }
            }
        }

        $file = $base_url.'/'.$url.'/'.$file;
        return $file;
    }

	/**
	 * Returns a file located in the "_core" folder
	 *
	 * @param    string     $file    The name of the file to find
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
	public function Core($file)
    {
        return $this->FilePath($file, '_core');
    }

    /**
	 * Returns a file located in the "_core/lib" folder
	 *
	 * @param    string     $file    The name of the file to find
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function CoreLib($file)
    {
   	    return $this->FilePath($file, '_core.lib');
    }

    /**
	 * Returns a file located in the "_core/output" folder
	 *
	 * @param    string     $file    The name of the file to find
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function CoreOutput($file)
    {
    	return $this->FilePath($file, '_core.output');
    }

    /**
	 * Returns an image located in the "_core/img" folder
	 *
	 * @param    string     $file    The name of the file to find
	 * @param    string     $alt     The alt-attribute text
	 * @return   string     Returns an HTML img tag
	 **/
    public function CoreImg($file, $alt = '')
    {
    	$alt = htmlspecialchars($alt, ENT_QUOTES);
    	$img = $this->URLpath($file, '_core.images');
   	    return '<img src="'.$img.'" alt="'.$alt.'" border="0" />';
    }

    /**
	 * Adds a JS file to the document header located in the "_core/js" folder
	 *
	 * @param    string     $file    The name of the file to find
	 **/
    public function CoreJS($file)
    {
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, '_core.js');
    	$doc->addScript($path);
    	unset($doc);
    }

    /**
	 * Adds a CSS file to the document header located in the "_core/css" folder
	 *
	 * @param    string     $file    The name of the file to find
	 **/
    public function CoreCSS($file)
    {
        $doc = JFactory::getDocument();
        $path = $this->URLpath($file, '_core.css');
    	$doc->addStyleSheet($path);
    	unset($doc);
    }

    /**
	 * Returns a file located in the "sections/$section" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $section    The section name
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function Section($file, $section = '' )
    {
    	if(!$section) $section = JRequest::getVar('section', $this->default_section);
    	return $this->FilePath($file, 'sections.'.$section);
    }

    /**
	 * Returns a file located in the "sections/$section/output" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $section    The section name
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function SectionOutput($file, $section = '')
    {
    	if(!$section) $section = JRequest::getWord('section', $this->default_section);
    	return $this->FilePath($file, 'sections.'.$section.'.output', true);
    }

    /**
	 * Returns an image located in the "sections/$section/images" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $section    The section name
	 * @param    string     $alt        The alt-attribute text
	 * @return   string     Returns an HTML img tag
	 **/
    public function SectionImg($file, $section = '', $alt = '')
    {
        if(!$section) $section = JRequest::getWord('section', $this->default_section);
    	$alt = htmlspecialchars($alt, ENT_QUOTES);
    	$img = $this->URLpath($file, 'sections.'.$section.'.images', false, true);
   	    return '<img src="'.$img.'" alt="'.$alt.'" border="0" />';
    }

    /**
	 * Adds a JS file to the document header located in the "sections/$section/js" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $section    The section name
	 **/
    public function SectionJS($file, $section = '')
    {
        if(!$section) $section = JRequest::getWord('section', $this->default_section);
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, 'sections.'.$section.'.js');
    	$doc->addScript($path);
    	unset($doc);
    }

    /**
	 * Adds a CSS file to the document header located in the "sections/$section/css" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $section    The section name
	 **/
    public function SectionCSS($file, $section = '')
    {
        if(!$section) $section = JRequest::getWord('section', $this->default_section);
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, 'sections.'.$section.'.css', false, true);
    	$doc->addStyleSheet($path);
    	unset($doc);
    }

    /**
	 * Returns a file located in the current theme folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function Theme($file = 'index.php')
    {
   	    return $this->FilePath($file, 'themes.'.$this->theme);
    }

    /**
	 * Returns a theme image
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $alt        The alt-attribute text
	 * @return   string     Returns an HTML img tag
	 **/
    public function ThemeImg($file, $alt = '')
    {
    	$alt = htmlspecialchars($alt, ENT_QUOTES);
    	$img = $this->URLpath($file, 'themes.'.$this->theme.'.images');
   	    return '<img src="'.$img.'" alt="'.$alt.'" border="0" />';
    }

    /**
	 * Adds a theme JS file to the document header
	 *
	 * @param    string     $file       The name of the file to find
	 **/
    public function ThemeJS($file)
    {
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, 'themes.'.$this->theme.'.js');
    	$doc->addScript($path);
    	unset($doc);
    }

    /**
	 * Adds a theme CSS file to the document header
	 *
	 * @param    string     $file       The name of the file to find
	 **/
    public function ThemeCSS($file)
    {
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, 'themes.'.$this->theme.'.css');
    	$doc->addStyleSheet($path);
    	unset($doc);
    }

    /**
	 * Returns a file located in the "panels/$panel" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $panel      The panel name
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function Panel($file, $panel)
    {
    	return $this->FilePath($file, 'panels.'.$panel, true);
    }

    /**
	 * Returns an image located in the "panels/$panel/images" folder
	 *
	 * @param    string     $file     The name of the file to find
	 * @param    string     $panel    The panel name
	 * @param    string     $alt      The alt-attribute text
	 * @return   string     Returns an HTML img tag
	 **/
    public function PanelImg($file, $panel, $alt = '')
    {
    	$alt = htmlspecialchars($alt, ENT_QUOTES);
    	$img = $this->URLpath($file, 'panels.'.$panel.'.images', false, true);
   	    return '<img src="'.$img.'" alt="'.$alt.'" border="0" />';
    }

    /**
	 * Adds a JS file to the document header located in the "panels/$panel/js" folder
	 *
	 * @param    string     $file     The name of the file to find
	 * @param    string     $panel    The panel name
	 **/
    public function PanelJS($file, $panel)
    {
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, 'panels.'.$panel.'.js');
    	$doc->addScript($path);
    	unset($doc);
    }

    /**
	 * Adds a CSS file to the document header located in the "panels/$panel/css" folder
	 *
	 * @param    string     $file     The name of the file to find
	 * @param    string     $panel    The panel name
	 **/
    public function PanelCSS($file, $panel)
    {
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, 'panels.'.$panel.'.css', false, true);
    	$doc->addStyleSheet($path);
    	unset($doc);
    }

    /**
	 * Returns a file located in the "processes/$process" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $process    The process name
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function Process($file, $process)
    {
    	return $this->FilePath($file, 'processes.'.$process);
    }

    /**
	 * Adds a CSS file to the document header located in the "processes/$process/css" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $process    The process name
	 **/
    public function ProcessCSS($file, $process)
    {
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, 'processes.'.$process.'.css', false, true);
    	$doc->addStyleSheet($path);
    	unset($doc);
    }

    /**
	 * Adds a JS file to the document header located in the "processes/$process/js" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $process    The process name
	 **/
    public function ProcessJS($file, $process)
    {
        $doc  = JFactory::getDocument();
        $path = $this->URLpath($file, 'processes.'.$process.'.js', false, true);
    	$doc->addScript($path);
    	unset($doc);
    }

    /**
	 * Returns a file located in the "$mods/$mod" folder
	 *
	 * @param    string     $file       The name of the file to find
	 * @param    string     $mod        The mod name
	 * @return   mixed      Returns the full file path if it was found. Otherwise returns error file path or true/false
	 **/
    public function Mod($file, $mod)
    {
        return $this->FilePath($file, 'mods.'.$mod);
    }

    /**
	 * Returns a user avatar img
	 *
	 * @param    integer    $id           The user id
	 * @param    boolean    $path_only    If true, return img URL only, otherwise return img tag
	 * @return   string     HTML img tag
	 **/
    public function Avatar($id, $path_only = false)
    {
        static $avatars = array();
        static $upath   = NULL;
        static $my_id   = NULL;
        static $my_pic  = NULL;

        // Check cache
        if(array_key_exists($id, $avatars)) {
            $this->debug->_('n', 'PFload::Avatar - Loading avatar from cache for user "'.$id.'"');
            return $avatars[$id];
        }

        // Set upload path
        if(is_null($upath)) {
            $config = PFconfig::GetInstance();
            $upath  = $config->Get('upload_path', 'profile');

            if($upath) {
                $upath = str_replace('\\','.', $upath);
                $upath = str_replace('/','.', $upath);
                if(substr($upath,0,1) == '.') $upath = substr($upath,1);
                if(substr($upath,-1,1) == '.') $upath = substr($upath,0,(strlen($upath) - 1));
            }
            else {
                $upath = 'images.'.$this->com->Get('name').'.profile';
            }
            unset($config);
        }

        // Set my id
        if(is_null($my_id) || is_null($my_pic)) {
            $user   = PFuser::GetInstance();
            $my_id  = (int) $user->GetId();
            $my_pic = strval($user->GetProfile('avatar', ''));
            unset($user);
        }

        $img = NULL;

        if($id == $my_id) $img = $my_pic;

        if($id && is_null($img)) {
            $db = PFdatabase::GetInstance();

            $query = "SELECT `content` FROM #__pf_user_profile"
                   . "\n WHERE user_id = '$id'"
                   . "\n AND parameter = 'avatar'";
                   $db->setQuery($query);
                   $img = $db->loadResult();

            unset($db);
        }

        if(!$img) {
            $this->debug->_('n', 'User "'.$id.'" has no avatar');
            $img = $this->ThemeImg('avatar.png', 'Avatar');
            $avatars[$id] = $img;
            unset($db);
            return $img;
        }

        $img = $this->URLpath($img, $upath, true);

        $img = '<img src="'.$img.'" border="0" alt="Avatar"/>';
        $avatars[$id] = $img;

        return $img;
    }

    /**
	 * Returns a project logo image
	 *
	 * @param    integer    $id    The project id
	 * @return   string     HTML img tag
	 **/
    public function Logo($id = 0)
    {
        static $logos = array();
        static $upath = NULL;

        // Check cache
        if(array_key_exists($id, $logos)) {
            $this->debug->_('n', 'PFload::Logo - Loading logo from cache for project "'.$id.'"');
            return $logos[$id];
        }

        // Set upload path
        if(is_null($upath)) {
            $config = PFconfig::GetInstance();
            $upath  = $config->Get('logo_save_path', 'projects');

            if($upath) {
                $upath = str_replace('\\','.', $upath);
                $upath = str_replace('/','.', $upath);
                if(substr($upath,0,1) == '.') $upath = substr($upath,1);
                if(substr($upath,-1,1) == '.') $upath = substr($upath,0,(strlen($upath) - 1));
            }
            else {
                $upath = 'images.'.$this->com->Get('name').'.projects.logos';
            }
            unset($config);
        }

        $img = null;
        if($id) {
            $db = PFdatabase::GetInstance();

            $query = "SELECT logo FROM #__pf_projects"
                   . "\n WHERE id = '$id'";
                   $db->setQuery($query);
                   $img = $db->loadResult();

            unset($db);
        }

        if(!$img) {
            $this->debug->_('n', 'PFload::Logo - Project "'.$id.'" has no logo');
            $config = PFconfig::GetInstance();
            $default = $config->Get('default_logo','theme_logo');
            if(!$default) $default = 'logo.png';
            $img = $this->ThemeImg($default, 'Logo', true);
            $logos[$id] = $img;
            unset($db,$config);
            return $img;
        }

        $img = $this->URLpath($img, $upath, true);
        $img = '<img src="'.$img.'" border="0" alt="Logo"/>';
        $logos[$id] = $img;

        return $img;
    }
}
?>