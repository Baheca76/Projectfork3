<?php
/**
* $Id: board.class.php 930 2013-04-06 02:09:33Z eaxs $
* @package    Projectfork
* @subpackage Board
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

class PFboardClass extends PFobject
{
    public function __construct()
    {
        parent::__construct();
    }

    public function GetTopicAuthor($id)
    {
        $db = PFdatabase::GetInstance();

        $query = "SELECT author FROM #__pf_topics"
               . "\n WHERE id = '$id'";
               $db->setQuery($query);
               $author = $db->loadResult();

        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return 0;
        }

        return $author;
    }

    public function GetReplyAuthor($id)
    {
        $db = PFdatabase::GetInstance();

        $query = "SELECT author FROM #__pf_topic_replies"
               . "\n WHERE id = '$id'";
               $db->setQuery($query);
               $author = $db->loadResult();

        if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return 0;
        }

        return $author;
    }

	public function LoadTopic($id)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT t.*, u.name FROM #__pf_topics AS t"
		       . "\n LEFT JOIN #__users AS u ON u.id = t.author"
		       . "\n WHERE t.id = '$id'";
		       $db->setQuery($query);
		       $row = $db->loadObject();

		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return NULL;
        }

		return $row;
	}

	public function LoadTopicList($limitstart, $limit, $project, $ob, $od, $keyword = '')
	{
	    // Load db object
	    $db = PFdatabase::GetInstance();

	    // Setup filter
		$filter = "";

		if($keyword) {
		  $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);
          $filter .= "\n AND t.title LIKE " . $keyword;
		}

		if(!$project) return array();

		// Load topic list
		$query = "SELECT t.*, u.name, COUNT(r.id) AS replies, IFNULL(MAX(r.edate), t.edate)"
               . "\n AS last_active FROM #__pf_topics AS t"
		       . "\n LEFT JOIN #__users AS u ON u.id = t.author"
		       . "\n LEFT JOIN #__pf_topic_replies AS r ON r.topic = t.id"
		       . "\n WHERE t.project IN($project)"
		       . $filter
		       . "\n GROUP BY t.id"
		       . "\n ORDER BY $ob $od"
		       . (($limit > 0) ? "\n LIMIT $limitstart, $limit" : "\n");
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

        // Log any errors
		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return 0;
        }

		if(!is_array($rows)) $rows = array();

		unset($db);
		return $rows;
	}

	public function LoadReply($id)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT r.*, u.name FROM #__pf_topic_replies AS r"
		       . "\n LEFT JOIN #__users AS u ON u.id = r.author"
		       . "\n WHERE r.id = $id GROUP BY r.id";
		       $db->setQuery($query);
		       $row = $db->loadObject();

		if($db->getErrorMsg()) {
		    $this->AddError($db->getErrorMsg());
			return false;
		}

		return $row;
	}

	public function LoadTopicReplies($topic)
	{
	    $db = PFdatabase::GetInstance();

		$query = "SELECT r.*, u.name FROM #__pf_topic_replies AS r"
		       . "\n LEFT JOIN #__users AS u ON u.id = r.author"
		       . "\n WHERE r.topic = $topic"
		       . "\n GROUP BY r.id"
		       . "\n ORDER BY r.cdate ASC";
		       $db->setQuery($query);
		       $rows = $db->loadObjectList();

		if(!is_array($rows)) $rows = array();

		// Log any errors
		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return array();
        }

		return $rows;
	}

	public function LoadSubscriptions($user_id = 0)
	{
	    // Load db object
	    $db = PFdatabase::GetInstance();

		if(!$user_id) {
		    $user = PFuser::GetInstance();
		    $user_id = $user->GetId();
			unset($user);
		}

		if(!$user_id) return array();

		// Load subscriptions
		$query = "SELECT topic_id FROM #__pf_topic_subscriptions"
               . "\n WHERE user_id = '$user_id'";
		       $db->setQuery($query);
		       $rows = $db->loadResultArray();

		// Log any errors
		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return 0;
        }

		if(!is_array($rows)) $rows = array();

        unset($db);
		return $rows;
	}

	public function CountTopics($project, $keyword = '')
	{
	    // Load db object
	    $db = PFdatabase::GetInstance();

	    // Setup filter
		$filter = "";
		if($keyword) {
		  $keyword = $db->Quote('%'.$db->getEscaped($keyword, true).'%', false);
          $filter .= "\n AND t.title LIKE " . $keyword;
		}
		if(!$project) return 0;

		// Count
		$query = "SELECT COUNT(t.id) FROM #__pf_topics AS t"
		       . "\n LEFT JOIN #__users AS u ON u.id = t.author"
		       . "\n LEFT JOIN #__pf_topic_replies AS r ON r.topic = t.id"
		       . "\n WHERE t.project IN($project)"
		       . $filter
		       . "\n GROUP BY t.id";
		       $db->setQuery($query);
		       $count = $db->loadResult();

		// Log any errors
		if($db->getErrorMsg()) {
            $this->AddError($db->getErrorMsg());
            return 0;
        }

        unset($db);
		return $count;
	}

	public function SaveTopic()
	{
	    $db = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();

		$title   = $db->Quote(JRequest::getVar('title'));
		$project = $user->GetWorkspace();
		$cdate   = $db->Quote(time());

		if(defined('PF_DEMO_MODE')) {
			$content = $db->Quote(JRequest::getVar('text'));
		}
		else {
			$content = $db->Quote(JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW));
		}

		// validate
		if($title == "''") {
			$this->AddError('V_TITLE');
			return false;
		}

		if($content == "''") {
			$this->AddError('V_CONTENT');
			return false;
		}

		// Save
		$query = "INSERT INTO #__pf_topics VALUES"
               . "\n (NULL, $title, $content,".$db->Quote($user->GetId()).",$project,$cdate,$cdate)";
		       $db->setQuery($query);
		       $db->query();

		$id = $db->insertid();

		if(!$id) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        $data = array($id);
        PFprocess::Event('save_topic', $data);
		return true;
	}

	public function SaveReply($topic)
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();

		$title   = $db->Quote(JRequest::getVar('title'));
		$topic   = $db->Quote($topic);
		$project = (int) $user->GetWorkspace();
		$cdate   = $db->Quote(time());

		if(defined('PF_DEMO_MODE')) {
			$content = $db->Quote(JRequest::getVar('text'));
		}
		else {
			$content = $db->Quote(JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW));
		}

		// validate
		if($title == "''") {
			$this->AddError('V_TITLE');
			return false;
		}

		if($content == "''") {
			$this->AddError('V_CONTENT');
			return false;
		}

		$query = "INSERT INTO #__pf_topic_replies VALUES"
               . "\n (NULL, $title, $content,".$db->quote($user->GetId()).",$project,$topic,$cdate,0)";
		       $db->setQuery($query);
		       $db->query();

		$id = $db->insertid();

		if(!$id) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        $data = array($id);
        PFprocess::Event('save_reply', $data);

		if(!defined('PF_DEMO_MODE')) {
			$this->SendNotification($topic, $id);
		}

		return true;
	}

	public function UpdateTopic($id)
	{
	    $db = PFdatabase::GetInstance();

		$title = $db->Quote(JRequest::getVar('title'));
		$id    = $db->Quote($id);
		$edate = time();

		if(defined('PF_DEMO_MODE')) {
			$content = $db->Quote(JRequest::getVar('text'));
		}
		else {
			$content = $db->Quote(JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW));
		}

		// Validate
		if($title == "''") {
			$this->AddError('V_TITLE');
			return false;
		}

		if($content == "''") {
			$this->AddError('V_CONTENT');
			return false;
		}

		// Update
		$query = "UPDATE #__pf_topics SET title = $title, content = $content, edate = $edate WHERE id = $id";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        $data = array($id);
        PFprocess::Event('update_topic', $data);

		return true;
	}

	public function UpdateReply($id)
	{
	    $db = PFdatabase::GetInstance();

		$title = $db->Quote(JRequest::getVar('title'));
		$id    = $db->Quote($id);
		$edate = time();

		if(defined('PF_DEMO_MODE')) {
			$content = $db->Quote(JRequest::getVar('text'));
		}
		else {
			$content = $db->Quote(JRequest::getVar('text', '', 'default', 'none', JREQUEST_ALLOWRAW));
		}

		// validate
		if($title == "''") {
			$this->AddError('V_TITLE');
			return false;
		}

		if($content == "''") {
			$this->AddError('V_CONTENT');
			return false;
		}

		$query = "UPDATE #__pf_topic_replies SET title = $title, content = $content, edate = $edate WHERE id = $id";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        $data = array($id);
        PFprocess::Event('update_reply', $data);

		return true;
	}

	public function DeleteTopic($id)
	{
	    $db   = PFdatabase::GetInstance();
	    $user = PFuser::GetInstance();

		$id = $db->Quote($id);

		$query = "DELETE FROM #__pf_topics WHERE id = $id";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		$query = "DELETE FROM #__pf_topic_replies WHERE topic = $id";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		$query = "DELETE FROM #__pf_topic_subscriptions WHERE topic_id = $id";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        $data = array($id);
        PFprocess::Event('delete_topic', $data);

		return true;
	}

	public function DeleteReply($id)
	{
	    $db = PFdatabase::GetInstance();
		$id = $db->Quote($id);

		$query = "DELETE FROM #__pf_topic_replies WHERE id = $id";
		       $db->setQuery($query);
		       $db->query();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

        $data = array($id);
        PFprocess::Event('delete_reply', $data);

		return true;
	}

	public function Subscribe($topics, $user_id)
	{
		if(!$user_id) return false;

        $db = PFdatabase::GetInstance();
        $data_topics = array();

		foreach ($topics AS $id)
		{
			$id = (int) $id;

			if(!$id) { continue; }

			$query = "SELECT topic_id FROM #__pf_topic_subscriptions"
                   . "\n WHERE topic_id = '$id' AND user_id = '$user_id'";
			       $db->setQuery($query);
			       $topic = $db->loadResult();

			if($topic) continue;

			$query = "INSERT INTO #__pf_topic_subscriptions"
                   . "\n VALUES(NULL, '$id', '$user_id')";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }

            $data_topics[] = $id;
		}

        $data = array($data_topics);
        PFprocess::Event('subscribe_topic', $data);

		return true;
	}

	function Unsubscribe($topics, $user_id)
	{
		if(!$user_id) return false;

        $db = PFdatabase::GetInstance();
        $data_topics = array();

		foreach ($topics AS $id)
		{
			$id = (int) $id;

			if(!$id) continue;

			$query = "DELETE FROM #__pf_topic_subscriptions"
                   . "\n WHERE topic_id = '$id' AND user_id = '$user_id'";
			       $db->setQuery($query);
			       $db->query();

			if($db->getErrorMsg()) {
			    $this->AddError($db->getErrorMsg());
			    return false;
		    }
            $data_topics[] = $id;
		}

        $data = array($data_topics);
        PFprocess::Event('unsubscribe_topics', $data);

		return true;
	}

	public function SendNotification($topic, $reply)
	{
		$my = PFuser::GetInstance();
		$db = PFdatabase::GetInstance();
		$jconfig = JFactory::getConfig();

		$query = "SELECT user_id FROM #__pf_topic_subscriptions WHERE topic_id = $topic";
		       $db->setQuery($query);
		       $result = $db->loadResultArray();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		if(!is_array($result)) return false;
		if(!count($result)) return false;

		$result = implode(',', $result);

		// Load subscribers
		$query = "SELECT id, name, email FROM #__users WHERE id IN($result)";
		       $db->setQuery($query);
		       $users = $db->loadObjectList();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		// Load topic
		$query = "SELECT * FROM #__pf_topics WHERE id = $topic";
		       $db->setQuery($query);
		       $topic = $db->loadObject();

		if($db->getErrorMsg()) {
			$this->AddError($db->getErrorMsg());
			return false;
		}

		// Load reply
		$reply = $this->LoadReply($reply);
		if(!is_object($topic)) return false;

		foreach ($users AS $user)
		{
			if($user->id == $my->GetId()) continue;

			// setup subject
			$subject = PFformat::Lang('PFL_SUBS_SUBJECT');
			$subject = str_replace('$title', $topic->title, $subject);

			// setup message
			$message = PFformat::Lang('PFL_SUBS_MESSAGE');
			$message = str_replace('$name', $user->name, $message);
			$message = str_replace('$user', $my->GetName(), $message);
			$message = str_replace('$title', $topic->title, $message);
			$message = str_replace('$content', strip_tags($reply->content), $message);
			// LOL!
			$message = str_replace('\n', "\n", $message);

			$mail = &JFactory::getMailer();
		    $mail->IsHTML(false);

		    // $mail->setSender( array( $jconfig->get('mailfrom'), $jconfig->get('fromname') ) );
		    $mail->setSubject( $subject );
	        $mail->setBody( $message );

	        $mail->addRecipient( $user->email );

	        $mail->Send();
	        unset($mail);
		}
	}

}
?>