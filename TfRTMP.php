<?php
$streamKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['key'] ?? '');

if (!$streamKey) {
    http_response_code(400);
    exit("No stream key");
}

$cmd = sprintf(
    'ffmpeg -re -i pipe:0 -c:v libx264 -preset veryfast -tune zerolatency -c:a aac -f flv rtmp://localhost/live/%s',
    escapeshellarg($streamKey)
);

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['file', '/var/log/ffmpeg-ingest.log', 'a'],
    2 => ['file', '/var/log/ffmpeg-ingest.log', 'a'],
];

$process = proc_open($cmd, $descriptors, $pipes);

if (!is_resource($process)) {
    http_response_code(500);
    exit("FFmpeg failed");
}

while (!feof(STDIN)) {
    fwrite($pipes[0], fread(STDIN, 8192));
}

fclose($pipes[0]);
proc_close($process);