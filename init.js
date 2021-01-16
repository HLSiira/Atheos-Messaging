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

//This is a test of the emergency system

(function(global) {

	var atheos = global.atheos,
		carbon = global.carbon;

	carbon.subscribe('system.loadExtra', () => atheos.Messaging.init());

	var self = null;

	atheos.Messaging = {

		path: atheos.path + 'plugins/Messaging/',
		controller: this.path + 'controller.php',
		dialog: this.path + 'dialog.php',

		bar: null,

		//Initialization function.
		init: function() {
			self = this;

			//Add the messaging div.
			var html = '<div id="messaging_bar"><ul class="tabList"></ul></div>';
			oX(html).insertBefore("#editor-bottom-bar");
			self.bar = oX('#messaging_bar');

			//Timer to check for messages.
			carbon.subscribe('chrono.mega', self.checkNew);
			self.checkNew();

			self.bar.on('click, auxclick', function(e) {
				e.stopPropagation();

				var tagName = e.target.tagName;
				var node = oX(e.target);

				if (tagName === 'UL') {
					return;
				}
				if (['I', 'A', 'SPAN'].indexOf(tagName) > -1) {
					node = node.parent('LI');
				}


				//LeftClick = Open
				if (e.which === 1 && tagName !== 'I') {
					var sender = node.attr('data-sender');
					self.openChat(sender);

					//MiddleClick = Close
				} else if (e.which === 2 || tagName === 'I') {
					node.remove();
				}
			});
		},

		//Show the form to create a new message.
		openNewDialog: function() {
			atheos.modal.load(300, {
				target: 'Messaging',
				action: 'openNewDialog',
				listener: self.create
			});
		},

		//Show the chat history.
		openChat: function(sender) {
			atheos.modal.load(300, {
				target: 'Messaging',
				action: 'openChat',
				sender,
				listener: self.create,
				callback: function() {
					var element = oX('#messaging_history').el;
					element.scrollTop = element.scrollHeight - element.clientHeight;
					atheos.common.hideOverlay();
				}
			});
		},

		//Get the count of users registered on the file.
		create: function(e) {
			e.preventDefault();
			var is_valid = true;
			var recipient = oX('#modal_content [name="recipient"]').value();
			var text = oX('#modal_content input[name="text"]').value();

			//Check for recipient selection.
			if (recipient.trim().length === 0) {
				atheos.toast.show('error', 'Error: A recipient must be selected.');
				is_valid = false;
			}

			// Check for empty message.
			if (text.trim().length === 0) {
				atheos.toast.show('error', 'Error: Message can\'t be empty.');
				is_valid = false;
			}

			if (is_valid) {
				//Send the message and close the modal form.
				self.send(recipient, text);
				if (oX('#messaging_history')) {
					setTimeout(function() {
						self.openChat(recipient);
					}, 250);
				} else {
					atheos.modal.unload();
				}
			}
		},

		send: function(recipient, text) {
			echo({
				url: atheos.controller,
				data: {
					target: 'Messaging',
					action: 'send',
					recipient,
					text
				},
				success: function(reply) {
					log(reply);
				}
			});
		},

		//Check for a new message.
		checkNew: function() {
			echo({
				url: atheos.controller,
				data: {
					target: 'Messaging',
					action: 'check'
				},
				success: function(reply) {
					if (reply.status !== 'success') return;

					for (var sender in reply.senders) {
						var count = reply.senders[sender];

						var tab = oX('#messaging_bar [data-sender="' + sender + '"]');

						if (!tab) {
							oX('#messaging_bar ul').append(`<li data-sender="${sender}"><a>${sender}<span class="count"></span><i class="fas fa-envelope"></i></a><i class="close fas fa-times-circle"></i></li>`);
						}
						tab = oX('#messaging_bar [data-sender="' + sender + '"]');
						if (count > 0) {
							tab.addClass('changed');
							tab.find('.count').html(` (${count})`);

							if (oX('#messaging_history') && oX('input[name="recipient"]').value() === sender) {
								self.openChat(sender);
							}
						} else {
							tab.removeClass('changed');
							tab.find('.count').html(``);
						}
					}
				}
			});
		},

		//Mark all messages as read.
		markAllRead: function(sender) {

			$.get(
				self.controller + "?action=markallread&sender=" + sender,
				function(data) {
					var responseData = atheos.jsend.parse(data);

					if (responseData) {
						//Messages have been marked as read.
					}
				}
			);
		}
	};
})(this);