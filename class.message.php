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

class Message {

	//////////////////////////////////////////////////////////////////////////80
	// PROPERTIES
	//////////////////////////////////////////////////////////////////////////80
	private $activeUser = null;
	private $db = null;

	//////////////////////////////////////////////////////////////////////////80
	// Constructor
	//////////////////////////////////////////////////////////////////////////80
	public function __construct($user) {
		$this->activeUser = SESSION("user");
		$this->db = Common::getObjStore("messages");
	}


	//////////////////////////////////////////////////////////////////////////80
	// Create a message.
	//////////////////////////////////////////////////////////////////////////80
	public function send($recipient = false, $message = false) {
		if (!$recipient || !$message) Common::send("error", "Missing recipient or text.");


		$date = date("Y-m-d H:i:s");

		$name = Common::saveCache($date . $this->activeUser . $recipient, $message, "messages");

		if (!$name) Common::send("error", "Message could not be sent.");


		$value = array(
			"sender" => $this->activeUser,
			"recipient" => $recipient,
			"name" => $name,
			"date" => $date,
			"unread" => 1
		);

		$results = $this->db->insert($value);

		if ($results) {
			Common::send("success");
		} else {
			Common::send("error", "Message could not be sent.");
		}
	}

	//////////////////////////////////////////////////////////////////////////80
	// Check for new messages.
	//////////////////////////////////////////////////////////////////////////80
	public function check() {
		$where = array(
			["recipient", "==", $this->activeUser],
		);

		$results = $this->db->select($where);
		$senders = array();

		if (empty($results)) Common::send("notice", "No new messages");

		foreach ($results as $message) {
			$sender = $message["sender"];
			$unread = $message["unread"];
			$senders[$sender] = isset($senders[$sender]) ? $senders[$sender] + $unread : 0;
		}

		//Prepare the return data.
		Common::send("success", $senders);
	}

	//////////////////////////////////////////////////////////////////////////80
	// Check for a new message.
	//////////////////////////////////////////////////////////////////////////80
	public function markAllRead($sender, $silent = true) {
		$where = array(
			["recipient", "==", $this->activeUser],
			["unread", "==", 1],
		);
		$value = array(
			"unread" => 0
		);

		$results = $this->db->update($where, $value);

		if(!$silent) Common::send("success", "Marked all as read");
	}

	//////////////////////////////////////////////////////////////////////////80
	// Get the message history.
	//////////////////////////////////////////////////////////////////////////80
	public function chatHistory($recipient) {
		$chat = array();

		//Get the sent messages.
		$where = array(
			["recipient", "==", $recipient],
			["sender", "==", $this->activeUser]
		);

		$sent = $this->db->select($where);
		foreach ($sent as $message) {
			$chat[] = array(
				"sender" => $message["sender"],
				"text" => Common::loadCache($message["name"], "messages"),
				"date" => $message["date"],
				"unread" => $message["unread"]
			);
		}

		//Get the recieved messages.
		$where = array(
			["recipient", "==", $this->activeUser],
			["sender", "==", $recipient]
		);
		$recieved = $this->db->select($where);

		foreach ($recieved as $message) {
			$chat[] = array(
				"sender" => $message["sender"],
				"text" => Common::loadCache($message["name"], "messages"),
				"date" => $message["date"],
				"unread" => $message["unread"]
			);
		}
		
		Common::saveJSON("chat", $chat);

		//Sort the messages.
		foreach ($chat as $key => $message) {
			$date[$key] = $message["date"];
		}

		array_multisort($date, SORT_ASC, $chat);

		//Prepare the return data.
		return $chat;
	}

	//////////////////////////////////////////////////////////////////////////80
	// Get users other than the user in session.
	//////////////////////////////////////////////////////////////////////////80
	public function listUsers() {
		$users = Common::loadJSON("users");
		$temp = array();

		//Remove the user in session.
		foreach ($users as $username => $data) {
			if ($username == $this->activeUser) continue;
			$temp[] = $username;
		}

		return $temp;
	}
}