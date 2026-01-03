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
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        /* ========================= */
        /* ===== BINARY = MEDIA ==== */
        /* ========================= */

        if (!is_string($msg)) {
            $key = $from->meta['key'];
            if (!$key || !isset($this->ffmpeg[$key])) return;

            fwrite($this->ffmpeg[$key]['stdin'], $msg);
            return;
        }

        /* ========================= */
        /* ===== JSON = CONTROL ==== */
        /* ========================= */

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

        echo "🚀 Starting FFmpeg for stream $key\n";

        $cmd = [
            'ffmpeg',
            '-loglevel', 'warning',
            '-fflags', 'nobuffer',
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
    }

    protected function stopFFmpeg(ConnectionInterface $conn) {
        $key = $conn->meta['key'];
        if (!$key || !isset($this->ffmpeg[$key])) return;

        echo "🛑 Stopping FFmpeg for stream $key\n";

        fclose($this->ffmpeg[$key]['stdin']);
        proc_terminate($this->ffmpeg[$key]['process']);

        unset($this->ffmpeg[$key]);
    }

    public function onClose(ConnectionInterface $conn) {
        $this->stopFFmpeg($conn);
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

new IoServer(
    new HttpServer(new WsServer(new TsunamiFlowWebSocketServer())),
    $socket,
    $loop
);

$loop->run();