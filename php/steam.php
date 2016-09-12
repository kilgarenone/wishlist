<?php

  include("steam_api_class.php");

	$appSearch = $_POST['search'];
    $searchLevel = $_POST['level'];
   


	
        if($searchLevel ===  "true"){            
            $searchSteam = new Steam($appSearch);

            $result = $searchSteam->steamXpath();  
            echo (json_encode($result));

         }
        else{
             $app = new GetSteamApp($appSearch);
              $result = $app->getApp();
            echo (json_encode($result)); 
        }
  

   
?>