<?php
$streamKey = $_POST['key'] ?? 'default';
$chunk = $_FILES['chunk']['tmp_name'] ?? null;

if (!$chunk) { http_response_code(400); exit("No chunk uploaded."); }

// Push incoming WebM chunk to RTMP & HLS
$cmd = sprintf(
    '/usr/bin/ffmpeg -re -i %s -c:v copy -c:a aac -f flv rtmp://3.143.179.123:1935/live/%s',
    escapeshellarg($chunk),
    escapeshellarg($streamKey)
);

// Run in background (non-blocking)
exec($cmd . " > /dev/null 2>&1 &");

echo "Chunk forwarded to RTMP";
?>