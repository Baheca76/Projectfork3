<?php
/**
* $Id: debug.php 837 2010-11-17 12:03:35Z eaxs $
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
class PFdebug
{
    /**
     * 1 if enabled, otherwise 0
     *
     * @var    int
     **/
    private $enabled;
    
    /**
     * Holds all log messages
     *
     * @var    array
     **/
    private $log;
    
    /**
     * Log messages to ignore
     *
     * @var    array
     **/
    private $ignore;
    
    /**
     * Joomla profiler object
     *
     * @var    object
     **/
    private $profiler;
    
    /**
     * Profiler microtime
     *
     * @var    float
     **/
    private $mtime;

    /**
     * Current task
     *
     * @var    string
     **/
    private $task;
    
    /**
     * Save method for log
     *
     * @var    integer
     **/
    private $save_method;
    
    
    /**
     * Class constructor
     *
     **/
	protected function __construct()
	{
        $this->enabled  = $this->IsEnabled();
        $this->profiler = new JProfiler();
        $this->mtime    = $this->profiler->getmicrotime();
        
        $this->ignore   = array();
        $this->log      = array();
        $this->log['n'] = array();
        $this->log['w'] = array();
        $this->log['e'] = array();
        $this->log['v'] = array();
        
        $this->task = JRequest::getVar('task');
        $this->save_method = (int) JRequest::getVar('save_method');
        
        // Save the log
        if($this->enabled && $this->task == 'debug_save') {
            $this->SaveLog($this->save_method);
        }
	}
	
	/**
     * Returns an instance of the class
     * 
     **/
	public function GetInstance()
	{
        static $self;
        
        if(is_object($self)) return $self;
        
        $self = new PFdebug();
        return $self;
    }

    /**
     * Checks whether debugging is enabled or not
     * 
     * @return   boolean    True if enabled, otherwise False     
     **/
	public function IsEnabled()
	{
	    static $isenabled = NULL;
	    
	    if(is_null($isenabled)) {
	        $db = JFactory::getDBO();
	        
            $query = "SELECT `content` FROM #__pf_settings"
	               . "\n WHERE `parameter` = 'debug'"
	               . "\n AND `scope` = 'system'";
	               $db->setQuery($query);
	               $isenabled = (int) $db->loadResult();
	               
	        unset($db);
        }
	    
	    if($isenabled == 1) return true;
	    return false;
	}
	
	/**
     * Enables or disables debugging and logging
     * 
     * @param    boolean    $state    Must be True or False
     **/
	public function SetEnabled($state)
	{
        $this->enabled = $state;
    }
    
    /**
     * Logs a message
     * 
     * @param    string    $type    Message type. n,w,e or v
     * @param    string    $msg     The message to log
     **/
    public function _($type, $msg = '')
    {
        if(!$this->enabled) return false;
        if(!$msg) return false;
        if(in_array($msg, $this->ignore)) return false;
        
        $mtime = $this->profiler->getmicrotime();
        $this->log[$type][] = sprintf('%.3f', ($mtime - $this->mtime)).' - '.$msg;
        $this->ignore[] = $msg;
    }

    /**
     * Returns the log array
     * 
     * @param    array    All logged messages
     **/
    public function GetLog()
    {
        return $this->log;
    }
    
    /**
     * Saves the current log
     * 
     * @param    integer    The method by which to save
     **/
    public function SaveLog($method)
    {
        switch($method)
        {
            default:
            case 0:
                $this->SaveToFile();
                break;
        }
    }
    
    /**
     * Saves the current log to a file download
     * 
     **/
    public function SaveToFile()
    {
        $db = JFactory::getDBO();
        
		$task       = JRequest::getVar('task', null, 'post');
        $template   = dirname(__FILE__).DS."..".DS."output".DS."debug.txt";
		$date       = date('Y/m/d');
		$notices    = '';
		$warnings   = '';
		$errors     = '';
		$violations = '';
        $user_env   = JRequest::getVar('user_env');
		
		if(!file_exists($template)) return false;

        // Prepare system info
        $system_info = "";
        $system_info .= "Projectfork Version: ".PF_VERSION_STRING."\n";
        $system_info .= "Joomla Version: ".JVERSION."\n";
        $system_info .= "PHP Built On: ".php_uname()."\n";
        $system_info .= "PHP Version: ".phpversion()."\n";
        $system_info .= "Database Version: ".$db->getVersion()."\n";
        $system_info .= "Database Collation: ".$db->getCollation()."\n";
        $system_info .= ":::::::::::::::::::::::::::::::::: Variables\n";
        $system_info .= "Section: ".JRequest::getVar('section')."\n";
        $system_info .= "Task: ".JRequest::getVar('section_task')."\n";
        $system_info .= "Query String: ".JRequest::getVar('qstring')."\n";
        $system_info .= "::::::::::::::::::::::::::::: System settings\n";

        $query = "SELECT * FROM #__pf_settings ORDER BY `scope` ASC";
               $db->setQuery($query);
               $rows = $db->loadObjectList();

        foreach($rows AS $row)
        {
            $system_info .= $row->scope."->".$row->parameter.": $row->content\n";
        }

        // prepare general user info
        $user = &JFactory::getUser();
        $user_info = "";
        $user_info .= "User ID: ".$user->id."\n";
        $user_info .= "User GID: ".$user->gid."\n";
        $user_info .= "User Type: ".$user->usertype."\n";
        $user_info .= "Access level: ".$user_env['accesslevel']."\n";
        $user_info .= "Score: ".$user_env['score']."\n";
        $user_info .= "Flag: ".$user_env['flag']."\n";
        $user_info .= "Workspace: ".$user_env['workspace']."\n";

        // prepare user profile
        $profile_info = "";
        if($user->id != 0) {
            $query = "SELECT * FROM #__pf_user_profile WHERE user_id = ".$db->Quote($user->id);
                   $db->setQuery($query);
                   $rows = $db->loadObjectList();

            foreach($rows AS $row)
            {
                $profile_info .= $row->parameter.": ".$row->content."\n";
            }
        }

        // prepare environment - groups
        $env_groups = implode(', ',$user_env['groups']);

        // prepare environment - user space
        $env_users = implode(', ',$user_env['userspace']);

        // prepare environment - projects
        $env_projects = implode(', ',$user_env['projects']);

        // prepare environment - permissions
        $env_permissions = "";
        foreach($user_env['tasks'] AS $s => $t)
        {
            foreach($t AS $v)
            {
                $env_permissions .= $s."->".$v."\n";
            }
        }

        // prepare notices
		foreach (JRequest::getVar('notices', array(), 'post', 'none', 2) AS $log)
		{
			$notices .= $log."\r\n";
		}

        // prepare warnings
		foreach (JRequest::getVar('warnings', array(), 'post', 'none', 2) AS $log)
		{
			$warnings .= $log."\r\n";
		}

        // prepare errors
		foreach (JRequest::getVar('errors', array(), 'post', 'none', 2) AS $log)
		{
			$errors .= $log."\r\n";
		}

        // prepare violations
		foreach (JRequest::getVar('violations', array(), 'post', 'none', 2) AS $log)
		{
			$violations .= $log."\r\n";
		}

        // prepare sections
        $section_output = "";
        $info = "";
        $query = "SELECT * FROM #__pf_sections";
               $db->setQuery($query);
               $sections = $db->loadObjectList();

        if(!is_array($sections)) $sections = array();

        foreach($sections AS $row)
        {
            $info .= "[enabled=$row->enabled]";
            $info .= "[score=$row->score]";
            $info .= "[flag=$row->flag]";
            $info .= "[tags=$row->tags]";
            $info .= "[default=$row->is_default]";
            $info .= "[version=$row->version]";
            $section_output .= "$row->name $info\r\n";
            $info = "";
        }
        unset($sections);

        // prepare panels
        $panel_output = "";
        $info = "";
        $query = "SELECT * FROM #__pf_panels";
               $db->setQuery($query);
               $panels = $db->loadObjectList();

        if(!is_array($panels)) $panels = array();

        foreach($panels AS $row)
        {
            $info .= "[enabled=$row->enabled]";
            $info .= "[score=$row->score]";
            $info .= "[flag=$row->flag]";
            $info .= "[position=$row->position]";
            $info .= "[version=$row->version]";
            $panel_output .= "$row->name $info\r\n";
            $info = "";
        }
        unset($panels);

        // prepare processes
        $process_output = "";
        $info = "";
        $query = "SELECT * FROM #__pf_processes";
               $db->setQuery($query);
               $processes = $db->loadObjectList();

        if(!is_array($processes)) $processes = array();

        foreach($processes AS $row)
        {
            $info .= "[enabled=$row->enabled]";
            $info .= "[score=$row->score]";
            $info .= "[flag=$row->flag]";
            $info .= "[position=$row->position]";
            $info .= "[version=$row->version]";
            $process_output .= "$row->name $info\r\n";
            $info = "";
        }
        unset($processes);

        // prepare themes
        $theme_output = "";
        $info = "";
        $query = "SELECT * FROM #__pf_themes";
               $db->setQuery($query);
               $themes = $db->loadObjectList();

        if(!is_array($themes)) $themes = array();

        foreach($themes AS $row)
        {
            $info .= "[enabled=$row->enabled]";
            $info .= "[default=$row->is_default]";
            $info .= "[version=$row->version]";
            $theme_output .= "$row->name $info\r\n";
            $info = "";
        }
        unset($themes);

        // prepare mods
        $mod_output = "";
        $info = "";
        $query = "SELECT * FROM #__pf_mods";
               $db->setQuery($query);
               $mods = $db->loadObjectList();

        if(!is_array($mods)) $mods = array();

        foreach($mods AS $row)
        {
            $query = "SELECT * FROM #__pf_mod_files WHERE name = ".$db->Quote($row->name);
                   $db->setQuery($query);
                   $mod_data = $db->loadObjectList();

            if(!is_array($mod_data)) $mod_data = array();
            
            $info .= "[enabled=$row->enabled]";
            $info .= "[version=$row->version]";
            $mod_output .= "$row->name $info\r\n";

            foreach($mod_data AS $d)
            {
                $mod_output .= "$d->filepath\r\n";
            }
            
            $mod_output .= "\r\n";
            $info = "";
        }
        unset($mods);


        // read the debug template
        $file = file($template);
        $file = implode("", $file);
        $file = trim($file);
        
        // replace placeholders
        $file = str_replace('{system}', $system_info, $file);
        $file = str_replace('{user_gi}', $user_info, $file);
        $file = str_replace('{user_profile}', $profile_info, $file);
        $file = str_replace('{env_groups}', $env_groups, $file);
        $file = str_replace('{env_users}', $env_users, $file);
        $file = str_replace('{env_projects}', $env_projects, $file);
        $file = str_replace('{env_permissions}', $env_permissions, $file);
        $file = str_replace('{date}', $date, $file);
        $file = str_replace('{notices}', trim($notices), $file);
        $file = str_replace('{warnings}', trim($warnings), $file);
        $file = str_replace('{errors}', trim($errors), $file);
        $file = str_replace('{violations}', trim($violations), $file);
        $file = str_replace('{sections}', trim($section_output), $file);
        $file = str_replace('{panels}', trim($panel_output), $file);
        $file = str_replace('{processes}', trim($process_output), $file);
        $file = str_replace('{themes}', trim($theme_output), $file);
        $file = str_replace('{mods}', trim($mod_output), $file);

		ob_end_clean();
        ob_end_clean();
		header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=pflog_".time().".txt");
        echo $file;
        die();
    }
}
?>