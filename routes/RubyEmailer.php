<?php
// Debug toggle (set environment variable DEBUG=1 to show PHP errors)
//$DEBUG = getenv('DEBUG') === '1';
//ini_set('display_errors', $DEBUG ? '1' : '0');
//error_reporting($DEBUG ? E_ALL : 0);

header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, X-Request-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../Tycadome/Variables/tycadomeVariables.php";
require_once __DIR__ . "/../Tycadome/Arrays/tycadomeArrays.php"; 

if (!$xmljson) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON body"]);
    exit;
}

foreach ($UiPathTypes as $field) {
    if (!isset($xmljson[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing field: $field"]);
        exit;
    }
}

function cleanInput($input, $max = 5000, $isUrl = false) {
    $value = substr(trim($input), 0, $max);
    if ($isUrl) {
        return filter_var($value, FILTER_VALIDATE_URL) ?: null;
    }
    return $value;
}
$decisionOption = cleanInput($xmljson['WhatWeDoinBro'], 200);
$msgSub = cleanInput($xmljson['MessageSubject'], 200);
$msgStart = cleanInput($xmljson['MessageStart'], 200);
$msgContinue = cleanInput($xmljson['MessageContinue'], 200);

$ytLink = cleanInput($xmljson['YoutubeLink'], 200, true);
$spotLink = cleanInput($xmljson['SpotifyLink'], 200, true);
$appleLink = cleanInput($xmljson['AppleLink'], 200, true);
$wavDl = cleanInput($xmljson['WavDownloadLink'], 200, true);
$mp3Dl = cleanInput($xmljson['Mp3DownloadLink'], 200, true);

foreach (['ytLink','spotLink','appleLink','wavDl','mp3Dl'] as $var) {
    if ($$var === null) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid URL for $var"]);
        exit;
    }
}

try {
    $inputArguments = json_encode([
    "songSubject" => $msgSub,
    "startMessage" => $msgStart,
    "mp3" => $mp3Dl,
    "wav" => $wavDl,
    "youtube" => $ytLink,
    "spotify" => $spotLink,
    "itunes" => $appleLink,
    "continueMessage" => $msgContinue,
    "apiReason" => $decisionOption
], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "InputArguments encoding failed"]);
    exit;
}

$accessToken = UiPath_Access_Token;

$triggerUrl = UiPath_TRIGGER_URL;

$triggerPayload = json_encode([
  "songSubject" => $msgSub,
  "startMessage" => $msgStart,
  "mp3" => $mp3Dl,
  "wav" => $wavDl,
  "youtube" => $ytLink,
  "spotify" => $spotLink,
  "itunes" => $appleLink,
  "continueMessage" => $msgContinue,
  "apiReason" => $decisionOption
], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

$ch = curl_init($triggerUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $triggerPayload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken"
    ]
]);

$triggerResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($triggerResponse === false) {
    http_response_code(500);
    echo json_encode([
        "error" => "cURL transport failure",
        "curl_error" => $curlErr
    ]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code(500);
    echo json_encode([
        "error" => "Trigger failed",
        "status" => $httpCode,
        "uipath_raw" => $triggerResponse
    ]);
    exit;
}

echo json_encode([
    "status" => "triggered",
    "uipath_raw" => json_decode($triggerResponse, true)
]);
exit;
?>