<?php
require "vendor/autoload.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory;
use React\Socket\Server as SocketServer;

class TsunamiFlowWebSocketServer implements MessageComponentInterface {

    protected \SplObjectStorage $clients;
    protected array $ffmpeg = []; // streamKey => [process, stdin]
    protected array $restream = []; // streamKey => [process]

    // Destination RTMP endpoints
    protected array $destinations = [
        'youtube' => 'rtmp://a.rtmp.youtube.com/live2/3egr-4vfq-56yj-amtg-e7v1',
        // 'twitch' => 'rtmp://live.twitch.tv/app/XXXX',
        // 'instagram' => 'rtmp://rtmp.instagram.com:80/rtmp/XXXX',
    ];

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        echo "🌊 TsunamiFlow WebSocket MEDIA server started\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $origin = $conn->httpRequest->getHeader('Origin')[0] ?? '';
        if ($origin !== 'https://tsunamiflow.club') {
            $conn->close();
            return;
        }

        $this->clients->attach($conn);

        parse_str($conn->httpRequest->getUri()->getQuery(), $params);

        $conn->meta = [
            'role' => $params['role'] ?? 'viewer',
            'key'  => $params['key']  ?? null
        ];

        echo "🟢 {$conn->resourceId} connected ({$conn->meta['role']})\n";

        // If a streamer joins, start restream if not already running
        if ($conn->meta['role'] === 'streamer') {
           
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $key = $from->meta['key'] ?? null;
        if (!$key) return;

        // Binary video from client
 if (!is_string($msg)) {
    if (!isset($this->ffmpeg[$key])) return;

    $bytes = @fwrite($this->ffmpeg[$key]['stdin'], $msg);
    if ($bytes === false) {
        echo "⚠️ FFmpeg pipe broken for $key, stopping stream\n";
        $this->stopFFmpeg($from);
    }
    return;
}
        // JSON control messages
        $data = json_decode($msg, true);
        if (!isset($data['type'])) return;

        switch ($data['type']) {
            case 'start_stream':
                $this->startFFmpeg($from);
                break;

            case 'stop_stream':
                $this->stopFFmpeg($from);
                break;
        }
    }

    protected function startFFmpeg(ConnectionInterface $conn) {
        $key = $conn->meta['key'];
        if (!$key || isset($this->ffmpeg[$key])) return;

        echo "🚀 Starting FFmpeg for local RTMP push: $key\n";

        $cmd = [
  'ffmpeg',
  '-loglevel', 'warning',
  '-fflags', 'nobuffer',
  '-flags', 'low_delay',
  '-analyzeduration', '0',
  '-probesize', '32',
  '-f', 'webm',
  '-i', 'pipe:0',
  '-c:v', 'copy',
  '-c:a', 'aac',
  '-f', 'flv',
  "rtmp://localhost/live/$key"
];

        $desc = [
            0 => ['pipe', 'w'], // stdin
            1 => ['file', "/tmp/ffmpeg-$key.log", 'a'],
            2 => ['file', "/tmp/ffmpeg-$key.err", 'a']
        ];

        $proc = proc_open($cmd, $desc, $pipes);
        if (!is_resource($proc)) return;

        stream_set_blocking($pipes[0], false);

        $this->ffmpeg[$key] = [
            'process' => $proc,
            'stdin'   => $pipes[0]
        ];

$this->startRestream($key);
    }

    protected function stopFFmpeg(ConnectionInterface $conn) {
        $key = $conn->meta['key'] ?? null;
        if (!$key || !isset($this->ffmpeg[$key])) return;

        echo "🛑 Stopping FFmpeg local push: $key\n";

        fclose($this->ffmpeg[$key]['stdin']);
        proc_terminate($this->ffmpeg[$key]['process']);

        unset($this->ffmpeg[$key]);

        // Also stop restream
        $this->stopRestream($key);
    }

    protected function startRestream(string $key) {
        if (isset($this->restream[$key])) {
            echo "♻️ Restream already running for $key\n";
            return;
        }

        $input = "rtmp://localhost/live/$key";

        // Use ffprobe to check if RTMP has video
        $cmdProbe = "ffprobe -v error -show_entries stream=codec_type -of json '$input'";
        exec($cmdProbe, $out, $ret);
        if ($ret !== 0) {
            echo "⚠️ No active local RTMP, waiting for WebSocket push for $key\n";
            // We rely on WebSocket pushing video via startFFmpeg
            $input = "rtmp://localhost/live/$key"; // input will be available once FFmpeg starts
        }

        echo "🚀 Starting restream for $key\n";

        $cmd = [
            'ffmpeg',
            '-re',
            '-i', $input,
            '-c:v', 'copy',
            '-c:a', 'copy',
        ];

        foreach ($this->destinations as $dest) {
            $cmd[] = '-f';
            $cmd[] = 'flv';
            $cmd[] = $dest;
        }

        $desc = [
            0 => ['pipe', 'r'],
            1 => ['file', "/tmp/restream-$key.log", 'a'],
            2 => ['file', "/tmp/restream-$key.err", 'a']
        ];

        $proc = proc_open($cmd, $desc, $pipes);
        if (!is_resource($proc)) return;

        $this->restream[$key] = [
            'process' => $proc,
            'pipes'   => $pipes
        ];
    }

    protected function stopRestream(string $key) {
        if (!isset($this->restream[$key])) return;

        echo "🛑 Stopping restream for $key\n";
        proc_terminate($this->restream[$key]['process']);
        unset($this->restream[$key]);
    }


protected function reapFFmpeg(string $key) {
    if (!isset($this->ffmpeg[$key])) return;

    $status = proc_get_status($this->ffmpeg[$key]['process']);
    if (!$status['running']) {
        fclose($this->ffmpeg[$key]['stdin']);
        unset($this->ffmpeg[$key]);
        echo "🧹 Reaped dead FFmpeg for $key\n";
    }
}

public function getActiveFFmpegKeys(): array {
    return array_keys($this->ffmpeg);
}
    public function onClose(ConnectionInterface $conn) {
        $key = $conn->meta['key'] ?? null;
        if ($key) {
            $this->stopFFmpeg($conn);
        }
        $this->clients->detach($conn);
        echo "🔴 {$conn->resourceId} disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "💥 {$e->getMessage()}\n";
        $conn->close();
    }
}

/* ============================= */
/* ===== SERVER BOOTSTRAP ===== */
/* ============================= */

$loop = Factory::create();

$socket = new SocketServer('127.0.0.1:8443', $loop);

// Create one server instance and save it
$server = new TsunamiFlowWebSocketServer();

new IoServer(
    new HttpServer(new WsServer($server)),
    $socket,
    $loop
);

// Periodically reap dead FFmpeg processes
$loop->addPeriodicTimer(5, function () use ($server) {
    foreach ($server->getActiveFFmpegKeys() as $key) {
        $server->reapFFmpeg($key);
    }
});

$loop->run();