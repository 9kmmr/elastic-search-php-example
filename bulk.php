<?php

require './vendor/autoload.php';
require 'env.php';
error_reporting(E_ALL ^ E_NOTICE);
use MXViceConstants\Constants;
use Elasticsearch\ClientBuilder;


$client = ClientBuilder::create()            // Instantiate a new ClientBuilder
            ->setElasticCloudId(Constants::elastic_cloud_id)  
            ->setBasicAuthentication(Constants::username, Constants::password)
            ->build();                       // Build the client object

            
$languages = Constants::languages;
// Delete index to clear out existing data           
$deleteParams = [];


foreach ($languages as $lang_key => $lang) {
    
    $deleteParams['index'] = Constants::ES_INDEX.'-'.$lang_key;
        
    if ($client->indices()->exists($deleteParams)) {
        $client->indices()->delete($deleteParams);
    }
    // Setup bulk index request for articles data
    $lists_article_files = glob(__DIR__ . "/datas/mxvice-articles/".$lang_key."/*.{json}", GLOB_BRACE);    
    $lists_article_files = array_chunk($lists_article_files, 50);
    
    foreach ($lists_article_files as $key => $lists_article_file) {
        $batchLines = [];
        $params = [];
        $params['index'] = Constants::ES_INDEX.'-'.$lang_key;
        $params['type']  = Constants::ES_TYPE;


        $files_name_logs = array($key=>array());
        if (count($lists_article_file) >0) {
            foreach ($lists_article_file as $k => $file) {
                $batchLines[] = '{ "index": { "_id": "' . basename($file) . '" } }';
                $files_name_logs[$key] = basename($file);
                $filedata = json_decode(file_get_contents($file),true);
                if (!$filedata['category']['parent']) {
                    $filedata['category']['parent'] = array("type"=>"object", "dynamic"=>true);
                };
                $batchLines[] = json_encode($filedata);
            }
            $params['body']  = implode("\n", $batchLines);
            
            // Bulk load articles data
            try {
                //code...
                $ret = $client->bulk($params);
                if ($ret['errors']) {
                    
                    echo "Bulk load of articles data $lang failed  in loop $key   !\n";
                    $filedata = json_decode(file_get_contents(__DIR__ . "/datas/mxvice-articles/$lang_key/$files_name_logs[$key]"), true);
                    if ($filedata['category']['parent']) {
                        unset($filedata['category']['parent']);
                    };
                    $document = [
                        'id'    => $files_name_logs[$key],
                        'index' => Constants::ES_INDEX.'-'.$lang_key,
                        'type'  => Constants::ES_TYPE,
                        'body'  => json_encode($filedata)
                    ];
                    $res = $client->index($document);   
                    echo "\n";
                   
                    if ($res['errors']) {
                        print_r($res);
                    } else {
                        echo 'single index successful with parent category removed';
                    }
                    
                } else {
                   
                    echo "Bulk load of articles data $lang completed successfully  in loop $key  !\n";
                }                              
                // unset the bulk response when done to save memory
                unset($ret);
            } catch (Exception $e) {
                print_r($e->getMessage());
            }
        }
    }
}


function writeLog($log) {

    $path="";
    file_put_contents( $path  , "index log :". $log.EOL, FILE_APPEND);
}



            