<?php
require_once __DIR__ . "/../../../../../../../vendor/autoload.php";

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

    public $nanoH;
    public $nanoP;
    public $nanoDb;
    public $nanoU;
    public $nanoPsw;
    public $nanoDSN;

    public bool $oneTimePayment;
    public string $paymentMethodId;
    public float $paymentAmount;
    public array $customerData; // ['email','name','description','countryCode','taxId']
    public string $type = "store"; // store, donation, subscription

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
        $this->nanoH = getenv("NANO_HOST");
        $this->nanoP = getenv("NANO_PORT");
        $this->nanoDb = getenv("NANO_DB");
        $this->nanoU = getenv("NANO_USER");
        $this->nanoPsw = getenv("NANO_PSW");
        $this->nanoDSN = "pgsql:host=$this->nanoH;port=$this->nanoP;dbname=$this->nanoDb;sslmode=require;channel_binding=require";
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

    public function writeSession(array $data)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    public function TsunamiDatabaseFlow()
    {
        global $tfSQLoptions, $nanoDSN, $nanoU, $nanoPsw;
        try {
            $pdo = new PDO($nanoDSN, $nanoU, $nanoPsw, $tfSQLoptions ?? []);

            // Create table if it doesn't exist
            $sql = "
    CREATE TABLE IF NOT EXISTS Members (
        id SERIAL PRIMARY KEY,
        membership_level VARCHAR(50) DEFAULT 'Free',
        tfUN VARCHAR(100) UNIQUE NOT NULL,
        tfFN VARCHAR(100),
        tfLN VARCHAR(100),
        tfNN VARCHAR(100),
        tfGen VARCHAR(50),
        tfBirth DATE,
        tfEM VARCHAR(255) UNIQUE NOT NULL,
        tfPSW VARCHAR(255) NOT NULL,

        chineseZodiacSign VARCHAR(100),
        westernZodiacSign VARCHAR(100),
        spiritAnimal VARCHAR(100),
        celticTreeZodiacSign VARCHAR(100),
        nativeAmericanZodiacSign VARCHAR(100),
        vedicAstrologySign VARCHAR(100),
        guardianAngel VARCHAR(100),
        chineseElement VARCHAR(100),
        eyeColorMeaning VARCHAR(100),
        greekMythologyArchetype VARCHAR(100),
        norseMythologyPatronDeity VARCHAR(100),
        egyptianZodiacSign VARCHAR(100),
        mayanZodiacSign VARCHAR(100),
        loveLanguage VARCHAR(100),
        birthStone VARCHAR(100),
        birthFlower VARCHAR(100),
        bloodType VARCHAR(10),
        attachmentStyle VARCHAR(100),
        charismaType VARCHAR(100),
        businessPersonality VARCHAR(100),
        tfUserDISC VARCHAR(100),
        socionicsType VARCHAR(100),
        learningStyle VARCHAR(100),
        financialPersonalityType VARCHAR(100),
        primaryMotivationStyle VARCHAR(100),
        creativeStyle VARCHAR(100),
        conflictManagementStyle VARCHAR(100),
        teamRolePreference VARCHAR(100),

        created TIMESTAMP DEFAULT NOW(),
        updated TIMESTAMP DEFAULT NOW()
    );
    ";

            // Run the SQL
            $pdo->exec($sql);
            error_log("✅ Table 'Members' verified or created successfully.");

        } catch (PDOException $e) {
            echo "❌ Database error: " . $e->getMessage();
            $pdo = null;
        }
        return $pdo;
    }

    public function insertTdbRow(string $table, array $columns)
    {
        $keys = array_keys($columns);
        $placeholders = array_map(fn($k) => ":$k", $keys);

        $sql = "INSERT INTO {$table} (" . implode(",", $keys) . ")
            VALUES (" . implode(",", $placeholders) . ")";

        $pdo = $this->TsunamiDatabaseFlow();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_combine($placeholders, array_values($columns)));
    }

    public function handleDatabaseError($e)
    {
        if ($e->getCode() == '23505') { // Postgres unique violation
            die("The username you choose is already being used. Please choose a new one.");
        } else {
            error_log($e->getMessage(), 0);
            file_put_contents("tferror.log", $e->getMessage() . "\n", FILE_APPEND);
            die("An error occurred. Please try again later.");
        }
    }
}
?>