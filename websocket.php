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

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        echo "🌊 TsunamiFlow WebSocket CONTROL server started\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $params);

        $conn->meta = [
            'role' => $params['role'] ?? 'viewer',
            'key'  => $params['key']  ?? null
        ];

        echo "🟢 Connection {$conn->resourceId} ({$conn->meta['role']})\n";

        $conn->send(json_encode([
            'type' => 'welcome',
            'id'   => $conn->resourceId,
            'role' => $conn->meta['role']
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        if (!is_string($msg)) {
            // HARD RULE: no binary over WebSocket
            return;
        }

        $data = json_decode($msg, true);
        if (!is_array($data) || !isset($data['type'])) {
            return;
        }

        switch ($data['type']) {

            case 'chat':
                $this->broadcast([
                    'type'     => 'chat',
                    'from'     => $from->resourceId,
                    'username' => $data['username'] ?? 'anon',
                    'message'  => $data['message'] ?? ''
                ]);
                break;

            case 'signal':
    if (!$from->meta['key']) return;

    foreach ($this->clients as $client) {
        if ($client === $from) continue;
        if ($client->meta['key'] !== $from->meta['key']) continue;

        $client->send(json_encode([
            'type' => 'signal',
            'from' => $from->resourceId,
            'data' => $data['data']
        ]));
    }
    break;

            case 'start_stream':
                echo "🚀 Stream requested by {$from->resourceId}\n";
                // You trigger FFmpeg / RTMP / WHIP OUTSIDE this process
                break;

            case 'stop_stream':
                echo "🛑 Stream stop requested by {$from->resourceId}\n";
                break;
        }
    }

    protected function broadcast(array $payload, ?ConnectionInterface $exclude = null) {
        $json = json_encode($payload);
        foreach ($this->clients as $client) {
            if ($exclude && $client === $exclude) continue;
            $client->send($json);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "🔴 Connection {$conn->resourceId} closed\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "💥 Error {$conn->resourceId}: {$e->getMessage()}\n";
        $conn->close();
    }
}

/* ============================= */
/* ===== SERVER BOOTSTRAP ===== */
/* ============================= */

$loop = Factory::create();

/*
  IMPORTANT:
  - NO TLS HERE
  - NGINX TERMINATES SSL
  - THIS IS PLAIN WS
*/
$socket = new SocketServer('127.0.0.1:8443', $loop);

new IoServer(
    new HttpServer(new WsServer(new TsunamiFlowWebSocketServer())),
    $socket,
    $loop
);

$loop->run();