<?php
/**
* $Id: mod.php 841 2011-01-20 11:15:49Z eaxs $
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

class PFmod
{
    private $mods;
    private $mod_files;
    private $path_base;
    private $file_pointer;
	
	protected function __construct()
	{
        $config = PFconfig::GetInstance();
        $com    = PFcomponent::GetInstance();
        $debug  = PFdebug::GetInstance();
        
        $cache_mods = (int) $config->Get('cache_mods');
        
        $this->path_base = $com->Get('path_backend');

        if($cache_mods) {
            $debug->_('n', 'PFmod::__construct - Loading mod data from cache');
            $cache = JFactory::getCache('com_projectfork.mods');
            $cache->setCaching(true);
            $this->mods      = $cache->call(array('PFmod', 'LoadActiveMods'));
            $this->mod_files = $cache->call(array('PFmod', 'LoadModFiles'), $this->mods);
            unset($cache);
        }
        else {
            $debug->_('n', 'PFmod::__construct - Mod cache is disabled');
            $this->mods      = $this->LoadActiveMods();
            $this->mod_files = $this->LoadModFiles($this->mods);
        }
        
        unset($cache,$config,$com,$debug,$cache_mods);
    }
    
    public function GetInstance()
    {
        static $self;
        
        if(is_object($self)) return $self;
        
        $self = new PFmod();
        return $self;
    }
	
	public function LoadActiveMods()
	{
	    $db = PFdatabase::GetInstance();
	    
        $query = "SELECT name FROM #__pf_mods"
               . "\n WHERE enabled = '1'";
               $db->setQuery($query);
               $mods = $db->loadResultArray();
               
        if(!is_array($mods)) $mods = array();
        
        unset($db,$query);
        
        return $mods;
    }
    
    public function LoadModFiles($mods)
    {
        $db     = PFdatabase::GetInstance();
        $debug  = PFdebug::GetInstance();
        $double = array();
        $data   = array();
        
        $data['__index'] = array();
        
        if(!count($mods)) return $data;
        
        foreach($mods AS $mod)
        {
            $n = $db->Quote($mod);
            $data[$mod] = array();
            
            $query = "SELECT filepath FROM #__pf_mod_files"
                   . "\n WHERE name = $n";
                   $db->setQuery($query);
                   $files = $db->loadResultArray();

            if(!is_array($files)) $files = array();
            
            foreach($files AS $file)
            {
                // Clean up path
                $file = str_replace('.DS.',DS,$file);
                if(substr($file, 0, 1) == DS) $file = substr($file, 1);

                // Check for double
                if(in_array($file, $double)) {
                    $debug->_('e','PFmod::LoadModFiles - File "'.$file.'" already used in another mod!');
                    continue;
                }
                
                // Register duplicate
                $double[] = $file;
                
                // Register file
                $debug->_('n','PFmod::LoadModFiles - Registering file "'.$file.'" for mod "'.$mod.'"');
                $data[$mod][] = $file;
                $data['__index'][$file] = $mod;
            }
        }
        
        unset($db, $debug,$mods,$files);
        return $data;
    }
    
    public function Exists($file)
    {
        if(!array_key_exists($file, $this->mod_files['__index'])) return false;
        
        $mod = $this->mod_files['__index'][$file];
        $this->file_pointer = 'mods'.DS.$mod.DS.$file;
        
        return true;
    }
	
	public function GetPointer()
	{
        return $this->file_pointer;
    }
}
?>