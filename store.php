<?php
$allowedOrigins = [
    "https://tsunamiflow.club",
    "https://www.tsunamiflow.club"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
// ============================
// ERROR REPORTING (DEV)
// ============================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();
require 'config.php';

if (!defined('PRINTFUL_API_KEY')) {
    http_response_code(500);
    echo json_encode([
        "error" => "PRINTFUL_API_KEY not defined"
    ]);
    exit;
}

if (!isset($_GET['fetch_printful_items'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
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
>