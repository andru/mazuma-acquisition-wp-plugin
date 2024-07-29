<?php
error_reporting(E_ERROR);
require_once('./vendor/autoload.php');

// we don't have access to the wordpress server, so we're loading from a .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// disable cors for dev
if ($_ENV['__DEV']) {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST');
  header("Access-Control-Allow-Headers: X-Requested-With");
}

// require_once __DIR__ . '/vendor/autoload.php';
/**
 * 
 * Some reminders for the awful SalesForce UI when setting this up
 * 
 * This requires a special API only salesforce user
 * That user must be assigned as the Client Credentials Flow > Run As user
 * Is that in the 'Run As' part in the edit app screen?
 * Nope! It's a different one on a different page which isn't logical to get to at all
 * Look in: Connected Apps > (this app row) [v] > [Manage] >> [Edit Policies] >>  Client Credentials Flow
 * 
 * https://salesforce.stackexchange.com/questions/421736/oauth-2-0-client-credentials-flow-no-client-credentials-user-enabled-but-the-us
 * 
 * ALSO IN THIS SCREEN
 * Set `IP Relaxation` to `Relax IP restrictions`
 * 
 * ALSO
 * SalesForce doesn't give API users access to query for Lead objects so:
 *  - create a new permission set
 *  - go to object settings
 *  - select Leads
 *  - edit, and check the 'View All' permission
 *  - save and go to the User list
 *  - click the user's name (don't edit), scroll down to Permission Set Assignments
 *  - add the newly created permission set
 * 
 */

define('SF_URL', $_ENV['SF_URL']);
define('SF_KEY', $_ENV['SF_KEY']);
define('SF_SECRET', $_ENV['SF_SECRET']);


// $_POST = [
//     'email' => 'andru@tinymighty.com',
//     'company' => 'TinyMighty Ltd',
//     'phone' => '+34888777888'
// ];
if (!count($_POST)) {
  header('HTTP/1.1 400 Bad Request');
  die('Post fields empty');
}

$lastSFHeaderCode = 501;

// get auth token from salesforce
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, SF_URL.'/services/oauth2/token');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => SF_KEY,
    'client_secret' => SF_SECRET
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$lastSFHeaderCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$json_resp = json_decode($response, true);
$token = false;
if ($json_resp) {
    $token = $json_resp['access_token'];
} else {
    echo 'Bad response';
    exit;
}

if (!$token) {  
    echo 'Bad token';
    exit;
}


// check if lead already exists
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, SF_URL."/services/data/v61.0/query/?q=SELECT+Id+FROM+Lead%20WHERE%20email='".trim($_POST["email"])."'");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$lastSFHeaderCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$lead = json_decode($response, true);

if (!$lead) {
  http_response_code($lastSFHeaderCode);
  die('Error querying lead');
}

if ($lead['totalSize']) {
    //update existing lead
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, SF_URL.$lead['records'][0]['attributes']['url']);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Authorization: Bearer ' . $token,
      'Content-Type: application/json'
  ]);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_POST));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLINFO_HEADER_OUT, true);

  $response = curl_exec($ch);
  $lastSFHeaderCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  http_response_code($lastSFHeaderCode);
  echo $response;
  exit;
} else {
  // create lead
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, SF_URL.'/services/data/v61.0/sobjects/Lead/');
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Authorization: Bearer ' . $token,
      'Content-Type: application/json'
  ]);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_POST));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLINFO_HEADER_OUT, true);

  $response = curl_exec($ch);
  $lastSFHeaderCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  curl_close($ch);
  http_response_code($lastSFHeaderCode);
  echo $response;
  exit;
}

header('HTTP/1.1 500 Internal Server Error');
die('Application Error');