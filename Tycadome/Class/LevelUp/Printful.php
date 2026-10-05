<?php
require_once __DIR__ . "/Basic/Money.php";

class BeginnerStore extends BasicServer
{
    public function BasicPrintfulRequest()
    {
        $ch = curl_init('https://api.printful.com/store/products');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . getenv("PRINTFUL_API_KEY")]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return ['result' => []];
        }
        curl_close($ch);

        $decoded = json_decode($response, true);
        return is_array($decoded) && isset($decoded['result']) ? $decoded : ['result' => []];
    }

    public function PrintfulProductionDescription($productId)
    {
        $ch = curl_init("https://api.printful.com/store/products/$productId");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . getenv("PRINTFUL_API_KEY")]);
        curl_setopt(
            $ch,
            CURLOPT_RETURNTRANSFER,
            true
        );
        $response = curl_exec($ch);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
        if (curl_errno($ch)) {
            curl_close($ch);
            return ['result' => []];
        }
        curl_close($ch);

        $decoded = json_decode($response, true);
        return is_array($decoded) && isset($decoded['result']) ? $decoded : ['result' => []];
    }

    public function getVariantandPrice($productId)
    {
        $prod = PrintfulProductionDescription($productId);
        return $prod['result'] ?? null; // Return full product
    }

    public function NPOtfTS(array $orderData)
    {
        $ch = curl_init('https://api.printful.com/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . getenv("PRINTFUL_API_KEY"),
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }
        curl_close($ch);

        $decodedResponse = json_decode($response, true);
        return $decodedResponse['result']['id'] ?? null;
    }

    public function fetchPrintItems()
    {
        if (isset($_GET['fetch_printful_items'])) {
            respond(['success' => true, 'items' => $_SESSION['PrintfulItems']['result'] ?? []]);
        }

        respond(['success' => true, 'message' => 'GET request received']);
    }

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

    public function CreatePrintfulOrder(array $cartItems, array $customer)
    {
        $apiKey = getenv("PRINTFUL_API_KEY");
        if (!$apiKey)
            return ['error' => 'Missing Printful API key'];

        $order = [
            "recipient" => [
                "name" => $customer['name'] ?? 'Unknown',
                "address1" => $customer['address1'] ?? '',
                "city" => $customer['city'] ?? '',
                "state_code" => $customer['state_code'] ?? '',
                "country_code" => $customer['country_code'] ?? '',
                "zip" => $customer['zip'] ?? '',
                "email" => $customer['email'] ?? '',
                "phone" => $customer['phone'] ?? ''
            ],
            "items" => []
        ];

        foreach ($cartItems as $item) {
            $order['items'][] = [
                "variant_id" => $item['variant_id'],
                "quantity" => $item['quantity']
            ];
        }

        $ch = curl_init('https://api.printful.com/orders');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . getenv("PRINTFUL_API_KEY"),
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true) ?? [];
        if ($httpCode >= 200 && $httpCode < 300)
            return ['success' => true, 'result' => $result];

        return ['success' => false, 'error' => $result['error'] ?? 'Unknown Printful error'];
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
}
