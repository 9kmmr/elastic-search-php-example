<?php
if (count($results) > 0) {
?>
<table class="table table-striped">
<thead>
  <th>Title</th>
  <th>Excerpt</th>
	<th>Author</th>
  <th>Date</th>
</thead>
<?php
    error_reporting(E_ALL ^ E_NOTICE);

    foreach ($results as $result) {
        $article = $result['_source'];
?>
<tr>
  <td><a href=""><?php echo $article['title']; ?></a></td>
  <td><?php echo $article['excerpt']; ?></td>
	<td><?php echo $article['author']; ?></td>
  <td><?php echo $article['date']; ?></td>
</tr>
<?php
    } // END foreach loop over results
?>
</table>
<?php
} // END if there are search results

else {
?>
<p>Sorry, no articles found :( Would you like to <a href="/index_ảticle.php">add</a> one?</p>
<?php

} // END elsif there are no search results

?>
