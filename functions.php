<?php
header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// --- ERRORS ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


if (session_status() === PHP_SESSION_NONE) {
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.tsunamiflow.club',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);

 session_start();
} else {

}


// --- Required files & namespaces ---
//require_once "Arrays.php";
require_once "config.php";
require_once __DIR__ . "/vendor/autoload.php";

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;

// --- NanoTech Database Credentials ---
$nanoH = NANO_HOST;
$nanoP = NANO_PORT;
$nanoDb = NANO_DB;
$nanoU = NANO_USER;
$nanoPsw = NANO_PSW;
$nanoDSN = "pgsql:host=$nanoH;port=$nanoP;dbname=$nanoDb;sslmode=require;channel_binding=require";

// --- Input ---
$TfRcI = @file_get_contents("php://input");
$UseThis = json_decode($TfRcI);

// --- Utility Functions ---
function getIpAddress() {
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) return $_SERVER["HTTP_CLIENT_IP"];
    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) return $_SERVER["HTTP_X_FORWARDED_FOR"];
    return $_SERVER["REMOTE_ADDR"];
}

function LogOut() {
    $_SESSION = [];
    session_unset();
    session_destroy();
    session_write_close();
    header("Location: index.php");
    exit;
}

function TsunamiInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validate_input($inputName, $inputArray, $type = 'string') {
    if (!isset($inputArray[$inputName]) || empty($inputArray[$inputName])) return null;
    $value = TsunamiInput($inputArray[$inputName]);
    if ($type === 'string' && preg_match("/^[a-zA-Z0-9-']+$/", $value)) return $value;
    if ($type === 'number') return filter_var($value, FILTER_VALIDATE_INT);
    if ($type === 'email') return filter_var($value, FILTER_VALIDATE_EMAIL);
    return $value;
}

function handleDatabaseError($e){
    if ($e->getCode() == '23505') { // Postgres unique violation
        die("The username you choose is already being used. Please choose a new one.");
    } else {
        error_log($e->getMessage(), 0);
        file_put_contents("tferror.log", $e->getMessage() . "\n", FILE_APPEND);
        die("An error occurred. Please try again later.");
    }
}

function respond(array $data, int $status = 200): void {
    header("Access-Control-Allow-Origin: https://tsunamiflow.club");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

function isApiRequest(): bool {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_SERVER['HTTP_X_REQUEST_TYPE'])
        || str_contains($contentType, 'application/json')
        || ($_SERVER['REQUEST_METHOD'] === 'POST');
}

