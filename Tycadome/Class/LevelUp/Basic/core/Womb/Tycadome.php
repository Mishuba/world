<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
class TycadomeServer
{
    public $data;
    public $xml;
    public $xmljson;
    public $servermethod;
    public $requestType;
    public $origin;
    public $allowed_origins;

    protected \SplObjectStorage $clients;
    protected array $ffmpeg = []; // streamKey => [process, stdin]
    protected array $restream = []; // streamKey => [process]

    // Destination RTMP endpoints
    protected array $destinations = [
        'youtube' => 'rtmp://a.rtmp.youtube.com/live2/3egr-4vfq-56yj-amtg-e7v1',
        // 'twitch' => 'rtmp://live.twitch.tv/app/XXXX',
        // 'instagram' => 'rtmp://rtmp.instagram.com:80/rtmp/XXXX',
    ];

    public function __construct($t)
    {
        $this->data = $t;
        $this->xml = @file_get_contents("php://input");
        $this->xmljson = json_decode(file_get_contents("php://input"), true) ?? $_POST ?? [];
        $this->servermethod = $_SERVER['REQUEST_METHOD'];
        $this->requestType = $_SERVER['HTTP_X_REQUEST_TYPE'] ?? ($_GET['type'] ?? null);
        $this->origin = $_SERVER['HTTP_ORIGIN'] ?? 'localhost';
        $this->allowed_origins = [
            "https://tsunamiflow.club",
            "https://tsunamiflow.onrender.com",
            "https://world-l87q.onrender.com"
        ];
        $this->clients = new \SplObjectStorage();
        echo "🌊 TsunamiFlow WebSocket MEDIA server started\n";
    }

    public function getIpAddress()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }

        if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }

        return $_SERVER["REMOTE_ADDR"];
    }

    public function TsunamiInput($data)
    {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public function validate_input($inputName, $inputArray, $type = 'string')
    {
        if (!isset($inputArray[$inputName]) || empty($inputArray[$inputName])) {
            return null;
        }
        $value = $this->TsunamiInput($inputArray[$inputName]);
        if ($type === 'string' && preg_match("/^[a-zA-Z0-9-']+$/", $value)) {
            return $value;
        }
        if ($type === 'number') {
            return filter_var($value, FILTER_VALIDATE_INT);
        }
        if ($type === 'email') {
            return filter_var($value, FILTER_VALIDATE_EMAIL);
        }
        return $value;
    }
    public function tycadome(string $id, string $type, string $action, array $meta, array $state, string $mode, array $payload)
    {
        return [
            "id" => $id,
            "type" => $type,
            "action" => $action,
            "meta" => $meta,
            "timestamp" => floor((microtime(true) * 1000) / 1000),
            "state" => $state,
            "mode" => $mode, //"async"
            "payload" => $payload // {},
        ];
    }

    public function respond(array $data, bool $Tycadome = false, int $status = 204)
    {
        header("Content-Type: application/json");
        if ($Tycadome === true) {
            $tf = $this->tycadome(
                $data["id"],
                $data["type"],
                $data["action"],
                $data["meta"],
                $data["state"],
                $data["mode"],
                $data["payload"]
            );
            //json_encode($tf, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_SLASHES);
            echo json_encode($tf);
        } else {
            echo json_encode($data);
        }
        exit;
    }
    public function isApiRequest()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_SERVER['HTTP_X_REQUEST_TYPE']) || str_contains($contentType, 'application/json')
            || ($_SERVER['REQUEST_METHOD'] === 'POST');
    }

    public function writeCookies(array $xmljson, int $days = 30)
    {
        $expiry = time() + (86400 * $days);
        foreach ($xmljson as $key => $value) {
            setcookie($key, $value, $expiry, "/");
            $_SESSION[$key] = $value;
        }
    }
}
?>