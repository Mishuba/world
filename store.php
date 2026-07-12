<?php

ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, X-Request-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    ob_end_clean();
    exit;
}

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

$requestType =
    $_SERVER['HTTP_X_REQUEST_TYPE']
    ?? ($_GET['type'] ?? null);

if ($requestType !== 'fetch_printful_items') {
    http_response_code(400);
    echo json_encode([
        "error" => "Invalid Request Type"
    ]);
    ob_end_flush();
    exit;
}

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
| Cache Configuration
|--------------------------------------------------------------------------
*/

$cacheFile = __DIR__ . "/cache/printful_items.json";
$cacheTTL  = 600;
$bypassCache = isset($_GET['bypass_cache']) && $_GET['bypass_cache'] === '1';

/*
|--------------------------------------------------------------------------
| Ensure Cache Directory Exists
|--------------------------------------------------------------------------
*/

$cacheDirectory = dirname($cacheFile);

if (!is_dir($cacheDirectory)) {
    $mkdirResult = mkdir($cacheDirectory, 0755, true);
    if (!$mkdirResult && !is_dir($cacheDirectory)) {
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to create cache directory"
        ]);
        ob_end_flush();
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Serve Cache (if valid and not bypassed)
|--------------------------------------------------------------------------
*/

if (
    !$bypassCache
    && file_exists($cacheFile)
    && (time() - filemtime($cacheFile)) < $cacheTTL
) {
    $cachedContent = file_get_contents($cacheFile);
    if ($cachedContent !== false) {
        echo $cachedContent;
        ob_end_flush();
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Fetch Products
|--------------------------------------------------------------------------
*/

$productsResponse = BasicPrintfulRequest();

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

$items = [];

foreach ($productsResponse['result'] as $product) {

    // Validate product ID exists
    if (empty($product['id'])) {
        error_log("Warning: Product missing ID: " . json_encode($product));
        continue;
    }

    $variantResponse = getVariantandPrice($product['id']);

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

$output = json_encode(
    [
        "items" => $items
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($output === false) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to encode JSON response"
    ]);
    ob_end_flush();
    exit;
}

/*
|--------------------------------------------------------------------------
| Save Cache
|--------------------------------------------------------------------------
*/

$cacheWritten = file_put_contents(
    $cacheFile,
    $output,
    LOCK_EX
);

if ($cacheWritten === false) {
    error_log("Warning: Failed to write cache file: " . $cacheFile);
}

echo $output;

ob_end_flush();
exit;
