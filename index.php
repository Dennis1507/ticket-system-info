<?php
	session_start();
	if(isset($_SESSION['userid'])) {
		$uri = "tickets.php";
	} else {
		$uri = "login.php";
	}
	header("Location: $uri");
	die;
?>