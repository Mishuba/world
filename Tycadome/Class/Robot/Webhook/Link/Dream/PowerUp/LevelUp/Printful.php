<?php
require_once __DIR__ . "/Basic/Money.php";

class BeginnerStore extends BasicServer
{
    public function BasicPrintfulRequest()
    {
        $ch = curl_init('https://api.printful.com/store/products');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . getenv("PRINTFUL_API_KEY", false)]);
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
        $prod = $this->PrintfulProductionDescription($productId);
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
        $_SESSION['PrintfulItems'] = $this->BasicPrintfulRequest();

        $items = [];

        foreach ($_SESSION['PrintfulItems'] as $product) {

            // Validate product ID exists
            if (empty($product['id'])) {
                error_log("Warning: Product missing ID: " . json_encode($product));
                continue;
            }

            $variantResponse = $this->getVariantandPrice($product['id']);

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
        } else {
            $this->respond(['success' => true, 'items' => $_SESSION['PrintfulItems']['result'] ?? []]);
            ob_end_flush();
            exit;
        }
    }

    public function addToPrintCart()
    {
        if (isset($_POST['addProductToCart'])) {
            $variantId = trim($_POST['product_id'] ?? '');
            $quantity = max(1, (int) ($_POST['StoreQuantity'] ?? 1));
            if (!$variantId)
                $this->respond(['error' => 'Missing product ID'], 400);

            $myProducts = $this->BasicPrintfulRequest();
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
                $this->respond(['error' => 'Variant not found'], 404);
        }
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
                $this->respond(['error' => 'Cart is empty'], 400);

            $result = $this->CreatePrintfulOrder($cartItems, $xmljson['customer'] ?? []);
            if (!empty($result['success']))
                unset($_SESSION['ShoppingCartItems']);
            $this->respond([
                'success' => !empty($result['success']),
                'order' => $result['result'] ?? null,
                'error' => $result['error'] ?? null
            ]);
        }
    }
}
