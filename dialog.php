<?php
    
    /*
    *  Copyright (c) Codiad & RustyGumbo, distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */
    
    require_once('../../common.php');
    require_once('class.message.php');

    /* Object */ $Message = new Message();

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////
    
    checkSession();
?>
<form>
<?php
    switch($_GET['action']){
    
        //////////////////////////////////////////////////////////////////
        // Create
        //////////////////////////////////////////////////////////////////
        case 'create':
            /* Array */ $users = $Message->GetOtherUsers();
?>
    <label>Recipient</label>
    <select name="lst_recipient">
        <option value="">Select a recipient...</option>
        <?php foreach($users as $user): ?>
        <option value="<?php echo $user['username']; ?>"><?php echo $user['username']; ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="txt_message" autofocus="autofocus" autocomplete="off" placeholder="Write a message..." />
    <button class="btn-left">Send</button>
    <button class="btn-right" onclick="codiad.modal.unload(); return false;">Cancel</button>
<?php
            break;
    
        //////////////////////////////////////////////////////////////////
        // History
        //////////////////////////////////////////////////////////////////
        case 'history':
            //Get received messages.
            $Message->sender = $_GET['sender'];
            $Message->recipient = $_SESSION['user'];
            /* Array */ $messages = $Message->GetHistory();
            
            //Mark all messages as read.
            $Message->MarkAllRead();
?>
    <label>Chat with <?php echo $_GET['sender']; ?></label>
    <div id="messaging-history-<?php echo $_GET['sender']; ?>" class="messaging-history">
<?php
            /* String */ $user = "";
            /* String */ $date = ""; //Used to print the date on the last message.
            
            foreach($messages as $message) {
                /* String */ $html_before = "";
                
                //Create a separator between user "bubbles".
                if($message['sender'] != $user) {
                    //Close the previous bubble.
                    if($user !== "") {
                        $html_before .= "</div>";
                    }
                    //Establish the new user.
                    $user = $message['sender'];
                    
                    //Print the date at the end of all messages.
                    if(isset($date)) {
                        $html_before .= "<div class='messaging-date'>" . $date . "</div>";
                    }
                    
                    //Open the bubble.
                    if($message['is_read']) {
                        $html_before .= "<div class='messaging-message'>";
                    } else {
                        $html_before .= "<div class='messaging-message new'>";
                    }
                    
                    //Print the username.
                    $html_before .=  "<div class='messaging-user'>" . $message['sender'] . "</div>";
                }
                
                //Print the prefixed HTML.
                echo $html_before;
                
                //Print the message.
                echo $message['message'] . "<br />";
                
                //Set the date variable.
                $date = $message['date'];
            }
            
            echo "<div class='messaging-date'>" . $date . "</div></div>";
?>
    </div>
    <input type="text" name="txt_message" autofocus="autofocus" autocomplete="off" placeholder="Write a message..." />
    <input type="hidden" name="hdn_recipient" value="<?php echo $_GET['sender']; ?>" />
    <button class="btn-left">Send</button>
    <button class="btn-right" onclick="codiad.modal.unload(); return false;">Cancel</button>
    <script>$('.messaging-history').scrollTop($('.messaging-history')[0].scrollHeight);</script>
<?php
            break;
    }
?>
</form>
