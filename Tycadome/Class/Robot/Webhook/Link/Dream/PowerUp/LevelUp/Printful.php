<?php
require_once __DIR__ . "/Basic/Money.php";

class BeginnerStore extends BasicServer
{
    private function printfulGet(string $url): array
    {
        $api = $_ENV["PRINTFUL_API_KEY"] ?? getenv("PRINTFUL_API_KEY");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer" . $api]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log("Printful cURL error: " . curl_error($ch));
            return ['result' => []];
        }
        $decoded = json_decode($response, true);
        return is_array($decoded) && isset($decoded['result']) ? $decoded : ['result' => []];
    }

    public function BasicPrintfulRequest()
    {
        return $this->printfulGet('https://api.printful.com/store/products');
    }

    public function PrintfulProductionDescription($productId)
    {
        return $this->printfulGet("https://api.printful.com/store/products/" . urlencode((string) $productId));
    }

    // Returns ['sync_product' => [...], 'sync_variants' => [...]] or null
    public function getVariantandPrice($productId)
    {
        $prod = $this->PrintfulProductionDescription($productId);
        return !empty($prod['result']) ? $prod['result'] : null;
    }

    public function NPOtfTS(array $orderData)
    {
        $ch = curl_init('https://api.printful.com/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . ($_ENV["PRINTFUL_API_KEY"] ?? getenv("PRINTFUL_API_KEY")),
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return null;
        }

        $decodedResponse = json_decode($response, true);
        return $decodedResponse['result']['id'] ?? null;
    }

    public function fetchPrintItems()
    {
        $list = $this->BasicPrintfulRequest();
        $_SESSION['PrintfulItems'] = $list;

        $items = [];

        foreach ($list['result'] ?? [] as $product) {
            if (empty($product['id'])) {
                error_log("Warning: Product missing ID: " . json_encode($product));
                continue;
            }

            $detail = $this->getVariantandPrice($product['id']);
            $variants = [];

            if (is_array($detail) && isset($detail['sync_variants'])) {
                foreach ($detail['sync_variants'] as $variant) {
                    $image = null;

                    foreach ($variant['files'] ?? [] as $file) {
                        if (!empty($file['preview_url'])) {
                            $image = $file['preview_url'];
                            break;
                        }
                    }

                    if ($image === null && !empty($product['thumbnail_url'])) {
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
                "product_id" => $product['id'],
                "name" => $product['name'] ?? '',
                "description" => "no description",
                "thumbnail" => $product['thumbnail_url'] ?? null,
                "variants" => $variants
            ];
        }

        $this->respond(['success' => true, 'items' => $items]);
    }

    public function addToPrintCart()
    {
        if (!isset($_POST['addProductToCart'])) {
            return;
        }

        $variantId = trim($_POST['product_id'] ?? '');
        $parentId = trim($_POST['parent_product_id'] ?? '');
        $quantity = max(1, (int) ($_POST['StoreQuantity'] ?? 1));

        if ($variantId === '') {
            $this->respond(['error' => 'Missing product ID'], false, 400);
        }

        // Narrow the search to one product if the front end sent its parent ID
        $productIds = [];
        if ($parentId !== '') {
            $productIds[] = $parentId;
        } else {
            foreach ($this->BasicPrintfulRequest()['result'] ?? [] as $p) {
                if (!empty($p['id'])) {
                    $productIds[] = $p['id'];
                }
            }
        }

        $found = null;
        foreach ($productIds as $pid) {
            $detail = $this->getVariantandPrice($pid);
            if (!$detail) {
                continue;
            }
            $product = $detail['sync_product'] ?? [];
            foreach ($detail['sync_variants'] ?? [] as $v) {
                if ((string) ($v['id'] ?? '') === $variantId) {
                    $found = [
                        'parent_product_id' => $product['id'] ?? $pid,
                        'name' => $product['name'] ?? ($v['name'] ?? 'Unknown'),
                        'variant_id' => $v['id'],
                        'variant_name' => $v['name'] ?? '',
                        'price' => (float) ($v['retail_price'] ?? 0),
                        'size' => $v['size'] ?? '',
                        'availability' => $v['availability_status'] ?? '',
                        'thumbnail' => $product['thumbnail_url'] ?? '',
                        'quantity' => $quantity
                    ];
                    break 2;
                }
            }
        }

        if (!$found) {
            $this->respond(['error' => 'Variant not found'], false, 404);
        }

        // Merge into the cart
        $_SESSION['ShoppingCartItems'] = $_SESSION['ShoppingCartItems'] ?? [];
        $merged = false;
        foreach ($_SESSION['ShoppingCartItems'] as &$existing) {
            if ((string) $existing['variant_id'] === (string) $found['variant_id']) {
                $existing['quantity'] += $quantity;
                $merged = true;
                break;
            }
        }
        unset($existing);
        if (!$merged) {
            $_SESSION['ShoppingCartItems'][] = $found;
        }

        $this->respond(['success' => true, 'items' => $_SESSION['ShoppingCartItems']]);
    }

    public function CartAction()
    {
        if (isset($_GET['cart_action'])) {
            switch ($_GET['cart_action']) {
                case 'view':
                    $this->respond(['success' => true, 'items' => $_SESSION['ShoppingCartItems'] ?? []]);
                    break;
                case 'clear':
                    $_SESSION['ShoppingCartItems'] = [];
                    $this->respond(['success' => true, 'message' => 'Cart cleared']);
                    break;
            }
        }
    }

    public function CreatePrintfulOrder(array $cartItems, array $customer)
    {
        $apiKey = $_ENV["PRINTFUL_API_KEY"] ?? getenv("PRINTFUL_API_KEY");
        if (!$apiKey) {
            return ['success' => false, 'error' => 'Missing Printful API key'];
        }

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
            // Store products are ordered by sync_variant_id
            $order['items'][] = [
                "sync_variant_id" => $item['variant_id'],
                "quantity" => $item['quantity'] ?? 1
            ];
        }

        $ch = curl_init('https://api.printful.com/orders');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true) ?? [];
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'result' => $result];
        }

        return ['success' => false, 'error' => $result['error']['message'] ?? ($result['error'] ?? 'Unknown Printful error')];
    }

    public function PrintulCheckout()
    {
        if (($this->xmljson['type'] ?? '') === 'Printful Checkout') {
            $cartItems = $_SESSION['ShoppingCartItems'] ?? [];
            if (empty($cartItems)) {
                $this->respond(['error' => 'Cart is empty'], false, 400);
            }

            $result = $this->CreatePrintfulOrder($cartItems, $this->xmljson['customer'] ?? []);
            if (!empty($result['success'])) {
                unset($_SESSION['ShoppingCartItems']);
            }
            $this->respond([
                'success' => !empty($result['success']),
                'order' => $result['result'] ?? null,
                'error' => $result['error'] ?? null
            ]);
        }
    }
}
