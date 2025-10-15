OB<?php
require __DIR__ . "/vendor/autoload.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

class TsunamiFlowWebSocketServer implements MessageComponentInterface {
	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage;
		echo "WebSocket server started ... \n";
	}

	public function onOpen(ConnectionInterface $conn) {
	//Called on new connection
		$this->clients->attach($conn);
		echo "New connection: ({$conn->resourceID})\n";
		$conn->send(json_encode([
			"type" => "welcome",
			"message" => "Connected to the dynamic WebSocket Server!"
		]));
	}

	public function onMessage(ConnectionInterface $from, $msg) {
	//When message received
		echo("Message from {$from->resourceID}: $msg\n");
		$data = json_decode($msg, true);
		
		switch($data["type"]) {
			case "chat":
				$message = $data["message"];
				$username = $data["username"];
				foreach($this->clients as $client) {
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
				foreach($this->clients as $client) {
					$client->send(json_encode([
						"type" => "start_game",
						"from" => $from->resourceId,
						"message" => $message,
						"username" => $username,
						"error" => $error
			case "game":
				$message = $data["message"];
				foreach($this->clients as $client) {
					$client->send(json_encode([
						"type" => "game",
						"from" => $from->resourceId,
						"message" => $message, //add more stuff below if you need to do anything extra for games.
						"username" => $username,
						"error" => $error
					]));
				}
				break;
			case "signal":
				//WebRC signaling
				
				break;

			default:
//				echo "using the default for the websocket";
				break;
		}
	}

	public function onClose(ConnectionInterface $conn) {
	//On Disconnect
		$this->clients->detach($conn);
		echo "Connection {$conn->resourceID} closed\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
	//On Error
		echo "Error on connection {$conn->resourceId}: {$e->getMessage()}\n";
		$conn->close();
	}
}

$server = IoServer::factory(new HttpServer(new WsServer(new TsunamiFlowWebSocketServer())), 8080);

$server->run();
