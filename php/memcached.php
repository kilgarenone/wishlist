<?php
	
	
	class Memcached {

		private static $MEMCACHED_HOST = "localhost";
        private static $MEMCACHED_PORT = "11211";

        private $id, $key, $memcache, $cacheOK;
        

		function __construct ($id){
			$this->id = $id;
			$this->key = 'artistID_'. $this->id;
			$this->memcache = new Memcache;
			$this->cacheOK = $this->memcache->connect(Memcached::$MEMCACHED_HOST, Memcached::$MEMCACHED_PORT);
		}

		protected function getMemcache(){
			$artistInfo = null;
					
			if($this->cacheOK === true){
				$artistInfo = $this->memcache->get($this->key);
			}

			if($artistInfo === false){
				return false;
			}

			return $artistInfo;
			
		}


		protected function setMemcache($artistInfo){

			$this->memcache->set($this->key, $artistInfo, 0, 10);

		}
		 
	}