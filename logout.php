<?php
SESSION_START();
include 'Chat.php';
$chat = new Chat();

// clear login_uuid from DB and cookie
$cookie_name = 'login_uuid';
$uuid = '';
setcookie($cookie_name, $uuid, time() - 3600, "/"); // before now to delete it
$chat->saveLoginUUID($_SESSION['userid'], $uuid);

$chat->updateUserOnline($_SESSION['userid'], 0);
$_SESSION['username'] = "";
$_SESSION['userid'] = "";
$_SESSION['login_details_id'] = "";

header("Location:index.php");
