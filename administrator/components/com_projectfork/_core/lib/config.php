<?php
/**
* $Id: config.php 837 2010-11-17 12:03:35Z eaxs $
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
class PFconfig
{
    /**
	 * Database object
	 *
	 * @var    Object
	 **/
	private $db;
	
	/**
	 * Debug object
	 *
	 * @var    Object
	 **/
	private $debug;
	
	/**
	 * Config settings pulled from #__pf_settings
	 *
	 * @var    Array
	 **/
    private $params;
    
    /**
	 * Constructor
	 *
	 **/
    protected function __construct()
    {
        $this->db     = PFdatabase::GetInstance();
        $this->debug  = PFdebug::GetInstance();
        $this->params = $this->LoadParams();
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
        
        $self = new PFconfig();
        return $self;
    }
    
    /**
	 * Returns a config value
	 *
	 * @param     string    $param    The param name
	 * @param     string    $scope    The config scope
	 * @return    string              Returns the value if the param exists. Returns NULL otherwise	 
	 **/
    public function Get($param = NULL, $scope = 'system')
    {
        if(!$param && array_key_exists($scope, $this->params)) return $this->params[$scope];
    	if(!array_key_exists($scope, $this->params)) return null;
    	if(array_key_exists($param, $this->params[$scope])) return $this->params[$scope][$param];

    	return NULL;
    }
    
    /**
	 * Sets a config value
	 *
	 * @param     string    $param      The param name
	 * @param     string    $content    The param value to set
	 * @param     string    $scope      The param scope
	 * @return    boolean               True on success, false otherwise
	 **/
    public function Set($param, $content = '', $scope = 'system')
    {
        $success     = true;
        $update      = true;
    	$tmp_content = $content;
    	$tmp_param   = $param;
    	$tmp_scope   = $scope;
    	
    	$content = $this->db->Quote($content);
    	$param   = $this->db->Quote($param);
    	$scope   = $this->db->Quote($scope);
    	
    	if(!is_array($this->params)) $this->params = array();
    	if(!array_key_exists($tmp_scope, $this->params)) $this->params[$tmp_scope] = array();
    	if(!array_key_exists($tmp_param, $this->params[$tmp_scope])) $update = false;
    	$this->params[$tmp_scope][$tmp_param] = $tmp_content;
    	
    	// Update database
    	if($update) {
    		$query = "UPDATE #__pf_settings SET `content` = $content"
                   . "\n WHERE `parameter` = $param"
                   . "\n AND `scope` = $scope";
    		       $this->db->setQuery($query);
    		       $this->db->query();
    		       
    		if($this->db->getErrorMsg()) $success = false;
    	}
    	else {
    		$query = "INSERT INTO #__pf_settings"
                   . "\n VALUES(NULL, $param, $content, $scope)";
    		       $this->db->setQuery($query);
    		       $this->db->query();
    		       
    		if($this->db->getErrorMsg()) $success = false;
    	}

    	return $success;
    }
    
    /**
	 * Permanently deletes a config parameter
	 *
	 * @param     string    $param      The param name
	 * @param     string    $scope      The param scope
	 * @return    boolean               True on success, false otherwise
	 **/
    public function Delete($param, $scope = 'system')
    {
        $success = true;
        
    	$param = $this->db->Quote($param);
    	$scope = $this->db->Quote($scope);
    	
    	$query = "DELETE FROM #__pf_settings"
               . "\n WHERE `parameter` = $param"
               . "\n AND `scope` = $scope";
    	       $this->db->setQuery($query);
    	       $this->db->query();
    	       
       if($this->db->getErrorMsg()) $success = false;
       
       return $success;
    }
    
    /**
	 * Loads all config params from the db table #__pf_settings
	 *
	 **/
    private function LoadParams()
    {
        $query = "SELECT `parameter`,`content`,`scope` FROM #__pf_settings"
               . "\n ORDER BY `scope` ASC";
    	       $this->db->setQuery($query);
    	       $params = $this->db->loadObjectList();

        $this_scope = "";
        $formatted  = array();
        
        foreach($params AS $param)
        {
            if($param->scope != $this_scope) {
                $this_scope = $param->scope;
                $formatted[$this_scope] = array();
            }
            $formatted[$this_scope][$param->parameter] = $param->content;
        }
        
        unset($params);
        return $formatted;
    }
}
?>