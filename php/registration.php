<?php

	require_once('FirePHPCore/fb.php');
	


	$user = $_POST['username'];
	// $pass = $_POST['password'];

	fb($user);