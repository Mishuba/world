<?php
header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
// ingest.php

<?php
// mishubaRestream.php
// Usage: call via browser or curl with ?key=STREAM_KEY

$streamKey = $_GET['key'] ?? null;

if (!$streamKey) {
    http_response_code(400);
    echo "Missing stream key";
    exit;
}

// Sanitize the stream key
$streamKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $streamKey);

// Path to your bash script
$scriptPath = '/usr/local/bin/ffmpeg-restream.sh';

// Run the script asynchronously so PHP doesn't wait for FFmpeg to finish
$cmd = escapeshellcmd("$scriptPath $streamKey") . " > /dev/null 2>&1 &";
exec($cmd);

echo "Restream started for stream key: $streamKey";

http_response_code(204);