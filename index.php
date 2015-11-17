<?php

require_once 'vendor/autoload.php';
require_once 'config/google.auth.php';

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;


session_start();
$client = new Google_Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUrl);
$client->setScopes(array('https://spreadsheets.google.com/feeds'));
//Prevent error with authentication
$client->getHttpClient()->setDefaultOption('verify', __DIR__ . DIRECTORY_SEPARATOR . 'cacert.pem');

print '<a href="' . $client->createAuthUrl() . '">Authenticate</a>';

if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    var_dump($client->getAccessToken());
    

    
    
    $accessToken = $client->getAccessToken();
    
    $accessToken = $accessToken["access_token"];
    
    $serviceRequest = new DefaultServiceRequest($accessToken);
    ServiceRequestFactory::setInstance($serviceRequest);
    
    
    $spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
    $spreadsheetFeed = $spreadsheetService->getSpreadsheets();
    echo "<h3>Lists of spreadsheets<h3>";
    foreach ($spreadsheetFeed as $feed) {
      echo $feed->getTitle();
    }
  
    
    $spreadsheet = $spreadsheetFeed->getByTitle('Knihovna');
    $worksheetFeed = $spreadsheet->getWorksheets();
    echo "<h3>Lists of file<h3>";
    foreach ($worksheetFeed as $feed) {
      echo $feed->getTitle();
    }    
    
    $worksheet = $worksheetFeed->getByTitle('List');

 
    $cellFeed = $worksheet->getCellFeed();

    $cellFeed->editCell(1,1, "Ahoj");
    $cellFeed->editCell(1,2, "svˆte");
    $cellFeed->editCell(1,3, "Ahoj");
    $cellFeed->editCell(1,4, "svˆte"); 

    $listFeed = $worksheet->getListFeed();
  /*  
    $row = array('name'=>'John', 'age'=>25);
    $listFeed->insert($row);

    echo "<br>";
    foreach ($listFeed as $feed) {
      echo $feed->getTitle();
    }    
*/

}