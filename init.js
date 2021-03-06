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

(function() {

	carbon.subscribe('system.loadExtra', () => atheos.Messaging.init());

	var self = false;

	atheos.Messaging = {

		bar: null,

		//Initialization function.
		init: function() {
			if (self) return;
			self = this;

			//Add the messaging div.
			var html = '<div id="messaging_bar"><ul class="tabList"></ul></div>';
			oX(html).insertBefore("#editor-bottom-bar");
			self.bar = oX('#messaging_bar');

			//Timer to check for messages.
			carbon.subscribe('chrono.mega', self.check);

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


				if (e.which === 1 && tagName !== 'I') {
					//LeftClick = Open
					var sender = node.attr('data-sender');
					self.openChat(sender);

				} else if (e.which === 2 || tagName === 'I') {
					//MiddleClick = Close
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
		check: function() {
			echo({
				url: atheos.controller,
				data: {
					target: 'Messaging',
					action: 'check'
				},
				settled: function(status, reply) {
					if (status !== 'success') return;

					for (var sender in reply) {
						var count = reply[sender];

						var tab = self.getTab(sender);

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

		getTab: function(sender) {
			let tab = oX('#messaging_bar [data-sender="' + sender + '"]');

			if (!tab) {
				tab = oX(`<li data-sender="${sender}"><a>${sender}<span class="count"></span><i class="fas fa-envelope"></i></a><i class="close fas fa-times-circle"></i></li>`);

				oX('#messaging_bar ul').append(tab);
			}
			return tab;
		},

		//Mark all messages as read.
		markAllRead: function(sender) {
		}
	};
})();