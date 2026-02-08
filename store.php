<?php
header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight requests
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
            "Authorization: Bearer {PRINTFUL_API_KEY}"
        ]
    ]);

    $response = curl_exec($ch);
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