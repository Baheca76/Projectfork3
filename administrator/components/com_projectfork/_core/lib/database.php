<?php
/**
* $Id: database.php 837 2010-11-17 12:03:35Z eaxs $
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

class PFdatabase
{
	private $db;
	private $debug;
	private $result;
	private $ticker;
	
    protected function __construct()
    {
    	$this->db     = JFactory::getDBO();
    	$this->debug  = PFdebug::GetInstance();
    	$this->result = null;
    	$this->ticker = 0;
    }
    
    public function GetInstance()
    {
        static $self;
        
        if(is_object($self)) return $self;
        
        $self = new PFdatabase();
        return $self;
    }
    
    public function projectFilter($field, $project = NULL, $syntax = 'AND')
    {
        $sql = "";
        
        if(!$project) {
            $user    = PFuser::GetInstance();
            $project = $user->GetWorkspace();
            
            if(!$project) {
                $projects = $user->Permission('projects');
                $count    = count($projects);
                $imp      = implode(',',$projects);
                
                $sql = ($count > 1) ? "\n $syntax $field IN($imp)" : "\n $syntax $field = '$imp'";
            }
            else {
                $sql = "\n $syntax $field = '$project'";
            }
            unset($user);
        }
        else {
            $sql = "\n $syntax $field = '$project'";
        }
        
        return $sql;
    }
    
    public function setQuery( $sql, $offset = 0, $limit = 0, $prefix='#__' )
    {
    	$this->db->setQuery( $sql, $offset, $limit, $prefix );
    	$this->debug->_('n', "PFdatabase::setQuery - ".$sql);
    	$this->ticker++;
    }
    
    public function getEscaped( $text, $extra = false )
	{
		$this->result = $this->db->getEscaped( $text, $extra );
		return $this->result;
	}
	
	
	public function getTicker()
	{
		return $this->ticker;
	}
	
	public function resetTicker()
	{
		$this->ticker = 0;
	}
    
    public function query()
    {
    	$this->result = $this->db->query();
    	$this->debug->_('e',$this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function getNumRows( $cur = null )
    {
    	$this->result = $this->db->getNumRows( $cur );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function loadResult()
    {
    	$this->result = $this->db->loadResult();
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function loadResultArray($numinarray = 0)
    {
    	$this->result = $this->db->loadResultArray( $numinarray );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function loadAssoc()
    {
    	$this->result = $this->db->loadAssoc();
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function loadAssocList( $key = '' )
    {
    	$this->result = $this->db->loadAssocList( $key );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function loadObject()
    {
    	$this->result = $this->db->loadObject();
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function loadObjectList( $key='' )
    {
    	$this->result = $this->db->loadObjectList( $key );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function loadRow()
    {
    	$this->result = $this->db->loadRow();
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function loadRowList( $key=null )
    {
    	$this->result = $this->db->loadRowList( $key );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function insertObject( $table, &$object, $keyName = NULL )
    {
    	$this->result = $this->db->insertObject( $table, $object, $keyName );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function updateObject( $table, &$object, $keyName, $updateNulls=true )
    {
    	$this->db->updateObject( $table, $object, $keyName, $updateNulls );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    }
    
    public function insertid()
    {
    	$this->result = $this->db->insertid();
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function getVersion()
    {
    	$this->result = $this->db->getVersion();
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function getTableList()
    {
    	$this->result = $this->db->getTableList();
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function getTableCreate( $tables )
    {
    	$this->result = $this->db->getTableCreate( $tables );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function getTableFields( $tables, $typeonly = true )
    {
    	$this->result = $this->db->getTableFields( $tables, $typeonly );
    	$this->debug->_('e', $this->db->getErrorMsg()); 
    	return $this->result;
    }
    
    public function getErrorMsg($escaped = false)
    {
    	$this->result = $this->db->getErrorMsg($escaped);
    	return $this->result;
    }
    
    public function getErrorNum() 
    {
    	$this->result = $this->db->getErrorNum();
    	return $this->result;
    }
 
    public function Quote($text, $escaped = true)
    {
    	return $this->db->Quote($text, $escaped);
    }
}	
?>