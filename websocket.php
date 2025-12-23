<?php
require "vendor/autoload.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Factory;
use React\Socket\Server as SocketServer;
use React\Socket\SecureServer;

class TsunamiFlowWebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $ffmpeg = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "🌊 TsunamiFlow WebSocket server started...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "🟢 New connection: ({$conn->resourceId})\n";

        $conn->send(json_encode([
            "type" => "welcome",
            "id" => $conn->resourceId,
            "message" => "Connected to the TsunamiFlow WebSocket Server!"
        ]));

        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $params);
        $streamKey = $params["key"] ?? null;
        $role = $params['role'] ?? 'broadcaster';

        if ($streamKey) {
            echo "🔑 Stream key: {$streamKey}\n";
            $this->startFfmpeg($conn, $streamKey, $role);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        if (!is_string($msg) || (strlen($msg) > 0 && strpos($msg[0], '{') !== 0)) {
            $this->handleBinaryStream($from, $msg);
            return;
        }

        echo("💬 Message from {$from->resourceId}: $msg\n");
        $data = json_decode($msg, true);
        if (!is_array($data)) return;

        switch ($data["type"] ?? "") {
            case "chat":
                $message = $data["message"];
                $username = $data["username"];
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        "type" => "chat",
                        "from" => $from->resourceId,
                        "message" => $message,
                        "username" => $username
                    ]));
                }
                break;

            case "start_game":
            case "game":
                $message = $data["message"];
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        "type" => $data["type"],
                        "from" => $from->resourceId,
                        "message" => $message
                    ]));
                }
                break;

            case "signal":
                // WebRTC signaling placeholder
                break;

            case "stop_stream":
                $this->stopFfmpeg($from);
                break;
        }
    }

    private function handleBinaryStream(ConnectionInterface $conn, $binary) {
        if (!isset($this->ffmpeg[$conn->resourceId])) {
            echo "⚠️ Binary received but no active FFmpeg process.\n";
            return;
        }

        $pipes = $this->ffmpeg[$conn->resourceId]["pipes"];
        if (is_resource($pipes[0])) {
            fwrite($pipes[0], $binary);
        }
    }

    private function startFfmpeg(ConnectionInterface $conn, $key, $role) {
        $base = "/var/www/world/live/$key";
        @mkdir($base, 0777, true);

        if ($role === "audio_only") {
            $cmd = "ffmpeg -loglevel error -f webm -i pipe:0 "
                 . "-c:a aac -b:a 128k -ar 44100 "
                 . "-f hls -hls_time 2 -hls_list_size 5 "
                 . "$base/audio.m3u8";
        } else {
            $cmd = "ffmpeg -loglevel error -f webm -i pipe:0 "
                 . "-c:v libx264 -preset veryfast -tune zerolatency "
                 . "-c:a aac -f flv "
                 . "rtmp://localhost/live/$key";
        }

        $spec = [
            0 => ["pipe", "w"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

        $proc = proc_open($cmd, $spec, $pipes);
        if (!is_resource($proc)) {
            echo "❌ Failed to start FFmpeg for key {$key}\n";
            return;
        }

        stream_set_blocking($pipes[0], false);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $this->ffmpeg[$conn->resourceId] = [
            "proc" => $proc,
            "stdin" => $pipes[0],
            "pipes" => $pipes,
            "key" => $key,
            "role" => $role,
            "err" => $pipes[2]
        ];

        echo "🚀 FFmpeg started for connection {$conn->resourceId} as {$role}\n";
    }

    private function checkFfmpegStderr() {
        foreach ($this->ffmpeg as $connId => $procData) {
            $stderr = $procData["pipes"][2];
            $output = stream_get_contents($stderr);
            if ($output) {
                echo "FFmpeg ({$connId}) stderr: $output\n";

                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        "type" => "ffmpeg_stderr",
                        "message" => trim($output)
                    ]));
                }
            }
        }
    }

    private function stopFfmpeg(ConnectionInterface $conn) {
        if (!isset($this->ffmpeg[$conn->resourceId])) return;
        
    
        $procData = $this->ffmpeg[$conn->resourceId];
        [$stdin, $stdout, $stderr] = $procData["pipes"];

        @fclose($stdin);
        @fclose($stdout);
        @fclose($stderr);
        proc_terminate($procData["proc"]);

        unset($this->ffmpeg[$conn->resourceId]);
        echo "🛑 FFmpeg stopped for connection {$conn->resourceId}\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->stopFfmpeg($conn);
        echo "🔴 Connection {$conn->resourceId} closed\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "💥 Error on connection {$conn->resourceId}: {$e->getMessage()}\n";
        $this->stopFfmpeg($conn);
        $conn->close();
    }
}

// === TLS/WSS server setup ===
$loop = Factory::create();
//$websocket = new WsServer(new TsunamiFlowWebSocketServer());

$TfServer = new TsunamiFlowWebSocketServer();
$loop->addPeriodicTimer(0.5, function() use ($TfServer) {
    $TfServer->checkFfmpegStderr();
});

$socket = new SocketServer('0.0.0.0:8443', $loop);
$secureSocket = new SecureServer($socket, $loop, [
    'local_cert' => '/etc/letsencrypt/live/world.tsunamiflow.club/fullchain.pem',
    'local_pk'   => '/etc/letsencrypt/live/world.tsunamiflow.club/privkey.pem',
    'allow_self_signed' => false,
    'verify_peer' => false
]);

new IoServer(new HttpServer(new WsServer($TfServer)), $secureSocket, $loop);
$loop->run();
?>