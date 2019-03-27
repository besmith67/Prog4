<?php
session_start();
session_destroy();  //resets playid (session id)
header("Location: login.php"); //redirect back to login
?>
