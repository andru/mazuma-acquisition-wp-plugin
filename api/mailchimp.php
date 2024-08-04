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

define('MAILCHIMP_API_KEY', $_ENV['MAILCHIMP_API_KEY']);
define('MAILCHIMP_LIST_ID', $_ENV['MAILCHIMP_LIST_ID']);

// API DOCS https://mailchimp.com/developer/marketing/api/list-members/

$client = new MailchimpMarketing\ApiClient();
$client->setConfig([
    'apiKey' => MAILCHIMP_API_KEY,
    'server' => 'us14',
]);

if (!count($_POST) || !$_POST['email'] || !$_POST['firstName'] || !$_POST['lastName']) {
  header('HTTP/1.1 400 Bad Request');
  die();
}

$subscriberHash = md5(strtolower($_POST['email']));

// setListMember(list_id, subscriber_hash, body)
$response = $client->lists->setListMember(MAILCHIMP_LIST_ID, $subscriberHash, [
    "email_address" => $_POST['email'],
    "merge_fields" => [
        "FNAME" => $_POST['firstName'],
        "LNAME" => $_POST['lastName'],
        "QUOT_MONTH" => $_POST['quoteMonthly'],
        "QUOT_CATCH" => $_POST['quoteCatchup'],
        "QUOT_SETUP" => $_POST['quoteSetup'],
    ],
    "status" => "subscribed",
    // "tags" => [
    //   ["name" => "quote-flow-quoted", "status" => $_POST["flowProgress"] === "quoted" || $_POST["flowProgress"] === "completed" ? "active" : "inactive"],
    //   ["name" => "quote-flow-completed", "status" => $_POST["flowProgress"] === "completed" ? "active" : "inactive"],
    //   ["name" => "quoteonly", "status" => $_POST['marketingOptIn'] === "true" ? "inactive" : "active"],
    // ],
]);

$tags = [
  ["name" => "quote-flow-quoted", "status" => ($_POST["flowProgress"] === "quoted" || $_POST["flowProgress"] === "completed") ? "active" : "inactive"],
  ["name" => "quote-flow-completed", "status" => $_POST["flowProgress"] === "completed" ? "active" : "inactive"],
  ["name" => "quoteonly", "status" => $_POST['marketingOptIn'] === "true" ? "inactive" : "active"],
];
$response2 = $client->lists->updateListMemberTags(MAILCHIMP_LIST_ID, $subscriberHash, [

    "tags" => $tags

]);


header("Content-Type: application/json");
echo json_encode([$response, $tags]);