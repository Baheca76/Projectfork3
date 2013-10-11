<?php
/**
* $Id: core.php 926 2012-06-25 15:09:42Z eaxs $
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
class PFobject
{
    /**
     * Error messages
     *
     * @var    array
     **/
    private $errors;

    /**
     * Amount of error messages
     *
     * @var    int
     **/
    private $error_num;


    /**
     * Constructor
     **/
    public function __construct()
    {
        $this->errors = array();
        $this->error_num = 0;
    }

    /**
     * Adds an error message
     *
     * @param    string     $msg        The message to add
     * @param    boolean    $display    Set to true if you want to display the message
     **/
    public function AddError($msg, $display = true)
    {
        $this->errors[] = $msg;
        $this->error_num++;

        if($display) {
            $core = PFcore::GetInstance();
            $core->AddMessage($msg);
            unset($core);
        }
    }

    /**
     * Checks for logged error messages
     *
     * @return    boolean    Returns True if there are errors, otherwise returns False
     **/
    public function HasError()
    {
        if($this->error_num > 0) return true;
        return false;
    }

    /**
     * Returns all logged error messages
     *
     * @return    array    The logged messages
     **/
    public function GetErrors()
    {
        return $this->errors;
    }

    /**
     * Redirects the browser once the script has finished
     *
     * @param    string    $link    The URL to redirect to
     * @param    mixed     $msg     A message that will be displayed after the redirect
     **/
    public function SetRedirect($link, $msg = NULL)
    {
        $core = PFcore::GetInstance();

        if(!is_null($msg)) $core->AddMessage($msg);

        $core->SetRedirect($link);
        unset($core);
    }

    /**
     * Redirects the browser without delay
     *
     * @param    string    $link    The URL to redirect to
     * @param    mixed     $msg     A message that will be displayed after the redirect
     **/
    public function Redirect($link, $msg = NULL)
    {
        $core = PFcore::GetInstance();

        if(!is_null($msg)) $core->AddMessage($msg);

        $core->Redirect($link);
        unset($core);
    }

    /**
     * Returns the class file path of the section
     *
     * @param     string    $section    Optional - The target section
     * @return    string    File path
     **/
    public function GetClass($section = NULL)
    {
        $load = PFload::GetInstance();

        if(is_null($section)) {
            $core = PFcore::GetInstance();
            $section = $core->GetSection();
            unset($core);
        }

        $file = $load->Section($section.'.class.php', $section);
        unset($load);

        return $file;
    }

    /**
     * Returns the helper file path of the section
     *
     * @param     string    $section    Optional - The target section
     * @return    string    File path
     **/
    public function GetHelper($section = NULL)
    {
        $load = PFload::GetInstance();

        if(is_null($section)) {
            $core = PFcore::GetInstance();
            $section = $core->GetSection();
            unset($core);
        }

        $file = $load->Section($section.'.helper.php', $section);
        unset($load);

        return $file;
    }

    /**
     * Returns the output file path of the section
     *
     * @param     string    $file       The output file name
     * @param     string    $section    Optional - The target section
     * @return    string    File path
     **/
    public function GetOutput($file, $section = NULL)
    {
        $load = PFload::GetInstance();

        if(is_null($section)) {
            $core = PFcore::GetInstance();
            $section = $core->GetSection();
            unset($core);
        }

        $file = $load->SectionOutput($file, $section);
        unset($load);

        return $file;
    }
}


/**
* @package       Projectfork
* @subpackage    Framework
**/
class PFsection
{
    /**
	 * Buffered section output
	 *
	 * @var    string
	 **/
    private $buffer;

    /**
	 * The name of the section
	 *
	 * @var    string
	 **/
    private $name;

    /**
	 * The name of the current task
	 *
	 * @var    string
	 **/
    private $task;

    /**
	 * Constructor
	 **/
    protected function __construct()
    {
        $core = PFcore::GetInstance();
        $this->buffer = NULL;
        $this->name = $core->GetSection();
        $this->task = $core->GetTask();

        unset($core);
    }

    /**
	 * Returns an instance of itself
	 *
	 * @return    object    Class object
	 **/
    public function GetInstance()
    {
        static $self = NULL;

        if(is_null($self)) $self = new PFsection();

        return $self;
    }

