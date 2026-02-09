<?php
<?php
$allowedOrigins = [
    "https://tsunamiflow.club",
    "https://www.tsunamiflow.club"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: https://tsunamiflow.club");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, X-Request-Type");
header("Vary: Origin");
header("Content-Type: application/json; charset=utf-8");

/* 🔥 PRE-FLIGHT MUST EXIT HERE */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

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
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ["error" => $error];
    }

    curl_close($ch);

    if ($httpCode >= 400) {
        return ["error" => "Printful HTTP Error " . $httpCode];
    }

    return json_decode($response, true);
}

// 🔒 File cache
$cacheFile = __DIR__ . "/cache/printful_items.json";
$cacheTTL  = 600;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    echo file_get_contents($cacheFile);
    exit;
}

// Fetch store products
$productsResponse = printfulRequest("/store/products");

if (!isset($productsResponse['result'])) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch products"]);
    exit;
}

$items = [];

foreach ($productsResponse['result'] as $product) {

    $variantResponse = printfulRequest("/store/products/" . $product['id']);

    $variants = [];
    if (isset($variantResponse['result']['sync_variants'])) {
        foreach ($variantResponse['result']['sync_variants'] as $variant) {

            $image = null;
            if (!empty($variant['files'])) {
                foreach ($variant['files'] as $file) {
                    if (!empty($file['preview_url'])) {
                        $image = $file['preview_url'];
                        break;
                    }
                }
            }

            if ($image === null && isset($product['thumbnail_url'])) {
                $image = $product['thumbnail_url'];
            }

            $variants[] = [
                "variant_id" => $variant['id'],
                "name"       => $variant['name'],
                "price"      => $variant['retail_price'],
                "sku"        => $variant['sku'],
                "image"      => $image
            ];
        }
    }

    $items[] = [
        "product_id"   => $product['id'],
        "name"         => $product['name'],
        "description"  => "no description" //$product['description'],
        "thumbnail"    => $product['thumbnail_url'],
        "variants"     => $variants
    ];
}

$output = json_encode([
    "items" => $items
]);

echo $output;
exit;