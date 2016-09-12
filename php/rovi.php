<?php

	require_once('FirePHPCore/fb.php');
	include_once("music_metadata_class.php");

	$artist = $state = "";


	if(isset($_POST['query'])){
		$artist = sanitizeString($_POST['query']);
	}

	if(isset($_POST['state'])){
		$state = sanitizeString($_POST['state']);
	}



switch($state) 
	{
		case 'search':
			$obj = new Music($artist);
			$artistFace = $obj->getArtist();
			echo (json_encode($artistFace));			
		break;

		case 'buttonPressed':
			$obj = new Music($artist);
			$discography = $obj->getDiscoURI();
			echo (json_encode($discography));			
		break;

		case 'cache':
			$obj = new Music($artist);
			$discography = $obj->completeCache();
			echo (json_encode($discography));			
		break;
	}
	

function sanitizeString($var){
	if(get_magic_quotes_gpc()){
		$var = stripslashes($var);
	}
	// $var = mysql_real_escape_string($var);  only usable when there is open sql connection
	$var = htmlentities($var);
	$var = strip_tags($var);
	return $var;
}
