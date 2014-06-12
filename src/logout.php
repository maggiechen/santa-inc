// Put this code in first line of web page. 
<p> This is the logout page </p>
<?php 
session_start();
session_destroy();
?>