function addToCart(array $item, int $quantity): array {
    if (!isset($_SESSION['ShoppingCartItems'])) $_SESSION['ShoppingCartItems'] = [];

    $found = false;
    foreach ($_SESSION['ShoppingCartItems'] as &$cartItem) {
        if ($cartItem['variant_id'] === $item['variant_id']) {
            $cartItem['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $item['quantity'] = $quantity;
        $_SESSION['ShoppingCartItems'][] = $item;
    }

    return ['item' => $item];
}


function TsunamiDatabaseFlow(){
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

function createCookieAndSession($key, $value, $days = 365){
    setcookie($key, $value, time() + (86400 * $days), "/");
    $_SESSION[$key] = $value;
}

// --- Stripe Helper ---
function getStripeClient($secretKey): StripeClient {
    return new StripeClient($secretKey);
}

function getTaxIdType($countryCode): ?string {
    $taxIdTypes = [
        "US" => "us_ein",
        "CA" => "ca_bn",
        "GB" => "gb_vat",
    ];
    return $taxIdTypes[$countryCode] ?? null;
}

// --- Main Stripe Payment Function ---
function WhichPaymentWeDoing(
    StripeClient $stripe,
    bool $oneTimePayment,
    string $paymentMethodId,
    float $paymentAmount,
    array $customerData, // ['email','name','description','countryCode','taxId']
    string $type = "store" // store, donation, subscription
): string {
    try {
        // Step 1: Create customer
        $customer = $stripe->customers->create([
            "email" => $customerData['email'],
            "name" => $customerData['name'],
            "description" => $customerData['description'] ?? "",
            "address" => ["country" => $customerData['countryCode']]
        ]);

        if (!$customer || !isset($customer->id)) {
            return json_encode(["error" => "Failed to create customer"]);
        }

        // Set default payment method
        $stripe->customers->update($customer->id, [
            "invoice_settings" => ["default_payment_method" => $paymentMethodId]
        ]);

        // Add tax ID if provided
        if (!empty($customerData['taxId'])) {
            $taxType = getTaxIdType($customerData['countryCode']);
            if ($taxType) {
                $stripe->customers->createTaxId($customer->id, [
                    "type" => $taxType,
                    "value" => $customerData['taxId']
                ]);
            }
        }

        // Step 2: Create PaymentIntent or Subscription
        if ($oneTimePayment) {
            $intent = $stripe->paymentIntents->create([
                "amount" => $paymentAmount,
                "currency" => "usd",
                "payment_method" => $paymentMethodId,
                "confirmation_method" => "manual",
                "confirm" => true,
                "automatic_payment_methods" => ["enabled" => true],
                "off_session" => true,
                "receipt_email" => $customerData['email'],
                "setup_future_usage" => "off_session"
            ]);

            $status = $intent->status;
            $clientSecret = $intent->client_secret ?? null;

        } else {
            $subscription = $stripe->subscriptions->create([
                "customer" => $customer->id,
                "items" => [["price" => $paymentAmount]],
                "collection_method" => "charge_automatically",
                "payment_behavior" => "default_incomplete",
                "expand" => ["latest_invoice.payment_intent"],
                "off_session" => true
            ]);

            $intent = $subscription->latest_invoice->payment_intent ?? null;
            $status = $intent->status ?? "unknown";
            $clientSecret = $intent->client_secret ?? null;
        }

        $messages = [
            "succeeded" => "Payment successful. Thank you!",
            "requires_action" => "Verification required to complete payment.",
            "requires_payment_method" => "Payment method issue. Try again.",
            "requires_capture" => "Bank requires confirmation.",
            "canceled" => "Payment failed. Try later."
        ];

        return json_encode([
            "success" => $status === "succeeded",
            "requires_action" => $status === "requires_action",
            "requires_confirmation" => $status === "requires_capture",
            "requires_source_action" => $status === "requires_payment_method",
            "message" => $messages[$status] ?? "Unknown status",
            "payment_intent_client_secret" => $clientSecret,
            "next_step" => $status === "succeeded" ? ($type === "store" ? "Printful_Order" : "none") : null,
            "error" => $status === "succeeded" ? "no error" : null
        ]);

    } catch (CardException | ApiErrorException $e) {
        return json_encode(["error" => $e->getMessage()]);
    } catch (Exception $e) {
        return json_encode(["error" => "Unexpected error: " . $e->getMessage()]);
    }
}

// --- Database Insert Function (Full) ---
function InputIntoDatabase(
    $membership, $userName, $firstName, $lastName, $nickName, $gender, $birthdate, $email, $password,
    $chineseZodiacSign, $westernZodiacSign, $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign, $vedicAstrologySign,
    $guardianAngel, $ChineseElement, $eyeColorMeaning, $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign,
    $MayanZodiacSign, $loveLanguage, $birthStone, $birthFlower, $bloodType, $attachmentStyle, $charismaType, $businessPersonality,
    $TFuserDISC, $socionicsType, $learningStyle, $financialPersonalityType, $primaryMotivationStyle, $creativeStyle,
    $conflictManagementStyle, $teamRolePreference
){
    try {
        $db = TsunamiDatabaseFlow();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // --- Insert into FreeLevelMembers ---
        $stmt = $db->prepare("INSERT INTO Members (tfUN, tfFN, tfLN, tfNN, tfGen, tfBirth, tfEM, tfPSW, created)
            VALUES (:tfUN, :tfFN, :tfLN, :tfNN, :tfGen, :tfBirth, :tfEM, :tfPSW, NOW())");
        $stmt->execute([
            ":tfUN" => $userName, ":tfFN" => $firstName, ":tfLN" => $lastName, ":tfNN" => $nickName,
            ":tfGen" => $gender, ":tfBirth" => $birthdate, ":tfEM" => $email, ":tfPSW" => $hashedPassword
        ]);

        // --- Session & Cookies ---
        foreach (["TfAccess" => ucfirst($membership), "Username" => $userName, "Birthday" => $birthdate,
                  "Gender" => $gender, "Nickname" => $nickName, "Email" => $email] as $k=>$v) createCookieAndSession($k, $v);

        // --- Additional inserts for Regular/VIP/Team members ---
        $tableMap = [
            "Regular" => "RegularMembers",
            "VIP" => "VIPMembers",
            "Team" => "TeamMembers"
        ];

        if (isset($tableMap[$membership])) {
            $stmtExtra = $db->prepare("INSERT INTO {$tableMap[$membership]} 
                (tfUN, tfFN, tfLN, tfNN, tfEM, tfBirth, tfGen, created) 
                VALUES (:tfUN, :tfFN, :tfLN, :tfNN, :tfEM, :tfBirth, :tfGen, NOW())");
            $stmtExtra->execute([
                ":tfUN" => $userName, ":tfFN" => $firstName, ":tfLN" => $lastName, ":tfNN" => $nickName,
                ":tfEM" => $email, ":tfBirth" => $birthdate, ":tfGen" => $gender
            ]);
        }

        // --- CSV Backup ---
        $csvFile = __DIR__ . "/user_backup.csv";
        $csvData = [
            $userName, $firstName, $lastName, $nickName, $gender, $birthdate, $email,
            $chineseZodiacSign, $westernZodiacSign, $spiritAnimal, $celticTreeZodiacSign, $nativeAmericanZodiacSign,
            $vedicAstrologySign, $guardianAngel, $ChineseElement, $eyeColorMeaning, $GreekMythologyArchetype,
            $NorseMythologyPatronDeity, $EgyptianZodiacSign, $MayanZodiacSign, $loveLanguage, $birthStone,
            $birthFlower, $bloodType, $attachmentStyle, $charismaType, $businessPersonality, $TFuserDISC,
            $socionicsType, $learningStyle, $financialPersonalityType, $primaryMotivationStyle, $creativeStyle,
            $conflictManagementStyle, $teamRolePreference, date("Y-m-d H:i:s")
        ];
        $handle = fopen($csvFile, 'a');
        fputcsv($handle, $csvData);
        fclose($handle);

        echo json_encode(["status"=>"success","message"=>"User $userName successfully registered as $membership member."]);

    } catch (PDOException $e) {
        handleDatabaseError($e);
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
        echo json_encode(["status"=>"error","message"=>"Unexpected error: ".$e->getMessage()]);
    }
}

// --- Login ---
function Login() {
    $tfUsername = $_POST["NavUserName"] ?? $_REQUEST["phpnun"] ?? null;
    $tfPassword = $_POST["NavPassword"] ?? $_REQUEST["phpnpsw"] ?? null;
    if (!$tfUsername || !$tfPassword) return;

    $tfUsername = validate_input("NavUserName", $_POST ?? $_REQUEST);
    $tfPassword = validate_input("NavPassword", $_POST ?? $_REQUEST);

    try {
        $pdo = TsunamiDatabaseFlow();
        $stmt = $pdo->prepare("SELECT * FROM Members WHERE tfUN = :username");
        $stmt->bindParam(':username', $tfUsername, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($tfPassword, $user['tfPSW'])) {
            session_regenerate_id(true);
            $_SESSION["UserName"] = $user['tfUN'];
            echo htmlspecialchars($tfUsername) . " is now logged in.";
        } else {
            echo "Incorrect Username or Password";
        }

    } catch (PDOException $e) {
        handleDatabaseError($e);
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
        echo "Unexpected error: " . $e->getMessage();
    }
}

// --- Printful functions ---
function BasicPrintfulRequest(): array {
    $ch = curl_init('https://api.printful.com/store/products');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . PRINTFUL_API_KEY]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) { curl_close($ch); return ['result'=>[]]; }
    curl_close($ch);

    $decoded = json_decode($response, true);
    return is_array($decoded) && isset($decoded['result']) ? $decoded : ['result'=>[]];
}

function PrintfulProductionDescription($productId): array {
    $ch = curl_init("https://api.printful.com/store/products/$productId");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . PRINTFUL_API_KEY]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) { curl_close($ch); return ['result'=>[]]; }
    curl_close($ch);

    $decoded = json_decode($response, true);
    return is_array($decoded) && isset($decoded['result']) ? $decoded : ['result'=>[]];
}

function getVariantandPrice($productId): ?array {
    $prod = PrintfulProductionDescription($productId);
    return $prod['result'] ?? null; // Return full product
}

function NPOtfTS(array $orderData): ?int {
    $ch = curl_init('https://api.printful.com/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PRINTFUL_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));

    $response = curl_exec($ch);
    if (curl_errno($ch)) { curl_close($ch); return null; }
    curl_close($ch);

    $decodedResponse = json_decode($response, true);
    return $decodedResponse['result']['id'] ?? null;
}

function CreatePrintfulOrder(array $cartItems, array $customer): array {
    $apiKey = PRINTFUL_API_KEY;
    if (!$apiKey) return ['error' => 'Missing Printful API key'];

    $order = [
        "recipient" => [
            "name"         => $customer['name'] ?? 'Unknown',
            "address1"     => $customer['address1'] ?? '',
            "city"         => $customer['city'] ?? '',
            "state_code"   => $customer['state_code'] ?? '',
            "country_code" => $customer['country_code'] ?? '',
            "zip"          => $customer['zip'] ?? '',
            "email"        => $customer['email'] ?? '',
            "phone"        => $customer['phone'] ?? ''
        ],
        "items" => []
    ];

    foreach ($cartItems as $item) {
        $order['items'][] = [
            "variant_id" => $item['variant_id'],
            "quantity"   => $item['quantity']
        ];
    }

    $ch = curl_init('https://api.printful.com/orders');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PRINTFUL_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true) ?? [];
    if ($httpCode >= 200 && $httpCode < 300) return ['success' => true, 'result' => $result];

    return ['success' => false, 'error' => $result['error'] ?? 'Unknown Printful error'];
}

// -------- Stripe Checkout Session --------
function CreateStripeCheckout(array $cartItems, string $successUrl, string $cancelUrl): array {
    $stripe = new StripeClient(STRIPE_SECRET_KEY);
    $lineItems = [];

    foreach ($cartItems as $item) {
        $price = floatval($item['price'] ?? 0);
        if ($price <= 0) continue;
        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'unit_amount' => (int)($price * 100), // Stripe in cents
                'product_data' => ['name' => $item['name'] . ' - ' . $item['variant_name']]
            ],
            'quantity' => $item['quantity']
        ];
    }

    if (empty($lineItems)) return ['success' => false, 'error' => 'No valid items in cart'];

    try {
        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $cancelUrl
        ]);
        return ['success' => true, 'url' => $session->url, 'id' => $session->id];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

