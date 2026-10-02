<?php
require_once __DIR__ . "/LevelUp/Printul.php";

class TfMembers extends BeginnerStore
{
    public function CheckoutNewSignUp()
    {
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
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'unit_amount' => $costMap[strtolower($membership)] ?? 2000,
                            'product_data' => ['name' => 'Community Member Signup Fee']
                        ],
                        'quantity' => 1
                    ]
                ],
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
    }
}