<?php
/**
* $Id: comments.php 852 2011-02-21 06:47:59Z eaxs $
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

if(!defined('PF_COMMENTS_PROCESS')) {
	define('PF_COMMENTS_PROCESS', 1);
	
class PFcomments
{
    private $scope;
    private $itemid;
    
    private function Check($content)
    {
        if(!$this->scope || !$this->itemid) return false;
		if(!$content) return false;
		
		return true;
    }
    
    public function Init($scope, $itemid)
    {
        $this->scope  = $scope;
        $this->itemid = $itemid;
    }
    
    public function Load($id)
    {
        $db = PFdatabase::GetInstance();
        $id = $db->Quote($id);
        
        $query = "SELECT * FROM #__pf_comments"
               . "\n WHERE id = $id";
			   $db->setQuery($query);
			   $row = $db->loadObject();
			       
		if($db->getErrorMsg()) {
            unset($db);
            return false;
        }
        
        unset($db);
		return $row;
    }
    
    public function LoadList()
	{
	    $db = PFdatabase::GetInstance();
	    
		$scope = $db->Quote($this->scope);
		$id    = $db->Quote($this->itemid);
			
		$query = "SELECT c.*, u.name FROM #__pf_comments AS c"
			   . "\n LEFT JOIN #__users AS u ON u.id = c.author"
			   . "\n WHERE c.scope = $scope"
			   . "\n AND c.item_id = $id"
			   . "\n GROUP BY c.cdate"
			   . "\n ORDER BY c.cdate DESC";
			   $db->setQuery($query);
			   $rows = $db->LoadObjectList();

		if(!is_array($rows)) $rows = array();
		if($db->getErrorMsg()) return false;
		
		return $rows;       
	}
	
	public function Save($title, $content, $author)
	{
		if(!$this->Check($content, $author)) return false;
		
		$db = PFdatabase::GetInstance();
		
		$title   = $db->Quote($title);
		$content = $db->Quote($content);
		$author  = $db->Quote(intval($author));
		$scope   = $db->Quote($this->scope);
		$id      = $db->Quote($this->itemid);
		$now     = time();
		
		$query = "INSERT INTO #__pf_comments VALUES"
               . "\n (NULL, $title, $content, $scope, $id, $author, $now)";
		       $db->setQuery($query);
		       $db->query();
		       
		if($db->getErrorMsg()) return false;
		return true;
	}
	
	public function Update($title, $content, $id)
	{
	    if(!$this->Check($content)) return false;
	    
	    $db = PFdatabase::GetInstance();
	    
		$title   = $db->Quote($title);
		$content = $db->Quote($content);
		$id      = $db->Quote(intval($id));
		
		$query = "UPDATE #__pf_comments"
               . "\n SET title = $title, content = $content"
		       . "\n WHERE id = $id";
		       $db->setQuery($query);
		       $db->query();
		            
		if($db->getErrorMsg()) return false;
		return true;
	}
	
	public function Delete($id)
	{
	    $db = PFdatabase::GetInstance();
		$id = $db->Quote($id);
		
		$query = "DELETE FROM #__pf_comments WHERE id = $id";
		       $db->setQuery($query);
		       $db->query();
		       
		if($db->getErrorMsg()) return false;
		return true;
	}
	
	public function RenderNew($title = '', $content = '', $date = null)
	{
		$title   = htmlspecialchars($title);
		$content = htmlspecialchars($content);
		$editor  = JFactory::getEditor();
        $avc     = class_exists('PFavatar');
        
        if(!$date) $date = time();
        
        $form   = new PFform();
        $date   = PFformat::ToDate($date);
        $my     = PFuser::GetInstance();
        $config = PFconfig::GetInstance();
        
        $formname = 'adminform';
        $avatar   = "";
        
        if($avc) $avatar = PFavatar::Display($my->GetId());
        
        $use_ce = (int) $config->Get('use_ce', 'comments');

        if($use_ce && !defined('PF_DEMO_MODE')) {
            $area = $editor->display( 'ctext',  $content , '100%', '350', '75', '20' ) ;
            $sb = '
            <script type="text/javascript">
            function save_pfcomment()
            {
                '.$editor->save( 'ctext' ).'
                document.'.$formname.'.submit();
            }
            </script>
            <input type="button" class="pf_button" value="'.PFformat::Lang('SAVE').'" onclick="save_pfcomment();"/>';
        }
        else {
            $area = "<textarea name='ctext' class='text' rows='5' cols='40'>$content</textarea>";
            $sb = "<input type='submit' class='pf_button' value='".PFformat::Lang('SAVE')."'/>";
        }

		$html = "<table class='pf_comments' width='100%' cellspacing='0' cellpadding='0'>";
		$html .= "<tr class='pf_row0'>";
		$html .= "<td class='date' align='center'>".$date."</td>";
		$html .= "<td class='title' valign='top' align='left'>".$form->InputField('title*', $title, 'size="40" maxlength="124"')."</td>";
		$html .= "</tr>";
		$html .= "<tr class='pf_row0'>";
		$html .= "<td class='author' valign='top' align='center' width='10%' nowrap='nowrap'>";
		$html .= "<div>".$avatar."</div>";
		$html .= "<strong>".$my->GetName()."</strong>";
		$html .= "</td>";
		$html .= "<td class='content' valign='top' align='left'>$area</td>";
		$html .= "</tr>";
		$html .= "<tr class='pf_row0'>";
		$html .= "<td class='save' colspan='2' align='right'>$sb</td>";
		$html .= "</tr>";
		$html .= "</table>";
		
		return $html;
	}
	
	public function RenderEdit($id)
	{
		$row = $this->Load($id);
		
		return $this->RenderNew($row->title, $row->content, $row->cdate);
	}
	
	public function RenderList($append_link = '')
	{
		$rows = $this->LoadList();
		$form = new PFform();
		
		$load = PFload::GetInstance();
		$user = PFuser::GetInstance();
		$core = PFcore::GetInstance();
		$avc  = class_exists('PFavatar');
		
		$k    = 0;
		$flag = $user->GetFlag();
		
		$edit_image = $load->ThemeImg('reply.png');
		$del_image  = $load->ThemeImg('action_delete.png');

		$html = "<a id='cform'></a><table class='pf_comments' width='100%' cellspacing='0' cellpadding='0'>";
		
		foreach ($rows AS $row)
		{
			// edit link
			if($flag == 'system_administrator' || $user->GetId() == $row->author) {
				$edit_link = PFformat::Link("section=".$core->GetSection()."&task=form_edit_comment&id=".$this->itemid."&cid=".$row->id.$append_link)."#cform";
			    $edit_link = "<a href='$edit_link' class='pf_button'>$edit_image</a>";
			}
			else {
				$edit_link = "";
			}
			
			// delete link
			if($flag == 'system_administrator' || $user->GetId() == $row->author) {
				$del_link = PFformat::Link("section=".$core->GetSection()."&task=task_delete_comment&id=".$this->itemid."&cid=".$row->id.$append_link);
			    $del_link = "<a href='$del_link' class='pf_button'>$del_image</a>";
			}
			else {
				$del_link = "";
			}

            if($user->Access('display_details', 'profile', $row->author)) {
                $plink_s = "<a href='".PFformat::Link("section=profile&task=display_details&id=$row->author")."'>";
                $plink_e = "</a>";
            }
            else {
                $plink_s = "";
		        $plink_e = "";
            }
            
            $avatar = "";
            if($avc) $avatar = PFavatar::Display($row->author);
			
			$html .= "<tr class='pf_row$k'>";
			$html .= "<td class='date' align='center'>".PFformat::ToDate($row->cdate)."</td>";
			$html .= "<td class='title' valign='top' align='left'>$row->title</td>";
			$html .= "</tr>";
			$html .= "<tr class='pf_row$k'>";
			$html .= "<td class='author' valign='top' align='center' width='10%' nowrap='nowrap'>";
			$html .= "<div>".$plink_s.$avatar.$plink_e."</div>";
			$html .= "<strong>".$plink_s.$row->name.$plink_e."</strong>";
			$html .= "</td>";
			$html .= "<td class='content' valign='top' align='left'>".nl2br($row->content)."</td>";
			$html .= "</tr>";
			$html .= "<tr class='pf_row$k'>";
			$html .= "<td class='edit' colspan='2' align='right'><span>".$edit_link.$del_link."</span></td>";
			$html .= "</tr>";
			
			$k = 1 - $k;
		}
		
		if(!count($rows)) {
			$html .= "<tr class='pf_row$k'>";
			$html .= "<td class='content' valign='top' align='center'>".PFformat::Lang('NO_COMMENTS')."</td>";
			$html .= "</tr>";
		}
		
		$html .= "</table>";
		
		return $html;
	}
}
}
?>
