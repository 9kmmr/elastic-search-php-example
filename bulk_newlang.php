<?php

require './vendor/autoload.php';
require 'env.php';
error_reporting(E_ALL ^ E_NOTICE);
use MXViceConstants\Constants;
use Elasticsearch\ClientBuilder;


class NewLanguageIndex {
    private $client;
    private $language;
    function __construct($language){

        $this->language = $language;
        $this->client = ClientBuilder::create()                                             // Instantiate a new ClientBuilder
                    ->setElasticCloudId(Constants::elastic_cloud_id)  
                    ->setBasicAuthentication(Constants::username, Constants::password)                    
                    ->build();                                                              // Build the client object
    }

    function add() {   
        
        foreach ($this->language as $lang_key => $lang) {        
                
            // Index the data folder in Elasticsearch
            // Setup bulk index request for articles data, group into array of 50 groups
            $lists_article_files = glob(__DIR__ . "/datas/mxvice-articles/".$lang_key."/*.{json}", GLOB_BRACE); 
            $lists_article_files = array_chunk($lists_article_files, 50);
            
            foreach ($lists_article_files as $key => $lists_article_file) {
                $batchLines = [];
                $params = [];
                $params['index'] = Constants::ES_INDEX.'-'.$lang_key;
                $params['type']  = Constants::ES_TYPE;
                if (count($lists_article_file) >0) {
                    foreach ($lists_article_file as $k => $file) {
                        // Check if recipe with this ID already exists
                        $exists = $this->client->exists([
                            'id'    => basename($file),
                            'index' => Constants::ES_INDEX.'-'.$lang_key,
                            'type'  => Constants::ES_TYPE
                        ]);
                        if (!$exists) {
    
                            $batchLines[] = '{ "index": { "_id": "' . basename($file) . '" } }';               
                            $filedata = json_decode(file_get_contents($file),true);
                            if (!$filedata['category']['parent']) {
                                $filedata['category']['parent'] = array("type"=>"object", "dynamic"=>true);
                            };
                            $batchLines[] = json_encode($filedata);
                        }
                    }
                    $params['body']  = implode("\n", $batchLines);

                    $ret = $this->client->bulk($params);

                    if ($ret['errors']) {
                       
                        echo "Bulk load of articles data $lang failed  in loop $key   !\n";
            
                    } else {
                      
                        echo "Bulk load of articles data $lang completed successfully  in loop $key  !\n";
                    }
                }
            }
        }
    }
}
