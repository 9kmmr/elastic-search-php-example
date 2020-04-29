# elastic-search-php-example

- add existed Article JSON files to elasticsearch with different language.
- bulk index new articles with new language
- index new single article in a single language
- implement search function on each JSON articles indexed

## run
1. Install application dependencies.

   ```sh
   $ composer install
   ```

2. Seed Elasticsearch index with initial article data.

   ```sh
   $ php bulk.php
   ```

3. Start the application using PHP's built-in web server.

   ```sh   
   $ php -S localhost:8000
   ```

4. Open your web browser and visit [`http://localhost:8000/dosearch.php`](http://localhost:8000/dosearch.php).

## Code Organization

* `data` * contains article datas in json file.
Elasticsearch*
* `env.php` * contains constants & credentials of Elasticsearch Cloud 
* `bulk.php` * bulk index article datas into Elasticsearch
* `bulk_newlang` * do the bulk index of new language and articles in existed directory
* `index_article.php` * index single article in a single language
* `dosearch.php` * do the searching example of articles
* `results` * view the search results
