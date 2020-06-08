/*
*  Copyright (c) Codiad & RustyGumbo, distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

(function(global, $){
    //Define core variables.
    var codiad = global.codiad,
        scripts = document.getElementsByTagName('script'),
        path = scripts[scripts.length-1].src.split('?')[0],
        curpath = path.split('/').slice(0, -1).join('/')+'/';

    //Instantiate the plugin.
    $(function() {
        $('head').append('<link rel="stylesheet" href="' + curpath + 'style.css" type="text/css" />');
        codiad.Messaging.init();
    });

    //Declare the plugin properties and methods.
    codiad.Messaging = {
        //Controller path.
        controller: curpath + 'controller.php',
        
        //Dialog path.
        dialog: curpath + 'dialog.php',
        
        //Message poll interval
        interval: 3000,
        
        //Initialization function.
        init: function() {
            var _this = this;
            
            //Add the messaging div.
            var html = "<div id='messaging-bar'><ul></ul></div>";
            $(html).insertBefore("#editor-bottom-bar");

            //Timer to check for messages.
            setInterval(function() {
                _this.checkNew();
            }, _this.interval);
        },
        
        //Show the form to create a new message.
        create: function() {
            var _this = this;

            //Show the modal form.
            codiad.modal.load(300, this.dialog + '?action=create');
            
            //Hook the submit event.
            $('#modal-content form').live('submit', function(e) {
                e.preventDefault();
                var /* Boolean */ is_valid = true;
                var /* String */ recipient = $(this).find('select[name="lst_recipient"]').val();
                var /* String */ message = $(this).find('input[name="txt_message"]').val();
                
                //Check for recipient selection.
                if(recipient.trim().length === 0) {
                    codiad.message.error("Error: A recipient must be selected.");
                    is_valid = false;
                }
                
                // Check for empty message.
                if (message.trim().length === 0) {
                    codiad.message.error("Error: Message can't be empty.");
                    is_valid = false;
                }
                
                if (is_valid) {
                    //Send the message and close the modal form.
                    _this.send(recipient, message);
                    codiad.modal.unload();
                }
            });
        },
        
        //Show the chat history.
        history: function(sender) {
            var _this = this;

            //Show the modal form.
            codiad.modal.load(500, this.dialog + '?action=history&sender=' + sender);
            
            //Hook the submit event.
            $('#modal-content form').live('submit', function(e) {
                e.preventDefault();
                var /* Boolean */ is_valid = true;
                var /* String */ recipient = $(this).find('input[name="hdn_recipient"]').val();
                var /* String */ message = $(this).find('input[name="txt_message"]').val();
                
                //Check for recipient selection.
                if(recipient.trim().length === 0) {
                    codiad.message.error("Error: A recipient must be selected.");
                    is_valid = false;
                }
                
                // Check for empty message.
                if (message.trim().length === 0) {
                    codiad.message.error("Error: Message can't be empty.");
                    is_valid = false;
                }
                
                if (is_valid) {
                    //Send the message and close the modal form.
                    _this.send(recipient, message);
                    codiad.modal.unload();
                }
            });
        },
        
        //Get the count of users registered on the file.
        send: function(recipient, message) {
            var _this = this;
            
            $.get(
                _this.controller + "?action=send&recipient=" + recipient + "&message=" + message,
                function(data) {
                    var /* Object */ responseData = codiad.jsend.parse(data);

                    //Empty response data means success.
                    if(!responseData) {
                        codiad.message.success("Your message has been sent.");
                    }
                }
            );
        },

        //Check for a new message.
        checkNew: function() {
            var _this = this;

            $.get(
                _this.controller + "?action=checknew",
                function(data) {
                    var /* Object */ responseData = codiad.jsend.parse(data);

                    if(responseData) {
                        $.each(responseData.senders, function(sender, count) {
                            var id = "messaging-" + sender;

                            //Check if there is a tab already open.
                            var chatTab = $("#" + id);

                            if(chatTab.length === 0) {
                                //Display a new chat tab.
                                var newLi = "<li id='" + id + "' class='tab-item changed'><a class='label'><span class='icon-chat'></span>" + sender + "<span class='count'> (" + count + ")</span></a><a class='close'>x</a></li>";
                                $('#messaging-bar ul').append(newLi);
                                chatTab = $("#" + id);

                                //Add a click event to open the chat box.
                                chatTab.find(".label").click(function() {
                                    chatTab.removeClass("changed");
                                    chatTab.find(".count").text("");
                                    _this.history(sender);
                                });

                                //Add a click event to close the tab.
                                chatTab.find(".close").click(function() {
                                    chatTab.remove();
                                    _this.markAllRead(sender);
                                });
                            } else {
                                chatTab.addClass("changed");
                            }
                        });
                    }
                }
            );
        },

        //Mark all messages as read.
        markAllRead: function(sender) {
            var _this = this;

            $.get(
                _this.controller + "?action=markallread&sender=" + sender,
                function(data) {
                    var /* Object */ responseData = codiad.jsend.parse(data);

                    if(responseData) {
                        //Messages have been marked as read.
                    }
                }
            );
        }
    };
})(this, jQuery);
