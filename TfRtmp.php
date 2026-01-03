<?php
header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 86400");

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