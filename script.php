<?php
require_once('pagerank.php');

$url = "http://www.google.de/";
echo "$url has Google PageRank: ".getPageRank($url);
?>