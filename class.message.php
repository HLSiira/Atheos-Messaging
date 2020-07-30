<?php

//////////////////////////////////////////////////////////////////////////////80
//  Atheos Messaging
//////////////////////////////////////////////////////////////////////////////80
// Copyright (c) 2020 Liam Siira (liam@siira.io), distributed as-is and without
// warranty under the MIT License. See [root]/license.md for more.
// This information must remain intact.
//////////////////////////////////////////////////////////////////////////////80
// Copyright (c) 2016 Codiad & RustyGumbo
// Source: https://github.com/RustyGumbo/Codiad-Messaging
//////////////////////////////////////////////////////////////////////////////80

require_once('file_db.php');

class Message {

	//////////////////////////////////////////////////////////////////////////80
	// PROPERTIES
	//////////////////////////////////////////////////////////////////////////80
	private $user = null;
	private $database = null;

	//////////////////////////////////////////////////////////////////////////80
	// Constructor
	//////////////////////////////////////////////////////////////////////////80
	public function __construct($user) {
		$this->user = $user;
		$this->database = new file_db(BASE_PATH . '/data');
	}


	//////////////////////////////////////////////////////////////////////////80
	// Create a message.
	//////////////////////////////////////////////////////////////////////////80
	public function send($recipient = false, $text = false) {
		if (!$recipient || !$text) {
			Common::sendJSON("E403m");
			die;
		}

		$date = date("Y-m-d H:i:s");

		$query = array('sender' => $this->user, 'recipient' => $recipient, 'text' => $text, 'date' => $date, 'unread' => 1);

		$results = $this->database->create($query, 'messaging');

		if ($results) {
			Common::sendJSON("S2000");
		} else {
			Common::sendJSON("error", "Message could not be sent.");
		}
	}

	//////////////////////////////////////////////////////////////////////////80
	// Check for new messages.
	//////////////////////////////////////////////////////////////////////////80
	public function check() {
		$query = array('recipient' => $this->user, 'unread' => 1, 'text' => "*", 'sender' => "*", 'date' => "*");
		$results = $this->database->select($query, 'messaging');
		$senders = array();
		$data = array();

		if ($results !== null) {
			foreach ($results as $result) {
				$senders[$result->get_field('sender')]++;
			}
			//Prepare the return data.
			$data['senders'] = $senders;
		}

		Common::sendJSON("success", $data);
	}

	//////////////////////////////////////////////////////////////////////////80
	// Check for a new message.
	//////////////////////////////////////////////////////////////////////////80
	public function markAllRead() {
		$query = array('recipient' => $this->user, 'unread' => 1, 'text' => "*", 'sender' => $this->sender, 'date' => "*");
		$results = $this->database->select($query, 'messaging');

		foreach ($results as $result) {
			//Update the message.
			$query = array(
				'sender' => $result->get_field('sender'),
				'recipient' => $result->get_field('recipient'),
				'text' => $result->get_field('text'),
				'date' => $result->get_field('date'),
				'unread' => 0
			);

			//Workaround: file_db does not provide an update method, the entry must be deleted and re-inserted.
			$result->remove();
			$this->database->create($query, 'text');
		}
	}

	//////////////////////////////////////////////////////////////////////////80
	// Get the message history.
	//////////////////////////////////////////////////////////////////////////80
	public function chatHistory($recipient) {
		$messages = array();

		//Get the received messages.
		$query = array('recipient' => $recipient, 'unread' => "*", 'text' => "*", 'sender' => $this->user, 'date' => '*');
		$results = $this->database->select($query, 'messaging');

		foreach ($results as $result) {
			$messages[] = array(
				'sender' => $result->get_field('sender'),
				'text' => $result->get_field('text'),
				'date' => $result->get_field('date'),
				'unread' => $result->get_field('unread')
			);
		}

		//Get the sent messages.
		$query = array('recipient' => $this->user, 'unread' => "*", 'text' => "*", 'sender' => $recipient, 'date' => '*');
		$results = $this->database->select($query, 'messaging');

		foreach ($results as $result) {
			$messages[] = array(
				'sender' => $result->get_field('sender'),
				'text' => $result->get_field('text'),
				'date' => $result->get_field('date'),
				'unread' => $result->get_field('unread')
			);
		}

		//Sort the messages.
		foreach ($messages as $key => $row) {
			$date[$key] = $row['date'];
		}

		array_multisort($date, SORT_ASC, $messages);

		//Prepare the return data.
		return $messages;
	}

	//////////////////////////////////////////////////////////////////////////80
	// Get users other than the user in session.
	//////////////////////////////////////////////////////////////////////////80
	public function listUsers() {
		$users = Common::readJSON('users');
		$temp = array();

		//Remove the user in session.
		foreach ($users as $username => $data) {
			if ($username == $this->user) continue;
			$temp[] = $username;
		}

		return $temp;
	}
}
?>