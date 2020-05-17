<?php
//include 'UUID.php';

class Chat
{
    private $host = 'localhost';
    private $user = 'phpzag_demo';
    private $password = "123";
    private $database = "phpzag_demo";
    
    private $chatTable = 'chat';
    private $chatUsersTable = 'chat_users';
    private $chatLoginDetailsTable = 'chat_login_details';

    //private $host = '127.0.0.1';
    private $db = 'phpzag_demo';
    //private $user = 'root';
    private $pass = '123';
    private $port = "3306";
    private $charset = 'utf8';
    private $pdo = '';

    private $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset};port={$this->port}";
        try {
            $this->pdo = new \PDO($dsn, $this->user, $this->pass, $this->options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }
    }

    // get data on one user = userid
    public function getUserDetailsPDO($userid)
    {
        // select a particular user by id
        $sql = "SELECT *
                FROM {$this->chatUsersTable}
                WHERE userid=:userid
                AND username=:username";
        $data = [
            'userid' => $userid,
            'username' => 'Rose',
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        //$user = $stmt->fetch();
        $result = $stmt->fetchAll();
        // go through all the returned rows
        foreach ($result as $row) {
            $username = $row['username'];
            error_log("username= $username {$row['username']}");
            // or go through all the fields in the returned row
            // foreach ($row as $field => $value) {
            //     error_log("$field= $value");
            // }

        }
    }

    ////////////////////////////////////////////////// end PDO


    // dnp PDO login local user based on username and password
    public function loginUsers($username, $password)
    {
        $sql = "SELECT userid, username
			        FROM {$this->chatUsersTable}
                    WHERE username= :username 
                    AND password= :password";
        $data = [
            'username' => $username,
            'password' => $password
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchAll();

        return $result;
    }

    // PDO get list of all users in the database
    public function chatUsers($userid)
    {
        $sql = "SELECT *
			        FROM {$this->chatUsersTable}
                    WHERE userid != :userid";
        $data = [
            'userid' => $userid,

        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchAll();

        return $result;
    }

    // PDO get data on one user = userid
    public function getUserDetails($userid)
    {
        $sql = "SELECT *
			        FROM {$this->chatUsersTable}
                    WHERE userid = :userid";
        $data = [
            'userid' => $userid,

        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchAll();

        return $result;
    }

    // PDO get username where user = userid
    public function getUserName($userid)
    {
        $sql = "SELECT username
			        FROM {$this->chatUsersTable}
                    WHERE userid = :userid";
        $data = [
            'userid' => $userid,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchColumn();
        return $result;
    }

    // PDO get avatar where user = userid
    public function getUserAvatar($userid)
    {
        $sql = "SELECT avatar
			        FROM {$this->chatUsersTable}
                    WHERE userid = :userid";
        $data = [
            'userid' => $userid,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchColumn();
        return $result;
    }

    // dnp PDO save online status
    public function updateUserOnline($userId, $online)
    {
        $sql = "UPDATE {$this->chatUsersTable}
			    SET online = :online
                WHERE userid = :userid";

        $data = [
            'online' => $online,
            'userid' => $userId,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    // PDO set a new message in the db based on loggeduser and touser
    public function insertChat($reciever_userid, $user_id, $chat_message, $hash_in)
    {
        $sql = "INSERT INTO {$this->chatTable}
               (reciever_userid, sender_userid, message, status)
                VALUES (:reciever_userid, :sender_userid, :message, :status)";

        $data = [
            'reciever_userid' => $reciever_userid,
            'sender_userid' => $user_id,
            'message' => $chat_message,
            'status' => '1',
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        $result = $this->getUserChat($user_id, $reciever_userid, $hash_in);
        echo json_encode($result);
    }

    // PDO get all conversations for loggeduser and touser
    public function getChatData($from_userId, $to_userId)
    {
        $sql = "SELECT *
			        FROM {$this->chatTable}
                    WHERE (sender_userid = :sender_userid
                    AND reciever_userid = :reciever_userid)
                    OR (sender_userid = :sender_userid2
                    AND reciever_userid = :reciever_userid2)
                    ORDER BY timestamp ASC";

        $data = [
            'sender_userid' => $from_userId,
            'reciever_userid' => $to_userId,
            'sender_userid2' => $to_userId,
            'reciever_userid2' => $from_userId,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchAll();
        return $result;
    }


    // get conversation and format with html for display on web page
    public function getUserChat($from_user_id, $to_user_id, $hash_in)
    {
        $fromUserAvatar = $this->getUserAvatar($from_user_id);
        $toUserAvatar = $this->getUserAvatar($to_user_id);

        $fromUserName = $this->getUserName($from_user_id);
        $toUserName = $this->getUserName($to_user_id);

        $userChat = $this->getChatData($from_user_id, $to_user_id);

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
        if ($hash == $hash_in) {
            $conversation = '';
        }
        $data = array(
            "conversation" => $conversation,
            "hash" => $hash,
        );
        //echo json_encode($data);
        return $data;
    }

    // get touser details and conversation formated for direct nsert into html page
    public function showUserChat($from_user_id, $to_user_id, $hash_in)
    {
        // get details about contact user
        $userDetails = $this->getUserDetails($to_user_id);
        $toUserAvatar = '';
        foreach ($userDetails as $user) {
            $toUserAvatar = $user['avatar'];
            $userSection = '
				<div class="pl-2">
				<img width="25px" height="25px" src="userpics/' . $user['avatar'] . '" alt=""
                 class=" float-left rounded-circle" >
                 <span class="float-left pl-2 my-0"> ' . $user['username'] . '</span>

				';
        }
        // get conversation between me(logged in user) and contact user
        $result = $this->getUserChat($from_user_id, $to_user_id, $hash_in);
        $conversation = $result['conversation'];
        // dnp hash
        $hash = $result['hash'];

        $this->updateChatStatus($to_user_id, $from_user_id);

        $this->updateCurrentSession($to_user_id, $from_user_id);

        // prepare to return html formated text to web page
        $data = array(
            "userSection" => $userSection,
            "conversation" => $conversation,
            "hash" => $hash,
        );
        echo json_encode($data); // return json encoded array with html formated text
    }

    // reset status flag for unread message count
    public function updateChatStatus($to_user_id, $from_user_id)
    {

        $sql = "UPDATE {$this->chatTable}
			    SET status = '0'
                WHERE sender_userid = :sender_userid
                AND reciever_userid = :reciever_userid
                AND status = '1'";

        $data = [
            'sender_userid' => $to_user_id,
            'reciever_userid' => $from_user_id,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    public function updateCurrentSession($to_user_id, $from_user_id)
    {

        $sql = "UPDATE {$this->chatUsersTable}
			    SET current_session = :current_session
                WHERE userid = :userid
                ";

        $data = [
            'current_session' => $to_user_id,
            'userid' => $from_user_id,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }


    // PDO get last conversation date for loggeduser and touser
    public function getUsersLastConversationDate($from_userId, $to_userId)
    {
        $sql = "SELECT timestamp
			        FROM {$this->chatTable}
                    WHERE (sender_userid = :sender_userid
                    AND reciever_userid = :reciever_userid)
                    OR (sender_userid = :sender_userid2
                    AND reciever_userid = :reciever_userid2)
                    ORDER BY timestamp DESC LIMIT 1";

        $data = [
            'sender_userid' => $from_userId,
            'reciever_userid' => $to_userId,
            'sender_userid2' => $to_userId,
            'reciever_userid2' => $from_userId,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchColumn();
        return $result;
    }

    // PDO get last conversation date for loggeduser and touser
    public function getUsersLastMessage($from_userId, $to_userId)
    {
        $sql = "SELECT message
			        FROM {$this->chatTable}
                    WHERE (sender_userid = :sender_userid
                    AND reciever_userid = :reciever_userid)
                    OR (sender_userid = :sender_userid2
                    AND reciever_userid = :reciever_userid2)
                    ORDER BY timestamp DESC LIMIT 1";

        $data = [
            'sender_userid' => $from_userId,
            'reciever_userid' => $to_userId,
            'sender_userid2' => $to_userId,
            'reciever_userid2' => $from_userId,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchColumn();
        return $result;
    }

    // check the status flag for unread message count
    public function getUnreadMessageCount($senderUserid, $recieverUserid)
    {
        $sql = "SELECT count(*)
			        FROM {$this->chatTable}
                    WHERE (sender_userid = :sender_userid
                    AND reciever_userid = :reciever_userid)
                    AND status = '1'";

        $data = [
            'sender_userid' => $senderUserid,
            'reciever_userid' => $recieverUserid,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchColumn();
        if ($result == '0') {
            $result = '';
        }
        return $result;
    }

    // dnp PDO insert login status
    public function insertUserLoginDetails($userId)
    {

        $sql = "INSERT INTO {$this->chatLoginDetailsTable}
                (userid)
                VALUES (:userid)";

        $data = [
            'userid' => $userId,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $lastInsertId = $this->pdo->lastInsertId();
        return $lastInsertId;
    }

    // dnp PDO set last activity time for user
    public function updateLastActivity($loginDetailsId)
    {
        // update loggedUser
        $sql = "UPDATE {$this->chatLoginDetailsTable}
                SET last_activity = now()
                WHERE id = :id";

        $data = [
            'id' => $loginDetailsId,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }


    // dnp PDO set last activity time for user
    public function getUserLastActivity($userId)
    {
        // update loggedUser
        $sql = "SELECT last_activity FROM {$this->chatLoginDetailsTable}
                WHERE userid = :userid
                ORDER BY last_activity DESC LIMIT 1";

        $data = [
            'userid' => $userId,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        $result = $stmt->fetchAll();
        // go through all the returned rows
        foreach ($result as $row) {
            return $row['last_activity'];
        }
    }


    // get all details for contact list
    public function xetContactListDetailsGood($loggedInUserId)
    {
        $loggedUser = $this->getUserDetails($_SESSION['userid']);
        $currentSession = '';
        $loggedUserName = '';
        foreach ($loggedUser as $user) {
            $currentSession = $user['current_session'];
            $loggedUserName = $user['username'];
            $buddyId = $user['buddy_id'];
            $userPic = $user['avatar'];

            // dnp trying to set some "variables" that javascript can use
            // loggedUserName
            // loggedUserid
            // toUserName
            // toUserId
            $out = '<span id="user_data" '
                . 'data-loggedusername="' . $loggedUserName . '"'
                . 'data-loggeduserid="' . $_SESSION['userid'] . '"'
                . 'data-currentsession="' . $user['current_session'] . '"'
                . 'data-buddyid="' . $buddyId . '"'
                . 'data-touserid=" "'
                . 'data-tousername=" "'
                . '></span>';
        }

        //$out = '';
        $out .= '<ul class="contacts">';
        $chatUsers = $this->chatUsers($_SESSION['userid']);
        foreach ($chatUsers as $user) {
            $status = 'offline';
            if ($user['online']) {
                $status = 'online';
            } else {
                $status = 'offline';
            }
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

    // get all details for contact list
    // called on initial load from index.php must return html formated text
    public function getContactListDetails($loggedInUserId)
    {

        $data =  $this->getContactListDetailsOne($loggedInUserId);
        echo $data['contactList'];
        
    }

    // get all details for contact list
    // called everytime contact list is openend
    public function getContactListDetailsOne($loggedInUserId)
    {

        $loggedUser = $this->getUserDetails($_SESSION['userid']);
        $currentSession = '';
        $loggedUserName = '';
        foreach ($loggedUser as $user) {
            $currentSession = $user['current_session'];
            $loggedUserName = $user['username'];
            $buddyId = $user['buddy_id'];
            $userPic = $user['avatar'];

            // dnp trying to set some "variables" that javascript can use
            // loggedUserName
            // loggedUserid
            // toUserName
            // toUserId
            $out = '<span id="user_data" '
                . 'data-loggedusername="' . $loggedUserName . '"'
                . 'data-loggeduserid="' . $_SESSION['userid'] . '"'
                . 'data-currentsession="' . $user['current_session'] . '"'
                . 'data-buddyid="' . $buddyId . '"'
                . 'data-touserid=" "'
                . 'data-tousername=" "'
                . '></span>';
        }

        //$out = '';
        $out .= '<ul class="contacts">';
        $chatUsers = $this->chatUsers($_SESSION['userid']);
        $unreadMsgTotal = 0;
        foreach ($chatUsers as $user) {
            $status = 'offline';
            if ($user['online']) {
                $status = 'online';
            } else {
                $status = 'offline';
            }
            $activeUser = '';
            if ($user['userid'] == $currentSession) {
                $activeUser = "active";
            }
            $lastActivity = $this->getUsersLastConversationDate($_SESSION['userid'], $user['userid']);
            $lastMessage = $this->getUsersLastMessage($_SESSION['userid'], $user['userid']);
            $unreadMsgCount = $this->getUnreadMessageCount($user['userid'], $_SESSION['userid']);
            $unreadMsgTotal = $unreadMsgTotal + $unreadMsgCount;

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
            $out .= '<span id="unread_' . $user['userid'] . '" class="badge badge-pill badge-danger"  >' . $unreadMsgCount . '</span>';

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

        // make array here with $out and $unreadMsgTotal
        // contactList
        // unreadMsgTotal
        $data = [
            'contactList' => $out,
            'unreadMsgTotal' => $unreadMsgTotal,
        ];
        return $data;
    }

    // dnp PDO save buddyID
    public function saveBuddyId($loggedUserId, $buddyId)
    {
        // update loggedUser
        $sql = "UPDATE {$this->chatUsersTable}
                SET buddy_id = :buddy_id
                WHERE userid = :userid";

        $data = [
            'buddy_id' => $buddyId,
            'userid' => $loggedUserId,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    // dnp PDO save login uuid
    public function saveLoginUUID($userid, $uuid)
    {
        $sql = "UPDATE {$this->chatUsersTable}
			    SET login_uuid = :login_uuid
                WHERE userid = :userid";

        $data = [
            'login_uuid' => $uuid,
            'userid' => $userid,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    // dnp PDO save typing status
    public function saveTypingStatus($is_type, $loggedUserId)
    {
        $sql = "UPDATE {$this->chatUsersTable}
			    SET is_typing = :is_typing
                WHERE userid = :userid";

        $data = [
            'is_typing' => $is_type,
            'userid' => $loggedUserId,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    // dnp PDO get typing status for buddy to see if they are typing to loggedUser
    public function loadTypingStatus($loggedUserId, $buddyId)
    {
        $sql = "SELECT is_typing FROM {$this->chatUsersTable}
                WHERE userid = :userid AND buddy_id = :buddy_id";
        $data = [
            'userid' => $buddyId,
            'buddy_id' => $loggedUserId,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        $result = $stmt->fetchAll();
        // go through all the returned rows
        $output = '';
        foreach ($result as $row) {
            $is_typing = $row['is_typing'];
            //error_log("is_typing= $is_typing");
            if ($is_typing == 'yes') {
                $output = 'Typing';
            }
        }
        return $output;
    }


    // dnp PDO login local user based on cookie uuid
    public function loginWithUUID($uuid)
    {
        $sql = "SELECT userid, username
			        FROM {$this->chatUsersTable}
                    WHERE login_uuid= :login_uuid";
        $data = [
            'login_uuid' => $uuid,
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        $result = $stmt->fetchAll();

        return $result;
    }
}
