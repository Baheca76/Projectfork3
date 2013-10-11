<?php
/**
* $Id: utilities.php 929 2013-04-06 01:07:07Z eaxs $
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

class PFformat
{

	public function AddOffset($timestamp)
	{
		$jversion = new JVersion();
		if($jversion->RELEASE != '1.5') {
			$conf =& JFactory::getConfig();
			$offset = $conf->getValue('config.offset');

			$instance =& JFactory::getDate($timestamp, $offset);
			$instance->setOffset($offset);
			$offset =  $instance->getOffsetFromGMT();
			$timestamp = $timestamp + $offset;
		}

		return $timestamp;
	}

	public function JhtmlCalendarDateFormat()
	{
		$formatted_date = 'Y-m-d';
		$jversion = new JVersion();
		if($jversion->RELEASE == '1.5') {
			$formatted_date = '%Y-%m-%d';
		}

		return $formatted_date;
	}

    public function ToDate($timestamp = 0)
    {
        static $format = NULL;

        $timestamp = (int) $timestamp;
        $jversion  = new JVersion();

		if(is_null($format)) {
            $config = PFconfig::GetInstance();
            $format = $config->Get('date_format');
            unset($config);
        }

		if($jversion->RELEASE == '1.5') {
			$d = strftime($format, $timestamp);

            return JHTML::date($d, $format);
		}
		else {
            return JHTML::date($timestamp, $format, null, false);
		}
    }

    public function ToTime($date, $hour = 0, $min = 0, $ampm = 0)
    {
        $config = PFconfig::GetInstance();

        if($config->Get('12hclock')) {
            if($ampm == 1) {
				$hour = ($hour == 12) ? $hour = 12 : $hour + 12;
			}
			else {
				$hour = ($hour == 12) ? $hour = 0 : $hour;
			}
            return strtotime($date." $hour:$min:00");
        }
        else {
            return strtotime($date." $hour:$min:00");
        }
    }

    public function IdHash($id, $section = NULL)
    {
	    static $j_secret  = NULL;
	    static $default_section = NULL;

	    // Check static vars
        if(is_null($j_secret)) {
            $config   = JFactory::getConfig();
            $j_secret = $config->getValue('secret');
            unset($config);
        }
        if(is_null($default_section)) {
            $core = PFcore::GetInstance();
            $default_section = $core->GetSection();
            unset($core);
        }

        if(is_null($section)) $section = $default_section;

        // Make hash
        $hash = md5($section.$id.$j_secret);

        return $hash;
    }

    public function CacheHash($trigger = "")
    {
        static $location = NULL;

        if(is_null($location)) {
            $com = PFcomponent::GetInstance();
            $location = $com->Get('location');
            unset($com);
        }

        $trigger = trim($trigger);
        $hash    = $location;

        if($trigger == "") return $hash;

        $user    = PFuser::GetInstance();
        $core    = PFcore::GetInstance();
        $id      = (int) JRequest::GetVar('id');

        $triggers = explode(',', $trigger);
        sort($triggers, SORT_STRING);

        foreach($triggers AS $part)
        {
            $part = strtolower(trim($part));
            $default = JRequest::GetVar($part);

            switch($part)
            {
                case 'project': $hash .= $user->GetWorkspace(); break;
                case 'user_id': $hash .= $user->GetId();        break;
                case 'item_id': if($id) { $hash .= $id; }       break;
                case 'section': $hash .= $core->GetSection();   break;
                case 'task':    $hash .= $core->GetTask();      break;
                default: if($default) {$hash .= JRequest::GetVar($part);} break;
            }
        }

        $hash = md5($hash);

        unset($user,$core);
        return $hash;
    }

    public function Link($link = "" , $amp_replace = true, $sef = true, $sess_hash = true)
    {
        static $core = NULL;

        if(is_null($core)) $core = PFcore::GetInstance();

        return $core->Link($link, $amp_replace, $sef, $sess_hash);
    }

    public function Lang($string)
    {
        static $lang = NULL;

        if(is_null($lang)) $lang = PFlanguage::GetInstance();

        return $lang->_($string);
    }

    public function Tag($tag = '')
    {

    }

    public function WorkspaceTitle($tag = 'span', $global = true, $linked = true)
    {
        $user = PFuser::GetInstance();
        $ws   = $user->GetWorkspace();

        $access = $user->Access('display_details', 'projects');

        if($ws) {
            $db = PFdatabase::GetInstance();
			$ls = "";
			$le = "";

            $query = "SELECT title,color FROM #__pf_projects WHERE id = '$ws'";
			       $db->setQuery($query);
			       $row = $db->loadObject();

			if(is_object($row)) {
				$color = 'class="ws_title"';

				$ls = "<a href=\"".PFformat::Link("section=projects&task=display_details&id=$ws")."\" $color>";
				$le = "</a>";
				if(!$access) {
					$ls = "";
					$le = "";
				}
				unset($db,$user);
				return "<$tag class=\"workspace_title\">".$ls.htmlspecialchars($row->title).$le."</$tag>";
			}
		}

		if($global) {
		    unset($user);
			return "<$tag class=\"workspace_title\">".PFformat::Lang('GLOBAL')."</$tag>";
		}

		return "";
    }

    public function SectionEditButton($section = NULL)
    {
        static $lightbox = NULL;
        static $access   = NULL;
        static $modal_loaded = 0;

        if(is_null($lightbox)) {
            $config = PFconfig::GetInstance();
            $lightbox = (int) $config->Get('edit_lightbox');
            unset($config);
        }

        if(is_null($access)) {
            $user = PFuser::GetInstance();
            $access = $user->Access('form_edit_section', 'config');
            unset($user);
        }

        if(!$access) return "";

        $core = PFcore::GetInstance();
        $sobj = $core->GetSectionObject($section);
        unset($core);

        if($lightbox) {
            if(!$modal_loaded) {
                JHTML::_('behavior.mootools');
                JHTML::_('behavior.modal');
                $modal_loaded = 1;
            }

            $link = PFformat::Link("section=config&task=form_edit_section&&rts=1&id=$sobj->id&render=section_ajax");
            return '<a class="section_edit modal" rel="{handler: \'iframe\', size: {x: 720, y: 480}}" href="'.$link.'">'
                  . '<span>'.PFformat::Lang('QL_CONFIG_SECTION').'</span></a>';
        }
        else {
            return '<a href="'.PFformat::Link("section=config&task=form_edit_section&&rts=1&id=$sobj->id").'" class="section_edit">'
                  .'<span>'.PFformat::Lang('QL_CONFIG_SECTION').'</span></a>';
        }
    }
}

class PFcache
{
    public function Clean($key = NULL)
    {
        if(is_null($key)) {
            $cache = JFactory::getCache('com_projectfork.user');
            $cache->clean();
            $cache = JFactory::getCache('com_projectfork.mods');
            $cache->clean();
            $cache = JFactory::getCache('com_projectfork.core');
            $cache->clean();
            $cache = JFactory::getCache('com_projectfork.panels');
            $cache->clean();
            unset($cache);
            return true;
        }

        $cache = JFactory::getCache('com_projectfork.'.$key);
        $cache->clean();
        unset($cache);
        return true;
    }
}


class PFform
{
    private $name;
    private $action;
    private $method;
    private $attributes;
    private $fields;
    private $return;
    private $render;
    private $itemid;
    private $bind;
    private $bind_data;
    private $esc_value;

    public function __construct($n = NULL, $act = NULL, $method = NULL, $attributes = NULL)
    {
        $this->name   = (is_null($n)) ? 'adminForm' : $n;
        $this->action = (is_null($act)) ? 'index.php' : $act;
        $this->method = (is_null($method)) ? 'post' : $method;
        $this->attributes = $attributes;

        $this->return = JRequest::getVar('return', NULL, 'GET');
        $this->render = JRequest::getVar('render');
        $this->itemid = (int) JRequest::GetVar('Itemid', 0);

        $this->bind      = false;
        $this->bind_data = NULL;

        $this->esc_value = true;

        if($this->action == 'index.php') {
            $uri = JFactory::getURI();
            $this->action = $uri->toString();
            unset($uri);
        }
    }

    public function SetBind($state = false, $data = NULL)
	{
        $this->bind = $state;
        $this->bind_data = $data;
    }

    public function SetEscape($state)
    {
        $this->esc_value = $state;
    }

    public function Start()
	{
	    $attr = (is_null($this->attributes)) ? '' : ' '.$this->attributes;
	    return '<form name="'.$this->name.'" action="'.$this->action.'" method="'.$this->method.'"'.$attr.'>';
	}

	public function End()
	{
        $html = "";
        if($this->itemid) $html .= $this->HiddenField('Itemid', $this->itemid);
        if($this->return) $html .= $this->HiddenField('return', $this->return);
        if($this->render) $html .= $this->HiddenField('render', $this->render);
		$html.= "</form>";
        return $html;
	}

	public function InputField($name, $value = '', $attributes = '')
	{
        $class = "";
		if(strstr($name, '*')) {
			$name  = str_replace('*', '', $name);
			$class = " class='required'";
		}

		$value = $this->BindField( $name, $value );
		if($this->esc_value) $value = htmlspecialchars($value, ENT_QUOTES);

		return '<input type="text" name="'.$name.'" value="'.$value.'"'.$class.' '.$attributes.'/>';
    }

    function FileField($name, $value = '', $attributes = '')
	{
		$class = "";
		if(strstr($name, '*')) {
			$name = str_replace('*', '', $name);
			$class = " class='required'";
		}

        return '<input type="file" name="'.$name.'" value="'.$value.'"'.$class.' '.$attributes.'/>';
	}

	public function HiddenField($name, $value = '', $id = '')
	{
	    $value = $this->BindField( $name, $value );

	    if($id) $id = ' id="'.$id.'"';

	    if($name == 'id' && $value) {
	        $idhash = '<input type="hidden" name="'.PFformat::IdHash($value).'" value="1" />';
	    }
	    else {
            $idhash = "";
        }

        if($this->esc_value) $value = htmlspecialchars($value, ENT_QUOTES);

        return '<input type="hidden" name="'.$name.'" value="'.$value.'"'.$id.'/>'.$idhash;
    }

    public function PasswordField($name, $value = '', $id = '')
    {
        $class = "";
		if(strstr($name, '*')) {
			$name  = str_replace('*', '', $name);
			$class = " class='required'";
		}

		$value = $this->BindField( $name, $value );

		if($this->esc_value) $value = htmlspecialchars($value, ENT_QUOTES);

		return '<input type="password" name="'.$name.'" value="'.$value.'"'.$class.'/>';
    }

    public function TextArea($name, $value = '', $cols = 70, $rows = 30, $id = '')
    {
        if($id) $id = ' id="'.$id.'"';

		$this->BindField( $name, $value );

		if($this->esc_value) $value = htmlspecialchars($value, ENT_QUOTES);

		return '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'"'.$id.'>'.$value.'</textarea>';
    }

    public function Label( $text, $id = NULL)
	{
		if($id) $id = ' id="'.$id.'"';

		return '<span class="pf_label"'.$id.'>'.$text.'</span>';
	}

	public function NavButton( $label, $link, $tooltip = NULL, $task = NULL, $section = NULL, $hide = false)
	{
		$config = PFconfig::GetInstance();
		$showtips =  (int) $config->Get('tooltip_help');

	    static $user = NULL;

	    if(is_null($user)) $user = PFuser::GetInstance();

		if(stristr($link, 'javascript') || stristr($link, '#')) {
			$link = " href=\"$link\"";
		}
		else {
			$link = PFformat::Link($link);
			$link = " href='$link'";
		}

		if(!is_null($tooltip) && $showtips) {
            $class = ' hasTip';
            $title = ' title="::'.PFformat::Lang($tooltip).'::"';
        }
        else {
            $class = '';
            $title = '';
        }

        // Permission check
        if(!is_null($task) || !is_null($section)) {
            if(!$user->Access($task, $section)) {
                if($hide) return "";
                return '<a class="pf_nav_gray'.$class.'"'.$title.'><span>'.PFformat::Lang($label).'</span></a>';
            }
        }

		return '<a class="pf_nav'.$class.'"'.$link.$title.'><span>'.PFformat::Lang($label).'</span></a>';
	}

	public function SelectList($name, $data, $preselect = NULL, $params = '', $field = 'value', $val = 'value')
	{
        $preselect = $this->BindField( $name, $preselect );

        if($params) $params = ' '.$params;

        $html = '<select name="'.$name.'"'.$params.'>';

        foreach($data AS $value => $label)
        {
            $selected = "";
            if($field == 'value' && $preselect == $value) $selected = ' selected="selected"';
            if($field == 'label' && $preselect == $label) $selected = ' selected="selected"';

            if($val == 'value') $html .= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';
            if($val == 'label') $html .= '<option value="'.$label.'"'.$selected.'>'.$label.'</option>';
        }

        $html .= '</select>';

        return $html;
    }

    public function SelectHour($name, $preselect = NULL, $params = "")
    {
        $config = PFconfig::GetInstance();

        if($config->Get('12hclock')) {
            $hours = array('00','01','02','03','04','05','06','07','08','09','10','11','12');
            if($preselect > 12) $preselect = $preselect - 12;
        }
        else {
            $hours = array('00','01','02','03','04','05','06','07','08','09','10','11','12',
		             '13','14','15','16','17','18','19','20','21','22','23');
        }

		return $this->SelectList($name, $hours, $preselect, $params, 'label');
    }

    public function SelectAmPm($name, $preselect = NULL, $params = "")
    {
        $config = PFconfig::GetInstance();

        if(!$config->Get('12hclock')) return "";

        $rows = array(0 => 'AM', 1 => 'PM');

        return $this->SelectList($name, $rows, $preselect, $params);
    }

    public function SelectMonth($name, $preselect = NULL, $params = "")
    {
        $months = array('1' => PFformat::Lang('JANUARY'), '2' => PFformat::Lang('FEBRUARY'),
                        '3' => PFformat::Lang('MARCH'), '4' => PFformat::Lang('APRIL'),
		                '5' => PFformat::Lang('MAY'), '6' => PFformat::Lang('JUNE'),
		                '7' => PFformat::Lang('JULY'), '8' => PFformat::Lang('AUGUST'),
		                '9' => PFformat::Lang('SEPTEMBER'), '10' => PFformat::Lang('OCTOBER'),
		                '11' => PFformat::Lang('NOVEMBER'), '12' => PFformat::Lang('DECEMBER'));

		return $this->SelectList($name, $months, $preselect, $params);
    }

    public function SelectMinute($name, $preselect = NULL, $params = "")
    {
        $minutes = array('00','05','10','15','20','25','30','35','40','45','50','55');

        return $this->SelectList($name, $minutes, $preselect, $params, 'label', 'label');
    }

    public function SelectNY($name, $preselect = NULL, $params = "")
	{
		$data = array(0 => PFformat::Lang('PFL_NO'), 1 => PFformat::Lang('PFL_YES'));

		return $this->SelectList($name, $data, $preselect, $params);
	}

	public function SelectTaskAssigned($name, $preselect = NULL, $params = "")
	{
        $data = array('1' => PFformat::Lang('EVERYONE'), '2' => PFformat::Lang('ME'));

        return $this->SelectList($name, $data, $preselect, $params);
    }

    public function SelectTaskStatus($name, $preselect = NULL, $params = "")
    {
        $data = array('0' => PFformat::Lang('SELECT_STATUS'), '1' => PFformat::Lang('INCOMPLETE'), '2' => PFformat::Lang('COMPLETE'));

        return $this->SelectList($name, $data, $preselect, $params);
    }

    public function SelectPriority($name, $preselect = NULL, $params = "")
    {
        $data = array('0' => PFformat::Lang('SELECT_PRIORITY'), '1' => PFformat::Lang('PRIO_VERY_LOW'), '2' => PFformat::Lang('PRIO_LOW'),
		              '3' => PFformat::Lang('PRIO_MEDIUM'), '4' => PFformat::Lang('PRIO_HIGH'), '5' => PFformat::Lang('PRIO_VERY_HIGH'));

		return $this->SelectList($name, $data, $preselect, $params);
    }

	public function SelectLanguage($name, $preselect = NULL, $params = "")
	{
	    static $langs = NULL;

	    if(is_null($langs)) {
            $core  = PFcore::GetInstance();
            $tmp   = $core->GetLanguages();
            $langs = array();

            foreach($tmp AS $row)
            {
                $langs[$row->name] = $row->title;
            }
            unset($core,$tmp);
        }

        return $this->SelectList($name, $langs, $preselect, $params);
    }

	public function SelectColor($name, $preselect = NULL, $params = "")
	{
        $colors = array('' => PFformat::Lang('PFL_NONE'),
                        'FF0000' => PFformat::Lang('RED'),
		                'FF9900' => PFformat::Lang('ORANGE'),
                        'E9E607' => PFformat::Lang('YELLOW'),
		                '0FD206' => PFformat::Lang('GREEN'),
                        '0033FF' => PFformat::Lang('BLUE'),
		                'FF00FF' => PFformat::Lang('PURPLE')
                        );

		return $this->SelectList($name, $colors, $preselect, $params);
    }

    public function SelectProgress($name, $preselect = NULL, $params = "")
    {
        $progress = array('0','5', '10', '15', '20', '25', '30', '35', '40', '45', '50',
		                  '55', '60', '65', '70', '75', '80', '85', '90', '95', '100');

		return $this->SelectList($name, $progress, $preselect, $params, 'label', 'label');
    }

    public function SelectTask($name, $preselect = NULL, $params = "")
    {
        $user   = PFuser::GetInstance();
        $config = PFconfig::GetInstance();
        $db     = PFdatabase::GetInstance();
        $lang   = PFlanguage::GetInstance();

		$ws     = $user->GetWorkspace();
		$use_ms = (int) $config->Get('use_milestones', 'tasks');

		if(!$ws) {
		    unset($user,$config,$db,$lang);
			return false;
		}

		if($params) $params = ' '.$params;

        $preselect = $this->BindField($name, $preselect);

        $html = '<select name="'.$name.'"'.$params.'>'
              . '<option value="0">'.$lang->_('SELECT_TASK').'</option>';

		if(!$use_ms) {
			$query = "SELECT id, title FROM #__pf_tasks"
                   . "\n WHERE project = '$ws' ORDER BY title ASC";
		           $db->setQuery($query);
		           $rows = $db->loadObjectList();

		    if(!is_array($rows)) $rows = array();

		    foreach ($rows AS $row)
		    {
			    $selected = '';
			    if($preselect == $row->id) $selected = ' selected="selected"';

			    $html .= "<option value='$row->id'$selected>".htmlspecialchars($row->title)."</option>";
		    }
		}
		else {
			$query = "SELECT t.id, t.title, t.milestone, m.id AS ms_id, m.title AS ms_title FROM #__pf_tasks AS t"
			       . "\n LEFT JOIN #__pf_milestones AS m ON m.id = t.milestone"
			       . "\n WHERE t.project = '$ws'"
			       . "\n ORDER BY ms_id, t.title ASC";
			       $db->setQuery($query);
		           $rows = $db->loadObjectList();

		    if(!is_array($rows)) $rows = array();

		    $last_ms = 0;
            $total   = count($rows);
		    foreach ($rows AS $i => $row)
		    {
		        $mstitle = htmlspecialchars($row->ms_title);
		        $ttitle  = htmlspecialchars($row->title);

                // pre-select?
                $selected = '';
                if($preselect == $row->id) $selected = ' selected="selected"';
                // Start optgroup ?
		    	if($i == 0) $html .= '<optgroup label="'.$lang->_('UNCATEGORIZED').'">';
		    	// New optgroup?
		    	if($row->ms_id != $last_ms) $html .= '</optgroup><optgroup label="'.$mstitle.'">';
			    // Option
			    $html .= '<option value="'.$row->id.'"'.$selected.'>'.$ttitle.'</option>';

                $last_ms = $row->ms_id;
                if($i+1 == $total) $html .= "</optgroup>\n";
		    }
		}
		$html .= "</select>";

		unset($user,$config,$db,$lang);
		return $html;
    }

    public function SelectMilestone($name, $preselect = NULL, $params = '')
    {
        $user = PFuser::GetInstance();
        $db   = PFdatabase::GetInstance();

        $project = $user->GetWorkspace();

        if(!$project) $project = implode(',', $user->Permission('projects'));
        $syntax = (strlen($project) > 1) ? "project IN($project)" : "project = '$project'";

        $query = "SELECT id, title FROM #__pf_milestones"
               . "\n WHERE $syntax"
               . "\n ORDER BY title ASC";
               $db->setQuery($query);
               $rows = $db->loadObjectList();

        $milestones = array('0' => PFformat::Lang('SELECT_MILESTONE'));

        if(!is_array($rows)) $rows = array();

        foreach($rows AS $row)
        {
            JFilterOutput::objectHTMLSafe($row);
            $milestones[$row->id] = $row->title;
        }

        unset($user,$db,$rows);
        return $this->SelectList($name, $milestones, $preselect, $params);
    }

	public function SelectUser($name, $preselect = NULL, $params = '', $global = false, $field = 'id')
	{
        $user    = PFuser::GetInstance();
        $db      = PFdatabase::GetInstance();
        $project = $user->GetWorkspace();

		if(!$project) {
			$project = $user->Permission('projects');
			$project = implode(',', $project);
		}

        if($global) {
            $query = "SELECT u.id, u.name, u.username FROM #__users AS u"
		           . "\n GROUP BY u.id"
		           . "\n ORDER by u.name,u.username ASC";
        }
        else {
            $query = "SELECT u.id, u.name, u.username FROM #__users AS u"
		           . "\n RIGHT JOIN #__pf_project_members AS pm ON pm.user_id = u.id AND pm.project_id IN($project)"
		           . "\n GROUP BY u.id"
		           . "\n ORDER by u.name,u.username ASC";
        }
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if(!is_array($rows)) $rows = array();
        if($params) $params = ' '.$params;

        $preselect = $this->BindField($name, $preselect);

        $html = '<select name="'.$name.'"'.$params.'>'
              . '<option value="0">'.PFformat::Lang('PFL_NONE').'</option>';

		foreach ($rows AS $i => $row)
		{
			$s = '';
			if(!$row->id) continue;
			if( $preselect == $row->$field) $s = ' selected="selected"';
			$usr_name = htmlspecialchars($row->name." ($row->username)");

            $html .= '<option value="'.$row->$field.'"'.$s.'>'.$usr_name.'</option>';
		}

		$html .= "</select>";

        unset($db,$user,$rows);
		return $html;
	}

	public function SelectGroup($name, $preselect = NULL, $p = '', $project = 0)
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();

		$ws = (int) $user->GetWorkspace();

		if(!$project) {
			if($ws) {
			    $projects = $ws;
		    }
		    else {
			    $projects = $user->Permission('projects');
			    $projects = implode(',', $projects);
		    }
		}
		else {
			$projects = $project;
		}

		if(!$projects) {
			$rows = array();
		}
		else {
			$query = "SELECT g.id,g.title, p.title AS project_title FROM #__pf_groups AS g"
			       . "\n RIGHT JOIN #__pf_projects AS p ON p.id = g.project"
			       . "\n WHERE g.project IN($projects)"
			       . "\n GROUP BY g.id ORDER BY project_title, g.title ASC";
			       $db->setQuery($query);
		           $rows = $db->loadObjectList();
		}


		if(!is_array($rows)) $rows = array();

		$h = "<select  name='$name' $p>";

		if($preselect == -1) $h .= "<option value='0'>".PFformat::Lang('SELECT_GROUP')."</option>";

		$preselect = $this->BindField( $name, $preselect );
        $total = count($rows);
		$current_project = "";
		foreach ($rows AS $i => $row)
		{
			if(!$current_project) $h .= "<optgroup label='".htmlspecialchars($row->project_title)."'>";

			if($current_project && ($current_project != $row->project_title)) {
		    	$h .= "<optgroup label='".htmlspecialchars($row->project_title)."'>";
			}

			$s = '';
			if( $preselect == $row->id) $s = "selected='selected'";

			$h .= "<option value='$row->id' $s>".htmlspecialchars($row->title)."</option>";

			$current_project = $row->project_title;
			if($current_project != $row->project_title || $total == ($i+1)) {
		    	$h .= "</optgroup>";
		    }
		}

		$h .= "</select>";

		return $h;
    }

    public function SelectJoomlaGroup($name, $preselect = NULL, $params = '')
    {
        $db = PFdatabase::GetInstance();
        $jversion = new JVersion();

        if($jversion->RELEASE == '1.5') {
            $query = "SELECT id AS value, name AS text"
		           . "\n FROM #__core_acl_aro_groups"
		           . "\n WHERE name != 'ROOT'"
		           . "\n AND name != 'USERS'";
		           $db->setQuery($query);
                   $rows = $db->loadObjectList();

            if(!is_array($rows)) $rows = array();

            $h = "<select name='$name' $params>";

            if($preselect == -1) {
			    $h .= "<option value='0'>".PFformat::Lang('SELECT_JGROUP')."</option>";
		    }

            $preselect = $this->BindField( $name, $preselect );

            foreach( $rows as $obj )
    		{
    			$s = "";
    			if($preselect == $obj->value) $s = "selected='selected'";

    			$h .= "<option value='$obj->value' $s>".htmlspecialchars($obj->text)."</option>";
    		}

    		$h .= "</select>";

    		return $h;
        }
        else {
            if(!is_array($preselect)) $preselect = array();

            $h = "<script>
                    window.addEvent('domready', function(){
                	document.id('user-groups').getElements('input').each(function(i){
                		// Event to check all child groups.
                		i.addEvent('check', function(e){
                			// Check the child groups.
                			document.id('user-groups').getElements('input').each(function(c){
                				if (this.getProperty('rel') == c.id) {
                					c.setProperty('checked', true);
                					c.setProperty('disabled', true);
                					c.fireEvent('check');
                				}
                			}.bind(this));
                		}.bind(i));

                		// Event to uncheck all the parent groups.
                		i.addEvent('uncheck', function(e){
                			// Uncheck the parent groups.
                			document.id('user-groups').getElements('input').each(function(c){
                				if (c.getProperty('rel') == this.id) {
                					c.setProperty('checked', false);
                					c.setProperty('disabled', false);
                					c.fireEvent('uncheck');
                				}
                			}.bind(this));
                		}.bind(i));

                		// Bind to the click event to check/uncheck child/parent groups.
                		i.addEvent('click', function(e){
                			// Check the child groups.
                			document.id('user-groups').getElements('input').each(function(c){
                				if (this.getProperty('rel') == c.id) {
                					c.setProperty('checked', true);
                					if (this.getProperty('checked')) {
                						c.setProperty('disabled', true);
                					} else {
                						// If there are no other siblings checked, set the parent enabled
                						var tmp = false;
                						document.id('user-groups').getElements('input[rel='+c.getProperty('id')+']').each(function(d){
                							if(d.getProperty('checked')){
                								tmp=true;
                							}
                						});
                						c.setProperty('disabled', tmp);
                					}
                					c.fireEvent('check');
                				}
                			}.bind(this));

                			// Uncheck the parent groups.
                			document.id('user-groups').getElements('input').each(function(c){
                				if (c.getProperty('rel') == this.id) {
                					c.setProperty('checked', false);
                					c.setProperty('disabled', false);
                					c.fireEvent('uncheck');
                				}
                			}.bind(this));
                		}.bind(i));

                		// Initialise the widget.
                		if (i.getProperty('checked')) {
                			i.fireEvent('click');
                		}
                	});
                });
             </script>";

             $h .= '<div id="user-groups">'.JHtml::_('access.usergroups', 'jform[groups]', array_keys($preselect))."</div>";
             return $h;
        }
    }

    public function SelectAccessLevel($name, $preselect = 0, $params = '', $project = 0)
    {
        $db     = PFdatabase::GetInstance();
        $user   = PFuser::GetInstance();

		if(!$project) {
			$project = $user->GetWorkspace();

			if(!$project) {
				$project = implode(',', $user->Permission('projects'));
			}
		}

		$filter = "\n WHERE project IN($project)";

		$query = "SELECT id, title FROM #__pf_access_levels $filter ORDER BY title ASC";
		       $db->setQuery($query);
		       $accesslevels = $db->loadObjectList();

		if(!is_array($accesslevels)) $accesslevels = array();

		$h = '<select name="'.$name.'" '.$params.'>';

		if($preselect == -1) $h .= "<option value='-1'>".PFformat::Lang('SELECT_ACCESSLEVEL')."</option>";

		$preselect = $this->BindField($name, $preselect);

		if($preselect == 0 && is_null(JRequest::getVar($name)) && $this->bind_data == 'REQUEST') {
			$ps = -1;
		}

		foreach($accesslevels AS $i => $lvl)
		{
			JFilterOutput::objectHTMLSafe($lvl);
			$s = '';
			if( $preselect == $lvl->id ) $s = "selected='selected'";

			$h .= "<option value='$lvl->id' $s>".PFformat::Lang($lvl->title)."</option>";
		}
		$h .= "</select>";

		return $h;
    }

	public function SelectPanelPosition($name, $preselect = NULL, $params = '')
    {
        $db = PFdatabase::GetInstance();

        $query = "SELECT position FROM #__pf_panels"
               . "\n GROUP by position"
               . "\n ORDER by position ASC";
		       $db->setQuery($query);
		       $rows = $db->loadResultArray();

		if(!is_array($rows)) $rows = array();

		$html = '<select name="'.$name.'" '.$params.'>
                 <option value="0">'.PFformat::Lang('SELECT_POSITION').'</option>';

		$preselect = $this->BindField($name, $preselect);

		$current_pos = "";
        $total = count($rows);
		foreach ($rows AS $i => $position)
		{
			$part = explode('_', $position);
			if($part[0] != $current_pos) {
                if($i == 0) {
                    $html .= '<optgroup label="'.$part[0].'">';
                }
                else {
                    $html .= '</optgroup><optgroup label="'.$part[0].'">';
                }
			}
			$s = '';
			if( $preselect == $position) $s = "selected='selected'";

			$html .= '<option value="'.$position.'" '.$s.'>'.$position.'</option>';

			$current_pos = $part[0];
			if($total == ($i+1)) {
				$html .= '</optgroup>';
			}
		}

        $html .= '</select>';

		return $html;
    }

    public function SelectProcessEvent($name, $preselect = NULL, $params = '')
    {
        $db = PFdatabase::GetInstance();

        $query = "SELECT event FROM #__pf_processes"
               . "\n GROUP by event"
               . "\n ORDER by event ASC";
		       $db->setQuery($query);
		       $rows = $db->loadResultArray();

		if(!is_array($rows)) $rows = array();

		$html = '<select name="'.$name.'" '.$params.'>
                 <option value="0">'.PFformat::Lang('SELECT_EVENT').'</option>';

		$preselect = $this->BindField($name, $preselect);

		$current_pos = "";
        $total = count($rows);
		foreach ($rows AS $i => $event)
		{
			$part = explode('_', $event);
			if($part[0] != $current_pos) {
                if($i == 0) {
                    $html .= '<optgroup label="'.$part[0].'">';
                }
                else {
                    $html .= '</optgroup><optgroup label="'.$part[0].'">';
                }
			}
			$s = '';
			if( $preselect == $event) $s = "selected='selected'";

			$html .= '<option value="'.$event.'" '.$s.'>'.$event.'</option>';

			$current_pos = $part[0];
			if($total == ($i+1)) {
				$html .= '</optgroup>';
			}
		}

        $html .= '</select>';

		return $html;
    }

    private function BindField($field, $value)
    {
        $value2 = NULL;

        // User Request
        if(is_string($this->bind_data)) {
            $value2 = JRequest::getVar($field, '', $this->bind_data);
        }

		// Object
		if( is_object($this->bind_data) && isset($this->bind_data->$field)) {
            $value2 = $this->bind_data->$field;
        }

		// Array
		if(is_array($this->bind_data)) {
			foreach ($this->bind_data AS $k => $v)
			{
				if(is_object($v) && isset($v->$field)) {
					$value2 = $v->$field;
				}
				if($k == $field && !is_object($v)) {
					$value2 = $v;
				}
			}
		}

		if($value2) $value = $value2;

		return $value;
    }
}


class PFtable
{
    private $titles;
    private $fields;
    private $ob;
    private $od;
    private $def_ob;
    private $def_od;

	public function __construct($titles, $fields, $def_ob, $def_od = 'ASC')
	{
		$this->titles = $titles;
	    $this->fields = $fields;
	    $this->def_ob = $def_ob;
	    $this->def_od = $def_od;

	    $this->ob = strval(JRequest::getVar('ob', $this->def_ob));
	    $this->od = strval(JRequest::getVar('od', $this->def_od));
	}

	public function TH($title)
	{
	    static $load = NULL;
	    static $lang = NULL;

	    if(is_null($load)) $load = PFload::GetInstance();
	    if(is_null($lang)) $lang = PFlanguage::GetInstance();

		$new_dir = ($this->od == 'ASC') ? 'DESC' : 'ASC' ;

		if ($this->ob == $this->fields[$title]) {
			if ($this->od == 'ASC') {
				$order_dir = "&nbsp;".$load->ThemeImg('arrow_top.png', 'alt="up"');
			}
			else {
				$order_dir = "&nbsp;".$load->ThemeImg('arrow_down.png', 'alt="up"');
			}
		}
		else {
			$order_dir = '';
		}

		$h = "<a href=\"javascript:reorderTable('".$this->fields[$title]."','".$new_dir."');\"><span>"
           . $lang->_($this->titles[$title])."</span></a>".$order_dir;

		return $h;
	}

	public function Menu($start = true)
	{
         return ($start == true) ? '<div class="pf_inline_menu"><ul>' : '</ul></div>';
    }

    public function MenuItem($link, $title = NULL, $class = NULL, $attributes = NULL)
    {
		$config = PFconfig::GetInstance();
		$showtips =  (int) $config->Get('tooltip_help');

		$hastip = is_null($title) ? "" : ' class="hasTip"';
        $class  = is_null($class) ? "pf_edit" : $class;
        $title2 = is_null($title) ? "" : PFformat::Lang($title);
        $title  = is_null($title) ? "" : ' title="::'.PFformat::Lang($title).'::"';
        $attributes = is_null($attributes) ? "" : ' '.$attributes;

		if (!$showtips) {
			$hastip = "";
			$title  = "";
		}

        $html = '<li class="'.$class.'">
                      <a href="'.$link.'"'.$hastip.$title.$attributes.'>
                          <span>'.$title2.'</span>
                      </a>
                 </li>';

        return $html;
    }

    public function ModalMenuItem($link, $title = NULL, $class = NULL, $attributes = NULL)
    {
		$config = PFconfig::GetInstance();
		$showtips =  (int) $config->Get('tooltip_help');

        $hastip = is_null($title) ? ' class="modal"' : ' class="hasTip modal"';
        $class  = is_null($class) ? "pf_edit" : $class;
        $title2 = is_null($title) ? "" : PFformat::Lang($title);
        $title  = is_null($title) ? "" : ' title="::'.PFformat::Lang($title).'::"';
        $attributes = is_null($attributes) ? "" : ' '.$attributes;

		if (!$showtips) {
			$hastip = "class=\"modal\"";
			$title  = "";
		}

        $html = '<li class="'.$class.'">
                      <a href="'.$link.'"'.$hastip.$title.$attributes.'>
                          <span>'.$title2.'</span>
                      </a>
                 </li>';

        return $html;
    }
}
?>