<?php

    /*
    *  Copyright (c) Codiad & RustyGumbo, distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */
	    
    require_once('file_db.php');
    $dataBase = new file_db(BASE_PATH . '/data/messaging');

    class Message {

        //////////////////////////////////////////////////////////////////
        // PROPERTIES
        //////////////////////////////////////////////////////////////////

        public $sender		= '';
        public $recipient	= '';
        public $message         = '';
        public $date            = '';
        public $is_read		= 0;

        //////////////////////////////////////////////////////////////////
        // Initialize Data Base
        //////////////////////////////////////////////////////////////////

        function getDB() {
            global $dataBase;
            return $dataBase;
        }

        //////////////////////////////////////////////////////////////////
        // METHODS
        //////////////////////////////////////////////////////////////////

        // -----------------------------||----------------------------- //

        //////////////////////////////////////////////////////////////////
        // Create a message.
        //////////////////////////////////////////////////////////////////
        
        public function Create(){
            //Set the message date.
            $curDate = new DateTime();
            $this->date = $curDate->format('Y-m-d H:i:s');

            /* Array */ $query = array('sender' => $this->sender, 'recipient' => $this->recipient, 'message' => $this->message, 'date' => $this->date, 'is_read' => 0);
            /* Array */ $results = $this->getDB()->create($query, 'message');

            return $results;
        }

        //////////////////////////////////////////////////////////////////
        // Check for new messages.
        //////////////////////////////////////////////////////////////////
        
        public function CheckNew(){
            /* Array */ $query = array('recipient' => $this->recipient, 'is_read' => 0, 'message' => "*", 'sender' => "*", 'date' => "*");
            /* Array */ $results = $this->getDB()->select($query, 'message');
            /* Array */ $senders = array();
            /* Array */ $data = array();

            if ($results != null) {
                foreach($results as $result) {
                    $senders[$result->get_field('sender')]++;
                }

                //Prepare the return data.
                $data['senders'] = $senders;
            }
            
            return $data;
        }

        //////////////////////////////////////////////////////////////////
        // Check for a new message.
        //////////////////////////////////////////////////////////////////
        
        public function MarkAllRead(){
            /* Array */ $query = array('recipient' => $this->recipient, 'is_read' => 0, 'message' => "*", 'sender' => $this->sender, 'date' => "*");
            /* Array */ $results = $this->getDB()->select($query, 'message');

            foreach($results as $result) {
                //Update the message.
                /* Array */ $query = array(
                    'sender'    => $result->get_field('sender'),
                    'recipient' => $result->get_field('recipient'),
                    'message'   => $result->get_field('message'),
                    'date'      => $result->get_field('date'),
                    'is_read'   => 1
                 );

                //Workaround: file_db does not provide an update method, the entry must be deleted and re-inserted.
                $result->remove();
                $this->getDB()->create($query, 'message');
            }
        }

        //////////////////////////////////////////////////////////////////
        // Get the message history.
        //////////////////////////////////////////////////////////////////
        
        public function GetHistory(){
            /* Array */ $messages = array();
            
            //Get the received messages.
            /* Array */ $query = array('recipient' => $this->recipient, 'is_read' => "*", 'message' => "*", 'sender' => $this->sender, 'date' => '*');
            /* Array */ $results = $this->getDB()->select($query, 'message');
            
            foreach($results as $result) {
                $messages[] = array(
                    'sender'    => $result->get_field('sender'),
                    'message'   => $result->get_field('message'),
                    'date'      => $result->get_field('date'),
                    'is_read'   => $result->get_field('is_read')
                );
            }
            
            //Get the sent messages.
            /* Array */ $query = array('recipient' => $this->sender, 'is_read' => "*", 'message' => "*", 'sender' => $this->recipient, 'date' => '*');
            /* Array */ $results = $this->getDB()->select($query, 'message');
            
            foreach($results as $result) {
                $messages[] = array(
                    'sender'    => $result->get_field('sender'),
                    'message'   => $result->get_field('message'),
                    'date'      => $result->get_field('date'),
                    'is_read'   => $result->get_field('is_read')
                );
            }
            
            //Sort the messages.
            foreach ($messages as $key => $row) {
                $date[$key]  = $row['date'];
            }
            
            array_multisort($date, SORT_ASC, $messages);

            //Prepare the return data.
            return $messages;
        }

        //////////////////////////////////////////////////////////////////
        // Get users other than the user in session.
        //////////////////////////////////////////////////////////////////
        public function GetOtherUsers(){
            /* Array */ $users = getJSON('users.php');

            //Remove the user in session.
            foreach($users as $key => $user) {
                if($user['username'] == $_SESSION['user']){
                    //unset($users[$key]);
                    break;
                }
            }

            return $users;
        }
    }
?>
