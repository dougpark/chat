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

    private function getNumRows($sqlQuery)
    {
        $result = mysqli_query($this->dbConnect, $sqlQuery);
        if (!$result) {
            die('Error in query: ' . mysqli_error($this->dbConnect));
        }
        $numRows = mysqli_num_rows($result);
        return $numRows;
    }

    public function loginUsers($username, $password)
    {
        $sqlQuery = "
			SELECT userid, username
			FROM " . $this->chatUsersTable . "
			WHERE username='" . $username . "' AND password='" . $password . "'";
        return $this->getData($sqlQuery);
    }

    public function chatUsers($userid)
    {
        $sqlQuery = "
			SELECT * FROM " . $this->chatUsersTable . "
			WHERE userid != '$userid'";
        return $this->getData($sqlQuery);
    }

    public function getUserDetails($userid)
    {
        $sqlQuery = "
			SELECT * FROM " . $this->chatUsersTable . "
			WHERE userid = '$userid'";
        return $this->getData($sqlQuery);
    }

    public function getUserName($userid)
    {
        $userDetails = $this->getUserDetails($userid);
        $userName = "";
        foreach ($userDetails as $user) {
            $userName = $user['username'];
        }
        return $userName;

    }

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
    public function updateUserOnline($userId, $online)
    {
        $sqlUserUpdate = "
			UPDATE " . $this->chatUsersTable . "
			SET online = '" . $online . "'
			WHERE userid = '" . $userId . "'";
        mysqli_query($this->dbConnect, $sqlUserUpdate);
    }

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

    // dnp
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

    // dnp
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

    // get chat messages formated for direct insert into html page
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

        // update users current chat session
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

    // update typing status
    public function updateTypingStatus($is_type, $loginDetailsId)
    {
        $sqlUpdate = "
			UPDATE " . $this->chatLoginDetailsTable . "
			SET is_typing = '" . $is_type . "'
			WHERE id = '" . $loginDetailsId . "'";
        mysqli_query($this->dbConnect, $sqlUpdate);
    }

    // get typing status
    public function fetchIsTypeStatus($userId)
    {
        $sqlQuery = "
		SELECT is_typing FROM " . $this->chatLoginDetailsTable . "
		WHERE userid = '" . $userId . "' ORDER BY last_activity DESC LIMIT 1";
        $result = $this->getData($sqlQuery);
        $output = '';
        foreach ($result as $row) {
            if ($row["is_typing"] == 'yes') {
                $output = 'Typing';
            }
        }
        return $output;
    }

    // update login status
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
}
