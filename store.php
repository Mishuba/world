<?php

ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, X-Request-Type");
header("Content-Type: application/json; charset=utf-8");

/*
|--------------------------------------------------------------------------
| Handle Preflight
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    ob_end_clean();
    exit;
}

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

/*
|--------------------------------------------------------------------------
| Validate Request Type
|--------------------------------------------------------------------------
*/

$requestType =
    $_SERVER['HTTP_X_REQUEST_TYPE']
    ?? ($_GET['type'] ?? null);

if ($requestType !== 'fetch_printful_items') {
    http_response_code(400);

    if (function_exists('respond')) {
        respond([
            "error" => "Invalid Request Type"
        ]);
    } else {
        echo json_encode([
            "error" => "Invalid Request Type"
        ]);
    }

    ob_end_flush();
    exit;
}

/*
|--------------------------------------------------------------------------
| Validate API Key
|--------------------------------------------------------------------------
*/

if (!defined('PRINTFUL_API_KEY') || empty(PRINTFUL_API_KEY)) {
    http_response_code(500);

    echo json_encode([
        "error" => "PRINTFUL_API_KEY not defined"
    ]);

    ob_end_flush();
    exit;
}

/*
|--------------------------------------------------------------------------
| Printful API Helper
|--------------------------------------------------------------------------
*/

function printfulRequest($endpoint)
{
    $ch = curl_init("https://api.printful.com" . $endpoint);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . PRINTFUL_API_KEY,
            "Content-Type: application/json"
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {

        $error = curl_error($ch);

        curl_close($ch);

        return [
            "error" => $error
        ];
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            "error" => "Invalid JSON response from Printful",
            "raw_response" => $response
        ];
    }

    if ($httpCode >= 400) {
        return [
            "error" => "Printful HTTP Error " . $httpCode,
            "response" => $decoded
        ];
    }

    return $decoded;
}

/*
|--------------------------------------------------------------------------
| Cache Configuration
|--------------------------------------------------------------------------
*/

$cacheFile = __DIR__ . "/cache/printful_items.json";
$cacheTTL  = 600;

/*
|--------------------------------------------------------------------------
| Ensure Cache Directory Exists
|--------------------------------------------------------------------------
*/

$cacheDirectory = dirname($cacheFile);

if (!is_dir($cacheDirectory)) {
    mkdir($cacheDirectory, 0755, true);
}

/*
|--------------------------------------------------------------------------
| Serve Cache
|--------------------------------------------------------------------------
*/

if (
    file_exists($cacheFile)
    && (time() - filemtime($cacheFile)) < $cacheTTL
) {
    echo file_get_contents($cacheFile);

    ob_end_flush();
    exit;
}

/*
|--------------------------------------------------------------------------
| Fetch Products
|--------------------------------------------------------------------------
*/

$productsResponse = printfulRequest("/store/products");

if (
    !is_array($productsResponse)
    || isset($productsResponse['error'])
    || !isset($productsResponse['result'])
) {
    http_response_code(500);

    echo json_encode([
        "error" => "Failed to fetch products",
        "details" => $productsResponse
    ]);

    ob_end_flush();
    exit;
}

/*
|--------------------------------------------------------------------------
| Build Item List
|--------------------------------------------------------------------------
*/

$items = [];

foreach ($productsResponse['result'] as $product) {

    $variantResponse = printfulRequest(
        "/store/products/" . $product['id']
    );

    $variants = [];

    if (
        is_array($variantResponse)
        && !isset($variantResponse['error'])
        && isset($variantResponse['result']['sync_variants'])
    ) {

        foreach (
            $variantResponse['result']['sync_variants']
            as $variant
        ) {

            $image = null;

            if (!empty($variant['files'])) {

                foreach ($variant['files'] as $file) {

                    if (!empty($file['preview_url'])) {
                        $image = $file['preview_url'];
                        break;
                    }
                }
            }

            if (
                $image === null
                && !empty($product['thumbnail_url'])
            ) {
                $image = $product['thumbnail_url'];
            }

            $variants[] = [
                "variant_id" => $variant['id'] ?? null,
                "name"       => $variant['name'] ?? '',
                "price"      => $variant['retail_price'] ?? null,
                "sku"        => $variant['sku'] ?? '',
                "image"      => $image
            ];
        }
    }

    $items[] = [
        "product_id"  => $product['id'] ?? null,
        "name"        => $product['name'] ?? '',
        "description" => "no description",
        "thumbnail"   => $product['thumbnail_url'] ?? null,
        "variants"    => $variants
    ];
}

/*
|--------------------------------------------------------------------------
| Encode Output
|--------------------------------------------------------------------------
*/

$output = json_encode(
    [
        "items" => $items
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($output === false) {

    http_response_code(500);

    echo json_encode([
        "error" => "Failed to encode JSON"
    ]);

    ob_end_flush();
    exit;
}

/*
|--------------------------------------------------------------------------
| Save Cache
|--------------------------------------------------------------------------
*/

file_put_contents(
    $cacheFile,
    $output,
    LOCK_EX
);

/*
|--------------------------------------------------------------------------
| Output Response
|--------------------------------------------------------------------------
*/

echo $output;

ob_end_flush();
exit;
