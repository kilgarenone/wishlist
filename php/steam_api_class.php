<?php
	require_once('FirePHPCore/fb.php');

	class Steam
	{
		
		private $doc;
		const APP_HOST = "http://store.steampowered.com/api/appdetails";
		const APP_SEARCH = "http://store.steampowered.com/search";
		const APP_FILTER = "basic,price_overview,release_date";



		function __construct($appSearch){
    
	    	$this->oldSetting = libxml_use_internal_errors(true);

			$gamesSearch = self::APP_SEARCH . '/?term=' . $appSearch. "&category1=998";

			$html = $this->curl($gamesSearch);
			$html = utf8_decode($html); 

			$this->doc = new DOMDocument;
			@$this->doc->loadHTML($html);

			libxml_clear_errors();
			libxml_use_internal_errors($this->oldSetting);
		}



		public function steamXpath(){
			$xpath = new DOMXPath($this->doc);
			$appIDs = $xpath->query('//div[@class="col search_capsule"][position()<3]/img');
			$ids = array();

			if($appIDs->length == 0){
				return null;	
			}

			foreach($appIDs as $appID){

				$stringID = $appID->getAttribute('src');
				preg_match('/\/apps\/(\d+)\//', $stringID, $matches);
				$ids[] = $matches[1];
			}

			$ids = implode(",", $ids);

			$result = $this->appDetails($ids);
			return $result;
		}



		protected function appDetails($ids){
			 
			$appUrl = self::APP_HOST . '/?filters=' . self::APP_FILTER . '&appids=';
			$appUrl .= $ids;
			
			$getSteamItem = $this->curl($appUrl);
			$getSteamItem = json_decode($getSteamItem, true);
			fb($getSteamItem);
			$games = $this->getSteam($getSteamItem);

			return $games;
		}



		protected function getSteam($getSteamItem){
			$obj = array();
			$apps = array();

			foreach($getSteamItem as $item){

				$app = $item['data'];
				
				
					$name = $app['name'];
					$id = $app['steam_appid'];
					$image = $app['header_image'];

					$obj['name'] = $name;
					$obj['image'] = $image;
					$obj['appID'] = $id;

					$apps[] = $obj;	
				
			}

			return $apps;
		}

		

		protected function curl($url){
				
			$timeout = 5;
			$curl = curl_init();

			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL,$url);

			$result = curl_exec($curl);
			//curl_close($curl);

			return $result;
		}
	}



	class GetSteamApp extends Steam
	{

		var $id;

		public function __construct($id){
			$this->id = $id;
		}


		public function getApp(){
			return Steam::appDetails($this->id);
		}


		protected function getSteam($getSteamItem){
			$obj = array();
			$apps = array();

			foreach($getSteamItem as $item){
				$app = $item['data'];

				$name = $app['name'];
				$id = $app['steam_appid'];
				$image = $app['header_image'];
				$releaseDate = $app['release_date']['date'];
				$comingSoon = $app['release_date']['coming_soon'];

				$obj['name'] = $name;
				$obj['image'] = $image;
				$obj['appID'] = $id;
				$obj['releaseDate'] = $releaseDate;
				$obj['comingSoon'] = $comingSoon;


				if(isset($app['price_overview'])){

					$prices = $app['price_overview'];

					$priceCurrency = $prices['currency'];
					$price = $prices['final'] ;
					$priceDiscount = $prices['discount_percent'];

					$obj['price'] = $price;
					$obj['currency'] = $priceCurrency;
					$obj['discount'] = $priceDiscount;
				}
			
				$apps[] = $obj;	
			}

			return $apps;
			
		}
	}

