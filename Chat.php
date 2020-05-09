<?php
class Chat
{
    private $host = 'localhost';
    private $user = 'phpzag_demo';
    private $password = "123";
    private $database = "phpzag_demo";
    private $chatTable = 'chat';
    private $chatUsersTable = 'chat_users';
    private $chatLoginDetailsTable = 'chat_login_details';
    private $dbConnect = false;
    public function __construct()
    {
        if (!$this->dbConnect) {
            $conn = new mysqli($this->host, $this->user, $this->password, $this->database);
            if ($conn->connect_error) {
                die("Error failed to connect to MySQL: " . $conn->connect_error);
            } else {
                $this->dbConnect = $conn;
            }
        }
    }

    // db connection
    private function getData($sqlQuery)
    {
        $result = mysqli_query($this->dbConnect, $sqlQuery);
        if (!$result) {
            die('Error in query: ' . mysqli_error($this->dbConnect));
        }
        $data = array();
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    // helper for unread message count
    private function getNumRows($sqlQuery)
    {
        $result = mysqli_query($this->dbConnect, $sqlQuery);
        if (!$result) {
            die('Error in query: ' . mysqli_error($this->dbConnect));
        }
        $numRows = mysqli_num_rows($result);
        return $numRows;
    }

    // login local user based on username and password
    public function loginUsers($username, $password)
    {
        $sqlQuery = "
			SELECT userid, username
			FROM " . $this->chatUsersTable . "
			WHERE username='" . $username . "' AND password='" . $password . "'";
        return $this->getData($sqlQuery);
    }

    // get list of all users in the database
    public function chatUsers($userid)
    {
        $sqlQuery = "
			SELECT * FROM " . $this->chatUsersTable . "
			WHERE userid != '$userid'";
        return $this->getData($sqlQuery);
    }

    // get data on one user = userid
    public function getUserDetails($userid)
    {
        $sqlQuery = "
			SELECT * FROM " . $this->chatUsersTable . "
			WHERE userid = '$userid'";
        return $this->getData($sqlQuery);
    }

    // get user name where user = userid
    public function getUserName($userid)
    {
        $userDetails = $this->getUserDetails($userid);
        $userName = "";
        foreach ($userDetails as $user) {
            $userName = $user['username'];
        }
        return $userName;

    }

    // get avatar where user = userid
    public function getUserAvatar($userid)
    {
        $sqlQuery = "
			SELECT avatar
			FROM " . $this->chatUsersTable . "
			WHERE userid = '$userid'";
        $userResult = $this->getData($sqlQuery);
        $userAvatar = '';
        foreach ($userResult as $user) {
            $userAvatar = $user['avatar'];
        }
        return $userAvatar;
    }

    // set user online status userid
    public function updateUserOnline($userId, $online)
    {
        $sqlUserUpdate = "
			UPDATE " . $this->chatUsersTable . "
			SET online = '" . $online . "'
			WHERE userid = '" . $userId . "'";
        mysqli_query($this->dbConnect, $sqlUserUpdate);
    }

    // set a new message in the db based on loggeduser and touser
    public function insertChat($reciever_userid, $user_id, $chat_message, $hash_in)
    {
        $sqlInsert = "
			INSERT INTO " . $this->chatTable . "
			(reciever_userid, sender_userid, message, status)
			VALUES ('" . $reciever_userid . "', '" . $user_id . "', '" . $chat_message . "', '1')";
        $result = mysqli_query($this->dbConnect, $sqlInsert);
        //error_log('inserted into sql chatTable, reciever_userid='.$sqlInsert);
        //error_log('this->dbConnect= '. $this->dbConnect);
        // error_log('result= '.$result);
        // error_log('sql error = ' .mysqli_connect_error());
        // error_log('Error in query: '. mysqli_error($this->dbConnect));

        if (!$result) {
            //error_log('bad result '. mysqli_error($this->dbConnect));
            //error_log('sql error = ' .mysqli_connect_error());
            //error_log('Error in query: '. mysqli_error($this->dbConnect));

            return ('Error in query: ' . mysqli_error($this->dbConnect));
        } else {
            //error_log('good result');
            $result = $this->getUserChat($user_id, $reciever_userid, $hash_in);
            $conversation = $result['conversation'];
            $hash = $result['hash'];
            $data = array(
                "conversation" => $conversation,
                "hash" => $hash,
            );
            echo json_encode($data);
        }
    }

    // dnp get last conversation date for loggeduser and touser
    public function getUsersLastConversationDate($from_user_id, $to_user_id)
    {
        $sqlQuery = "
					SELECT * FROM " . $this->chatTable . "
					WHERE (sender_userid = '" . $from_user_id . "'
					AND reciever_userid = '" . $to_user_id . "')
					OR (sender_userid = '" . $to_user_id . "'
					AND reciever_userid = '" . $from_user_id . "')
					ORDER BY timestamp DESC LIMIT 1";
        $result = $this->getData($sqlQuery);
        foreach ($result as $row) {
            return $row['timestamp'];
        }
    }

    // dnp get last message for loggeduser and touser
    public function getUsersLastMessage($from_user_id, $to_user_id)
    {
        $sqlQuery = "
					SELECT * FROM " . $this->chatTable . "
					WHERE (sender_userid = '" . $from_user_id . "'
					AND reciever_userid = '" . $to_user_id . "')
					OR (sender_userid = '" . $to_user_id . "'
					AND reciever_userid = '" . $from_user_id . "')
					ORDER BY timestamp DESC LIMIT 1";
        $result = $this->getData($sqlQuery);
        foreach ($result as $row) {
            return $row['message'];
        }
    }

    // get conversation and format with html for display on web page
    public function getUserChat($from_user_id, $to_user_id, $hash_in)
    {
        $fromUserAvatar = $this->getUserAvatar($from_user_id);
        $toUserAvatar = $this->getUserAvatar($to_user_id);

        $fromUserName = $this->getUserName($from_user_id);
        $toUserName = $this->getUserName($to_user_id);

        $sqlQuery = "
			SELECT * FROM " . $this->chatTable . "
			WHERE (sender_userid = '" . $from_user_id . "'
			AND reciever_userid = '" . $to_user_id . "')
			OR (sender_userid = '" . $to_user_id . "'
			AND reciever_userid = '" . $from_user_id . "')
			ORDER BY timestamp ASC";
        $userChat = $this->getData($sqlQuery);
        //dnp
        $conversation = '<ul class="chat">';
        foreach ($userChat as $chat) {
            $user_name = '';
            if ($chat["sender_userid"] == $to_user_id) {
                // left side header
                $conversation .= '<li class="left clearfix sent">';
                $conversation .= ' <span class="chat-img float-left">';
                $conversation .= '  <img width="50px" height="50px" src="userpics/' . $toUserAvatar . '" alt="" ';
                $conversation .= '  class="rounded-circle">';
                $conversation .= ' </span>';
                $conversation .= ' <div class="chat-body clearfix">';
                $conversation .= '  <div class="header">';
                $conversation .= '   <strong class="primary-font">' . $toUserName . '</strong> <small';
                $conversation .= '   class="float-right text-muted">';
                $conversation .= '   <span class="far fa-clock"></span> ' . $chat['timestamp'] . '</small>';
                $conversation .= '  </div>';
            } else {
                $conversation .= '<li class="replies right clearfix">';
                $conversation .= ' <span class="chat-img float-right">';
                $conversation .= '  <img width="50px" height="50px" src="userpics/' . $fromUserAvatar . '" alt="" ';
                $conversation .= '  class="rounded-circle">';
                $conversation .= ' </span>';
                $conversation .= ' <div class="chat-body clearfix">';
                $conversation .= '  <div class="header">';
                $conversation .= '   <strong class="float-right primary-font">' . $fromUserName . '</strong> <small';
                $conversation .= '   class=" text-muted">';
                $conversation .= '   <span class="far fa-clock"></span> ' . $chat['timestamp'] . '</small>';
                $conversation .= '  </div>';
            }

            // message text
            $conversation .= '  <p>' . $chat["message"] . '</p>';
            $conversation .= ' </div>';
            $conversation .= '</li>';
        }
        $conversation .= '</ul>';
        // dnp hash
        $hash = hash('crc32b', $conversation);
        // if hash matches hash_in then no changes so do not return a big string
        if ($hash == $hash_in) {$conversation = '';}
        $data = array(
            "conversation" => $conversation,
            "hash" => $hash,
        );
        //echo json_encode($data);
        return $data;
    }

    // get touser details and conversation formated for direct insert into html page
    public function showUserChat($from_user_id, $to_user_id, $hash_in)
    {
        // get details about contact user
        $userDetails = $this->getUserDetails($to_user_id);
        $toUserAvatar = '';
        foreach ($userDetails as $user) {
            $toUserAvatar = $user['avatar'];
            $userSection = '
				<div class="pl-2 pt-2 ">
				<img width="50px" height="50px" src="userpics/' . $user['avatar'] . '" alt=""
				 class=" float-left rounded-circle" >
				</div>
				<div class="">
				<span class="float-left pl-2 pt-2"> <h3>' . $user['username'] . '</h3></span>
				</div>
				';
        }
        // get conversation between me(logged in user) and contact user
        $result = $this->getUserChat($from_user_id, $to_user_id, $hash_in);
        $conversation = $result['conversation'];
        // dnp hash
        $hash = $result['hash'];

        // update chat user read status
        $sqlUpdate = "
			UPDATE " . $this->chatTable . "
			SET status = '0'
			WHERE sender_userid = '" . $to_user_id . "' AND reciever_userid = '" . $from_user_id . "' AND status = '1'";
        mysqli_query($this->dbConnect, $sqlUpdate);

        // update users current chat session, so... current_session is buddy_id
        $sqlUserUpdate = "
			UPDATE " . $this->chatUsersTable . "
			SET current_session = '" . $to_user_id . "'
			WHERE userid = '" . $from_user_id . "'";
        mysqli_query($this->dbConnect, $sqlUserUpdate);

        // prepare to return html formated text to web page
        $data = array(
            "userSection" => $userSection,
            "conversation" => $conversation,
            "hash" => $hash,
        );
        echo json_encode($data); // return json encoded array with html formated text
    }

    // get unread message count for user and buddy
    public function getUnreadMessageCount($senderUserid, $recieverUserid)
    {
        $sqlQuery = "
			SELECT * FROM " . $this->chatTable . "
			WHERE sender_userid = '$senderUserid' AND reciever_userid = '$recieverUserid' AND status = '1'";
        $numRows = $this->getNumRows($sqlQuery);
        $output = '';
        if ($numRows > 0) {
            $output = $numRows;
        }
        return $output;
    }

    // set/update typing status
    public function updateTypingStatus($is_type, $loginDetailsId, $buddyId)
    {
        $sqlUpdate = "
			UPDATE " . $this->chatLoginDetailsTable . "
			SET is_typing = '" . $is_type . "' , buddy_id = '" . $buddyId . "'
            WHERE id = '" . $loginDetailsId . "'";
        //error_log($sqlUpdate);
        mysqli_query($this->dbConnect, $sqlUpdate);
    }

    // get typing status
    public function fetchIsTypeStatus($userId, $buddyId)
    {
        $sqlQuery = "
		SELECT is_typing, buddy_id FROM " . $this->chatLoginDetailsTable . "
		WHERE userid = '" . $userId . "' ORDER BY last_activity DESC LIMIT 1";
        $result = $this->getData($sqlQuery);
        $output = '';
        foreach ($result as $row) {
            if ($row["is_typing"] == 'yes' and $row["buddy_id"] == $buddyId) {
                $output = 'Typing';
            }
        }
        return $output;
    }

    // set/update login status
    public function insertUserLoginDetails($userId)
    {
        $sqlInsert = "
			INSERT INTO " . $this->chatLoginDetailsTable . " (userid)
			VALUES ('" . $userId . "')";
        //error_log('insert sql= ' . $sqlInsert);

        mysqli_query($this->dbConnect, $sqlInsert);
        $lastInsertId = mysqli_insert_id($this->dbConnect);
        return $lastInsertId;
    }

    // set last activity time for user
    public function updateLastActivity($loginDetailsId)
    {
        $sqlUpdate = "
			UPDATE " . $this->chatLoginDetailsTable . "
			SET last_activity = now()
			WHERE id = '" . $loginDetailsId . "'";
        mysqli_query($this->dbConnect, $sqlUpdate);
    }

    // get last activity time for user
    public function getUserLastActivity($userId)
    {
        $sqlQuery = "
			SELECT last_activity FROM " . $this->chatLoginDetailsTable . "
			WHERE userid = '$userId' ORDER BY last_activity DESC LIMIT 1";
        $result = $this->getData($sqlQuery);
        foreach ($result as $row) {
            return $row['last_activity'];
        }
    }

    // get last activity time for loggedInUser
    public function getUserListDetails($loggedInUserId)
    {

        $loggedUser = $this->getUserDetails($_SESSION['userid']);
        $currentSession = '';
        $loggedUserName = '';
        foreach ($loggedUser as $user) {
            $currentSession = $user['current_session'];
            $loggedUserName = $user['username'];
            $userPic = $user['avatar'];

            // dnp trying to set some "variables" that javascript can use
            // loggedUserName
            // loggedUserid
            // toUserName
            // toUserId
            echo '<span id="user_data" '
                . 'data-loggedusername="' . $loggedUserName . '"'
                . 'data-loggeduserid="' . $_SESSION['userid'] . '"'
                . 'data-currentsession="' . $user['current_session'] . '"'
                . 'data-touserid=" "'
                . 'data-tousername=" "'
                . '></span>';

        }

        $out = '';
        $out .= '<ul class="contacts">';
        $chatUsers = $this->chatUsers($_SESSION['userid']);
        foreach ($chatUsers as $user) {
            $status = 'offline';
            if ($user['online']) {
                $status = 'online';
            } else { $status = 'offline';}
            $activeUser = '';
            if ($user['userid'] == $currentSession) {
                $activeUser = "active";
            }
            $lastActivity = $this->getUsersLastConversationDate($_SESSION['userid'], $user['userid']);
            $lastMessage = $this->getUsersLastMessage($_SESSION['userid'], $user['userid']);

            //$lastActivity = $chat->getUserLastActivity($user['userid']);

            $out .= '<li id="' . $user['userid'] . '" class="left clearfix contact ' . $activeUser . '" data-touserid="' . $user['userid'] . '" data-tousername="' . $user['username'] . '">';
            $out .= ' <button type="button" data-dismiss="modal" class="text-left btn-block"';
            $out .= ' style="padding: 0; border: none; background: none;">';

            // contact image
            //echo '<span class="float-left">';
            $out .= '<img width="50px" height="50px" 25px, src="userpics/' . $user['avatar'] . '" alt="" class="rounded-circle float-left">';
            //echo "</span>";

            // TBD contact on-line status
            $out .= '<span id="status_' . $user['userid'] . '" class="float-left contact-status ' . $status . '"></span>';

            // contact name
            $out .= '<div class="contacts-body clearfix pr-1">';
            // echo '<div class="header">';
            $out .= '<strong class="primary-font">' . $user['username'] . '</strong>';

            // contact un-read message count
            $out .= '<span id="unread_' . $user['userid'] . '" class="badge badge-pill badge-danger"  >' . $this->getUnreadMessageCount($user['userid'], $_SESSION['userid']) . '</span>';

            // contact is typing
            $out .= '<small class="float-right text-dark"><span id="xisTyping_' . $user['userid'] . '" class="isTyping"></span></small>';

            //  contact last activity
            $out .= '<small class="float-right text-dark">';
            $out .= '<span class="far fa-clock"></span> ' . $lastActivity;
            $out .= '</small>';

            //  contact last message text
            $out .= '<p class="text-dark " style="font-size: .75rem">';
            $out .= $lastMessage;
            $out .= '</p>';

            // echo '</div>'; // end header
            $out .= '</div>'; // end contacts-body
            $out .= '</button>';
            $out .= '</li>';
        }
        $out .= '</ul>';

        echo $out;

    }

    // dnp save buddyId
    public function saveBuddyId($loggedUserId, $buddyId)
    {
        $sqlUpdate = "
			UPDATE " . $this->chatUsersTable . "
			SET buddy_id = '" . $buddyId . "'
            WHERE userid = '" . $loggedUserId . "'";
        // error_log($sqlUpdate);
        mysqli_query($this->dbConnect, $sqlUpdate);
    }

    // dnp save typing status
    public function saveTypingStatus($is_type, $loggedUserId)
    {
        $sqlUpdate = "
			UPDATE " . $this->chatUsersTable . "
			SET is_typing = '" . $is_type . "'
            WHERE userid = '" . $loggedUserId . "'";
        //error_log($sqlUpdate);
        mysqli_query($this->dbConnect, $sqlUpdate);
    }

    // dnp get typing status for buddy to see if they are typing to loggedUser
    public function loadTypingStatus($loggedUserId, $buddyId)
    {
        $sqlQuery = "
		SELECT is_typing FROM " . $this->chatUsersTable . "
        WHERE userid = '" . $buddyId . "' AND buddy_id = '" . $loggedUserId . "'";
        $result = $this->getData($sqlQuery);
        $output = '';
        foreach ($result as $row) {
            if ($row["is_typing"] == 'yes') {
                $output = 'Typing';
            }
        }
        return $output;
    }

}
