<?php
defined('_JEXEC') or die('Restricted access');

// Requires PHP 5.3.3 and later.

// This snippet (and some of the curl code) due to the Facebook SDK.
if (!function_exists('curl_init')) {
  throw new Exception('Stripe plugin needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Stripe plugin needs the JSON PHP extension.');
}
if (!function_exists('mb_detect_encoding')) {
  throw new Exception('Stripe plugin needs the Multibyte String PHP extension.');
}

/**
 * STRIPE LIB - 3.21.0
 */

// Stripe singleton
require(dirname(__FILE__) . '/Stripe/Stripe.php');

// Utilities
require(dirname(__FILE__) . '/Stripe/Util/AutoPagingIterator.php');
require(dirname(__FILE__) . '/Stripe/Util/RequestOptions.php');
require(dirname(__FILE__) . '/Stripe/Util/Set.php');
require(dirname(__FILE__) . '/Stripe/Util/Util.php');

// HttpClient
require(dirname(__FILE__) . '/Stripe/HttpClient/ClientInterface.php');
require(dirname(__FILE__) . '/Stripe/HttpClient/CurlClient.php');

// Errors
require(dirname(__FILE__) . '/Stripe/Error/Base.php');
require(dirname(__FILE__) . '/Stripe/Error/Api.php');
require(dirname(__FILE__) . '/Stripe/Error/ApiConnection.php');
require(dirname(__FILE__) . '/Stripe/Error/Authentication.php');
require(dirname(__FILE__) . '/Stripe/Error/Card.php');
require(dirname(__FILE__) . '/Stripe/Error/InvalidRequest.php');
require(dirname(__FILE__) . '/Stripe/Error/RateLimit.php');

// Plumbing
require(dirname(__FILE__) . '/Stripe/ApiResponse.php');
require(dirname(__FILE__) . '/Stripe/JsonSerializable.php');
require(dirname(__FILE__) . '/Stripe/StripeObject.php');
require(dirname(__FILE__) . '/Stripe/ApiRequestor.php');
require(dirname(__FILE__) . '/Stripe/ApiResource.php');
require(dirname(__FILE__) . '/Stripe/SingletonApiResource.php');
require(dirname(__FILE__) . '/Stripe/AttachedObject.php');
require(dirname(__FILE__) . '/Stripe/ExternalAccount.php');

// Stripe API Resources
require(dirname(__FILE__) . '/Stripe/Account.php');
require(dirname(__FILE__) . '/Stripe/AlipayAccount.php');
require(dirname(__FILE__) . '/Stripe/ApplicationFee.php');
require(dirname(__FILE__) . '/Stripe/ApplicationFeeRefund.php');
require(dirname(__FILE__) . '/Stripe/Balance.php');
require(dirname(__FILE__) . '/Stripe/BalanceTransaction.php');
require(dirname(__FILE__) . '/Stripe/BankAccount.php');
require(dirname(__FILE__) . '/Stripe/BitcoinReceiver.php');
require(dirname(__FILE__) . '/Stripe/BitcoinTransaction.php');
require(dirname(__FILE__) . '/Stripe/Card.php');
require(dirname(__FILE__) . '/Stripe/Charge.php');
require(dirname(__FILE__) . '/Stripe/Collection.php');
require(dirname(__FILE__) . '/Stripe/CountrySpec.php');
require(dirname(__FILE__) . '/Stripe/Coupon.php');
require(dirname(__FILE__) . '/Stripe/Customer.php');
require(dirname(__FILE__) . '/Stripe/Dispute.php');
require(dirname(__FILE__) . '/Stripe/Event.php');
require(dirname(__FILE__) . '/Stripe/FileUpload.php');
require(dirname(__FILE__) . '/Stripe/Invoice.php');
require(dirname(__FILE__) . '/Stripe/InvoiceItem.php');
require(dirname(__FILE__) . '/Stripe/Order.php');
require(dirname(__FILE__) . '/Stripe/OrderReturn.php');
require(dirname(__FILE__) . '/Stripe/Plan.php');
require(dirname(__FILE__) . '/Stripe/Product.php');
require(dirname(__FILE__) . '/Stripe/Recipient.php');
require(dirname(__FILE__) . '/Stripe/Refund.php');
require(dirname(__FILE__) . '/Stripe/SKU.php');
require(dirname(__FILE__) . '/Stripe/Source.php');
require(dirname(__FILE__) . '/Stripe/Subscription.php');
require(dirname(__FILE__) . '/Stripe/ThreeDSecure.php');
require(dirname(__FILE__) . '/Stripe/Token.php');
require(dirname(__FILE__) . '/Stripe/Transfer.php');
require(dirname(__FILE__) . '/Stripe/TransferReversal.php');

/**
 * STRIPE BRIDGE - AVOID NAMESPACE ERRORS
 */
class StripeBridge {
	public static function setApiKey($data) { return \Stripe\Stripe::setApiKey($data); }
	public static function setApiVersion($data) { return \Stripe\Strip::setApiVersion($data); }
	public static function Charge_create($data) { return \Stripe\Charge::create($data); }
	public static function Customer_create($data) { return \Stripe\Customer::create($data); }
}
