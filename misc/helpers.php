<?php

function debug($var) {
  if (isset($_GET['debug'])) {
    var_dump($var);
  }
}

function search_db_api($title) {
  $apiURL = 'http://www.databazeknih.cz/suggest.php?q=' . urlencode($title);
  debug($apiURL);
  $response = file_get_contents($apiURL);
  $response = str_replace(array('([{','}])'),array('[{','}]'),$response);
  return json_decode($response);
}

function get_info_from_page($response) {
  $link = 'http://www.databazeknih.cz/knihy/' . $response->link . '-' . $response->id;
  debug($link);
  $html = file_get_contents($link);
  
  $dom = new DOMDocument();
  @$dom->loadHTML($html);
 
  $finder = new DOMXPath($dom);
 
  $rating = $year = $ratingCount = false;
 
  $elms = $finder->query('//a[@class="bpoints"]');
  if ($elms) {
    $rating = $elms->item(0)->firstChild->nodeValue;
  }
  $elms = $finder->query('//a[@class="bpointsm"]');
  if ($elms) {
    $ratingCount = $elms->item(0)->firstChild->nodeValue;
  }

  $elms = $finder->query('//strong');
  if ($elms) {
    $year = $elms->item(0)->firstChild->nodeValue;
  }
  

  $rating = trim(str_replace('%','',$rating));
  $ratingCount = trim(str_replace('hodnocení','',$ratingCount));
  
 
  return array(
    $rating,
    $ratingCount,
    $year
  );
  
}