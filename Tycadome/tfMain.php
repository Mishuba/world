<?php
$allowed_origins = [
    "https://tsunamiflow.club",
    "https://tsunamiflow.onrender.com",
    "https://world-l87q.onrender.com"
];

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/../Database/Cookies/',
        'domain' => 'https://world-l87q.onrender.com',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);

    session_start();
} else if (session_status() === PHP_SESSION_ACTIVE) {
    // Session is already active, do nothing
} else {
    // Handle other session statuses if needed disabled
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, x-request-type");

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Vary: Origin");
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //http_response_code(200); // shows 200
    http_response_code(204); // No Content shown but passes.
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . "/Class/Detha.php";

$TycadomeBackend = new Detha();

switch ($TycadomeBackend->isApiRequest()) {
    case "fetchRadioSongs":
        $TycadomeBackend->RadioPlaylist($_SERVER['REQUEST_METHOD'], getenv("CLOUDFLARE_R2_ACCESS_KEY"), getenv("CLOUDFLARE_R2_SECRET_KEY"), getenv("CLOUDFLARE_R2_ENDPOINT"));
        break;

    case "fetch_printful_items":
        $productsResponse = $_SESSION['PrintfulItems'] ?? $TycadomeBackend->BasicPrintfulRequest();

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

            if (ob_get_level() > 0) {
                ob_end_flush();
            }
            exit;
        }

        $items = [];

        foreach ($productsResponse['result'] as $product) {

            // Validate product ID exists
            if (empty($product['id'])) {
                error_log("Warning: Product missing ID: " . json_encode($product));
                continue;
            }

            $variantResponse = $TycadomeBackend->getVariantandPrice($product['id']);

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
                        "name" => $variant['name'] ?? '',
                        "price" => $variant['retail_price'] ?? null,
                        "sku" => $variant['sku'] ?? '',
                        "image" => $image
                    ];
                }
            }

            $items[] = [
                "product_id" => $product['id'] ?? null,
                "name" => $product['name'] ?? '',
                "description" => "no description",
                "thumbnail" => $product['thumbnail_url'] ?? null,
                "variants" => $variants
            ];
        }

        $output = json_encode(
            [
                "items" => $items
            ]
        );

        if ($output === false) {
            http_response_code(500);
            echo json_encode([
                "error" => "Failed to encode JSON response"
            ]);
            ob_end_flush();
            exit;
        }

        $showSuccess = true;

        echo $output;

        ob_end_flush();
        break;

    case "websocket":
        // Periodically reap dead FFmpeg processes
        /*
        $TycadomeBackend->loop->addPeriodicTimer(5, function () use ($TycadomeBackend) {
            foreach ($TycadomeBackend->getActiveFFmpegKeys() as $key) {
                $TycadomeBackend->reapFFmpeg($key);
            }
        });
        $TycadomeBackend->loop->run();
        */
        break;
    default:

        break;

}
?>