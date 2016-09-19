<?php
	session_start();
	$_SESSION['logout'] = true;
	header('Location: index.php');