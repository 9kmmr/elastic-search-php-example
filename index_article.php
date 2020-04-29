<?php

require './vendor/autoload.php';
require 'env.php';
error_reporting(E_ALL ^ E_NOTICE);
use MXViceConstants\Constants;
        // Build the client object
class NewLanguageIndex {

    private $client;
    private $language;

    function __construct($language){

        $this->language = $language;
        $this->client = ClientBuilder::create()            // Instantiate a new ClientBuilder
                    ->setElasticCloudId(Constants::elastic_cloud_id)  
                    ->setBasicAuthentication(Constants::username, Constants::password) 
                    
                    ->build();                       // Build the client object
    }
    
    /**
     * add_from_file
     *
     * @param  mixed $article_id
     * @return void
     */
    function add_from_file($article_id) {  

        $lang_key = array_key_first($this->language);
        
        $exists = $this->client->exists([
            'id'    => $article_id,
            'index' => Constants::ES_INDEX.'-'.$lang_key,
            'type'  => Constants::ES_TYPE
        ]);
    
        if (!$exists) {
            
            $document = [
                'id'    => $article_id,
                'index' => Constants::ES_INDEX.'-'.$lang_key,
                'type'  => Constants::ES_TYPE,
                'body'  => json_encode(json_decode(file_get_contents(__DIR__ . "/datas/mxvice-articles/$lang_key/$article_id.json")))
            ];
            $res = $this->client->index($document);   
            if ($res['errors']) {

            }
        }
    }    
    /**
     * add_live: add article to elasticsearch on the fly
     *
     * @param  mixed $article_id
     * @param  mixed $article_data
     * @return boolean
     */
    function add_live($article_id,$article_data) {  

        $lang_key = array_key_first($this->language);
        
        $exists = $this->client->exists([
            'id'    => $article_id,
            'index' => Constants::ES_INDEX.'-'.$lang_key,
            'type'  => Constants::ES_TYPE
        ]);
    
        if (!$exists) {
            
            $document = [
                'id'    => $article_id,
                'index' => Constants::ES_INDEX.'-'.$lang_key,
                'type'  => Constants::ES_TYPE,
                'body'  => json_encode(json_decode($article_data))
            ];
            $res = $this->client->index($document);   
            if ($res['errors']) {

            }
        }
    }
}