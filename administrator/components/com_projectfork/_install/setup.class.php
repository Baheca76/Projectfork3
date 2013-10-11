<?php
/**
* $Id: setup.class.php 837 2010-11-17 12:03:35Z eaxs $
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


class PFsetupClass
{
    private $elements;
    private $errors;

    public function __construct()
    {
        $this->errors = null;
        $this->elements = array('sql_access_flags', 'sql_access_levels',
                                'sql_groups', 'sql_languages',
                                'sql_panels', 'sql_processes',
                                'sql_sections', 'sql_section_tasks',
                                'sql_settings', 'sql_themes',
                                'sql_example');
    }

    public function AddError($e)
    {
        if(is_null($this->errors)) $this->errors = array();
        $this->errors[] = $e;
    }

    public function GetErrors()
    {
        return $this->errors;
    }

    public function RunSQL($file, $user_id = 0)
    {
        $db   = &JFactory::getDBO();
        $user = &JFactory::getUser();
        $file = dirname(__FILE__).DS.'mysql'.DS.$file;
        $now  = time();
        $future = time() + (86400 * 7);
        
        if(!file_exists($file)) {
            $this->AddError("$file does not exist!");
            return false;
        }

        $buffer = file_get_contents($file);

        if ( $buffer === false ) {
            $this->AddError("Failed to read file: $file");
		    return false;
		}

        $queries = $db->splitSql($buffer);
        if (count($queries) == 0) return true;

        foreach ($queries as $query)
		{
			$query = trim($query);
            
            // Replace placeholders
            $query = str_replace('{now}', $now, $query);
            $query = str_replace('{future}', $future, $query);
            
            if(!$user_id) {
                $query = str_replace('{uid},',$user->id.',', $query);
            }
            else {
                $query = str_replace('{uid},',$user_id.',', $query);
            }
            
			if ($query != '' && $query{0} != '#') {
                
				$db->setQuery($query);
				if (!$db->query()) {
                    $this->add_error("SQL error: ".$db->stderr(true));
					return false;
				}
			}
		}

        return true;
    }
    
    public function J16AclUpdate()
    {
        $db = &JFactory::getDBO();
        
        $queries = array();
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'group_1' WHERE `parameter` = 'group_0'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'group_2' WHERE `parameter` = 'group_18'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'group_3' WHERE `parameter` = 'group_19'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'group_4' WHERE `parameter` = 'group_20'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'group_5' WHERE `parameter` = 'group_21'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'group_6' WHERE `parameter` = 'group_23'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'group_7' WHERE `parameter` = 'group_24'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'group_8' WHERE `parameter` = 'group_25'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'accesslevel_1' WHERE `parameter` = 'accesslevel_0'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'accesslevel_2' WHERE `parameter` = 'accesslevel_18'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'accesslevel_3' WHERE `parameter` = 'accesslevel_19'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'accesslevel_4' WHERE `parameter` = 'accesslevel_20'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'accesslevel_5' WHERE `parameter` = 'accesslevel_21'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'accesslevel_6' WHERE `parameter` = 'accesslevel_23'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'accesslevel_7' WHERE `parameter` = 'accesslevel_24'";
        $queries[] = "UPDATE #__pf_settings SET `parameter` = 'accesslevel_8' WHERE `parameter` = 'accesslevel_25'";
        
        foreach($queries AS $query)
        {
            $db->setQuery($query);
            $db->query();
        }
    }
}
?>