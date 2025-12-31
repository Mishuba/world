<?php
header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// ingest.php

$key = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['key'] ?? '');
if (!$key) {
    http_response_code(400);
    exit('Missing key');
}

$pipe = "/tmp/stream_{$key}.pipe";
$flag = "/tmp/stream_{$key}.running";

/*
  Start FFmpeg only once per stream
*/
if (!file_exists($flag)) {
    if (!file_exists($pipe)) {
        posix_mkfifo($pipe, 0666);
    }

    file_put_contents($flag, time());

    // fire-and-forget ffmpeg
    exec("/var/www/world/live/hls/ffmpeg.sh {$key} > /dev/null 2>&1 &");
}

/*
  Write chunk into FIFO
*/
$in  = fopen("php://input", "rb");
$out = fopen($pipe, "ab");

if ($in && $out) {
    stream_copy_to_stream($in, $out);
}

fclose($in);
fclose($out);

http_response_code(204);