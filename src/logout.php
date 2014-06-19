// Put this code in first line of web page. 
<p> This is the logout page </p>
<?php 
	session_start();
	$_SESSION = array();
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	session_unset();
	session_destroy();
	header("location: login.php");
	exit();
?>
