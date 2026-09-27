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

require_once __DIR__ . "/../Tycadome/Variables/tycadomeVariables.php";
require "vendor/autoload.php";

use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

if (!defined('STRIPE_SECRET_KEY')) die("Error: STRIPE_SECRET_KEY not defined");
$stripe = new StripeClient(STRIPE_SECRET_KEY ?? '');

$action = $xmljson['action'] ?? '';

try {
    $saveCustomer = $xmljson['saveCustomer'] ?? false;
    $customerId = $xmljson['customerId'] ?? null;
    $email = $xmljson['email'] ?? null;
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
            $amount = $xmljson['amount']; // in cents
            $currency = $xmljson['currency'] ?? 'usd';

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
            $priceId = $xmljson['priceId']; // Stripe Price ID

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