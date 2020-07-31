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

require_once('class.message.php');

$activeUser = Common::data("user", "session");
$Message = new Message($activeUser);

switch ($action) {

	//////////////////////////////////////////////////////////////////
	// Create
	//////////////////////////////////////////////////////////////////
	case 'openNewDialog':
		$users = $Message->listUsers();
		$options = "";
		foreach ($users as $username) {
			$options .= "<option value=\"$username\">$username</option>\n";
		}
		?>
		<label class="title"><i class="fas fa-envelope"></i>Send a new message</label>
		<form>
			<select name="recipient">
				<option default noselect disabled value="">Select a recipient...</option>
				<?php echo $options; ?>
			</select>
			<input type="text" name="text" autofocus="autofocus" autocomplete="off" placeholder="Write a message..." />
			<button class="btn-left">Send</button>
			<button class="btn-right" onclick="atheos.modal.unload(); return false;">Cancel</button>
		</form>
		<?php
		break;

	//////////////////////////////////////////////////////////////////
	// History
	//////////////////////////////////////////////////////////////////
	case 'openChat':
		//Get received messages.
		$recipient = Common::data("sender");
		$messages = $Message->chatHistory($recipient);

		$user = "";
		$date = "";

		$chat = "";

		$bubble = "";
		$unread = false;

		foreach ($messages as $message) {
			if ($message["text"] === "") continue;

			// Set the initial sender
			if ($user === "") {
				$user = $message["sender"];
			}

			//Create a separator between user "bubbles".
			if ($message["sender"] !== $user) {
				$class = "message";
				if ($unread) {
					if ($user === $activeUser) {
						$class .= " blue";
					} else {
						$class .= " green";
					}
				}

				$user = "<span class=\"user\">$user</span>";

				$bubble = $user . $bubble;
				$bubble .= $date;



				$bubble = "<div class=\"$class\">$bubble</div>";

				$chat .= $bubble;
				$bubble = "";
				$user = $message["sender"];
				$unread = false;
			}

			$date = "<span class=\"date\">" . $message["date"] . "</span>";
			$bubble .= "<span class=\"text\">" . $message["text"] . "</span>";

			//Open the bubble.
			if ($message['unread'] === "1") $unread = true;
		}

		$class = "message";
		if ($unread) {
			if ($user === $activeUser) {
				$class .= " blue";
			} else {
				$class .= " green";
			}
		}

		$user = "<span class=\"user\">$user</span>";
		$bubble = $user . $bubble;
		$bubble .= $date;
		$bubble = "<div class=\"$class\">$bubble</div>";
		$chat .= $bubble;

		//Mark all messages as read.
		$Message->markAllRead($recipient);
		?>
		<label class="title"><i class="fas fa-envelope"></i>Chat with <?php echo $recipient; ?></label>
		<form>
			<div id="messaging_history">
				<?php echo $chat; ?>
			</div>
			<input type="hidden" name="recipient" value="<?php echo $recipient; ?>" />
			<input type="text" name="text" autofocus="autofocus" autocomplete="off" placeholder="Write a message..." />
			<button class="btn-left">Send</button>
			<button class="btn-right" onclick="atheos.modal.unload(); return false;">Cancel</button>
		</form>
		<?php
		break;
}
?>
</form>