//Email
function sendReceipt($to, $orderDetails) {
    /*
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.yourserver.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your@email.com';
        $mail->Password = 'yourpassword';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your@email.com', 'Tsunami Flow Store');
        $mail->addAddress($to);
        $mail->Subject = 'Your Receipt';
        $mail->Body = "Thank you for your order!\n\n" . print_r($orderDetails, true);
        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: ".$e->getMessage());
    }
        */
}


//Webrtc Functions 
//curl response to make php 
/*
curl -X POST \
    -H "Authorization: Bearer $APITOKEN" \
    -H "Content-TYpe: application/json" -d `{"ttl": 86400}` \
    https://rtc.live.cloudlflare.com/v1/turn/keys/$TurnTokenID/credentials/generate 

    JSON response (Passed to Servers) 
    {
        "iceServers": {
            "urls": [
                "stun:stun.cloudflare.com:3478",
                "turn:turn.cloudflare.com:3478?transport=udp",
                "turn:turn.cloudflare.com:3478?transport=tcp",
                "turn:turn.cloudflare.com:3478?transport=tcp"
                ],
                "username": "mishuba14@gmail.com" //i think,
                "credential": "#SuperSayian14+" //i think, 
                }
    }
*/

function updateYouTube($title, $description) {
    global $config;
    $broadcastId = "YOUR_BROADCAST_ID";

    $url = "https://www.googleapis.com/youtube/v3/liveBroadcasts?part=snippet";
    $urlVideo = "https://www.googleapis.com/youtube/v3/videos?part=snippet";
    $data = [
        'id' => $broadcastId,
        'snippet' => [
            'title' => $title,
            'description' => $description
        ]
    ];

    $headers = [
    "Authorization: Bearer {$config['youtube_token']}",
    "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("YouTube update failed: $response");
    }
    curl_close($ch);
}

