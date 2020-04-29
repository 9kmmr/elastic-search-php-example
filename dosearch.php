<?php

require './vendor/autoload.php';
require 'env.php';

use MXViceConstants\Constants;
use Elasticsearch\ClientBuilder;

// Get search results from Elasticsearch if the user searched for something
$results = [];

if (!empty($_REQUEST['submitted'])) {
    
    $client = ClientBuilder::create()            // Instantiate a new ClientBuilder
                ->setElasticCloudId(Constants::elastic_cloud_id)  
                ->setBasicAuthentication(Constants::username, Constants::password)           
                ->build();                       // Build the client object

    // Setup search query
    $searchParams['index'] = Constants::ES_INDEX; // which index to search
    $searchParams['type']  = Constants::ES_TYPE;  // which type within the index to search
    $searchParams['body'] = [];

    // First, setup full text search bits
    $fullTextClauses = [];
    if ($_REQUEST['q']) {      
        $fullTextClauses[] = [ 'match' => [ 'content' => $_REQUEST['q'] ] ];    
    }

    if (count($fullTextClauses) > 0) {
        $query = ['multi_match' => ['query' => $_REQUEST['q'], 'fields' => ["title", "content", "author", "excerpt"]   ]];
      
    } else {
      $query = [ 'match_all' => (object) [] ];
    }

    // Build complete search request body    
    $searchParams['body'] = [ 'query' => $query ];
    // paginations
    //$searchParams['from'] = 0;
    //$searchParams['size'] = 10;
    
    // Send search query to Elasticsearch and get results
    $queryResponse = $client->search($searchParams);
    $results = $queryResponse['hits']['hits'];
}
?>
<html>
    <head>
        <title>Recipe Search &mdash; Simple</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" />
    </head>
    <body>
        <div class="container">
            <h1>Recipe Search &mdash; Simple</h1>
            <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-inline">
                <input name="q" value="<?php echo $_REQUEST['q']; ?>" type="text" placeholder="What are you looking for?" class="form-control input-lg" size="40" />
                <input type="hidden" name="submitted" value="true" />
                <input type="submit" value="Search" class="btn btn-lg" />
    
            </form>
    <?php

    if (isset($_REQUEST['submitted'])) {
        print_r($queryResponse['hits']['total']);
    include __DIR__ . "/results.php";
    }

    ?>
        </div>
    </body>
</html>
