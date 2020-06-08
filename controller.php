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

    //////////////////////////////////////////////////////////////////
    // Send a message.
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='send'){
        $Message->sender = $_SESSION['user'];
        $Message->recipient = $_GET['recipient'];
        $Message->message = $_GET['message'];
        $results = $Message->Create();
        
        if ($results != null) {
            echo formatJSEND("success");
        } else {
            echo formatJSEND("error", "Error: Your message could not be sent.");
        }
    }

    //////////////////////////////////////////////////////////////////
    // Check for a new message.
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='checknew'){
        $Message->recipient = $_SESSION['user'];
        $data = $Message->CheckNew();
        
        echo formatJSEND("success", $data);
    }

    //////////////////////////////////////////////////////////////////
    // Mark all messages as read.
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='markallread'){
        $Message->sender = $_GET['sender'];
        $Message->recipient = $_SESSION['user'];
        $Message->MarkAllRead();
        
        echo formatJSEND("success");
    }
?>