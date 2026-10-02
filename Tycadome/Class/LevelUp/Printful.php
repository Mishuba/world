<?php
require_once __DIR__ . "/Basic/Money.php";

class BeginnerStore extends BasicServer
{
    public function addToPrintCart()
    {
        if (isset($_POST['addProductToCart'])) {
            $variantId = trim($_POST['product_id'] ?? '');
            $quantity = max(1, (int) ($_POST['StoreQuantity'] ?? 1));
            if (!$variantId)
                respond(['error' => 'Missing product ID'], 400);

            $myProducts = BasicPrintfulRequest();
            $_SESSION['PrintfulItems'] = $myProducts;

            $found = null;
            foreach ($myProducts['result'] ?? [] as $product) {
                $variants = $product['sync_variants'] ?? $product['variants'] ?? [];
                foreach ($variants as $v) {
                    if ((string) ($v['id'] ?? '') === (string) $variantId) {
                        $found = [
                            'parent_product_id' => $product['id'] ?? null,
                            'name' => $product['name'] ?? ($v['name'] ?? 'Unknown'),
                            'variant_id' => $v['id'] ?? $variantId,
                            'variant_name' => $v['name'] ?? '',
                            'price' => (float) ($v['retail_price'] ?? ($v['price'] ?? 0)),
                            'size' => $v['size'] ?? ($v['size_name'] ?? ''),
                            'availability' => $v['availability_status'] ?? ($v['availability'] ?? ''),
                            'thumbnail' => $product['thumbnail_url'] ?? ($product['image'] ?? '')
                        ];
                        break 2;
                    }
                }
            }
            if (!$found)
                respond(['error' => 'Variant not found'], 404);

            $result = addToCart($found, $quantity);
            respond(['success' => true, 'cart_count' => count($_SESSION['ShoppingCartItems']), 'item' => $result['item']]);
        }
    }

    public function PrintulCheckout()
    {
        if (($xmljson['type'] ?? '') === 'Printful Checkout') {
            $cartItems = $_SESSION['ShoppingCartItems'] ?? [];
            if (empty($cartItems))
                respond(['error' => 'Cart is empty'], 400);

            $result = CreatePrintfulOrder($cartItems, $xmljson['customer'] ?? []);
            if (!empty($result['success']))
                unset($_SESSION['ShoppingCartItems']);
            respond([
                'success' => !empty($result['success']),
                'order' => $result['result'] ?? null,
                'error' => $result['error'] ?? null
            ]);
        }
    }

    public function CartAction()
    {
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
    }

    public function fetchPrintItems()
    {
        if (isset($_GET['fetch_printful_items'])) {
            respond(['success' => true, 'items' => $_SESSION['PrintfulItems']['result'] ?? []]);
        }

        respond(['success' => true, 'message' => 'GET request received']);
    }
}
