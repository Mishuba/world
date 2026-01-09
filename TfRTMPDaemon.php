<?php
$streamKey = $argv[1] ?? null;
if (!$streamKey) exit("Usage: php TfRTMPDaemon.php STREAMKEY\n");

$socketFile = "/tmp/stream-$streamKey.sock";
if (!file_exists($socketFile)) {
    touch($socketFile);
}

// Launch FFmpeg once
$ffmpegCmd = sprintf(
    '/usr/bin/ffmpeg -f webm -i pipe:0 -c:v libx264 -preset veryfast -tune zerolatency -c:a aac -f flv rtmp://localhost/live/%s',
    escapeshellarg($streamKey)
);

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['file', "/var/log/ffmpeg-$streamKey.log", 'a'],
    2 => ['file', "/var/log/ffmpeg-$streamKey.log", 'a'],
];

$process = proc_open($ffmpegCmd, $descriptors, $pipes);
$ffmpegStdin = $pipes[0];

// Read from socket file continuously
while (true) {
    $data = file_get_contents($socketFile);
    if ($data) {
        fwrite($ffmpegStdin, $data);
        file_put_contents($socketFile, ''); // clear after writing
    }
    usleep(50000); // 50ms loop
}