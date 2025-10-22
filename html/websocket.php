<?php
require __DIR__ . "/vendor/autoload.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

class TsunamiFlowWebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $ffmpegProcesses = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "🌊 TsunamiFlow WebSocket server started...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "🟢 New connection: ({$conn->resourceId})\n";

        $conn->send(json_encode([
            "type" => "welcome",
            "message" => "Connected to the TsunamiFlow WebSocket Server!"
        ]));

        // Parse ?key= from query string
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $params);
        $streamKey = $params["key"] ?? null;

        if ($streamKey) {
            echo "🔑 Stream key: {$streamKey}\n";
            $this->startFfmpeg($conn, $streamKey);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Check if it's binary WebM data from the browser
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
                $message = $data["message"];
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        "type" => "start_game",
                        "from" => $from->resourceId,
                        "message" => $message
                    ]));
                }
                break;

            case "game":
                $message = $data["message"];
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        "type" => "game",
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
        if (!isset($this->ffmpegProcesses[$conn->resourceId])) {
            echo "⚠️ Binary received but no active FFmpeg process.\n";
            return;
        }

        $pipes = $this->ffmpegProcesses[$conn->resourceId]["pipes"];
        if (is_resource($pipes[0])) {
            fwrite($pipes[0], $binary);
        }
    }

    private function startFfmpeg(ConnectionInterface $conn, $streamKey) {
        // Change this RTMP target if needed
        $rtmpUrl = "rtmp://world.tsunamiflow.club/live/$streamKey";

        $cmd = "ffmpeg -loglevel warning -fflags nobuffer -re -f webm -i pipe:0 "
             . "-c:v libx264 -preset veryfast -tune zerolatency "
             . "-c:a aac -ar 44100 -b:a 128k -f flv " . escapeshellarg($rtmpUrl);

        $spec = [
            0 => ["pipe", "r"], // stdin (input)
            1 => ["pipe", "w"], // stdout (optional)
            2 => ["pipe", "w"], // stderr
        ];

        $proc = proc_open($cmd, $spec, $pipes);
        if (is_resource($proc)) {
            stream_set_blocking($pipes[0], false);
            stream_set_blocking($pipes[2], false);

            $this->ffmpegProcesses[$conn->resourceId] = [
                "proc" => $proc,
                "pipes" => $pipes,
                "key" => $streamKey
            ];

            echo "🚀 FFmpeg started for connection {$conn->resourceId} → $rtmpUrl\n";
        } else {
            echo "❌ Failed to start FFmpeg for key {$streamKey}\n";
        }
    }

    private function stopFfmpeg(ConnectionInterface $conn) {
        if (!isset($this->ffmpegProcesses[$conn->resourceId])) return;

        $procData = $this->ffmpegProcesses[$conn->resourceId];
        [$stdin, $stdout, $stderr] = $procData["pipes"];

        @fclose($stdin);
        @fclose($stdout);
        @fclose($stderr);
        proc_terminate($procData["proc"]);

        unset($this->ffmpegProcesses[$conn->resourceId]);
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

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TsunamiFlowWebSocketServer()
        )
    ),
    8080
);

$server->run();