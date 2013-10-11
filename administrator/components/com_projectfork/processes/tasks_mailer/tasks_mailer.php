<?php
/**
* $Id: tasks_mailer.php 926 2012-06-25 15:09:42Z eaxs $
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

class PFtasksmailer
{
    private $recipients;
    private $subject;
    private $body;
    private $id;

    private $type;

    public function SetId($task_id)
    {
        $this->id = $task_id;
    }

    public function SetType($type)
    {
        $this->type = $type;
    }

    public function SetRecipients($recipients = null)
    {
        $config = PFconfig::GetInstance();
        $db     = PFdatabase::GetInstance();

        if(is_null($recipients)) {
            $n_author   = (int) $config->Get('notify_author', 'tasks');
		    $n_assigned = (int) $config->Get('notify_assigned', 'tasks');
		    $n_members  = (int) $config->Get('notify_members', 'tasks');

            $recipients = array();
            $double     = array();
            $tmp        = array();

            if($this->type == 'save_milestone' || $this->type == 'update_milestone') {
                $query = "SELECT project FROM #__pf_milestones WHERE id = '$this->id'";
                       $db->setQuery($query);
                       $project = $db->loadResult();
            }
            else {
                $query = "SELECT project FROM #__pf_tasks WHERE id = '$this->id'";
                       $db->setQuery($query);
                       $project = $db->loadResult();
            }

            // get author email
            if($n_author) {
                if($this->type == 'save_milestone' || $this->type == 'update_milestone') {
                    $query = "SELECT u.email FROM #__users AS u RIGHT JOIN #__pf_milestones AS m ON m.author = u.id"
                           . "\n WHERE m.id = '$this->id'";
                           $db->setQuery($query);
                           $email = $db->loadResult();
                }
                else {
                    $query = "SELECT u.email FROM #__users AS u RIGHT JOIN #__pf_tasks AS t ON t.author = u.id"
                           . "\n WHERE t.id = '$this->id'";
                           $db->setQuery($query);
                           $email = $db->loadResult();
                }

                if(!is_null($email)) $recipients[] = $email;
            }

            // get assigned user email
            if($n_assigned && $this->type != 'save_milestone' && $this->type != 'update_milestone') {
                $query = "SELECT u.email FROM #__users AS u"
                       . "\n RIGHT JOIN #__pf_task_users AS tu ON tu.task_id = '$this->id'"
                       . "\n AND tu.user_id = u.id"
                       . "\n WHERE tu.task_id = '$this->id'"
                       . "\n GROUP BY u.id";
                       $db->setQuery($query);
                       $emails = $db->loadResultArray();

                if(!is_array($emails)) $emails = array();

                foreach($emails AS $email)
                {
                    $recipients[] = $email;
                }
            }

            // get email of project members
            if($n_members && $project) {
                if($this->type == 'save_milestone' || $this->type == 'update_milestone') {
                    $query = "SELECT u.email FROM #__users AS u"
                           . "\n RIGHT JOIN #__pf_milestones AS m ON m.project = '$project'"
                           . "\n RIGHT JOIN #__pf_project_members AS pm ON pm.project_id = '$project'"
                           . "\n WHERE m.id = '$this->id' AND pm.project_id = '$project'"
                           . "\n AND u.id = pm.user_id"
                           . "\n GROUP BY u.id";
                           $db->setQuery($query);
                           $emails = $db->loadResultArray();
                }
                else {
                    $query = "SELECT u.email FROM #__users AS u"
                           . "\n RIGHT JOIN #__pf_tasks AS t ON t.project = '$project'"
                           . "\n RIGHT JOIN #__pf_project_members AS pm ON pm.project_id = '$project'"
                           . "\n WHERE t.id = '$this->id' AND pm.project_id = '$project'"
                           . "\n AND u.id = pm.user_id"
                           . "\n GROUP BY u.id";
                           $db->setQuery($query);
                           $emails = $db->loadResultArray();
                }

                if(!is_array($emails)) $emails = array();

                foreach($emails AS $email)
                {
                    $recipients[] = $email;
                }
            }

            foreach($recipients AS $recipient)
            {
                if(!in_array($recipient, $double)) {
                    $tmp[]    = $recipient;
                    $double[] = $recipient;
                }
            }

            $this->recipients = $tmp;
        }
        else {
            $this->recipients = $recipients;
        }
    }

    public function SetBody($body = null)
    {
        $db     = PFdatabase::GetInstance();
        $config = PFconfig::GetInstance();
        $com    = PFcomponent::GetInstance();

        if(!is_null($body)) {
            $this->body = $body;
        }
        else {
            // Include helper class
            require_once( PFobject::GetHelper('tasks') );
            // TASK BODY
            if($this->type == 'save_task' || $this->type == 'update_task') {
                if($config->Get('html_emails') == 1) {
                    $this->body = PFformat::Lang('EM_TASKS_BODY_HTML');
                }
                else {
                    $this->body = PFformat::Lang('EM_TASKS_BODY_PLAIN');
                }

                $class = new PFtasksClass();
                $task = $class->LoadTask($this->id);

                if(count($task->assigned)) {
                    $assigned = implode(',', $task->assigned);

                    $query = "SELECT name FROM #__users WHERE id IN($assigned)";
                           $db->setQuery($query);
                           $assigned = $db->loadResultArray();

                   $assigned = implode(', ', $assigned);
                }
                else {
                    $assigned = "";
                }

                if($task->milestone) {
                    $query = "SELECT title FROM #__pf_milestones WHERE id = '$task->milestone'";
                           $db->setQuery($query);
                           $milestone = $db->loadResult();
                }
                else {
                    $milestone = PFformat::Lang('NOT_SET');
                }

                // replace tags
                $assigned = implode(',', $task->assigned);
                if($assigned == "") {
                    $assigned = PFformat::Lang('NOT_SET');
                }
                else {
                    $query = "SELECT name FROM #__users WHERE id IN($assigned)";
                           $db->setQuery($query);
                           $assigned = implode(',', $db->loadResultArray());
                }

                // Overwrite location to create a frontend link
                $location = $com->Get('location');
                $com->Set('location', 'frontend');
                $itemlink = PFformat::Link("section=tasks&task=display_details&id=$this->id", true, false, false);
                $com->Set('location', $location);

                $this->body = str_replace('{milestone}', $milestone, $this->body);
                $this->body = str_replace('{priority}', PFtasksHelper::RenderPriority($task->priority), $this->body);
                $this->body = str_replace('{progress}', $task->progress."%", $this->body);
                $this->body = str_replace('{progress}', $task->progress."%", $this->body);
                if(!$task->edate) {
                    $this->body = str_replace('{deadline}', PFformat::Lang('NOT_SET'), $this->body);
                }
                else {
                    $this->body = str_replace('{deadline}', PFformat::ToDate($task->edate), $this->body);
                }
                $this->body = str_replace('{assigned}', $assigned, $this->body);
                if($config->Get('html_emails') == 1) {
                    $this->body = str_replace('{link}', "<a href='$itemlink'>".$itemlink."</a>", $this->body);
                    $this->body = str_replace('{content}', $task->content, $this->body);
                }
                else {
                    $task->content = str_replace('<p>',"\n", $task->content);
                    $task->content = str_replace('<br />',"\n", $task->content);
                    $task->content = str_replace('<br/>',"\n", $task->content);
                    $task->content = str_replace('<br>',"\n", $task->content);
                    $this->body = str_replace('{content}', strip_tags($task->content), $this->body);
                    $this->body = str_replace('{link}', $itemlink, $this->body);
                }
            }

            // MILESTONE BODY
            if($this->type == 'save_milestone' || $this->type == 'update_milestone') {
                if($config->get('html_emails') == 1) {
                    $this->body = PFformat::Lang('EM_MS_BODY_HTML');
                }
                else {
                    $this->body = PFformat::Lang('EM_MS_BODY_PLAIN');
                }

                $class    = new PFtasksClass();
                $priority = (int) JRequest::getVar('prio');
                $content  = JRequest::getVar('content');
                $deadline = JRequest::getVar('has_deadline');
		        $edate    = JRequest::getVar('edate');
		        $hour     = (int) JRequest::getVar('hour');
		        $min      = (int) JRequest::getVar('minute');
		        $edate    = $edate." $hour:$min";

                if(!$deadline) {
                    $edate = PFformat::Lang('NOT_SET');
                }

                $this->body = str_replace('{priority}', PFtasksHelper::RenderPriority($priority), $this->body);
                $this->body = str_replace('{content}', $content, $this->body);
                $this->body = str_replace('{deadline}', $edate, $this->body);
            }

            // COMMENT BODY
            if($this->type == 'save_comment') {
                $title   = JRequest::getVar('title');
        	    $content = JRequest::getVar('ctext');

                // Overwrite location to create a frontend link
                $location = $com->Get('location');
                $com->Set('location', 'frontend');
                $itemlink = PFformat::Link("section=tasks&task=display_details&id=$this->id", true, false, false);
                $com->Set('location', $location);

                if($config->Get('html_emails') == 1) {
                    $this->body = PFformat::Lang('EM_TCOMMENT_BODY_HTML');
                    $content = JRequest::getVar('ctext', '', 'default', 'none', JREQUEST_ALLOWRAW);

                }
                else {
                    $this->body = PFformat::Lang('EM_TCOMMENT_BODY_PLAIN');
                }

                $this->body = str_replace('{title}', $title, $this->body);
                $this->body = str_replace('{content}', $content, $this->body);

                if($config->Get('html_emails') == 1) {
                    $this->body .= "<br /><br /><a href='$itemlink'>".$itemlink."</a>";
                }
                else {
                    $this->body .= "\n\n".$itemlink;
                }
            }
        }
    }

    public function SetSubject($subject = null)
    {
        $my = PFuser::GetInstance();
        $db = PFdatabase::GetInstance();

        if($this->type == 'save_task') {
            if(!is_null($subject)) {
                $this->subject = $subject;
            }
            else {
                $this->subject = PFformat::Lang('EM_TASKS_SUBJECT_NEW');
            }

            $query = "SELECT p.title FROM #__pf_projects AS p"
                   . "\n RIGHT JOIN #__pf_tasks AS t ON t.project = p.id"
                   . "\n WHERE t.id = '$this->id'";
                   $db->setQuery($query);
                   $project_title = $db->loadResult();

            $query = "SELECT title FROM #__pf_tasks WHERE id = '$this->id'";
                   $db->setQuery($query);
                   $task_title = $db->loadResult();

            $query = "SELECT m.title FROM #__pf_milestones AS m"
                   . "\n RIGHT JOIN #__pf_tasks AS t ON t.milestone = m.id"
                   . "\n WHERE t.id = '$this->id'";
                   $db->setQuery($query);
                   $milestone_title = $db->loadResult();

            $this->subject = str_replace('{task}', $task_title, $this->subject);
            $this->subject = str_replace('{milestone}', $milestone_title, $this->subject);
            $this->subject = str_replace('{project}', $project_title, $this->subject);
            $this->subject = str_replace('{name}', $my->GetName(), $this->subject);
        }

        if($this->type == 'update_task') {
            if(!is_null($subject)) {
                $this->subject = $subject;
            }
            else {
                $this->subject = PFformat::Lang('EM_TASKS_SUBJECT_UPDATE');
            }

            $query = "SELECT p.title FROM #__pf_projects AS p"
                   . "\n RIGHT JOIN #__pf_tasks AS t ON t.project = p.id"
                   . "\n WHERE t.id = '$this->id'";
                   $db->setQuery($query);
                   $project_title = $db->loadResult();

            $query = "SELECT title FROM #__pf_tasks WHERE id = '$this->id'";
                   $db->setQuery($query);
                   $task_title = $db->loadResult();

            $query = "SELECT m.title FROM #__pf_milestones AS m"
                   . "\n RIGHT JOIN #__pf_tasks AS t ON t.milestone = m.id"
                   . "\n WHERE t.id = '$this->id'";
                   $db->setQuery($query);
                   $milestone_title = $db->loadResult();

            $this->subject = str_replace('{task}', $task_title, $this->subject);
            $this->subject = str_replace('{milestone}', $milestone_title, $this->subject);
            $this->subject = str_replace('{project}', $project_title, $this->subject);
            $this->subject = str_replace('{name}', $my->GetName(), $this->subject);
        }

        if($this->type == 'save_milestone') {
            if(!is_null($subject)) {
                $this->subject = $subject;
            }
            else {
                $this->subject = PFformat::Lang('EM_MS_SUBJECT_NEW');
            }
            $project = (int) $my->GetWorkspace();

            $query = "SELECT title FROM #__pf_projects"
                   . "\n WHERE id = '$project'";
                   $db->setQuery($query);
                   $project_title = $db->loadResult();

            $this->subject = str_replace('{milestone}', JRequest::getVar('title'), $this->subject);
            $this->subject = str_replace('{project}', $project_title, $this->subject);
            $this->subject = str_replace('{name}', $my->GetName(), $this->subject);
        }

        if($this->type == 'update_milestone') {
            if(!is_null($subject)) {
                $this->subject = $subject;
            }
            else {
                $this->subject = PFformat::Lang('EM_MS_SUBJECT_UPDATE');
            }
            $project = (int) $my->GetWorkspace();

            $query = "SELECT title FROM #__pf_projects"
                   . "\n WHERE id = '$project'";
                   $db->setQuery($query);
                   $project_title = $db->loadResult();

            $this->subject = str_replace('{milestone}', JRequest::getVar('title'), $this->subject);
            $this->subject = str_replace('{project}', $project_title, $this->subject);
            $this->subject = str_replace('{name}', $my->GetName(), $this->subject);
        }

        // COMMENT
        if($this->type == 'save_comment') {
            if(!is_null($subject)) {
                $this->subject = $subject;
            }
            else {
                $this->subject = PFformat::Lang('EM_TCOMMENT_SUBJECT');
            }
            $project = (int) $my->GetWorkspace();

            $query = "SELECT title FROM #__pf_projects"
                   . "\n WHERE id = '$project'";
                   $db->setQuery($query);
                   $project_title = $db->loadResult();

            $query = "SELECT title FROM #__pf_tasks WHERE id = '$this->id'";
                   $db->setQuery($query);
                   $task_title = $db->loadResult();

            $this->subject = str_replace('{project}', $project_title, $this->subject);
            $this->subject = str_replace('{task}', $task_title, $this->subject);
            $this->subject = str_replace('{name}', $my->GetName(), $this->subject);
        }
    }

    public function Send()
    {
        $mail    = JFactory::getMailer();
        $jconfig = JFactory::getConfig();
        $config  = PFconfig::GetInstance();

        // dont send email if we have demo mode enabled
        if(defined('PF_DEMO_MODE')) return false;

        if(!$this->subject) $this->SetSubject();
        if(!$this->body) $this->SetBody();
        if(!count($this->recipients)) return false;

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
    }
}
?>