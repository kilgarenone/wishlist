<?php

	include("amazon_api_class.php");

	
  $title = $_POST['search'];
  $director = $_POST['director'];
	
  $obj = new AmazonProductAPI();

	try
    {
       
      $result = $obj->searchProducts($title,
                                    AmazonProductAPI::DVD,
                                    "TITLE",
                                    $director); 
    }
    catch(Exception $e)
    {
      echo $e->getMessage();
    }

    echo (json_encode($result));

