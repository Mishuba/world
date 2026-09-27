<?php
require_once __DIR__ . "/../Tycadome/Variables/tycadomeVariables.php";
require_once __DIR__ . "/../Tycadome/Arrays/tycadomeArrays.php";
require_once __DIR__ . "/../Tycadome/Function/functions.php";
try {
    if ($servermethod === 'POST' && isApiRequest()) {

        // ---- Add Product to Cart ----
        if (isset($_POST['addProductToCart'])) {
            $variantId = trim($_POST['product_id'] ?? '');
            $quantity = max(1, (int)($_POST['StoreQuantity'] ?? 1));
            if (!$variantId) respond(['error' => 'Missing product ID'], 400);

            $myProducts = BasicPrintfulRequest();
            $_SESSION['PrintfulItems'] = $myProducts;

            $found = null;
            foreach ($myProducts['result'] ?? [] as $product) {
                $variants = $product['sync_variants'] ?? $product['variants'] ?? [];
                foreach ($variants as $v) {
                    if ((string)($v['id'] ?? '') === (string)$variantId) {
                        $found = [
                            'parent_product_id' => $product['id'] ?? null,
                            'name' => $product['name'] ?? ($v['name'] ?? 'Unknown'),
                            'variant_id' => $v['id'] ?? $variantId,
                            'variant_name' => $v['name'] ?? '',
                            'price' => (float)($v['retail_price'] ?? ($v['price'] ?? 0)),
                            'size' => $v['size'] ?? ($v['size_name'] ?? ''),
                            'availability' => $v['availability_status'] ?? ($v['availability'] ?? ''),
                            'thumbnail' => $product['thumbnail_url'] ?? ($product['image'] ?? '')
                        ];
                        break 2;
                    }
                }
            }
            if (!$found) respond(['error' => 'Variant not found'], 404);

            $result = addToCart($found, $quantity);
            respond(['success' => true, 'cart_count' => count($_SESSION['ShoppingCartItems']), 'item' => $result['item']]);
        }

        // ---- Stripe Checkout ----
        if (($xmljson['type'] ?? '') === 'Stripe Checkout') {
            $cartItems = $_SESSION['ShoppingCartItems'] ?? [];
            if (empty($cartItems)) respond(['error' => 'Cart is empty'], 400);

            $checkout = CreateStripeCheckout($cartItems, "$allowed_origins[2]/tfMain.php?type=Printful Checkout", "$allowed_origins[2]/cancelled.php");
            respond([
                'success' => !empty($checkout['success']),
                'checkout_url' => $checkout['url'] ?? null,
                'session_id' => $checkout['id'] ?? null,
                'error' => $checkout['error'] ?? null
            ]);
        }

        // ---- Printful Checkout ----
        if (($xmljson['type'] ?? '') === 'Printful Checkout') {
            $cartItems = $_SESSION['ShoppingCartItems'] ?? [];
            if (empty($cartItems)) respond(['error' => 'Cart is empty'], 400);

            $result = CreatePrintfulOrder($cartItems, $xmljson['customer'] ?? []);
            if (!empty($result['success'])) unset($_SESSION['ShoppingCartItems']);
            respond([
                'success' => !empty($result['success']),
                'order' => $result['result'] ?? null,
                'error' => $result['error'] ?? null
            ]);
        }

        // ---- Subscribers Signup ----
        if (($xmljson['type'] ?? '') === 'Subscribers Signup') {
            $membership = $_POST['membershipLevel'] ?? 'free';
            $userData = $_POST;
            if (!empty($userData['TFRegisterPassword'])) {
                $userData['TFRegisterPassword'] = password_hash($userData['TFRegisterPassword'], PASSWORD_DEFAULT);
            }

            if ($membership === 'free') {
                InputIntoDatabase($membership, ...array_values($userData));
                respond(['success' => true, 'message' => 'Free membership created']);
            }

            $costMap = ['regular' => 400, 'vip' => 700, 'team' => 1000];
            $metadata = array_map('strval', $userData); // Convert all values to string for Stripe

            $s = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $costMap[strtolower($membership)] ?? 2000,
                        'product_data' => ['name' => 'Community Member Signup Fee']
                    ],
                    'quantity' => 1
                ]],
                'success_url' => "$allowed_origins[2]/tfMain.php?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => "$allowed_origins[2]/failed.php",
                'metadata' => $metadata
            ]);

            if (!empty($s->url)) {
                header("Location: " . $s->url);
                exit;
            } else {
                respond(['error' => 'Stripe session missing URL'], 500);
            }
        }

        respond(['error' => 'Invalid POST type'], 400);
    }

    // ---- GET Requests ----
    if ($servermethod === 'GET' && isApiRequest()) {
        if (isset($_GET['cart_action'])) {
            switch ($_GET['cart_action']) {
                case 'view':
                    respond(['success' => true, 'items' => $_SESSION['ShoppingCartItems'] ?? []]);
                    break;
                case 'clear':
                    $_SESSION['ShoppingCartItems'] = [];
                    respond(['success' => true, 'message' => 'Cart cleared']);
                    break;
            }
        }

        if (isset($_GET['fetch_printful_items'])) {
            respond(['success' => true, 'items' => $_SESSION['PrintfulItems']['result'] ?? []]);
        }

        respond(['success' => true, 'message' => 'GET request received']);
    }

} catch (Exception $e) {
    respond(['error' => $e->getMessage()], 500);
}
?>