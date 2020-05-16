<?php
session_start();
include 'Chat.php';
$chat = new Chat();

// get user list
if ($_POST['action'] == 'update_user_list') {
    $chatUsers = $chat->chatUsers($_SESSION['userid']);
    $data = array(
        "profileHTML" => $chatUsers,
    );
    echo json_encode($data);
}

// add a new chat message
if ($_POST['action'] == 'insert_chat') {
    // error_log('chat_action insert_chat received');
    // error_log('to_user_id= ' . $_POST['to_user_id']);
    // error_log('userid= ' . $_SESSION['userid']);
    // error_log('message= ' . $_POST['chat_message']);
    // error_log('about to call insertChat');

    $chat->insertChat($_POST['to_user_id'], $_SESSION['userid'], $_POST['chat_message'], $_POST['hash']);
}

// get html formated chat messages
if ($_POST['action'] == 'show_chat') {
    $chat->showUserChat($_SESSION['userid'], $_POST['to_user_id'], $_POST['hash']);
}

// get chat messages
if ($_POST['action'] == 'update_user_chat') {

    $result = $chat->getUserChat($_SESSION['userid'], $_POST['to_user_id'], $_POST['hash']);
    //$data = array(
    //    "conversation" => $conversation,
    //);
    // echo json_encode($data);
    // dnp hash
    echo json_encode($result);
}

// get user details like name, etc.
if ($_POST['action'] == 'get_user_details') {

    $result = $chat->getUserDetails($_POST['userid']);
    echo json_encode($result);
}

// get unread message count
if ($_POST['action'] == 'update_unread_message') {
    $count = $chat->getUnreadMessageCount($_POST['to_user_id'], $_SESSION['userid']);
    $data = array(
        "count" => $count,
    );
    echo json_encode($data);
}

// update typing status
// if ($_POST['action'] == 'update_typing_status') {
//     $chat->updateTypingStatus($_POST["is_type"], $_SESSION["login_details_id"], $_POST["buddy_id"]);
// }

// get typing status
// if ($_POST['action'] == 'show_typing_status') {
//     $message = $chat->fetchIsTypeStatus($_POST['to_user_id'], $_POST["buddy_id"]);
//     $data = array(
//         "message" => $message,
//     );
//     echo json_encode($data);
// }

// save buddyId loggedUserId, buddyId
if ($_POST['action'] == 'save_buddy_id') {
    $chat->saveBuddyId($_POST["loggedUserId"], $_POST["buddyId"]);
}

// save typing status
if ($_POST['action'] == 'save_typing_status') {
    $chat->saveTypingStatus($_POST["is_type"], $_POST["loggedUserId"]);
}

// load typing status
if ($_POST['action'] == 'load_typing_status') {
    $message = $chat->loadTypingStatus($_POST['loggedUserId'], $_POST["buddyId"]);
    $data = array(
        "message" => $message,
    );
    echo json_encode($data);
}

// get html formated contacts list
if ($_POST['action'] == 'get_contact_list_details') {
    $message = $chat->getContactListDetailsOne($_SESSION['userid']);
    echo json_encode($message);
}
