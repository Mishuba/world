<?php

header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");

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

// ============================
// SESSION
// ============================
session_start();

// ============================
// INCLUDES
// ============================
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/vendor/autoload.php";

use Stripe\StripeClient;

// ============================
// HELPERS
// ============================
function respond(array $data, int $status = 200): void {
    header("Access-Control-Allow-Origin: https://tsunamiflow.club");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With");

    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit; // 🔴 REQUIRED
}

// ============================
// API MODE DETECTION
// ============================
$isApi = function_exists('isApiRequest') ? isApiRequest() : false;

// ============================
// INPUT NORMALIZATION
// ============================
$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents("php://input");
$jsonData = json_decode($rawInput, true);

if (is_array($jsonData)) {
    $_POST = array_merge($_POST, $jsonData);
}

// ============================
// STRIPE
// ============================
if (!defined('STRIPE_SECRET_KEY')) {
    if ($isApi) respond(['error' => 'Stripe key missing'], 500);
    die('Stripe misconfigured');
}

$stripe = new StripeClient(STRIPE_SECRET_KEY);
$domain = "https://www.tsunamiflow.club";

// ============================
// SESSION DEFAULTS
// ============================
$_SESSION["visit_count"] = ($_SESSION["visit_count"] ?? 0) + 1;
$_SESSION["UserPreferences"] ??= ["Chosen_Companion" => "Ackma Hawk"];
$_SESSION["Setting"] ??= ["font_style" => "auto"];

foreach ([
    "TfGuestCount",
    "freeMembershipCount",
    "lowestMembershipCount",
    "middleMembershipCount",
    "highestMembershipCount",
    "TfMemberCount"
] as $key) {
    $_SESSION[$key] ??= 0;
}

if (!($_SESSION["TfNifage"] ?? false)) {
    $_SESSION["TfGuestCount"]++;
} else {
    match ($_SESSION["TfAccess"] ?? "free") {
        "Regular" => $_SESSION["lowestMembershipCount"]++,
        "Vip"     => $_SESSION["middleMembershipCount"]++,
        "Team"    => $_SESSION["highestMembershipCount"]++,
        default   => $_SESSION["freeMembershipCount"]++
    };
    $_SESSION["TfMemberCount"]++;
}

setcookie("TfAccess", $_SESSION["TfAccess"] ?? "guest", time() + 86400 * 30, "/", "", true, true);
setcookie("visit_count", $_SESSION["visit_count"], time() + 86400, "/", "", true, true);

// ==================================================
// ===================== API ========================
// ==================================================
if ($isApi) {

    // ---------- POST ----------
    if ($method === 'POST') {

        // ADD PRODUCT TO CART
        if (isset($_POST['addProductToCart'])) {

            $variantId = trim($_POST['product_id'] ?? '');
            $quantity  = max(1, (int)($_POST['StoreQuantity'] ?? 1));

            if (!$variantId) respond(['error' => 'Missing product ID'], 400);

            $products = BasicPrintfulRequest();
            $_SESSION['PrintfulItems'] = $products;

            $found = null;

            foreach ($products['result'] ?? [] as $product) {
                foreach ($product['sync_variants'] ?? [] as $v) {
                    if ((string)$v['id'] === (string)$variantId) {
                        $found = [
                            'parent_product_id' => $product['id'],
                            'name'              => $product['name'],
                            'variant_id'        => $v['id'],
                            'variant_name'      => $v['name'],
                            'price'             => (float)$v['retail_price'],
                            'size'              => $v['size'] ?? '',
                            'availability'      => $v['availability_status'] ?? '',
                            'thumbnail'         => $product['thumbnail_url'] ?? ''
                        ];
                        break 2;
                    }
                }
            }

            if (!$found) respond(['error' => 'Variant not found'], 404);

            $result = addToCart($found, $quantity);

            respond([
                'success'    => true,
                'cart_count' => count($_SESSION['ShoppingCartItems']),
                'item'       => $result['item']
            ]);
        }

        // STRIPE CHECKOUT
        if (($_POST['type'] ?? '') === 'Stripe Checkout') {

            $cart = $_SESSION['ShoppingCartItems'] ?? [];
            if (!$cart) respond(['error' => 'Cart empty'], 400);

            $checkout = CreateStripeCheckout(
                $cart,
                "$domain/tfMain.php?type=Printful Checkout",
                "$domain/cancelled.php"
            );

            respond([
                'success' => !empty($checkout['success']),
                'url'     => $checkout['url'] ?? null,
                'id'      => $checkout['id'] ?? null
            ]);
        }

        // PRINTFUL CHECKOUT
        if (($_POST['type'] ?? '') === 'Printful Checkout') {

            $cart = $_SESSION['ShoppingCartItems'] ?? [];
            if (!$cart) respond(['error' => 'Cart empty'], 400);

            $result = CreatePrintfulOrder($cart, $_POST['customer'] ?? []);

            if (!empty($result['success'])) {
                unset($_SESSION['ShoppingCartItems']);
            }

            respond($result);
        }

        respond(['error' => 'Invalid POST action'], 400);
    }

    // ---------- GET ----------
    if ($method === 'GET') {

        if (($_GET['cart_action'] ?? '') === 'view') {
            respond(['items' => $_SESSION['ShoppingCartItems'] ?? []]);
        }

        if (($_GET['cart_action'] ?? '') === 'clear') {
            $_SESSION['ShoppingCartItems'] = [];
            respond(['success' => true]);
        }

        if (isset($_GET['fetch_printful_items'])) {
            respond(['items' => $_SESSION['PrintfulItems']['result'] ?? []]);
        }

        respond(['message' => 'GET OK']);
    }

    respond(['error' => 'Unsupported method'], 405);
}

// ==================================================
// ================== PAGE MODE =====================
// ==================================================

$myProductsFr = $_SESSION['PrintfulItems'] ?? BasicPrintfulRequest();
$myProductsFr['result'] ??= [];
$showSuccess = true;