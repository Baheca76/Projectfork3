<?php
/**
* $Id: language.php 840 2011-01-20 11:03:13Z eaxs $
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
class PFlanguage
{
    /**
     * Holds information about all installed languages
     *
     * @var    array
     **/
    private $languages;
    
    /**
     * The language to load
     *
     * @var    string
     **/
    private $language;
    
    /**
     * Holds all language tokens
     *
     * @var    array
     **/
    private $tokens;
    
    /**
     * List of language files that have already been loaded
     *
     * @var    array
     **/
    private $loaded;
    
    /**
     * Class constructor
     *
     **/
    public function __construct()
    {
        $core = PFcore::GetInstance();
        $user = PFuser::GetInstance();
        
        $this->languages = $core->GetLanguages();
        $usr_lang = $user->GetProfile('language');

        if(!$usr_lang) {
            $found = false;
            foreach($this->languages AS $row)
            {
                if(!is_object($row)) continue;
                
                if($row->is_default == '1') {
                    $user->SetProfile('language', $row->name);
                    $usr_lang = $row->name;
                    $found    = true;
                }
            }
            
            if(!$found) {
                $user->SetProfile('language', 'english');
                $usr_lang = 'english';
            }
        }
        
        if(!array_key_exists($usr_lang, $this->languages)) {
            $found = false;
            foreach($this->languages AS $lang)
            {
                if(!is_object($lang)) continue;
                
                if($lang->is_default == '1') {
                    $usr_lang = $lang->name;
                    $found    = true;
                }
            }
            if(!$found) $usr_lang = 'english';
        }
        
        $this->language = $usr_lang;
        $this->tokens   = array();
        $this->loaded   = array();
        
        unset($core, $user);
    }
    
    /**
     * Returns an instance of the class
     *
     * @return    object    Class object
     **/
    public function GetInstance()
    {
        static $self = NULL;
        
        if(is_null($self)) $self = new PFlanguage();
        
        return $self;
    }
    
    /**
     * Parses a language file
     *
     * @param    string    $name    The name of the extension or language file
     * @param    string    $type    The extension type
     * @param    string    $lang    The language to load from          
     **/
    public function Parse($name, $type, $lang)
    {
        $load  = PFload::GetInstance();
        $debug = PFdebug::GetInstance();
        
        $load->Set404(false);
        $file = $load->FilePath($type.'_'.$name.'.ini', 'languages.'.$lang);
        $load->Set404(true);
        
        if(!$file) {
            $debug->_('w', 'PFlanguage::Parse - Language file "'.$type.'_'.$name.'.ini" not found!');
            // Try default english
            if($lang != 'english') {
                $load->Set404(false);
                $file = $load->FilePath($type.'_'.$name.'.ini', 'languages.english');
                $load->Set404(true);
                
                if(!$file) {
                    unset($load,$debug);
                    return array();
                }
            }
            else {
                unset($load,$debug);
                return array();
            }
        }
        
        $tmp_tokens = parse_ini_file($file);
        $tokens     = array();
        
        if(!is_array($tmp_tokens)) $tmp_tokens = array();
        
        foreach($tmp_tokens AS $k => $v)
        {
            if(array_key_exists($k,$tokens)) {
                $debug->_('w', 'PFlanguage::Parse - Language token "'.$k.'" is already loaded!');
                continue;
            }
            else {
                $tokens[$k] = $v;
            }
        }
        
        unset($debug,$tmp_tokens,$load);
        return $tokens;
    }
    
    /**
     * Loads a language file
     *
     * @param    string    $name    The name of the extension or language file
     * @param    string    $type    The extension type       
     **/
    public function Load($name, $type)
    {
        $debug = PFdebug::GetInstance();
        $key   = $type.$name;
        
        if(in_array($key, $this->loaded)) {
            $debug->_('w', 'PFlanguage::Load - Language file "'.$type.'_'.$name.'.ini" is already loaded!');
            return false;
        }
        
        $debug->_('n', 'PFlanguage::Load - Loading file "'.$type.'_'.$name.'.ini"');
        $tokens = $this->Parse($name, $type, $this->language);
        
        $this->tokens   = array_merge($this->tokens,$tokens);
        $this->loaded[] = $key;
        
        unset($debug,$tokens);
        return true;
    }
    
    /**
     * Translates a token
     *
     * @param     string    $k    The token key
     * @return    string          The translated token
     **/
    public function _($k)
    {
        if(!array_key_exists($k, $this->tokens)) {
            $debug = PFdebug::GetInstance();
            $debug->_('w', 'PFlanguage::_ - String "'.$k.'" is not translated');
            unset($debug);
            return $k;
        }
        return $this->tokens[$k];
    }
    
    /**
     * Checks whether a token exists or not
     *
     * @param     string    $token    The token key
     * @return    boolean             True if exists, otherwise False
     **/
    public function TokenExists($token)
    {
        if(!array_key_exists($token, $this->tokens)) return false;
        return true;
    }
    
    /**
     * Returns the current language
     *
     * @return    string    The current language name
     **/
    public function GetLanguage()
    {
        return $this->language;
    }
}
?>