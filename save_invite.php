<?php
require_once __DIR__ . '/vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

/* ===============================
   CONFIG
================================= */

$connectionString = getenv('AZURE_STORAGE_CONNECTION_STRING');
$containerName = 'rsvp';
$blobName = 'wedding_guest_list_2026.csv';

$blobClient = BlobRestProxy::createBlobService($connectionString);

/* ===============================
   INPUT
================================= */

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$city    = trim($_POST['city'] ?? '');
$state   = trim($_POST['state'] ?? '');
$zip     = trim($_POST['zip'] ?? '');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit;
}

$timestamp = date('Y-m-d H:i:s');

/* ===============================
   LOAD EXISTING CSV
================================= */

$tempFile = tempnam(sys_get_temp_dir(), 'rsvp_');
$csvData = '';

try {
    $blob = $blobClient->getBlob($containerName, $blobName);
    $csvData = stream_get_contents($blob->getContentStream());
} catch (ServiceException $e) {
    // File doesn't exist yet — create header
    $csvData = "Date Submitted,Name,Email,Address,City,State,Zip\n";
}

/* ===============================
   APPEND NEW ROW
================================= */

$csvData .= sprintf(
    "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
    $timestamp, $name, $email, $address, $city, $state, $zip
);

/* ===============================
   UPLOAD BACK TO BLOB
================================= */

file_put_contents($tempFile, $csvData);
$blobClient->createBlockBlob($containerName, $blobName, fopen($tempFile, 'r'));
unlink($tempFile);

/* ===============================
   EMAIL NOTIFICATION
================================= */

$to = "sampbaer@gmail.com, kelcee5young@gmail.com";
$subject = "New Wedding RSVP Submission";
$message =
"New RSVP submission:\n\n".
"Name: $name\n".
"Email: $email\n".
"Address: $address\n".
"City: $city\n".
"State: $state\n".
"Zip: $zip\n".
"Submitted: $timestamp";

$headers = "From: rsvp@kelceesam.azurewebsites.net\r\n";
$headers .= "Reply-To: $email\r\n";

mail($to, $subject, $message, $headers);

/* ===============================
   DONE
================================= */
http_response_code(200);
