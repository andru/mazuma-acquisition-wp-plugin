<?php
require_once('./vendor/autoload.php');

// we don't have access to the wordpress server, so we're loading from a .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// move to docker compose
define('COMPANIESHOUSE_API_KEY', $_ENV['COMPANIESHOUSE_API_KEY']);

// disable cors for dev
if ($_ENV['__DEV']) {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST');
  header("Access-Control-Allow-Headers: X-Requested-With");
}

function companiesHouseApi($url) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode(COMPANIESHOUSE_API_KEY)));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}

// search for companies by name
if (isset($_GET['q'])) {
  // normalise search term
  $name = $_GET['q'];
  $output = array();
  $response = companiesHouseApi("https://api.companieshouse.gov.uk/advanced-search/companies?company_name_includes=".urlencode($name)."&company_status=active");
  if ($response) {
    $data = json_decode($response, true);
    if ($data['items']) {
      foreach ($data['items'] as $item) {
        $output[] = array(
          'company_name' => $item['company_name'],
          'company_number' => $item['company_number'],
          'compnay_profile' => $item['links']['company_profile']
        );
      }
    } else {
      header('HTTP/1.1 404 Not Found');
    }
  }
  header('Content-Type: application/json');
  echo json_encode($output);
  exit;
}

// get filing history by company number
if (isset($_GET['c'])) {
  $output = "";
  $response = companiesHouseApi("https://api.companieshouse.gov.uk/company/".$_GET['c']);
  if ($response) {
    $data = json_decode($response, true);
    $output = $data['accounts'];
  } else {
    header('HTTP/1.1 404 Not Found');
  }
  header('Content-Type: application/json');
  echo json_encode($output);
  exit;
}


header('HTTP/1.1 500 Internal Server Error');
exit;