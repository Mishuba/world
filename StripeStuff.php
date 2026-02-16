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
    switch ($action) {
        case 'createPaymentIntent':
            $amount = $input['amount']; // in cents
            $currency = $input['currency'] ?? 'usd';

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            echo json_encode(['clientSecret' => $paymentIntent->client_secret]);
            break;

        case 'createSubscription':
            $customerEmail = $input['email'];
            $priceId = $input['priceId']; // Stripe Price ID

            $customer = \Stripe\Customer::create([
                'email' => $customerEmail
            ]);

            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => $priceId],
                ],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            echo json_encode([
                'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret,
                'subscriptionId' => $subscription->id
            ]);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}


?>