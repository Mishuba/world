<?php
// xml php request handler
$xml = file_get_contents("php://input");
$xmljson = json_decode($xml, true) ?? $_POST ?? [];
$servermethod = $_SERVER['REQUEST_METHOD'];
$requestType = $_SERVER['HTTP_X_REQUEST_TYPE'] ?? ($_GET['type'] ?? null);
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'localhost';
$accountId = CLOUDFLARE_R2_ACCOUNT_ID ?: null;
$accessKey = CLOUDFLARE_R2_ACCESS_KEY ?: null;
$secretKey = CLOUDFLARE_R2_SECRET_KEY ?: null;
$r2Endpoint = CLOUDFLARE_R2_ENDPOINT ?: "https://radio.tsunamiflow.club"; // e.g. https://<account-id>.r2.cloudflarestorage.com
$bucketName = CLOUDFLARE_R2_NAME ?: 'tsunami-radio';
$stripe_sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"] ?? "";
//$stripe_webhook_event = "";
//cloudflare things
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
define(cloudflare, getenv("") ?: 'localhost');
?>