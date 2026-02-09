<?php
// Debug toggle (set environment variable DEBUG=1 to show PHP errors)
//$DEBUG = getenv('DEBUG') === '1';
//ini_set('display_errors', $DEBUG ? '1' : '0');
//error_reporting($DEBUG ? E_ALL : 0);

header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, X-Request-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/vendor/autoload.php";

// Ensure request type
$requestType =
    $_SERVER['HTTP_X_REQUEST_TYPE']
    ?? ($_GET['type'] ?? null);

if ($requestType !== 'fetch_printful_items') {
    http_response_code(400);
    respond(["error" => "Invalid Request Type"]);
    exit;
}

if (!defined('PRINTFUL_API_KEY')) {
    http_response_code(500);
    echo json_encode([
        "error" => "PRINTFUL_API_KEY not defined"
    ]);
    exit;
}


function printfulRequest($endpoint) {
    $ch = curl_init("https://api.printful.com" . $endpoint);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . PRINTFUL_API_KEY
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        return [
            "error" => curl_error($ch)
        ];
    }

    curl_close($ch);

    return json_decode($response, true);
}

// 1️⃣ Fetch store products
$productsResponse = printfulRequest("/store/products");

if (!isset($productsResponse['result'])) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch products"]);
    exit;
}

$items = [];

foreach ($productsResponse['result'] as $product) {

    // 2️⃣ Fetch variants for each product
    $variantResponse = printfulRequest("/store/products/" . $product['id']);

    $variants = [];
    if (isset($variantResponse['result']['sync_variants'])) {
        foreach ($variantResponse['result']['sync_variants'] as $variant) {
            $variants[] = [
                "variant_id" => $variant['id'],
                "name"       => $variant['name'],
                "price"      => $variant['retail_price'],
                "sku"        => $variant['sku'],
                "image"      => $variant['files'][0]['preview_url'] ?? null
            ];
        }
    }

    $items[] = [
        "product_id"   => $product['id'],
        "name"         => $product['name'],
        "description"  => $product['description'],
        "thumbnail"    => $product['thumbnail_url'],
        "variants"     => $variants
    ];
}

echo json_encode([
    "items" => $items
]);
exit;
>