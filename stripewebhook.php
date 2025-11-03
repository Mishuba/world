<?php
require "vendor/autoload.php";
require "config.php";

use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use \Stripe\Exception\ApiErrorException;

//Get Payload
$payload = @file_get_contents("php://input");
//file_put_contents("/tmp/stripe_log.json", $payload . PHP_EOL, FILE_APPEND);

//\Stripe\Stripe::setApiKey(WebhookSecretKey);
//$StfPk = new StripeClient(WebhookSecretKey);

$sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"] ?? "";

function SendToTfClub ($TfCurl, $token, $membership, $FirstName, $LastName, $NickName, $Gender, $Birthday, $Email, $Username, $Password, $ChineseZodiacSign, $WesternZodiacSign, $SpiritAnimal, $CelticTreeZodiacSign, $NativeAmericanZodiacSign, $VerdicAstrologySign, $GuardianAngel, $ChineseElement, $EyeColorMeaning, $GreekMythologyArchetype, $NorseMythologyPatronDeity, $EgyptianZodiacSign, $MayanZodiacSign, $LoveLanguage, $BirthStone, $BirthFlower, $BloodType, $AttachmentStyle, $CharismaType, $BusinessPersonality, $DISC, $SocionicsType, $LearningStyle, $FinancialPersonalityType, $PrimaryMotivationStyle, $CreativeStyle, $ConflictManagementStyle, $TeamRolePreference){
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

try {
$event = Webhook::constructEvent($payload, $sig_header, WebhookSigningSecret);

} catch (\UnexpectedValueException $e) {
	http_response_code(400);
	header("Content-Type: text/plain");
	echo("Webhook Failed" . $e->getMessage());
	exit();
} catch (\Stripe\Execption\SignatureVerificationException $e) {
	http_response_code(400);
	header("Content-Type: text/plain");
	error_log("Webhook signature verification failed: " . $e->getMessage());
	echo("Webhook Signature Verficiation Failed: " . $e->getMessage());
	exit();
} finally {
	$TfEventId = $event->id;
	$TfEventObject = $event->object;
	$TfEventAppVersion = $event->api_version;
	$TfEventCreated = $event->created;
	$TfEventLiveMode = $event->livemode;
	switch($event->type) {
		case "charge.captured":
			http_response_code(200);
			$session = $event->data->object;
			$WorkNow = json_encode([
				"status" => success,
				"id" => $event->data->object->id,
				"event" => $event->type,
				"object" => $event->object,
				"created" => $event->created
			]);
			echo (WorkNow);
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
			$session = $event->data->object;

			echo("ok");
			break;
		case "checkout.session.async_payment_succeeded":
			//Occurs when a payment intent using a delayed payment method finally succeeds.
			http_response_code(200);
			$session = $event->data->object;
			$id = $session->id;
			$

			$CheckoutDude = json_encode([
				"id" => $id,
				"object" => $session,
				"amount_subtotal" => $session->amount_subtotal,
				"amount_total" => $session->amount_total,
				"tax_liability_type" => $session->automatic_tax->liability->type,
				"tax_provider" => $session->automatic-tax->provider,
				"tax_status" => $session->automatic->status,
				"cancel_url" => $session->cancel_url,
				"client_reference_id" => $session->client_reference_id,
				"client_secret" => $session->client_secret,
				"collected_information" => $session->collected_information,
				"consent" => $session->conset,
				"created" => $session->created,
				"currency" => $session->currency,
				"currency_conversion" => $session->currency_conversion,
				"custom_fields" => $session->custom_fields,
				"custom_text" => $session->custom_text->after_submit,
				"shipping_address" => $session->custom_text->shipping_address,
				"custom_text_submit" => $session->custom_text->submit,
				"customer" => $session->customer,
				"customer_creation" => $session->customer_creation,
				"customer_details" => $session->customer_details,
				"customer_email" => $session->customer_email,
				"discounts" => $session->discounts,
				"expires_at" => $session->expires_at,
				"invoice" => $session->invoice,
				"invoice_data_account_tax_ids" => $session->invoice_creation->invoice_data->account_tax_ids,
				"invoice_data_custom_fields" => $session->invoice_creation->invoice_data->custom_fields,
				"invoice_data_description" => $session->invoice_creation->invoice_data->description,
				"invoice_data_footer" => $session->invoice_creation->invoice_data->footer,
				"invoice_data_issuer" => $session->invoice_creation->invoice_data->issuer,
				"invoice_data_metadata" => $session->invoice_creation->invoice_data->metadata,
				"invoice_data_rendering_options" => $session->invoice_creation->invoice_data->rendering_options,
				"metadata" => $session->metadata,
				"mode" => $session->mode,
				"origin_context" => $session->origin_context,
				"payment_intent" => $session->payment_intent,
				"payment_link" => $session->payment_link,
				"payment_method_collection" => $session->payment_method_collection,
				"payment_method_configuration_details_id" => $session->payment_method_configuration_details->id,
				"payment_method_options_card_request_three_d_secure" => $session->payment_method_options->card->request_three_d_secure,
				"payment_method_types" => $session->payment_method_types,
				"payment_status" => $session->payment_status,
				"permissions" => $session->permissions,
				"recovered_from" => $session->recovered_from,
				"saved_payment_method_options" => $session->saved_payment_method_options,
				"setup_intent" => $session->setup_intent,
				"shipping_address_collection" => $session->shipping_address_collection,
				"shipping_cost" => $session->shipping_cost,
				"shipping_details" => $session->shipping_details,
				"shipping_options" => $session->shipping_options,
				"status" => $session->session,
				"submit_type" => $session->submit_type,
				"subscription" => $session->subscription,
				"success_url" => $session->success_url,
				"total_details_amount_discount" => $session->total_details->amount_discount,
				"total_details_amount_shipping" => $session->total_details->amount_shipping,
				"total_details_amount_tax" => $session->total_details->amount_tax,
				"total_details_ui_mode" => $session->total_details->ui_mode,
				"total_details_url" => $session->total_details->url,
				"total_details_wallet_options" => $session->total_details->wallet_options,
				"previous_attributes" => $session->previous_attributes
			]);
			// echo($CheckoutDude);
			echo("ok");
			break;
		case "checkout.session.completed":
		http_response_code(200);
			// Occurs when a checkout Session has been successfully completed.
		$session = $event->data->object;
		if($event->data->object->metadata->membership === "regular" || $event->data->object->metadata->membership === "vip" || $event->data->object->metadata->membership === "team") {
			header("Content-Type: application/json");
			$tfMetadata = $session->metadata;
			$totalAmount = $session->line_items->price_data->unit_amount;
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
		echo("ok");
			break;
		case "checkout.session.expired":
			//Occurs when a Checkout Session is expired.
			http_response_code(200);
			$session = $event->data->object;
			
			echo("ok");
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
			$session = $event->data->object;
			$id = $session->id;
			$TfObject = $session->object;
			$amount = $session->amount_paid;
			$currency = $session->currency;
			$status = $session->status;
			$paymentIntentId = $session->payment->payment_intent;
			$TfInvoice = json_encode([
				"status" => "success",
				"id" => $id,
				"object" => $TfObject,
				"tfStatus" => $status,
				"payment_intent_id" => $paymentIntentId
			]);
			echo($TfInvoice);
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
			echo("some kind of stripe event I do not know");
			exit();
			break;
	}
}
?>
