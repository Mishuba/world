<?php
// Debug toggle (set environment variable DEBUG=1 to show PHP errors)
//$DEBUG = getenv('DEBUG') === '1';
//ini_set('display_errors', $DEBUG ? '1' : '0');
//error_reporting($DEBUG ? E_ALL : 0);

header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, X-Request-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$streamKey = $_GET['key'] ?? null;
if (!$streamKey) {
    http_response_code(400);
    exit;
}

$streamKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $streamKey);

$scriptPath = '/var/www/world/hls/scripts/ffmpeg-restream.sh';
$cmd = escapeshellcmd("$scriptPath $streamKey") . " > /dev/null 2>&1 &";
exec($cmd);

http_response_code(204);
exit;