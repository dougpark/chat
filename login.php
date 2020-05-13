<?php
SESSION_START();
include 'UUID.php';
include 'Chat.php';
$chat = new Chat();

// check for remember-me cookie
$cookie_name = 'login_uuid';
if (!isset($_COOKIE[$cookie_name])) {
} else {
	// cookie already set
	// get login info from DB here
	$user = $chat->loginWithUUID($_COOKIE[$cookie_name]);
	loginUser($user);
}

// called with valid user object
function loginUser($user)
{
	global $chat, $cookie_name;

	$_SESSION['username'] = $user[0]['username'];
	$_SESSION['userid'] = $user[0]['userid'];
	$chat->updateUserOnline($user[0]['userid'], 1);

	// set login_uuid to cookie and to DB
	$uuid = UUID::v4();
	setcookie($cookie_name, $uuid, time() + (86400 * 365), "/"); // 86400 = 1 day
	$chat->saveLoginUUID($user[0]['userid'], $uuid);

	$lastInsertId = $chat->insertUserLoginDetails($user[0]['userid']);
	//error_log('lastInsertId= ' . $lastInsertId);
	$_SESSION['login_details_id'] = $lastInsertId;

	header("Location:index.php");
}

// login with form username and password
$loginError = '';
if (!empty($_POST['username']) && !empty($_POST['pwd'])) {

	$user = $chat->loginUsers($_POST['username'], $_POST['pwd']);
	if (!empty($user)) {
		loginUser($user);
	} else {
		$loginError = "Invalid username or password!";
	}
}

include './header.php';

?>
<title>Chat</title>
<?php //include('./container.php');
?>
<div class="container">
	<h2 class="align-center">Chat</h2>
	<div class="row">
		<div class="col-sm-4">
			<h4>Login:</h4>
			<form method="post">
				<div class="form-group">
					<?php if ($loginError) { ?>
						<div class="alert alert-warning"><?php echo $loginError; ?></div>
					<?php } ?>
				</div>
				<div class="form-group">
					<label for="username">User:</label>
					<input type="username" class="form-control" name="username" required>
				</div>
				<div class="form-group">
					<label for="pwd">Password:</label>
					<input type="password" class="form-control" name="pwd" required>
				</div>
				<button type="submit" name="login" class="btn btn-success">Login</button>
			</form>
			<br>
			<p><b>User</b> : Adam<br><b>Password</b> : 123</p>
			<p><b>User</b> : Rose<br><b>Password</b> : 123</p>
			<p><b>User</b> : Smith<br><b>Password</b>: 123</p>
			<p><b>User</b> : March<br><b>Password</b>: 123</p>
		</div>

	</div>
</div>
<?php include './footer.php'; ?>