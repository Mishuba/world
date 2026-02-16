<?php
header("Access-Control-Allow-Origin: https://tsunamiflow.club");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With, X-Request-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    $saveCustomer = $input['saveCustomer'] ?? false;
    $customerId = $input['customerId'] ?? null;
    $email = $input['email'] ?? null;
    $customer = null;

    // Reuse or create customer if needed
    if ($saveCustomer) {
        if ($customerId) {
            $customer = \Stripe\Customer::retrieve($customerId);
        } else {
            $customer = \Stripe\Customer::create([
                'email' => $email
            ]);
            $customerId = $customer->id;
        }
    }

    switch ($action) {

        case 'createPaymentIntent':
            $amount = $input['amount']; // in cents
            $currency = $input['currency'] ?? 'usd';

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'customer' => $customerId ?? null,
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            echo json_encode([
                'clientSecret' => $paymentIntent->client_secret,
                'customerId' => $customerId
            ]);
            break;

        case 'createSubscription':
            $priceId = $input['priceId']; // Stripe Price ID

            if (!$customer) {
                // Create customer if not already done
                $customer = \Stripe\Customer::create([
                    'email' => $email
                ]);
                $customerId = $customer->id;
            }

            $subscription = \Stripe\Subscription::create([
                'customer' => $customerId,
                'items' => [['price' => $priceId]],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $clientSecret = $subscription->latest_invoice->payment_intent->client_secret ?? null;

            echo json_encode([
                'clientSecret' => $clientSecret,
                'subscriptionId' => $subscription->id,
                'customerId' => $customerId
            ]);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>