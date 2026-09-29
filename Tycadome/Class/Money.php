<?php

use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class BeginnerServer extends BasicServer
{

    public function StripeOne()
    {
        if (!defined('STRIPE_SECRET_KEY'))
            die("Error: STRIPE_SECRET_KEY not defined");
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
    }

    public function SendToTfClub($TfCurl, $token, $membership, $FirstName, $LastName, $NickName, $Gender, $Birthday, $Email, $Username, $Password, $ChineseZodiacSign, $WesternZodiacSign, $SpiritAnimal, $CelticTreeZodiacSign, $NativeAmericanZodiacSign, $VerdicAstrologySign, $GuardianAngel, $ChineseElement, $EyeColorMeaning, $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign, $MayanZodiacSign, $LoveLanguage, $BirthStone, $BirthFlower, $BloodType, $AttachmentStyle, $CharismaType, $BusinessPersonality, $DISC, $SocionicsType, $LearningStyle, $FinancialPersonalityType, $PrimaryMotivationStyle, $CreativeStyle, $ConflictManagementStyle, $TeamRolePreference)
    {
        $LetsDoThis = curl_setopt($TfCurl, CURLOPT_POSTFIELDS, [
            "token" => $token,
            "membership" => $membership,
            "FirstName" => $FirstName,
            "LastName" => $LastName,
            "NickName" => $NickName,
            "Gender" => $Gender,
            "Birthday" => $Birthday,
            "Email" => $Email,
            "Username" => $Username,
            "Password" => $Password,
            "ChineseZodiacSign" => $ChineseZodiacSign,
            "WesternZodiacSign" => $WesternZodiacSign,
            "SpiritAnimal" => $SpiritAnimal,
            "CelticTreeZodiacSign" => $CelticTreeZodiacSign,
            "NativeAmericanZodiacSign" => $NativeAmericanZodiacSign,
            "VedicAstrologySign" => $VerdicAstrologySign,
            "GuardianAngel" => $GuardianAngel,
            "ChineseElement" => $ChineseElement,
            "EyeColorMeaning" => $EyeColorMeaning,
            "GreekMythologyArchetype" => $GreekMythologyArchetype,
            "NorseMythologyPatronDeity" => $NorseMythologyPatronDeity,
            "EgyptianZodiacSign" => $EgyptianZodiacSign,
            "MayanZodiacSign" => $MayanZodiacSign,
            "LoveLanguage" => $LoveLanguage,
            "BirthStone" => $BirthStone,
            "BirthFlower" => $BirthFlower,
            "BloodType" => $BloodType,
            "AttachmentStyle" => $AttachmentStyle,
            "CharismaType" => $CharismaType,
            "BusinessPersonality" => $BusinessPersonality,
            "DISC" => $DISC,
            "SocionicsType" => $SocionicsType,
            "LearningStyle" => $LearningStyle,
            "FinancialPersonalityType" => $FinancialPersonalityType,
            "PrimaryMotivationStyle" => $PrimaryMotivationStyle,
            "CreativeStyle" => $CreativeStyle,
            "ConflictManagementStyle" => $ConflictManagementStyle,
            "TeamRolePreference" => $TeamRolePreference
        ]);
        return $LetsDoThis;
    }

    public function StripeTwo()
    {
        try {
            if (!defined('STRIPE_SECRET_KEY'))
                die("Error: STRIPE_SECRET_KEY not defined");
            $stripe = new StripeClient(STRIPE_SECRET_KEY ?? '');

            $stripe_webhook_event = Webhook::constructEvent($xml, $stripe_sig_header, WebhookSigningSecret);

        } catch (\UnexpectedValueException $e) {
            http_response_code(400);
            header("Content-Type: text/plain");
            echo ("Webhook Failed" . $e->getMessage());
            exit();
        } catch (SignatureVerificationException $e) {
            http_response_code(400);
            header("Content-Type: text/plain");
            error_log("Webhook signature verification failed: " . $e->getMessage());
            echo ("Webhook Signature Verficiation Failed: " . $e->getMessage());
            exit();
        } finally {
            $TfEventId = $stripe_webhook_event->id;
            $TfEventObject = $stripe_webhook_event->object;
            $TfEventAppVersion = $stripe_webhook_event->api_version;
            $TfEventCreated = $stripe_webhook_event->created;
            $TfEventLiveMode = $stripe_webhook_event->livemode;

            $stripe_webhook_event_type = $stripe_webhook_event->type;
            switch ($stripe_webhook_event_type) {
                case "charge.captured":
                    http_response_code(200);
                    $current_stripe_session = $stripe_webhook_event->data->object;
                    $WorkNow = json_encode([
                        "status" => "success",
                        "id" => $stripe_webhook_event->data->object->id,
                        "event" => $stripe_webhook_event_type,
                        "object" => $stripe_webhook_event->object,
                        "created" => $stripe_webhook_event->created
                    ]);
                    echo ($WorkNow);
                    break;
                case "charge.expired":

                    break;
                case "charge.failed":

                    break;
                case "charge.pending":

                    break;
                case "charge.refunded":

                    break;
                case "charge.succeeded":

                    break;
                case "charge.updated":

                    break;
                case "charge.dispute.closed":

                    break;
                case "charge.dispute.created":

                    break;
                case "charge.dispute.funds_reinstated":

                    break;
                case "charge.dispute.funds_withdrawn":

                    break;
                case "charge.dispute.updated":

                    break;
                case "charge.refund.updated":

                    break;
                case "checkout.session.async_payment_failed":
                    //Occurs when a payment intent using a delayed payment method fails
                    http_response_code(200);
                    $current_stripe_session = $stripe_webhook_event->data->object;

                    echo ("ok");
                    break;
                case "checkout.session.async_payment_succeeded":
                    //Occurs when a payment intent using a delayed payment method finally succeeds.
                    http_response_code(200);
                    $current_stripe_session = $stripe_webhook_event->data->object;
                    $id = $current_stripe_session->id;

                    $CheckoutDude = json_encode([
                        "id" => $id,
                        "object" => $current_stripe_session,
                        "amount_subtotal" => $current_stripe_session->amount_subtotal,
                        "amount_total" => $current_stripe_session->amount_total,
                        "tax_liability_type" => $current_stripe_session->automatic_tax->liability->type,
                        "tax_provider" => $current_stripe_session->automatic->tax->provider,
                        "tax_status" => $current_stripe_session->automatic->status,
                        "cancel_url" => $current_stripe_session->cancel_url,
                        "client_reference_id" => $current_stripe_session->client_reference_id,
                        "client_secret" => $current_stripe_session->client_secret,
                        "collected_information" => $current_stripe_session->collected_information,
                        "consent" => $current_stripe_session->conset,
                        "created" => $current_stripe_session->created,
                        "currency" => $current_stripe_session->currency,
                        "currency_conversion" => $current_stripe_session->currency_conversion,
                        "custom_fields" => $current_stripe_session->custom_fields,
                        "custom_text" => $current_stripe_session->custom_text->after_submit,
                        "shipping_address" => $current_stripe_session->custom_text->shipping_address,
                        "custom_text_submit" => $current_stripe_session->custom_text->submit,
                        "customer" => $current_stripe_session->customer,
                        "customer_creation" => $current_stripe_session->customer_creation,
                        "customer_details" => $current_stripe_session->customer_details,
                        "customer_email" => $current_stripe_session->customer_email,
                        "discounts" => $current_stripe_session->discounts,
                        "expires_at" => $current_stripe_session->expires_at,
                        "invoice" => $current_stripe_session->invoice,
                        "invoice_data_account_tax_ids" => $current_stripe_session->invoice_creation->invoice_data->account_tax_ids,
                        "invoice_data_custom_fields" => $current_stripe_session->invoice_creation->invoice_data->custom_fields,
                        "invoice_data_description" => $current_stripe_session->invoice_creation->invoice_data->description,
                        "invoice_data_footer" => $current_stripe_session->invoice_creation->invoice_data->footer,
                        "invoice_data_issuer" => $current_stripe_session->invoice_creation->invoice_data->issuer,
                        "invoice_data_metadata" => $current_stripe_session->invoice_creation->invoice_data->metadata,
                        "invoice_data_rendering_options" => $current_stripe_session->invoice_creation->invoice_data->rendering_options,
                        "metadata" => $current_stripe_session->metadata,
                        "mode" => $current_stripe_session->mode,
                        "origin_context" => $current_stripe_session->origin_context,
                        "payment_intent" => $current_stripe_session->payment_intent,
                        "payment_link" => $current_stripe_session->payment_link,
                        "payment_method_collection" => $current_stripe_session->payment_method_collection,
                        "payment_method_configuration_details_id" => $current_stripe_session->payment_method_configuration_details->id,
                        "payment_method_options_card_request_three_d_secure" => $current_stripe_session->payment_method_options->card->request_three_d_secure,
                        "payment_method_types" => $current_stripe_session->payment_method_types,
                        "payment_status" => $current_stripe_session->payment_status,
                        "permissions" => $current_stripe_session->permissions,
                        "recovered_from" => $current_stripe_session->recovered_from,
                        "saved_payment_method_options" => $current_stripe_session->saved_payment_method_options,
                        "setup_intent" => $current_stripe_session->setup_intent,
                        "shipping_address_collection" => $current_stripe_session->shipping_address_collection,
                        "shipping_cost" => $current_stripe_session->shipping_cost,
                        "shipping_details" => $current_stripe_session->shipping_details,
                        "shipping_options" => $current_stripe_session->shipping_options,
                        "status" => $current_stripe_session->session,
                        "submit_type" => $current_stripe_session->submit_type,
                        "subscription" => $current_stripe_session->subscription,
                        "success_url" => $current_stripe_session->success_url,
                        "total_details_amount_discount" => $current_stripe_session->total_details->amount_discount,
                        "total_details_amount_shipping" => $current_stripe_session->total_details->amount_shipping,
                        "total_details_amount_tax" => $current_stripe_session->total_details->amount_tax,
                        "total_details_ui_mode" => $current_stripe_session->total_details->ui_mode,
                        "total_details_url" => $current_stripe_session->total_details->url,
                        "total_details_wallet_options" => $current_stripe_session->total_details->wallet_options,
                        "previous_attributes" => $current_stripe_session->previous_attributes
                    ]);
                    // echo($CheckoutDude);
                    echo ("ok");
                    break;
                case "checkout.session.completed":
                    http_response_code(200);
                    // Occurs when a checkout Session has been successfully completed.
                    $current_stripe_session = $stripe_webhook_event->data->object;
                    if ($stripe_webhook_event->data->object->metadata->membership === "regular" || $stripe_webhook_event->data->object->metadata->membership === "vip" || $stripe_webhook_event->data->object->metadata->membership === "team") {
                        header("Content-Type: application/json");
                        $tfMetadata = $current_stripe_session->metadata;
                        $totalAmount = $current_stripe_session->line_items->price_data->unit_amount;
                        $metadataArray = $tfMetadata->toArray();

                        $SendToTfClub = curl_init("https://www.tsunamiflow.club/server.php");
                        curl_setopt($SendToTfClub, CURLOPT_POST, true);

                        $tfSubType = $metadataArray["membership"];
                        SendToTfClub($SendToTfClub, "TsunamiFlowClubStripeToken", $tfSubType, $metadataArray["FirstName"], $metadataArray["LastName"], $metadataArray["LastName"], $metadataArray["NickName"], $metadataArray["Gender"], $metadataArray["Birthday"], $metadataArray["Email"], $metadataArray["Username"], $metadataArray["Password"], $metadataArray["ChineseZodiacSign"], $metadataArray["WesternZodiacSign"], $metadataArray["SpiritAnimal"], $metadataArray["CelticTreeZodiacSign"], $metadataArray["NativeAmericanZodiacSign"], $metadataArray["VedicAstrologySign"], $metadataArray["GuardianAngel"], $metadataArray["ChineseElement"], $metadataArray["EyeColorMeaning"], $metadataArray["GreekMythologyArchetype"], $metadataArray["NorseMythologyPatronDeity"], $metadataArray["EgyptianZodiacSign"], $metadataArray["MayanZodiacSign"], $metadataArray["LoveLanguage"], $metadataArray["Birthstone"], $metadataArray["BirthFlower"], $metadataArray["BloodType"], $metadataArray["AttachmentStyle"], $metadataArray["CharismaType"], $metadataArray["BusinessPersonality"], $metadataArray["DISC"], $metadataArray["SocionicsType"], $metadataArray["LearningStyle"], $metadataArray["FinancialPersonalityType"], $metadataArray["PrimaryMotivationStyle"], $metadataArray["CreativeStyle"], $metadataArray["ConflictManagementStyle"], $metadataArray["TeamRolePreference"]);
                        curl_setopt($SendToTfClub, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($SendToTfClub);
                        curl_close($SendToTfClub);
                    } else {
                        header("Content-Type: text/plain");
                        echo "Completed checkout not from website";
                    }
                    echo ("ok");
                    break;
                case "checkout.session.expired":
                    //Occurs when a Checkout Session is expired.
                    http_response_code(200);
                    $current_stripe_session = $stripe_webhook_event->data->object;

                    echo ("ok");
                    break;
                case "identity.verification_session.redacted":
                    //Occurs whenever a VerificationSession is redacted.
                    break;
                case "identity.verification_session.canceled":
                    //Occurs whenever a VerificationSession is canceled
                    break;
                case "identity.verification_session.created":
                    //Occurs whenever a VerificationSession is created.
                    break;
                case "identity.verification_session.processing":
                    //Occurs whenever a VerificationSession transitions to require user input.
                    break;
                case "identity.verification_session.requires_input":
                    //Occurs whenever a VerificationSession transitions to require user input.
                    break;
                case "identity.verification_session.verified":
                    //Occurs whenever a VerificationSession transitions to verified.
                    break;
                case "invoice_payment.paid":
                    http_response_code(200);
                    header("Content-Type: application/json");
                    $current_stripe_session = $stripe_webhook_event->data->object;
                    $id = $current_stripe_session->id;
                    $TfObject = $current_stripe_session->object;
                    $amount = $current_stripe_session->amount_paid;
                    $currency = $current_stripe_session->currency;
                    $status = $current_stripe_session->status;
                    $paymentIntentId = $current_stripe_session->payment->payment_intent;
                    $TfInvoice = json_encode([
                        "status" => "success",
                        "id" => $id,
                        "object" => $TfObject,
                        "tfStatus" => $status,
                        "payment_intent_id" => $paymentIntentId
                    ]);
                    echo ($TfInvoice);
                case "issuing_authorization.request":
                    //Represents a synchronous request for authorization.
                    break;
                case "issuing_authorization.created":
                    //Occurs whenever an authorization is created.
                    break;
                case "issuing_authorization.updated":
                    //Occurs whenever an authorization is updated.
                    break;
                case "issuing_card.created":
                    //Occurs whenever a card is created.
                    break;
                case "issuing_card.updated":
                    //Occurs whenever a card is updated.
                    break;
                case "issuing_cardholder.created":
                    //Occurs whenever a cardholder is created.
                    break;
                case "issuing_cardholder.updated":
                    //Occurs whenever a cardholder is updated.
                    break;
                case "issuing_dispute.closed":
                    //Occurs whevener a dispute is won, lost or expired
                    break;
                case "issuing_dispute.created":
                    //Occurs whenever a dispute is created.
                    break;
                case "issuing_dispute.funds_reinstated":
                    //Occurs whenever funds are reinstated to your account for an issuing dispute.
                    break;
                case "issuing_dispute.funds_rescinded":
                    //Occurs whenever funds are deducted from your account for an issuing dispute.
                    break;
                case "issuing_dispute.submitted":
                    //Occurs whenever a dispute is submitted.
                    break;
                case "issuing_dispute.updated":
                    //Occurs whenever a dispute is updated.
                    break;
                case "issuing_token.created":
                    //Occurs whever an issuing digital wallet token is created.
                    break;
                case "issuing_token.updated":
                    //Occurs whenever an issuing digital wallet token is updated.
                    break;
                case "issuing_transaction.created":
                    //Occurs whenever an issuing transaction is created.
                    break;
                case "issuing_transaction.purchase_details_receipt_updated":
                    //Occurs whenever an issuing transaction is updated with receipt data.
                    break;
                case "issuing_transaction.updated":
                    //Occurs whenever an issuing transaction is updated.
                    break;
                case "payment_intent.amount_capturable_updated":
                    //Occurs when a PaymentIntent has funds to be captured. 
                    break;
                case "payment_intent.canceled":
                    //Occurs when a PaymentIntent is canceled.
                    break;
                case "payment_intent.created":
                    //Occurs when a PaymentIntent is created.
                    break;
                case "payment_intent.partially_funded":
                    //Occurs when funds are applied to a customer_balanace PaymentIntent.
                    break;
                case "payment_intent.payment_failed":
                    //Occurs when a PaymentIntent has failed the attempt to create a PaymentIntent.
                    break;
                case "payment_intent.processing":
                    //Occurs when a pyamentIntent has started processing.
                    break;
                case "payment_intent.requires_action":
                    //Occurs when a PaymentIntent transitions to requires_action state
                    break;
                case "payment_intent.succeeded":
                    //Occurs when a PaymentIntent has successfully completed payment.
                    break;
                case "payment_method.attached":
                    //Occurs whenever a new payment method is attached
                    break;
                case "payment_method.automatically_updated":
                    //Occurs whenever a payment method's details are automatically
                    break;
                case "payment_method.detached":
                    //Occurs whenever a payment method is detached from a customer.
                    break;
                case "payment_method.updated":
                    //Occurs whenever a payment method is updated via the 
                    break;
                case "person.created":
                    //Occurs whenever a person associated with an account is created.
                    break;
                case "person.deleted":
                    //Occurs whenever a person associated with an account is deleted.
                    break;
                case "person.updated":
                    //Occurs whenever a person associated with an account is updated.
                    break;
                case "price.created":
                    //Occurs whenever a price is created.
                    break;
                case "price.deleted":
                    //Occurs whenever a price is deleted.
                    break;
                case "price.updated":
                    //Occurs whenever a price is updated.
                    break;
                case "product.created":
                    //Occurs whenever a product is created.
                    break;
                case "product.deleted":
                    //Occurs whenever a product is deleted.
                    break;
                case "product.updated":
                    //Occurs whenever a product is updated.
                    break;
                case "refund.created":
                    //Occurs whenever a refund is created.
                    break;
                case "refund.failed":
                    //Occurs whenever a refund has failed.
                    break;
                case "refund.updated":
                    //Occurs whenevr a refund is updated.
                    break;
                case "setup_intent.canceled":
                    //Occurs when a SetupIntent is canceled.
                    break;
                case "setup_intent.created":
                    //Occurs when a new SetupIntent is created.
                    break;
                case "setup_intent.requires_action":
                    //Occurs when a SetupIntent is in requires_action state.
                    break;
                case "setup_intent.setup_failed":
                    //Occurs when a SetupIntent has failed the attempt to setup a payment method.
                    break;
                case "setup_intent.succeeded":
                    //Occurs when an SetupIntent has successfully setup a payment method.
                    break;
                case "subscription_schedule.aborted":
                    //Occurs whenever a subscription schedule is canceled due to the underlying delinquency.
                    break;
                case "subscription_schedule.canceled":
                    //Occurs whenever a subscription schedule is canceled.
                    break;
                case "subscription_schedule.completed":
                    //Occurs whenever a subscription schedule is completed.
                    break;
                case "subscription_schedule.created":
                    //Occurs whenever a new subscription schedule is created.
                    break;
                case "subscription_schedule.expiring":
                    //Occurs 7 days before a subscription schedule will expire.
                    break;
                case "subscription_schedule.released":
                    //Occurs whenever a new subscription schedule is released.
                    break;
                case "subscription_schedule.updated":
                    //Occurs whenever a subscription schedule is updated.
                    break;
                default:
                    http_response_code(200);
                    header("Content-Type: text/plain");
                    echo ("some kind of stripe event I do not know");
                    exit();
                    break;
            }
        }
    }
}

?>