function updateTwitch($title) {
    global $config;
    $clientId = "YOUR_TWITCH_CLIENT_ID";
    $channelId = "YOUR_CHANNEL_ID";

    $url = "https://api.twitch.tv/helix/channels?broadcaster_id=$channelId";
    $data = ['title' => $title];
    $headers = [
        "Authorization: Bearer {$config['twitch_token']}",
        "Client-Id: $clientId",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 204) {
        error_log("Twitch update failed: $response");
    }
    curl_close($ch);
}

function updateTwitter($title, $description) {
    global $config;
    // Simplified example — Twitter v2 preferred now
    $url = "https://api.twitter.com/2/tweets";
        $data = ["text" => "$title\n\n$description"];
        $headers = [
            "Authorization: Bearer {$config['twitter_token']}",
            "Content-Type: application/json"
        ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Twitter update failed: $response");
    }
    curl_close($ch);
}

function updateInstagram($title, $description) {
    // Facebook Graph API for Instagram posting
    $accessToken = "YOUR_INSTAGRAM_ACCESS_TOKEN";
    $pageId = "YOUR_INSTAGRAM_PAGE_ID";

    $url = "https://graph.facebook.com/v12.0/$pageId/media";
    $data = [
        'caption' => "$title\n\n$description",
        'access_token' => $accessToken,
        'image_url' => 'https://your-image-url.com/image.jpg' // Instagram requires a media URL!
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("Instagram update failed: $response");
    }
    curl_close($ch);
}

