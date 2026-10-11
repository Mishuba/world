<?php
/*
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
*/
/*
//server listens on this port below
const PORT = process.env.PORT || 3000;

app.listen(PORT, "0.0.0.0", () => {
  console.log(`Server listening on ${PORT}`);
});
*/
$allowed_origins = [
    "https://tsunamiflow.club",
    "https://tsunamiflow.onrender.com",
    "https://world-l87q.onrender.com"
];

session_start();

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");

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

//require_once __DIR__ . "/../routes/store.php";
require_once __DIR__ . "/Class/Detha.php";

$TycadomeBackend = new Detha();

switch ($TycadomeBackend->isApiRequest()) {
    case "fetchRadioSongs":

        $TycadomeBackend->RadioPlaylist($_SERVER['REQUEST_METHOD'], getenv(""), getenv(""), getenv(""), "array");
        break;

    case "x-request-type":
    case "fetch_printful_items":
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
        | Fetch Products
        |--------------------------------------------------------------------------
        */

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

        $showSuccess = true;

        echo $output;

        ob_end_flush();
        break;

    case "websocket":
        new IoServer(
            new HttpServer(new WsServer($TycadomeBackend)),
            $this->socket,
            $this->loop
        );

        // Periodically reap dead FFmpeg processes
        $TycadomeBackend->loop->addPeriodicTimer(5, function () use ($TycadomeBackend) {
            foreach ($TycadomeBackend->getActiveFFmpegKeys() as $key) {
                $TycadomeBackend->reapFFmpeg($key);
            }
        });
        $TycadomeBackend->loop->run();
        break;
    default:

        break;

}

/*

*/
?>