<?php
require "vendor/autoload.php";

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

        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $params);
        $streamKey = $params["key"] ?? null;

        if ($streamKey) {
            echo "🔑 Stream key: {$streamKey}\n";
            $this->startFfmpeg($conn, $streamKey);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $this->checkFfmpegStderr(); // log FFmpeg errors safely

        // Detect if message is binary
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
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $params);
        $role = $params["role"] ?? "broadcaster";

        if ($role === "audio_only") {
            $output = "/var/www/html/live/$streamKey/audio.m3u8"; // HLS location
            $cmd = "ffmpeg -fflags +nobuffer -flags low_delay -re -f webm -i pipe:0 "
                 . "-c:a aac -ar 44100 -b:a 128k "
                 . "-f hls -hls_time 2 -hls_list_size 3 -hls_flags delete_segments "
                 . escapeshellarg($output);
        } else {
            $rtmpUrl = "rtmp://localhost/live/$streamKey";
            $cmd = "ffmpeg -fflags +nobuffer -flags low_delay -re -f webm -i pipe:0 "
                 . "-c:v libx264 -preset veryfast -tune zerolatency "
                 . "-c:a aac -ar 44100 -b:a 128k "
                 . "-f flv " . escapeshellarg($rtmpUrl);
        }

        $spec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

        $proc = proc_open($cmd, $spec, $pipes);
        if (!is_resource($proc)) {
            echo "❌ Failed to start FFmpeg for key {$streamKey}\n";
            return;
        }

        stream_set_blocking($pipes[0], true);
        stream_set_blocking($pipes[2], false);

        $this->ffmpegProcesses[$conn->resourceId] = [
            "proc" => $proc,
            "pipes" => $pipes,
            "key" => $streamKey,
            "role" => $role
        ];

        echo "🚀 FFmpeg started for connection {$conn->resourceId} as {$role}\n";
    }

    private function checkFfmpegStderr() {
        foreach ($this->ffmpegProcesses as $connId => $procData) {
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