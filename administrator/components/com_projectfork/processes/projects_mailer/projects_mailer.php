<?php
/**
* $Id: projects_mailer.php 926 2012-06-25 15:09:42Z eaxs $
* @package   Projectfork
* @copyright Copyright (C) 2006-2009 Tobias Kuhn. All rights reserved.
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

class PFprojectsmailer
{
    private $recipients;

    private $subject;

    private $body;

    private $id;

    private $user_id;

    private $type;

    public function SetId($task_id)
    {
        $this->id = $task_id;
    }

    public function SetType($type)
    {
        $this->type = $type;
    }

    public function SetUserId($id)
    {
        $this->user_id = $id;
    }

    public function SetRecipients($recipients = null)
    {
        $db = PFdatabase::GetInstance();

        if(is_null($recipients)) {
            if($this->type == 'approve') {
                $query = "SELECT email FROM #__users"
    		           . "\n WHERE gid = '25'"
    		           . "\n GROUP BY id";
    		           $db->setQuery($query);
    		           $this->recipients = $db->loadResultArray();
            }
            if($this->type == 'join_request') {
                $query = "SELECT u.email FROM #__pf_projects AS p"
    	               . "\n RIGHT JOIN #__users AS u ON u.id = p.author"
    	               . "\n WHERE p.id = '$this->id'";
                       $db->setQuery($query);
                       $this->recipients = $db->loadResult();
            }
            if($this->type == 'admin_approved') {
                $query = "SELECT u.email FROM #__pf_projects AS p"
    	               . "\n RIGHT JOIN #__users AS u ON u.id = p.author"
    	               . "\n WHERE p.id = '$this->id'";
                       $db->setQuery($query);
                       $this->recipients = $db->loadResult();
            }
        }
        else {
            $this->recipients = $recipients;
        }
    }

    public function SetBody($body = null)
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();

        if(!is_null($body)) {
            $this->body = $body;
        }
        else {
            if($this->type == 'join_request') {
                $query = "SELECT * FROM #__users WHERE id = '$this->user_id'";
                       $db->setQuery($query);
                       $user = $db->loadObject();

                $query = "SELECT title FROM #__pf_projects WHERE id = '$this->id'";
                       $db->setQuery($query);
                       $p_name = $db->loadResult();

                $query = "SELECT u.name FROM #__pf_projects AS p"
    	               . "\n RIGHT JOIN #__users AS u ON u.id = p.author"
    	               . "\n WHERE p.id = '$this->id'";
                       $db->setQuery($query);
                       $aname = $db->loadResult();

                // Overwrite location to create a frontend link
                $com = PFcomponent::GetInstance();
                $location = $com->Get('location');
                $com->Set('location', 'frontend');
                $itemlink = PFformat::Link("section=users&task=list_requests&workspace=$this->id", true, false, true);
                $com->Set('location', $location);

                if($config->Get('html_emails') == 1) {
                    $message = PFformat::Lang('EM_PROJECT_BODY_JRHTML');
                    $message = $message = str_replace('{link}',"<a href='$itemlink'>".$itemlink. "</a>", $message);
                }
                else {
                    $message = PFformat::Lang('EM_PROJECT_BODY_JRPLAIN');
                    $message = $message = str_replace('{link}',$itemlink, $message);
                }

    	        $message = str_replace('{a_name}', $aname, $message);
    	        $message = str_replace('{u_name}', $user->name, $message);
    	        $message = str_replace('{p_name}', $p_name, $message);
    	        $message = str_replace('{uemail}', $user->email, $message);
            }

            if($this->type == 'approve') {
                $my     = PFuser::GetInstance();
                $com    = PFcomponent::GetInstance();
                $config = PFconfig::GetInstance();

                $class = new PFprojectsClass();
                $row   = $class->Load($this->id);

                // Overwrite location to create a frontend link
                $location = $com->Get('location');
                $com->Set('location', 'frontend');
                $itemlink = PFformat::Link("section=projects&status=2", true, false, true);

                $com->Set('location', $location);

                if($config->Get('html_emails') == '1') {
                    $message = PFformat::Lang('EM_PROJECT_BODY_AAPPHTML');
                    $message = str_replace('{content}', $row->content, $message);
                    $message = str_replace('{link}', "<a href='$itemlink'>".$itemlink."</a>", $message);
                }
                else {
                    $message = PFformat::Lang('EM_PROJECT_BODY_AAPPPLAIN');
                    $row->content = str_replace('<p>',"\n", $row->content);
                    $row->content = str_replace('<br />',"\n", $row->content);
                    $row->content = str_replace('<br/>',"\n", $row->content);
                    $row->content = str_replace('<br>',"\n", $row->content);
                    $row->content = strip_tags($row->content);
                    $message = str_replace('{content}', $row->content, $message);
                    $message = str_replace('{link}', $itemlink, $message);
                }

                $message = str_replace('{name}', $my->GetName(), $message);
    	        $message = str_replace('{title}', $row->title, $message);
    	        $message = str_replace('{email}', $my->GetEmail(), $message);
            }

            if($this->type == 'admin_approved') {
                $class = new PFprojectsClass();
                $row   = $class->load($this->id);

                $query = "SELECT u.name FROM #__pf_projects AS p"
    	               . "\n RIGHT JOIN #__users AS u ON u.id = p.author"
    	               . "\n WHERE p.id = '$this->id'";
                       $db->setQuery($query);
                       $aname = $db->loadResult();

                if($config->Get('html_emails') == '1') {
                    $message = PFformat::Lang('EM_PROJECT_BODY_AAPPIHTML');
                }
                else {
                    $message = PFformat::Lang('EM_PROJECT_BODY_AAPPIPLAIN');
                }

                $message = str_replace('{name}', $aname, $message);
                $message = str_replace('{title}', $row->title, $message);
            }

            $this->body = $message;
        }
    }

    public function SetSubject($subject = null)
    {
        $my = PFuser::GetInstance();
        $db = PFdatabase::GetInstance();

        if(is_null($subject)) {
            $query = "SELECT title FROM #__pf_projects WHERE id = '$this->id'";
                   $db->setQuery($query);
                   $title = $db->loadResult();

            if($this->type == 'join_request') {
    	        $this->subject = str_replace('{title}', $title, PFformat::Lang('EM_PROJECT_SUBJECT_JR'));
            }
            if($this->type == 'approve') {
                $this->subject = str_replace('{title}', $title, PFformat::Lang('EM_PROJECT_SUBJECT_AAPP'));
            }
            if($this->type == 'admin_approved') {
                $this->subject = PFformat::Lang('EM_PROJECT_SUBJECT_AAPPI');
            }
        }
        else {
            $this->subject = $subject;
        }

        unset($my, $db);
    }

    public function Send()
    {
        $mail    = JFactory::getMailer();
        $config  = PFconfig::GetInstance();
        $jconfig = JFactory::getConfig();

        // dont send email if we have demo mode enabled
        if(defined('PF_DEMO_MODE')) return false;

        if(!$this->subject) $this->SetSubject();
        if(!$this->body) $this->SetBody();

        if($config->Get('html_emails')) {
            $mail->IsHTML(true);
        }
        else {
            $mail->IsHTML(false);
            $this->body = str_replace('\n', "\n", $this->body);
        }


		// $mail->setSender( array( $jconfig->get('mailfrom'), $jconfig->get('fromname') ) );
		$mail->setSubject( $this->subject );
	    $mail->setBody( $this->body );
        $mail->addRecipient( $this->recipients );
        $mail->Send();
        unset($mail, $config);
    }
}
?>