<?php
 
require_once 'aws_signed_request.php';

include_once("memcached.php"); 


    class AmazonProductAPI extends Memcached
    {

        private static $PUBLIC    = "";
        private static $PRIVATE   = "";
        private static $ASSOCIATE_TAG = "";

        private $public_key;
        private $private_key;
        private $associate_tag;
        private $local_site;

        const MUSIC = "Music";
        const DVD   = "DVD";
        const GAMES = "VideoGames";
        


        function __construct(){

            $this->public_key = AmazonProductAPI::$PUBLIC;
            $this->private_key = AmazonProductAPI::$PRIVATE;
            $this->local_site = "com";
            $this->associate_tag = AmazonProductAPI::$ASSOCIATE_TAG;
        }



        private function getAmazonDetails($json){

            if ($json === False){

                throw new Exception("Could not connect to Amazon");
            }
            else{

                if ($json['Items']['TotalResults'] > 0){

                    $amazon = array();
                    $obj = array();

                    $items = $json['Items']['Item'];
                    $items = array_slice($items, 0, 2);
                    foreach ($items as $item){
                        $obj['id'] = $item['ASIN'];
                        $obj['image'] = $item['MediumImage']['URL'];
                        $obj['price'] = isset($item['OfferSummary']['LowestNewPrice']) ? $item['OfferSummary']['LowestNewPrice']['FormattedPrice'] : NULL;
                        $obj['releaseDate'] = isset($item['ItemAttributes']['ReleaseDate']) ? $item['ItemAttributes']['ReleaseDate'] : NULL;
                        $obj['title'] =  $item['ItemAttributes']['Title'];
                        $obj['type'] =  $item['ItemAttributes']['Binding'];

                        $amazon[] = $obj;
                    }

                    return $amazon;

                }
                else{

                    return null;
                    throw new Exception("No results found.");

                }
            }
        }
     


        private function queryAmazon($parameters){

            return aws_signed_request($this->local_site,
                                      $parameters,
                                      $this->public_key,
                                      $this->private_key,
                                      $this->associate_tag);
        }
     


        public function searchProducts($search,$category,$searchType, $director){

            switch($searchType){
                case "ASIN" :
                    $parameters = array("Operation"     => "ItemLookup",
                                        "ItemId"        => $search,
                                        "SearchIndex"   => $category,
                                        "IdType"        => "UPC",
                                        "ResponseGroup" => "Small");
                                break;
     
                case "TITLE" :
                    $parameters = array("Operation"     => "ItemSearch",
                                        "Title"         => $search,
                                        "Director"       => $director,
                                        "SearchIndex"   => $category,
                                        "ResponseGroup" => "Images,Small,OfferSummary,ItemAttributes");
                                break;

                case "MP3" :
                    $parameters = array("Operation"     => "ItemSearch",
                                        "Title"         => $search,
                                        "Artist"    => $director,
                                        "SearchIndex"   => $category,
                                        "ResponseGroup" => "Images,Small,OfferSummary,ItemAttributes");
                                break;
            }

            $json_response = $this->queryAmazon($parameters);
     
            return $this->getAmazonDetails($json_response);
     
        }
     


        // public function getItemByAsin($asin_code){

        //     $parameters = array("Operation"     => "ItemLookup",
        //                         "ItemId"        => $asin_code,
        //                         "ResponseGroup" => "Medium");
     
        //     $json_response = $this->queryAmazon($parameters);
     
        //     return $this->getAmazonDetails($json_response);

        // }
     


        // public function getItemByKeyword($keyword, $product_type){
            
        //     $parameters = array("Operation"   => "ItemSearch",
        //                         "Keywords"    => $keyword,
        //                         "SearchIndex" => $product_type);
     
        //     $xml_response = $this->queryAmazon($parameters);
     
        //     return $this->verifyXmlResponse($xml_response);

        // }
    }
 
