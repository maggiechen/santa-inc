<p id = "welcome-title"> Welcome </p>
<p id = "description"> Started in A.D. 326, Santa Inc. is a dedicated and privately 
owned company, lead by the visionary Nicholas Claus. Its goal is to deliver presents 
to all the children of the world. After nearly 2 millenia of hard work and dedication, 
Santa Inc. has become the forefront of holiday spirit and celebration.</p>

<p id = "sign-up-button">
<form method="POST" action="santa-inc.php">
<p><input type="submit" value="Sign up" name="signup"></p>
</form>
</p>

<p id = "login-button">
<form method="POST" action="santa-inc.php">
<p><input type="submit" value="Log in" name="login"></p>
</form>
</p>


<?php

if (array_key_exists('signup', $_POST)) {
	header("location: signup.php");
}

if (array_key_exists('login', $_POST)) {
	header("location: login.php");
}

?>