function updateFacebook($title, $description) {
    global $config;

    $accessToken = "YOUR_FACEBOOK_ACCESS_TOKEN";
    $liveVideoId = "YOUR_LIVE_VIDEO_ID";
    $headers = ["Authorization: Bearer {$config['facebook_token']}","Content-Type: application/json"];
                    $data = ["message" => "$title\n\n$description"];

    $url = "https://graph.facebook.com/v12.0/$liveVideoId";
    $urlchat = "https://graph.facebook.com/{$config['facebook_page_id']}/feed";
    $data = [
        'title' => $title,
        'description' => $description,
        'access_token' => $accessToken
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        error_log("Facebook update failed: $response");
    }
    curl_close($ch);
}

function updateTikTok($title, $description) {
    // Placeholder: TikTok API needs a proper App setup
    error_log("TikTok API update function not implemented yet.");
}

function updateTumblr($title, $description) {
    global $config;
    $accessToken = "YOUR_TUMBLR_ACCESS_TOKEN";
    $blogName = "YOUR_BLOG_NAME.tumblr.com";

    $url = "https://api.tumblr.com/v2/blog/$blogName/post";
    $data = [
        'type' => 'text',
        'title' => $title,
        'body' => $description,
        'access_token' => $accessToken
    ];
    $headers = ["Authorization: Bearer {$config['tumblr_token']}", "Content-Type: application/json"];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Tumblr update failed: $response");
    }
    curl_close($ch);
}

function updatePinterest($title, $description) {
    global $config;

    $url = "https://api.pinterest.com/v5/pins";
    $headers = [
        "Authorization: Bearer {$config['pinterest_token']}",
        "Content-Type: application/json"
    ];
    $data = [
        "title" => $title,
        "description" => $description,
        "link" => "https://yourdomain.com",
        "board_id" => "YOUR_BOARD_ID",
        "media_source" => ["source_type" => "image_url", "url" => "https://yourdomain.com/image.jpg"]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [$headers],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        error_log("Pinterest update failed: $response");
    }
    curl_close($ch);
}
?>