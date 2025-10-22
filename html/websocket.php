<?php
require __DIR__ . "/vendor/autoload.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

class TsunamiFlowWebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $ffmpegProcesses = []; // 🆕 Track ffmpeg pipes

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "WebSocket server started ... \n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection: ({$conn->resourceId})\n";

        $conn->send(json_encode([
            "type" => "welcome",
            "message" => "Connected to the dynamic WebSocket Server!"
        ]));

        // 🆕 Get stream key from query string (optional)
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $params);
        $streamKey = $params["key"] ?? null;

        if ($streamKey) {
            echo "Stream key detected: {$streamKey}\n";
            $this->startFfmpeg($conn, $streamKey);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // 🆕 Detect if it's binary WebM data from browser stream
        if (!is_string($msg)) {
            $this->handleBinaryStream($from, $msg);
            return;
        }

        echo("Message from {$from->resourceId}: $msg\n");
        $data = json_decode($msg, true);

        if (!is_array($data)) {
            return; // Ignore malformed JSON
        }

        switch ($data["type"] ?? "") {
            case "chat":
                $message = $data["message"];
                $username = $data["username"];
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        "type" => "chat",
                        "from" => $from->resourceId,
                        "message" => $message,
                        "username" => $username,
                        "error" => ""
                    ]));
                }
                break;

            case "start_game":
                $message = $data["message"];
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        "type" => "start_game",
                        "from" => $from->resourceId,
                        "message" => $message,
                        "error" => ""
                    ]));
                }
                break;

            case "game":
                $message = $data["message"];
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        "type" => "game",
                        "from" => $from->resourceId,
                        "message" => $message,
                        "error" => ""
                    ]));
                }
                break;

            case "signal":
                // WebRTC signaling (not modified)
                break;

            // 🆕 Add a way to stop FFmpeg manually if needed
            case "stop_stream":
                $this->stopFfmpeg($from);
                break;

            default:
                // no-op
                break;
        }
    }

    // 🆕 Handle binary data from MediaRecorder
    private function handleBinaryStream(ConnectionInterface $conn, $binary) {
        if (!isset($this->ffmpegProcesses[$conn->resourceId])) {
            echo "⚠️ Received binary data but no FFmpeg pipe open.\n";
            return;
        }

        $pipes = $this->ffmpegProcesses[$conn->resourceId]["pipes"];
        if (is_resource($pipes[0])) {
            fwrite($pipes[0], $binary);
        }
    }

    // 🆕 Start FFmpeg for this connection
    private function startFfmpeg(ConnectionInterface $conn, $streamKey) {
        $rtmpUrl = "rtmp://tsunamiflow.club/live/" . escapeshellarg($streamKey);
        $cmd = "ffmpeg -re -i - -c:v libx264 -preset veryfast -tune zerolatency "
             . "-c:a aac -ar 44100 -b:a 128k -f flv {$rtmpUrl}";

        $descriptors = [
            0 => ["pipe", "r"], // stdin (browser video/audio chunks)
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"], // stderr
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (is_resource($process)) {
            stream_set_blocking($pipes[0], false);
            $this->ffmpegProcesses[$conn->resourceId] = [
                "proc" => $process,
                "pipes" => $pipes,
                "key" => $streamKey
            ];
            echo "FFmpeg started for connection {$conn->resourceId} -> stream {$streamKey}\n";
        } else {
            echo "❌ Failed to start FFmpeg for stream {$streamKey}\n";
        }
    }

    // 🆕 Stop FFmpeg cleanly
    private function stopFfmpeg(ConnectionInterface $conn) {
        if (!isset($this->ffmpegProcesses[$conn->resourceId])) {
            return;
        }

        $procData = $this->ffmpegProcesses[$conn->resourceId];
        [$stdin, $stdout, $stderr] = $procData["pipes"];

        fclose($stdin);
        fclose($stdout);
        fclose($stderr);
        proc_terminate($procData["proc"]);

        unset($this->ffmpegProcesses[$conn->resourceId]);
        echo "FFmpeg stopped for connection {$conn->resourceId}\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->stopFfmpeg($conn); // 🆕 stop process when client disconnects
        echo "Connection {$conn->resourceId} closed\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error on connection {$conn->resourceId}: {$e->getMessage()}\n";
        $this->stopFfmpeg($conn); // 🆕 ensure cleanup
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