    /**
	 * Checks whether the requested section and task can be accessed by the user
	 *
	 * @return    boolean    True if access is allowed, otherwise False
	 **/
    public function CheckAccess()
    {
        $user    = PFuser::GetInstance();
        $id      = (int) JRequest::GetVar('id');
        $section = JRequest::getVar('section');

        // Check basic permission to access the current section and task
        if(!$user->Access($this->task, $this->name)) {
            return false;
        }

        // Check Id hash
        if($id) {
            $hash = PFformat::IdHash($id, $section);
            $hash_exists = JRequest::getVar($hash);

            if(!$hash_exists) {
                // Check session
                if(array_key_exists('pf_hashes', $_SESSION)) {
                    if(!is_array($_SESSION['pf_hashes'])) $_SESSION['pf_hashes'] = array();
                    if(in_array($hash, $_SESSION['pf_hashes'])) return true;
                    return false;
                }
                else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
	 * Includes the section files and buffers the output
	 * which is stored in $buffer
	 **/
    public function PreRender($check_access = true)
    {
        if($check_access) {
            if(!$this->CheckAccess()) {
                // No access to the section or task
                $this->AccessRedirect();
                return false;
            }
        }

        $load = PFload::GetInstance();
        $load->Set404(false);

        $controller = $load->Section($this->name.'.controller.php', $this->name);
        $class      = $load->Section($this->name.'.class.php', $this->name);

        $load->Set404(true);

        if(!defined('PF_PAGINATION')) {
            jimport('joomla.html.pagination');
            define('PF_PAGINATION', 1);
        }

		ob_start();
		if($class) require_once($class);
		if($controller) require_once($controller);
		require_once($load->Section($this->name.'.init.php', $this->name));
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->buffer = $buffer;

		unset($buffer,$load);

		return true;
    }

    /**
	 * Prints the buffered section output and then clears it
	 **/
    public function Render()
    {
        $section = PFsection::GetInstance();

        echo $section->GetBuffer();
        $section->CleanBuffer();

        unset($section);
    }

    /**
	 * Returns the buffered section output. Returns NULL if the buffer is empty
	 *
	 * @return    string    Section output
	 **/
    public function GetBuffer()
    {
        return $this->buffer;
    }

    /**
	 * Clears the section buffer
	 **/
    public function CleanBuffer()
    {
        $this->buffer = NULL;
    }

    /**
	 * Redirects the browser to either the section main screen, control panel or Joomla login page
     *
	 **/
    private function AccessRedirect()
    {
        $jversion = new JVersion();

        $user   = PFuser::GetInstance();
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();
        $core   = PFcore::GetInstance();
        $juri   = JFactory::getURI();

        // Get the default section
        $query = "SELECT name FROM #__pf_sections WHERE is_default = '1'";
			   $db->setQuery($query);
			   $default_section = $db->loadResult();

		if(!$default_section) $default_section = 'controlpanel';

        // Get hide template setting
        $hide_tmpl = $config->Get('hide_template');

        // Set the returning URL for login
        $return = base64_encode($juri->toString());

        if(!$user->Access(NULL, $this->name)) {
            // Redirect to login page if we are not logged in
            if($user->GetId() == 0 || !$user->Access(NULL, $default_section)) {
                if($jversion->RELEASE == '1.5') {
                    global $mainframe;
                    $link = JRoute::_('index.php?option=com_user&view=login&return='.$return);
                    $mainframe->redirect($link, PFformat::Lang('NO_PAGE_ACCESS'));
        		    $mainframe->close();
                }
                else {
                    $link = JRoute::_('index.php?option=com_users&view=login&return='.$return);
                    $app  = JFactory::getApplication();
        			$app->redirect($link, PFformat::Lang('NO_PAGE_ACCESS'));
                }
                return true;
            }
            else {
                $core->Redirect('section='.$default_section, 'NO_PAGE_ACCESS');
                return true;
            }
        }
        else {
            $core->Redirect('section='.$this->name, 'NO_PAGE_ACCESS');
            return true;
        }
    }
}

/**
* @package       Projectfork
* @subpackage    Framework
**/
class PFpanel
{
    /**
	 * Loads a single panel and returns the buffered output
	 *
	 * @param     mixed     $panel    The panel name or object to render
	 * @param     string    $style    The panel template style to use
	 * @param     string    $ckey     The panel cache key. Only needed when caching is enabled
	 * @return    string    The buffered panel content
	 **/
    public function Render($panel, $style = 'panel_default', $ckey = '')
    {
        static $theme_name = NULL;
        static $templates  = array();
        static $use_score  = NULL;

        $debug = PFdebug::GetInstance();

        // Check theme name
        if(is_null($theme_name)) {
            $core = PFcore::GetInstance();
            $theme_name = $core->GetTheme();
            unset($core);
        }

        if(is_null($use_score)) {
            $config = PFconfig::GetInstance();
            $use_score = (int) $config->Get('use_score');
        }

        // Check style template
        if(!array_key_exists($style, $templates)) {
            $templates[$style] = PFpanel::ParseTemplate($style.'.html', $theme_name);
        }

        // Load panel by name instead of object
        if(!is_object($panel)) {
            $debug->_('n', 'PFpanel::Render - Attempting to find panel by name "'.$panel.'"');
            $core   = PFcore::GetInstance();
            $list   = $core->GetPanels();
            $object = NULL;

            foreach($list as $pos)
            {
                foreach($pos AS $pnl)
                {
                    if($pnl->name == $panel) $object = $pnl;
                }
            }

            if(is_null($object)) {
                $debug->_('e', 'PFpanel::Render - Panel "'.$panel.'" not found!');
                unset($debug,$core,$list);
                return "";
            }
            $debug->_('n', 'PFpanel::Render - Panel found by name "'.$panel.'"');
            $panel = $object;

            // Assuming the language file has not been loaded manually
            $lang = PFlanguage::GetInstance();
            $lang->Load($panel->name, 'panel');

            unset($object, $lang);
        }

        // Check permission
        $user  = PFuser::GetInstance();
        $score = $user->Permission('score');
		$flag  = $user->Permission('flag');
		unset($user);

		$flag_ok  = false;
		$score_ok = false;

		if($panel->flag != '') {
		    if($panel->flag == $flag) {
			    $flag_ok = true;
			}
			else {
			    if($panel->flag == 'project_administrator' && $flag == 'system_administrator') $flag_ok = true;
			}
		}
		else {
			$flag_ok = true;
		}

		if($score < $panel->score) {
			if($flag == 'system_administrator') $score_ok = true;
			if(!$use_score) $score_ok = true;
		}
		else {
			$score_ok = true;
		}

		// Include if permissions are ok
		if($score_ok && $flag_ok) {
		    $debug->_('n', 'PFpanel::Render - Panel permissions OK, loading panel "'.$panel->name.'"');
		    $load = PFload::GetInstance();
		    // Include the panel
            ob_start();
			require_once($load->Panel($panel->name.'.php', $panel->name));
			$panel_content = ob_get_contents();
			ob_end_clean();
			unset($load,$debug);

			$panel_content = trim($panel_content);
			if(!$panel_content) return "";

			// Put the content into the template
			$this_template = $templates[$style];
			$this_template = PFpanel::PushTemplate($this_template, $panel, $panel_content);

			unset($panel_content);
			return $this_template;
		}
		else {
            $debug->_('v', 'PFpanel::Render - Insufficient permissions to run panel "'.$panel->name.'"');
            unset($debug);
            return "";
        }
    }

    /**
	 * Parses a panel template wrapper file and returns its contents
	 *
	 * @param     string    $file     The file to load
	 * @param     string    $theme    The theme from which to load the file
	 * @return    string    The file content
	 **/
    public function ParseTemplate($file, $theme)
    {
        // Load objects
        $load = PFload::GetInstance();
        $core = PFcore::GetInstance();

        $file = $load->FilePath($file, 'themes.'.$theme.'.html_core');

        if(!file_exists($file)) return "";

        $file = file($file);

        return implode("\n", $file);
    }

    /**
	 * Replaces the variables inside the panel template with the actual content
	 *
	 * @param     string    $template    The template html code
	 * @param     object    $panel       The panel object
	 * @param     string    $content     The panel content
	 * @return    string    The template with the variables replaced
	 **/
    public function PushTemplate($template, $panel, $content)
    {
        static $modal_loaded = 0;
        // Load objects
        $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();

        // Get settings
        $can_edit   = $user->Access('form_edit_panel', 'config');
        $show_edit  = $config->Get('panel_edit');
        $show_title = $config->Get('show_title', $panel->name);
        $new_title  = trim($config->Get('override_title', $panel->name));
        $lightbox   = (int) $config->Get('edit_lightbox');

        // Override title
        if($new_title) $panel->title = $new_title;

        // Push content to template
        $template = str_replace('{content}', $content, $template);
		$template = str_replace('{name}', $panel->name, $template);
		$template = str_replace('{title}', PFformat::Lang($panel->title), $template);

		if($can_edit && $show_edit) {
		    if(!$lightbox) {
                $return = base64_encode('index.php?'.$_SERVER['QUERY_STRING']);
                $link   = "section=config&task=form_edit_panel&id=$panel->id&return=$return";
                $edit   = '<a class="panel_edit" href="'.PFformat::Link($link).'">'.PFformat::Lang('EDIT').'</a>';
            }
            else {
                if(!$modal_loaded) {
                    JHTML::_('behavior.mootools');
                    JHTML::_('behavior.modal');
                    $modal_loaded = 1;
                }
                $link = PFformat::Link("section=config&task=form_edit_panel&id=$panel->id&render=section_ajax");
                $edit = '<a class="panel_edit modal" rel="{handler: \'iframe\', size: {x: 720, y: 480}}" href="'.$link.'">'
                        . PFformat::Lang('EDIT').'</a>';
            }


            $template = str_replace('{edit}', $edit, $template);
        }
        else {
            $template = str_replace('{edit}', '', $template);
        }

        if($show_title) {
            $template = str_replace('{show_title}', '', $template);
        }
        else {
            $template = str_replace('{show_title}', ' style="display:none"', $template);
        }

        // Unset objects
		unset($user,$config);

		// Return template
		return $template;
    }

    /**
	 * Loads panels and modules which are assigned to the given position
	 *
	 * @param     string    $position    The panel position
	 * @param     string    $pfstyle     The panel wrapper template style
	 * @param     string    $jstyle      The joomla module wrapper style
	 **/
    public function Position($position, $pfstyle = 'panel_default', $jstyle = 'xhtml')
    {
        static $ids  = array();
        static $list = NULL;

        $core   = PFcore::GetInstance();
        $config = PFconfig::GetInstance();
        $cache  = JFactory::GetCache('com_projectfork.panels');
        $lang   = PFlanguage::GetInstance();
        $user   = PFuser::GetInstance();

        $workspace = $user->Permission('workspace');
		$uid  = $user->GetId();
		$ugid = $user->GetGid();

        $panel_pos = (int) $config->Get('debug_panels');
        $can_edit  = $user->Access('form_edit_panel', 'config');
        $show_edit = $config->Get('panel_edit');

        if(is_null($list)) $list = $core->GetPanels();

        // Check caching
        $cache_panels = (int) $config->Get('cache_panels');
        if($cache_panels) {
            $cache->SetCaching(true);
            $load = PFload::GetInstance();
        }

        // Get re-route
        $reroute = PFpanel::ReroutePosition($position);

        if($reroute && array_key_exists($position, $list)) {
            $list[$reroute]  = $list[$position];
            $list[$position] = array();
        }

        // Debug panel position?
        if($panel_pos) {
            echo '<div class="debug_panel_pos">
                <span class="pf_pos">PF: '.$position.'</span>
                <span class="jos_pos">Joomla: pf_'.$position.'</span>
            </div>';
        }

        // Render joomla modules inside pf
        $attribs    = array("style" => $jstyle);
        $subModules = &JModuleHelper::getModules('pf_'.$position);

        foreach($subModules as $subModule)
        {
            JModuleHelper::renderModule($subModule, $attribs);
            echo $subModule->content;
        }
        unset($subModules);

        // Check if there are any panels on the position
        if(!array_key_exists($position, $list)) {
            $debug = PFdebug::GetInstance();
            $debug->_('w', 'PFpanel::Position - No panels found on position "'.$position.'"');

            // Debug panel close tag
            if($panel_pos) echo '</div>';

            unset($core,$debug,$list,$config,$cache,$lang);
            return false;
        }

        // Loop through the panels
        foreach($list[$position] AS $panel)
        {
            // Load language file
	        $lang->Load($panel->name, 'panel');

            $do_cache = (int) $panel->caching;

            if($cache_panels && $do_cache && $uid) {
                // Search and include no-cache file for CSS and JS files
                $load->Set404(false);
                $no_cache = $load->Panel('nocache.php', $panel->name);
                $load->Set404(true);

                if($no_cache) require_once($no_cache);

                // Call panel from cache
                $cache_key = PFformat::CacheHash($panel->cache_trigger);
                $panel_content = $cache->call(array('PFpanel','Render'), $panel, $pfstyle, $cache_key);
            }
            else {
                $panel_content = PFpanel::Render($panel, $pfstyle);
            }
            $panel_content = trim($panel_content);
            if($panel_content == "") continue;
            echo $panel_content;
            unset($panel_content);
        }

        unset($core,$list,$config,$cache,$lang);
    }

    public function CountPosition($position)
    {
        $core   = PFcore::GetInstance();
        $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();
        $list   = $core->GetPanels();

        if(!array_key_exists($position, $list)) return 0;

        $score = $user->GetScore();
        $flag  = $user->GetFlag();
        $count = 0;

        $use_score = (int) $config->Get('use_score');

        foreach($list[$position] AS $panel)
        {
            $flag_ok  = false;
		    $score_ok = false;

		    if($panel->flag != '') {
		        if($panel->flag == $flag) {
			        $flag_ok = true;
			    }
			    else {
			        if($panel->flag == 'project_administrator' && $flag == 'system_administrator') $flag_ok = true;
			    }
		    }
		    else {
			    $flag_ok = true;
		    }

    		if($score < $panel->score) {
    			if($flag == 'system_administrator') $score_ok = true;
    			if(!$use_score) $score_ok = true;
    		}
    		else {
    			$score_ok = true;
    		}

    		// Permissions are ok
    		if($score_ok && $flag_ok) $count++;
        }

        return $count;
    }

    public function ReroutePosition($from, $to = NULL)
    {
        static $reroutes = array();

        if(is_null($to) && count($reroutes)) {
            if(array_key_exists($from, $reroutes)) return $reroutes[$from];
            return NULL;
        }
        if($from && $to) $reroutes[$from] = $to;
        return NULL;
    }
}

/**
* @package       Projectfork
* @subpackage    Framework
**/
class PFprocess
{
    /**
	 * Loads a process
	 *
	 * @param     mixed    $process    Process name or object to load from
	 * @param     array    $data       Process arguments
	 **/
    public function Render($process, $data = array())
    {
        static $use_score = NULL;

        $debug = PFdebug::GetInstance();

        if(is_null($use_score)) {
            $config = PFconfig::GetInstance();
            $use_score = (int) $config->Get('use_score');
        }

        // Load process by name instead of object
        if(!is_object($process)) {
            $debug->_('n', 'PFprocess::Render - Attempting to find process by name "'.$process.'"');
            $core   = PFcore::GetInstance();
            $list   = $core->GetProcesses();
            $object = NULL;

            foreach($list as $event)
            {
                foreach($event AS $proc)
                {
                    if($proc->name == $process) $object = $proc;
                }
            }

            if(is_null($object)) {
                $debug->_('e', 'PFprocess::Render - Process "'.$process.'" not found!');
                unset($debug,$core,$list);
                return false;
            }
            $debug->_('n', 'PFprocess::Render - Process found by name "'.$process.'"');
            $process = $object;
            unset($object);
        }

		// Check permission
		$user  = PFuser::GetInstance();
        $score = $user->Permission('score');
		$flag  = $user->Permission('flag');

		$flag_ok  = false;
		$score_ok = false;

		unset($user);

		if($process->flag != '') {
		    if($process->flag == $flag) {
			    $flag_ok = true;
			}
			else {
			    if($process->flag == 'project_administrator' && $flag == 'system_administrator') $flag_ok = true;
			}
		}
		else {
			$flag_ok = true;
		}

		if($score < $process->score) {
			if($flag == 'system_administrator') $score_ok = true;
			if(!$use_score) $score = true;
		}
		else {
			$score_ok = true;
		}

		// Include if permissions are ok
		if($score_ok && $flag_ok) {
		    $debug->_('n', 'PFprocess::Render - Process permissions OK, loading process "'.$process->name.'"');
		    $load = PFload::GetInstance();
			require_once($load->Process($process->name.'.php', $process->name));
			unset($load,$debug,$lang);
			return true;
		}
		else {
            $debug->_('v', 'PFprocess::Render - Insufficient permissions to run process "'.$process->name.'"');
            unset($debug);
            return false;
        }
    }

    /**
	 * Loads processes from an event
	 *
	 * @param     string    $event    The process event
	 * @param     array     $data     Process arguments
	 **/
    public function Event($event, $data = array())
    {
        $core  = PFcore::GetInstance();
        $debug = PFdebug::GetInstance();
        $lang  = PFlanguage::GetInstance();
        $list  = $core->GetProcesses();

        $debug->_('n', 'PFprocess::Event - Calling process event "'.$event.'"');

        if(!array_key_exists($event, $list)) {
            $debug->_('w', 'PFprocess::Event - No processes found for event "'.$event.'"');
            unset($core, $debug, $list, $lang);
            return false;
        }

        foreach($list[$event] AS $proc)
        {
            if($proc->event == $event) {
                $lang->Load($proc->name, 'process');
                PFprocess::Render($proc, $data);
            }
        }

        unset($core, $debug, $list, $lang);
        return true;
    }
}

/**
* @package       Projectfork
* @subpackage    Framework
**/
class PFtheme
{
    public function ProjectColor()
    {
        static $color = NULL;

        if(is_null($color)) {
            $user = PFuser::GetInstance();
            $db   = PFdatabase::GetInstance();

            $ws = $user->GetWorkspace();

            if(!$ws) {
                $color = "";
                return "";
            }

            $query = "SELECT color FROM #__pf_projects WHERE id = '$ws'";
                   $db->setQuery($query);
                   $c = $db->loadResult();

            $colors = array('FF0000' => 'red',
		                    'FF9900' => 'orange',
                            'E9E607' => 'yellow',
		                    '0FD206' => 'green',
                            '0033FF' => 'blue',
		                    'FF00FF' => 'purple');

		    if(array_key_exists($c, $colors)) $color = $colors[$c];
            if(is_null($color)) $color = "";
        }

        return $color;
    }
}

/**
* @package       Projectfork
* @subpackage    Framework
**/
class PFcore
{
    /**
	 * Available sections
	 *
	 * @var    array
	 **/
    private $sections;

    /**
	 * The current section
	 *
	 * @var    string
	 **/
    private $section;

    /**
	 * Available section tasks
	 *
	 * @var    array
	 **/
    private $tasks;

    /**
	 * The current task
	 *
	 * @var    string
	 **/
    private $task;

    /**
	 * Available processes
	 *
	 * @var    array
	 **/
    private $processes;

    /**
	 * Available panels
	 *
	 * @var    array
	 **/
    private $panels;

    /**
	 * Available languages
	 *
	 * @var    array
	 **/
    private $languages;

    /**
	 * Available themes
	 *
	 * @var    array
	 **/
    private $themes;

    /**
	 * The current item id
	 *
	 * @var    int
	 **/
    private $itemid;

    /**
	 * Current notification messages
	 *
	 * @var    array
	 **/
    private $messages;

    /**
	 * Current redirect URL
	 *
	 * @var    string
	 **/
    private $redirect;

    /**
	 * Constructor
	 *
	 **/
	protected function __construct()
	{
	    $config = PFconfig::GetInstance();
	    $com    = Pfcomponent::GetInstance();

	    $cache_core = (int) $config->Get('cache_core');

	    if($cache_core) {
	        $cache = JFactory::getCache('com_projectfork.core');
	        $cache->setCaching(true);
            $this->sections  = $cache->call(array('PFcore','LoadSections'));
            $this->tasks     = $cache->call(array('PFcore','LoadTasks'), $this->sections);
            $this->processes = $cache->call(array('PFcore','LoadProcesses'));
            $this->languages = $cache->call(array('PFcore','LoadLanguages'));
            $this->panels    = $cache->call(array('PFcore','LoadPanels'));
            $this->themes    = $cache->call(array('PFcore','LoadThemes'));
            unset($cache);
        }
        else {
            $this->sections  = $this->LoadSections();
            $this->tasks     = $this->LoadTasks($this->sections);
            $this->processes = $this->LoadProcesses();
            $this->languages = $this->LoadLanguages();
            $this->panels    = $this->LoadPanels();
            $this->themes    = $this->LoadThemes();
        }

        $this->itemid   = (int) JRequest::getVar('Itemid', 0);
        $this->messages = $this->GetMessages(true);
        $this->section  = $this->CurrentSection();
        $this->task     = JRequest::getWord('task');
        $this->redirect = NULL;

		// Add Joomla JS if in frontend
		if($com->Get('location') == 'frontend') {
			$j_doc = JFactory::getDocument();
			$j_uri = JFactory::getURI();
			$j_doc->addScript($j_uri->base(false).'includes/js/joomla.javascript.js');
			unset($j_doc, $j_uri);
		}

		unset($com, $config);
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

        $self = new PFcore();
        return $self;
    }

    /**
	 * Loads all installed sections info
	 *
	 * @return    array     $sections    Section info
	 **/
    public function LoadSections()
    {
        $db = PFdatabase::GetInstance();
        $sections = array();

        $fields = array('id', 'name', 'title', 'enabled', 'score', 'flag', 'tags',
                        'is_default', 'ordering', 'author', 'email', 'website',
                        'version', 'license', 'copyright', 'create_date',
                        'install_date');

        $fields = implode(', ', $fields);

        $query = "SELECT $fields FROM #__pf_sections"
               . "\n ORDER BY ordering ASC";
               $db->setQuery($query);
               $data = $db->loadObjectList();

        if(!is_array($data)) $data = array();

        foreach ($data AS $section)
		{
			$sections[$section->name] = $section;
		}

        unset($db, $data);
        return $sections;
    }

    /**
	 * Loads all installed panel info
	 *
	 * @return    array     $panels    Panel info
	 **/
    public function LoadPanels()
    {
        $db = PFdatabase::GetInstance();
        $panels = array();

        $fields = array('id', 'name', 'title', 'enabled', 'score', 'flag', 'position',
                        'ordering', 'author', 'email', 'website',
                        'version', 'license', 'copyright', 'create_date',
                        'install_date', 'caching', 'cache_trigger');

        $fields = implode(', ', $fields);

        $query = "SELECT $fields FROM #__pf_panels"
               . "\n WHERE enabled = '1'"
               . "\n ORDER BY position,ordering ASC";
               $db->setQuery($query);
               $data = $db->loadObjectList();

        if(!is_array($data)) $data = array();

        foreach($data as $panel)
        {
            $pos = $panel->position;

            if(!array_key_exists($pos, $panels)) $panels[$pos] = array();

            $panels[$pos][] = $panel;
        }

        return $panels;
    }

    /**
	 * Loads all section tasks
	 *
	 * @return    array     $tasks    Section tasks
	 **/
    public function LoadTasks($sections)
    {
        $db        = PFdatabase::GetInstance();
        $tasks     = array();
		$sub_tasks = array();
		$task_map  = array();

		$fields = array('id', 'section', 'name', 'title', 'description',
                        'score', 'flag', 'tags', 'parent', 'ordering');

        $fields = implode(', ', $fields);

		$query = "SELECT $fields FROM #__pf_section_tasks";
			   $db->setQuery($query);
			   $data = $db->loadObjectList();

		if(!is_array($data)) $data = array();

		foreach ($sections AS $section => $object)
		{
			$tasks[$section]    = array();
			$task_map[$section] = array();
		}

		foreach ($data AS $i => $task)
		{
			if($task->parent != '') {
				$sub_tasks[] = $task;
			}
			else {
				$tasks[$task->section][$task->name]    = $task;
				$task_map[$task->section][$task->name] = $task->name;
			}
		}

		// inherit access settings from parent task
		foreach ($sub_tasks AS $id => $task)
		{
			$key      = $task_map[$task->section][$task->parent];
			$tmp_task = $tasks[$task->section][$key];

			$task->score = $tmp_task->score;
			$task->flag  = $tmp_task->flag;
			$task->tags  = $tmp_task->tags;

			$tasks[$task->section][$task->name] = $task;
		}

		unset($db, $data, $sub_tasks, $task_map);
		return $tasks;
    }

    /**
	 * Loads all installed process info
	 *
	 * @return    array     $processes    Processes info
	 **/
    public function LoadProcesses()
    {
        $db        = PFdatabase::GetInstance();
        $processes = array();

        $fields = array('id', 'name', 'title', 'enabled', 'score', 'flag', 'event',
                        'ordering', 'author', 'email', 'website',
                        'version', 'license', 'copyright', 'create_date',
                        'install_date');

        $fields = implode(', ', $fields);

        // Load process list
        $query = "SELECT $fields FROM #__pf_processes"
               . "\n WHERE enabled = '1'"
               . "\n ORDER BY event,ordering";
		       $db->setQuery($query);
		       $data = $db->loadObjectList();

        if(!is_array($data)) $data = array();

        foreach($data AS $process)
        {
            $key = $process->event;
            if(!array_key_exists($key, $processes)) $processes[$key] = array();
            $processes[$key][] = $process;
        }

        unset($db, $data);
        return $processes;
    }

    /**
	 * Loads all installed theme info
	 *
	 * @return    array     $themes    Theme info
	 **/
    public function LoadThemes()
    {
        $db = PFdatabase::GetInstance();
        $themes = array();
        $themes['__default'] = 'default';

        $fields = array('id', 'name', 'title', 'enabled', 'is_default',
                        'author', 'email', 'website',
                        'version', 'license', 'copyright', 'create_date',
                        'install_date');

        $fields = implode(', ', $fields);

        $query = "SELECT $fields FROM #__pf_themes";
               $db->setQuery($query);
               $data = $db->LoadObjectList();

        if(!is_array($data)) $data = array();

        foreach($data AS $theme)
        {
            if($theme->is_default == '1') $themes['__default'] = $theme->name;

            $themes[$theme->name] = $theme;
        }

        unset($db,$data);
        return $themes;
    }

    /**
	 * Loads all installed languages info
	 *
	 * @return    array     $languages    Language info
	 **/
    public function LoadLanguages()
    {
        $db        = PFdatabase::GetInstance();
        $languages = array();

        $fields = array('id', 'name', 'title', 'published', 'is_default',
                        'author', 'email', 'website',
                        'version', 'license', 'copyright', 'create_date',
                        'install_date');

        $fields = implode(', ', $fields);

        // Load language list
        $query = "SELECT $fields FROM #__pf_languages"
               . "\n WHERE published = '1'";
		       $db->setQuery($query);
		       $data = $db->loadObjectList();

        if(!is_array($data)) $data = array();

        foreach($data AS $lang)
        {
            $languages[$lang->name] = $lang;
        }

        unset($db, $data);
        return $languages;
    }

    /**
	 * Finds and returns the current section
	 *
	 * @return    string     $section    The section name
	 **/
    public function CurrentSection()
    {
        $section = JRequest::getWord('section');

        if(!$section) {
            foreach($this->sections AS $tmp)
            {
                if($tmp->is_default == '1') $section = $tmp->name;
            }
        }

        return $section;
    }

    /**
	 * Returns all messages which are saved in the session
	 *
	 * @param     boolean    $clean       If true, clears all messages
	 * @return    array      $messages    The messages
	 **/
	public function GetMessages($clean = false)
	{
	    if(is_array($this->messages)) return $this->messages;

	    $messages = array();

		if(array_key_exists('pf_messages', $_SESSION)) {
			foreach ($_SESSION['pf_messages'] AS $message)
			{
				$message = strip_tags($message);
				$message = htmlspecialchars($message, ENT_QUOTES);
				$messages[] = $message;
			}
		}

		if($clean) $_SESSION['pf_messages'] = array();

		return $messages;
	}

	/**
	 * Creates a link
	 *
	 * @param    string     $l              The base link
	 * @param    boolean    $amp_replace    If true, masks all ampersands
	 * @param    boolean    $sef            If true, takes SEF into account
	 * @param    boolean    $sess_hash      If true, puts any ID hash into the session, otherwise adds it to the link
	 * @return   string     $link           The final link
	 **/
	public function Link($l = '', $amp_replace = true, $sef = true, $sess_hash = true)
	{
	    static $base_url  = NULL;
	    static $com_name  = NULL;
	    static $com_loc   = NULL;
	    static $workspace = NULL;
	    static $pnames    = array();

	    // Check static vars
        if(is_null($base_url) || is_null($com_name) || is_null($com_loc)) {
            $com = PFcomponent::GetInstance();
            $com_loc  = $com->Get('location');
            $com_name = $com->Get('name');
            if($com_loc == 'frontend') $base_url = $com->Get('url_root');
            if($com_loc == 'backend')  $base_url = $com->Get('url_root').'administrator/';
            unset($com);
        }
        if(is_null($workspace)) {
            $user = PFuser::GetInstance();
            $workspace = (int) $user->Permission('workspace');
            unset($user);
        }

        if($sef) {
           $link = 'index.php?option='.$com_name;
        }
        else {
            $link = $base_url.'index.php?option='.$com_name;
        }

        $parts  = explode('&', $l);
        $id     = 0;
        $ws     = 0;
        $s      = '';
        $t      = '';
        $append = '';
        $ws_isset = false;

        foreach($parts AS $part)
        {
            $var = explode('=', $part);
            if(count($var) != 2) continue;

            switch($var[0])
            {
                case 'workspace': $ws = intval($var[1]); $ws_isset = true; break;
                case 'section':   $s  = strval($var[1]); break;
                case 'task':      $t  = strval($var[1]); break;
                case 'id':        $id = intval($var[1]); break;
                default:
                    $append .= '&'.$var[0].'='.$var[1];
                    break;
            }
        }

        // Fix workspace
        if(!$ws && !$ws_isset) $ws = $workspace;

        // Append workspace
        if($ws) $link .= "&workspace=".$ws;
        if(!$ws && $ws_isset) $link .= "&workspace=".$ws;

        // Append section
        if($s) $link .= "&section=".$s;

        // Append task
        if($t) $link .= "&task=".$t;

        // Append id
        if($id) $link .= "&id=".$id;

        // Add id hash to session
        if($id) {
            if($sess_hash) {
                $id_hash = PFformat::IdHash($id, $s);
                if(!array_key_exists('pf_hashes', $_SESSION)) $_SESSION['pf_hashes'] = array();
                if(!in_array($id_hash, $_SESSION['pf_hashes'])) $_SESSION['pf_hashes'][] = $id_hash;
            }
            else {
                $link .= "&".PFformat::IdHash($id, $s)."=1";
            }
        }

        // Add the rest
        if($append) $link .= $append;

        // Append Itemid
	    if($com_loc == 'frontend') {
	    	$link = $link."&Itemid=".$this->itemid;

            if($sef) {
                $link = JRoute::_($link, $amp_replace);
            }
            else {
                if($amp_replace == true) $link = str_replace("&", "&amp;", $link);
            }
	    }
	    else {
            // Mask Ampersand
		    if($amp_replace == true) $link = str_replace("&", "&amp;", $link);
        }

        unset($parts,$append);

		return $link;
	}

	/**
	 * Redirects the browser to the target URL
	 *
	 * @param    string     $link    The target URL
	 * @param    mixed      $msg     One or multiple messages to appear after the redirect
	 **/
	public function Redirect($link = '', $msg = NULL)
	{
	    $jversion = new JVersion();
	    $return   = JRequest::getVar('return', '', 'POST');
        $render   = JRequest::getVar('render');

        if($render) return "";
		if(!$link) $link = $this->redirect;

        if($return) {
            $link = base64_decode($return);
        }
        else {
            $link = $this->Link($link, false);
        }

		$link = str_replace("&amp;", "&", $link);

		if(!is_null($msg)) $this->addMessage($msg);

	    if($jversion->RELEASE == '1.5') {
            global $mainframe;
            $mainframe->redirect($link);
		    $mainframe->close();
        }
        else {
            $app = JFactory::getApplication();
			$app->redirect($link);
        }
	}

	/**
	 * Sets the a location to redirect to when the PF script reaches the end
	 *
	 * @param    string     $link    The target URL
	 * @param    mixed      $msg     One or multiple messages to appear after the redirect
	 **/
	public function SetRedirect($link, $msg = NULL)
	{
		$this->redirect = $link;
		if(!is_null($msg)) $this->AddMessage($msg);

		return true;
	}

	/**
	 * Clears any redirect that has been set via SetRedirect
	 *
	 * @return    boolean   True
	 **/
	public function ClearRedirect()
	{
        $this->redirect = NULL;

        return true;
    }

	/**
	 * Adds one or multiple messages which will appear after a page redirect
	 *
	 * @param    mixed      $msg     One or multiple messages
	 **/
	public function AddMessage($msg)
	{
	    if(is_array($msg)) {
            foreach($msg AS $m)
            {
                if(!$m) continue;
                $_SESSION['pf_messages'][] = $m;
            }
        }
        else {
            if($msg) $_SESSION['pf_messages'][] = $msg;
        }

		return true;
	}

	/**
	 * Clears all messages
	 *
	 **/
	public function ClearMessages()
	{
		$_SESSION['pf_message'] = array();
		$this->messages         = array();
	}

	/**
	 * Returns all sections
	 *
	 * @return    array    $sections
	 **/
	public function GetSections()
	{
        return $this->sections;
    }

    /**
	 * Returns the current section
	 *
	 * @return    string    $section
	 **/
    public function GetSection()
	{
        return $this->section;
    }

    /**
	 * Returns a specific section object
	 *
	 * @return    object
	 **/
    public function GetSectionObject($section = NULL)
    {
        if(is_null($section)) $section = $this->GetSection();

        return $this->sections[$section];
    }

    /**
	 * Returns all section tasks
	 *
	 * @return    array
	 **/
    public function GetTasks()
	{
        return $this->tasks;
    }

    /**
	 * Returns all section tasks
	 *
	 * @return    array
	 **/
    public function GetTask()
	{
        return $this->task;
    }

    /**
	 * Returns all registered languages
	 *
	 * @return    array
	 **/
    public function GetLanguages()
    {
        return $this->languages;
    }

    /**
	 * Returns all registered processes
	 *
	 * @return    array
	 **/
    public function GetProcesses()
    {
        return $this->processes;
    }

    /**
	 * Returns all registered panels
	 *
	 * @return    array
	 **/
    public function GetPanels()
    {
        return $this->panels;
    }

    /**
	 * Returns all registered themes
	 *
	 * @return    array
	 **/
    public function GetThemes()
    {
        return $this->themes;
    }

    /**
	 * Returns the current theme
	 *
	 * @return    object
	 **/
    public function GetTheme()
    {
        return $this->themes['__default'];
    }

    /**
	 * Returns all current item id
	 *
	 * @return    array
	 **/
    public function GetItemid()
	{
        return $this->itemid;
    }

    /**
	 * Checks whether a redirect is set or not
	 *
	 * @return    boolean
	 **/
    public function HasRedirect()
    {
        return (is_null($this->redirect)) ? false : true;
    }
}
?>