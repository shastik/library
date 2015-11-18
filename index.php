<?php

require_once 'vendor/autoload.php';
require_once 'config/google.auth.php';
require_once 'misc/helpers.php';

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

print '<a href="' . $client->createAuthUrl() . '">Authenticate to Google Account</a>';

if (isset($_GET['code'])) {

  try {
      $client->authenticate($_GET['code']);
      
      debug($client->getAccessToken());
      
  
      
      
      $accessToken = $client->getAccessToken();
      
      $accessToken = $accessToken["access_token"];
      
      $serviceRequest = new DefaultServiceRequest($accessToken);
      ServiceRequestFactory::setInstance($serviceRequest);
      
      
      $spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
      
      $spreadsheetFeed = $spreadsheetService->getSpreadsheets();
      $spreadsheet = $spreadsheetFeed->getByTitle('Knihovna');
      
      $worksheetFeed = $spreadsheet->getWorksheets();    
      $worksheet = $worksheetFeed->getByTitle('List');
  
      $rowCount = $worksheet->getRowCount();
      $colCount = $worksheet->getColCount();
   
      $cellFeed = $worksheet->getCellFeed();
  
      $break = false;
      $processedRecords = array();
      for ($row = 1; $row <= $rowCount; $row++) {
        //skip first row (it is header)
        if ($row == 1) {
          continue;
        }
        $bookTitle = $processStatus = false;
        
        if ($cellEntry = $cellFeed->getCell($row,1)) {
        
          $bookTitle = $cellEntry->getContent();
          
          if ($cellEntry = $cellFeed->getCell($row,2)) {
            $processStatus = $cellEntry->getContent(); 
          }
          
          if ($bookTitle && ($processStatus != 'OK')) {
            $response = search_db_api($bookTitle);
            if ($response) {
              $response = $response[0];
              $cellFeed->editCell($row,2, 'OK');
              $cellFeed->editCell($row,3, $response->name);
              $cellFeed->editCell($row,4, $response->surname);
              
              list($rating,$ratingCount,$year) = get_info_from_page($response);
              
              $cellFeed->editCell($row,5, $year);
              $cellFeed->editCell($row,6, $rating);
              $cellFeed->editCell($row,7, $ratingCount);
              
              $processedRecords[] = $response;        
           
            }
           
          }      
        }
        
        
        if (!$bookTitle) {
          break;
        }
      }
      
      echo sprintf('<p>Number of new processed records %s</p>', count($processedRecords));

    } catch (Google\Spreadsheet\UnauthorizedException $e) {
      echo sprintf('<p>%s. Please click again on link above.</p>', $e->getMessage());
    }

}