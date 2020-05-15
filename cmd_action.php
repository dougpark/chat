<?php
session_start();
include 'Cmd.php';
$cmd = new Cmd();

// get user list
if ($_POST['action'] == 'run_command') {
    $chatUsers = $chat->chatUsers($_SESSION['userid']);
    $data = array(
        "profileHTML" => $chatUsers,
    );
    echo json_encode($data);
}
