<?php
/**
* $Id: config.helper.php 837 2010-11-17 12:03:35Z eaxs $
* @package    Projectfork
* @subpackage Config
* @copyright  Copyright (C) 2006-2010 Tobias Kuhn. All rights reserved.
* @license    http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License License as published by
* the Free Software Foundation, either version 3 of the License,
* or any later version.
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

class PFconfigHelper
{
    public function SelectSpecialCondition($name, $preselect = NULL, $params = '')
    {
        $rows = array(''    => PFformat::Lang('SA_NONE'),
		              '-p'  => PFformat::Lang('SA_PUBLIC'),
		              '-ws' => PFformat::Lang('SA_WORKSPACE'),
		              '-l'  => PFformat::Lang('SA_LOGGED_IN'),
		              '-a'  => PFformat::Lang('SA_AUTHOR_ONLY'));
		              
		$form = new PFform();
		return $form->Selectlist($name, $rows, $preselect, $params);
    }
    
    public function SelectFlag($name, $preselect = NULL, $params = '')
	{
		static $rows = null;
		
		if($rows == null) {
		    $db   = PFdatabase::GetInstance();
		    $rows = array();
		    
			$query = "SELECT name, title FROM #__pf_access_flags ORDER BY id ASC";
		           $db->setQuery($query);
		           $tmprows = $db->loadObjectList();
		           
		    $rows[""] = PFformat::Lang('PFL_NONE');     
		    foreach($tmprows AS $row)
            {
                $k = $row->name;
                $v = PFformat::Lang($row->title);
                $rows[$k] = $v;
            }
		}
		
		$form = new PFform();
		return $form->Selectlist($name, $rows, $preselect, $params);
	}
	
	public function SelectAutoEnable($name, $preselect = NULL)
	{
        $rows = array('1' => PFformat::Lang('AUTO_ENABLE_Y'),
                      '0' => PFformat::Lang('AUTO_ENABLE_N'));
                      
        $form = new PFform();
		return $form->Selectlist($name, $rows, $preselect);              
    }
    
    public function HTMLparams($params, $scope)
    {
        if(!is_object($params)) {
            // No params found
			$html = '<fieldset class="adminform">
                         <table class="admintable" width="100%">
                             <tbody>
                                 <tr>
                                     <td align="center">'.PFformat::Lang('PARAMS_NA').'</td>
                                 </tr>
                             </tbody>
                         </table>
                     </fieldset>';
		
		    return $html;
		}
  
        $config = PFconfig::GetInstance();
		
		$html = "<fieldset class=\"adminform\">";
		$html .= "<table class='admintable' width='100%'><tbody>";
		
		$children = $params->children();
		    
		foreach ($children AS $param)
		{
			JFilterInput::clean($param, 'array');
			$type = $param->attributes('type');
			
			switch ($type) 
			{
				case 'text':
				    $value = $param->attributes('value');
				    $name  = $param->attributes('name');
				    $desc  = htmlspecialchars(PFformat::Lang($param->attributes('desc')), ENT_QUOTES);
				    $desc  = str_replace('\n', '<br />', $desc);
				    $title = $param->attributes('title');
				    $size  = (int) $param->attributes('size');
					$v2    = $config->Get($name, $scope);
					
					if(!$size) $size = "50";
					if(!is_null($v2)) $value = $v2;

                    $html .= '
                    <tr>
                        <td class="key" valign="top" width="150">'.htmlspecialchars(PFformat::Lang($title), ENT_QUOTES).'</td>
                        <td valign="top"><input type="text" value="'.htmlspecialchars(PFformat::Lang($value), ENT_QUOTES).'" name="params['.htmlspecialchars(PFformat::Lang($name), ENT_QUOTES).']" size="'.$size.'"/></td>
                        <td valign="top">'.$desc.'</td>
                    </tr>';
				    break;
				    
				case 'textarea':
				    $value = $param->attributes('value');
				    $name  = $param->attributes('name');
				    $desc  = htmlspecialchars(PFformat::Lang($param->attributes('desc')), ENT_QUOTES);
				    $desc  = str_replace('\n', '<br />', $desc);
				    $title = $param->attributes('title');
				    $rows  = (int) $param->attributes('rows');
				    $cols  = (int) $param->attributes('cols');
					$v2    = $config->Get($name, $scope);
					
					if(!$rows) $rows = 10;
					if(!$cols) $cols = 30;
					if(!is_null($v2)) $value = $v2;

                    $html .= '
                    <tr>
                        <td class="key" valign="top" width="150">'.htmlspecialchars(PFformat::Lang($title), ENT_QUOTES).'</td>
                        <td valign="top"><textarea name="params['.htmlspecialchars(PFformat::Lang($name), ENT_QUOTES).']" rows="'.$rows.'" cols="'.$cols.'">'.$value.'</textarea></td>
                        <td valign="top">'.$desc.'</td>
                    </tr>';
					break;
					
				case 'select':
				    $options = $param->children();
					$value = $param->attributes('value');
				    $name  = $param->attributes('name');
				    $desc  = htmlspecialchars(PFformat::Lang($param->attributes('desc')), ENT_QUOTES);
				    $desc  = str_replace('\n', '<br />', $desc);
				    $title = $param->attributes('title');
				    $size  = (int) $param->attributes('size');
					$v2    = $config->Get($name, $scope);
					
					if($size) $size = ' size="'.$size.'"';
					if(!$size) $size = "";
					if(!is_null($v2)) $value = $v2;
					
					if(!is_array($options)) continue;

					$html .= '
                    <tr>
                        <td class="key" width="150" valign="top">'.htmlspecialchars(PFformat::Lang($title), ENT_QUOTES).'</td>
                        <td valign="top">
                        <select name="params['.htmlspecialchars(PFformat::Lang($name), ENT_QUOTES).']"'.$size.'>';
                    
                    foreach($options AS $option)
                    {
                        $ps = '';
                        if($value == $option->attributes('value')) $ps = ' selected="selected"';
                        $html .= '
                        <option value="'.htmlspecialchars($option->attributes('value'),ENT_QUOTES).'"'.$ps.'>'.htmlspecialchars(PFformat::Lang($option->data()), ENT_QUOTES).'</option>';
                    }
                    
                    $html .= '
                    </select>
                    </td>
                    <td valign="top">'.$desc.'</td>
                    </tr>';
					break;	

				case 'separator':
				    $title = $param->attributes('title');
				    
				    $html .= '
                    <tr>
                        <td class="key" width="150">'.htmlspecialchars(PFformat::Lang($title), ENT_QUOTES).'</td>
                        <td><hr/></td>
                    </tr>';
					break;
					
				case 'slider_start':
				case 'cat_start':
				    $title = $param->attributes('title');
				    
				    $html .= '
                    <tr>
                        <td colspan="3" class="pf_cfg_cat"><h3>'.htmlspecialchars(PFformat::Lang($title), ENT_QUOTES).'</h3></td>
                    </tr>';
					break;
					
				case 'slider_end':
				case 'cat_end':
				    $html .= '';
					break;
			}
			unset($param);
		}
		
		$html .= '
        </tbody></table>
        </fieldset>';
		
		return $html;
    }
    
    public function HTMLsectionPermissions($section_name)
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();
        
        $use_score = (int) $config->Get('use_score');
        
    	$query = "SELECT * FROM #__pf_section_tasks"
    	       . "\n WHERE section = '$section_name' AND parent = ''"
    	       . "\n ORDER BY ordering ASC";
    	       $db->setQuery($query);
    	       $rows = $db->loadObjectlist();

    	$html = "";
    	$form = new PFform();
    	
    	if(is_array($rows)) {
    		ob_start();
    		?>
    		<fieldset class="adminform">
    		   <legend><?php echo PFformat::Lang('PERMISSION_CONFIG');?></legend>
    		   <table class="pf_table adminlist" width="100%" cellpadding="0" cellspacing="0">
    		      <thead>
    		         <tr>
    		            <th><?php echo PFformat::Lang('TITLE');?></th>
    		            <th><?php echo PFformat::Lang('DESC');?></th>
    		            <th><?php echo PFformat::Lang('ORDERING');?></th>
    		            <th <?php if(!$use_score) echo 'style="display:none"'; ?>><?php echo PFformat::Lang('SCORE');?></th>
    		            <th><?php echo PFformat::Lang('FLAG');?></th>
    		            <th><?php echo PFformat::Lang('SPECIAL_CONDITION');?></th>
    		         </tr>
    		      </thead>
    		      <tbody>
    		         <?php
    		         $k = 0;
    		         foreach ($rows AS $row)
    		         {
    		            JFilterOutput::objectHTMLSafe($row);
    		         	?>
    		         	<tr class="pf_row<?php echo $k;?> row<?php echo $k;?>">
    		         	   <td><strong><?php echo PFformat::Lang($row->title);?></strong> (<?php echo $row->name;?>)<?php echo $form->HiddenField("permission[$row->id][id]", (int)$row->id);?></td>
    		         	   <td><?php echo PFformat::Lang($row->description);?></td>
    		         	   <td><?php echo $form->InputField("permission[$row->id][ordering]", $row->ordering, 'size="5" style="text-align:center;"');?></td>
    		         	   <td <?php if(!$use_score) echo 'style="display:none"'; ?>><?php echo $form->InputField("permission[$row->id][score]", (int)$row->score, 'size="5"');?></td>
    		         	   <td><?php echo PFconfigHelper::SelectFlag("permission[$row->id][flag]", $row->flag);?></td>
    		         	   <td><?php echo PFconfigHelper::SelectSpecialCondition("permission[$row->id][tags]", $row->tags);?></td>
    		         	</tr>
    		         	<?php
    		         	$k = 1 - $k;
    		         }
    		         ?>
    		      </tbody>
    		   </table>
    		</fieldset>
    		<?php
    		$html = ob_get_contents();
    		ob_end_clean();
    	}
    	
    	return $html;
    }
}
?>