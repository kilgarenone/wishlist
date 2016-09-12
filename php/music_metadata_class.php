<?php
	require_once('FirePHPCore/fb.php');

	include_once("amazon_api_class.php");

	class Music extends AmazonProductAPI
	{

		private $query;
		private static $DISCO_HOST = "http://api.rovicorp.com/data/v1.1/name/info";



		function __construct ($param){
			$this->query = $param;
		}



		public function getArtist(){
			$artistImage = Music::$DISCO_HOST . "?apikey=". SigGen::getAPIKey(). "&sig=" . SigGen::createMD5Hash(). "&name=".$this->query. "&include=Discography,Images". "&imagesize=300-600x200-500". "&type=Main";
			$artist = $this->curl($artistImage);
			fb($artist);
			return $this->getArtistInfo($artist);
			
		}



		private function getArtistInfo($artist){
			$artisan = json_decode($artist, true);
			$artistObj = array();
			//fb($artist);
			$artistObj['roviID'] = $artisan['name']['ids']['nameId'];

			$memcache = new Memcached($artistObj['roviID']);
			$artistCache = $memcache->getMemcache();
			// fb($artistCache);

			if($artistCache === false){

				$artistObj['name'] = $artisan['name']['name'];
				$artistObj['image'] = $artisan['name']['images'][0]['url'];
				
				$artistObj = $this->buildArtist($artisan, $artistObj);
							// fb($artistObj);

				$initArtist = array('id' => $artistObj['roviID'], 'name' => $artistObj['name'], 'image' => $artistObj['image']);
				
				if($artistObj['upcomingRelease']){
					$initArtist['upcomingRelease'] = $artistObj['upcomingRelease'];
				}

				$memcache->setMemcache($artistObj);
			}
			else{
				$initArtist = array('id' => $artistCache['roviID'], 'name' => $artistCache['name'], 'image' => $artistCache['image']);

				if($artistCache['upcomingRelease']){
					$initArtist['upcomingRelease'] = $artistCache['upcomingRelease'];
				}
			}

				return $initArtist;
		}



		private function buildArtist($artisan, $artistObj){
			
			$artistObj['amgID'] = $artisan['name']['ids']['amgPopId'];
			
			$discography = $artisan['name']['discography'];

			foreach($discography as $album){
				$albumID = $album['ids']['amgPopId'];
				preg_match('/(\d+)/', $albumID, $matches);
				$albumObj['amgAlbumID'] = $matches[1];
				$albumObj['title'] = $album['title'];
				$albumObj['releaseDate'] = $album['year'];

				if(new DateTime($albumObj['releaseDate']) > new DateTime('2014-03-15')){
					$artistObj['upcomingRelease'] = $albumObj['releaseDate'];
				}
				
				$artistObj['discography'][] = $albumObj;
			}

			return $artistObj;
		}



		public function getDiscoURI(){
			
			$memcache = new Memcached($this->query);
			$artistCache = $memcache->getMemcache();
			if($artistCache['cacheReady'] === 'OK'){
				fb($artistCache);
				fb("cache done(button pressed)");

			}
			else{
				fb("sleeping");
				sleep(1);
				$this->getDiscoURI();
			}
			// foreach($artistCache['discography'] as $disco){
			// 	$artistCache['amazon']
			// 				fb($disco['amazon']);

			// }
			//return $this->getAlbumDetails($discoURI);	
		}


		public function completeCache(){
			$memcache = new Memcached($this->query);
			$artistCache = $memcache->getMemcache();

			$albums = $artistCache['discography'];

			for($i = 0; $i < count($albums); $i++){
				 $artistCache['discography'][$i]['amazon'] = $this->getAmazonMusic($albums[$i]['title'], $artistCache['name']);

			}	

			$artistCache['cacheReady'] = "OK";
			fb('Caching Done');
			$memcache->setMemcache($artistCache);


		}

		private function getAmazonMusic($title, $name){
			$obj = array();
			$amazonObj = array();
			$amazonMusic = new AmazonProductAPI();
			$amazonMusicDetails = $amazonMusic->searchProducts($title, 
															AmazonProductAPI::MUSIC,
															"MP3",
															$name);

			foreach($amazonMusicDetails as $release) {
				$obj['ASIN'] = $release['id'];
				$obj['imageUrl'] = $release['image'];
				$obj['price'] = $release['price'];
				$obj['amazonReleaseDate'] = $release['releaseDate'];
				$obj['type'] = $release['type'];

				$amazonObj[] = $obj;
			}

			return $amazonObj;
		
		}



		public static function curl($url){
				
			$timeout = 2;
			$curl = curl_init();

			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL,$url);

			$result = curl_exec($curl);
			curl_close($curl);

			return $result;
		}
	}
	


	class SigGen
	{
		public static function getTimeStamp(){
			return time();
		}

		public static function getSharedSecret(){
			return "mgFsPFMNJT";
		}

		public static function getAPIKey(){
			return "2kke6pa55c7c7qjd4kuhb5mj";
		}

		public static function createMD5Hash(){
			$string = self::getAPIKey() . self::getSharedSecret() . self::getTimeStamp();
			$md = md5($string);
			return $md;
		}
  	}

  	



