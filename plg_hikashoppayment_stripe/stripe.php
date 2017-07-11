<?php

class plgHikashoppaymentStripe extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD',
		'BDT','BGN','BIF','BMD','BND','BOB','BRL','BSD','BWP','BZD','CAD','CDF',
		'CHF','CLP','CNY','COP','CRC','CVE','CZK','DJF','DKK','DOP','DZD','EEK',
		'EGP','ETB','EUR','FJD','FKP','GBP','GEL','GIP','GMD','GNF','GTQ','GYD',
		'HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','ISK','JMD','JNY','KES',
		'KGS','KHR','KMF','KRW','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL',
		'LVL','MAD','MDL','MGA','MKD','MNT','MOP','MRO','MUR','MVR','MWK','MXN',
		'MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','PAB','PEN','PGK','PHP',
		'PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SEK',
		'SGD','SHP','SLL','SOS','SRD','STD','SVC','SZL','THB','TJS','TOP','TRY',
		'TTD','TWD','TZS','UAH','UGX','USD','UYI','UZS','VEF','VND','VUV','SWT',
		'XAF','XCD','XOF','XPF','YER','ZAR','ZMW'
	);
	var $multiple = true;
	var $name = 'stripe';
	var $doc_form = 'stripe';

	/**
	 * This array contains the specific configuration needed (back end)
	 */
	var $pluginConfig = array(
		'publishable_key' => array('STRIPE_PUBLISHABLE_KEY', 'input'),
		'secret_key' => array('STRIPE_SECRET_KEY', 'input'),
		'debug' => array('DEBUG', 'boolean', '0'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	/**
	 *
	 */
	public function __construct(&$subject, $config)
	{
		return parent::__construct($subject, $config);
	}

	/**
	 *
	 */
	protected function init()
	{
		static $init = null;
		if($init !== null)
			return $init;

		if (version_compare(PHP_VERSION, '5.3.3') < 0) {
			$app = JFactory::getApplication();
			if($app->isAdmin())
				$app->enqueueMessage('Stripe plugin requires PHP 5.3.3 or later', 'error');

			$init = false;
			return $init;
		}

		try {
			include_once(dirname(__FILE__).'/lib/Stripe.php');
			$init = true;
		} catch(Exception $e) {
			$app = JFactory::getApplication();
			if($app->isAdmin())
				hikashop_display($e->getMessage());
			$init = false;
		}
		return $init;
	}

	/**
	 *
	 */
	public function onPaymentDisplay(&$order, &$methods, &$usable_methods)
	{
		if(!$this->init())
			return false;
		return parent::onPaymentDisplay($order, $methods, $usable_methods);
	}

	/**
	 *
	 */
	public function onAfterOrderConfirm(&$order, &$methods, $method_id)
	{
		if(!$this->init())
			return false;
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		
		$vars = array();
		$address1 = '';
		$address2 = '';

		if(!empty($order->cart->billing_address->address_street2))
			$address2 = substr($order->cart->billing_address->address_street2, 0, 99);

		if(!empty($order->cart->billing_address->address_street)) {
			if(strlen($order->cart->billing_address->address_street) > 100) {
				$address1 = substr($order->cart->billing_address->address_street, 0, 99);
				if(empty($address2))
					$address2 = substr($order->cart->billing_address->address_street, 99, 199);
			} else {
				$address1 = $order->cart->billing_address->address_street;
			}
		}

		$vars['address_line1'] = $address1;
		$vars['address_line2'] = $address2;
		$vars['address_zip'] = @$order->cart->billing_address->address_post_code;
		$vars['address_city'] = @$order->cart->billing_address->address_city;
		if((!isset($order->cart->billing_address->address_state->zone_code_3) || is_numeric($order->cart->billing_address->address_state->zone_code_3)) && !empty($order->cart->billing_address->address_country->zone_name)){
			$vars['address_state'] = @$order->cart->billing_address->address_state->zone_name;
		}else{
			$vars['address_state'] = @$order->cart->billing_address->address_state->zone_code_3;
		}
		$vars['address_country'] = @$order->cart->billing_address->address_country->zone_code_2;
		$this->vars = $vars;

		$this->notifyurl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&orderid='.$order->order_id;
		$this->order =& $order;
		return $this->showPage('end');
	}

	/**
	 * Set default values when creating a new instance
	 */
	public function getPaymentDefaultValues(&$element)
	{
		if(!$this->init())
			return false;

		$element->payment_name = 'Stripe';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,American_Express';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';
	}

	/**
	 *
	 */
	public function onPaymentNotification(&$statuses)
	{
		if(!$this->init())
			return false;

		$order_id = (int)$_REQUEST['orderid'];
		$dbOrder = $this->getOrder($order_id);

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
		{
			$this->writeToLog('The system can\'t load the payment params');
			return false;
		}
		$this->loadOrderData($dbOrder);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;

		//
		//
		$currency = $this->currency->currency_code;
		$amout = round($dbOrder->order_full_price, 2) * 100;
		$desc = JText::sprintf('ORDER_NUMBER').' : '.$order_id;

		StripeBridge::setApiKey(trim($this->payment_params->secret_key));
		// StripeBridge::setApiVersion('2013-12-03');
		$token = $_POST['stripeToken'];

		$stripe_customer_id = !empty($this->user->user_params->stripe_customer_id) ? $this->user->user_params->stripe_customer_id : null;
		
		try {
			if(empty($stripe_customer_id) && !empty($this->user->user_email)) {
				// Create the customer in Stripe
				$customer = StripeBridge::Customer_create(array(
					'email' => $this->user->user_email,
					'source' => $token,
				));
				
				// Store the Stripe Customer ID in HikaShop
				$user = new stdClass();
				$user->user_id = $this->user->user_id;
				$user->user_params = $this->user->user_params;
				$user->user_params->stripe_customer_id = $customer->id;
				
				$userClass = hikashop_get('class.user');
				$userClass->save($user);
				
				// 
				$stripe_customer_id = $customer->id;
			} else if(!empty($stripe_customer_id)) {
				$customer = StripeBridge::Customer_retrieve($stripe_customer_id);
				// Create a new card for the customer
				$card = $customer->sources->create(array('source' => $token));
				$customer->default_source = $card->id;
				$customer->save();
			}

			// Make the charge
			if(!empty($stripe_customer_id)) {
				$charge = StripeBridge::Charge_create(array(
					'amount' => $amout, // amount in cents, again
					'currency' => $currency,
					'description' => $desc,
					'customer' => $stripe_customer_id
				));
			} else {
				$charge = StripeBridge::Charge_create(array(
					'amount' => $amout, // amount in cents, again
					'currency' => $currency,
					'description' => $desc,
					'card' => $token
				));
			}
		}
		catch(Exception $e)
		{
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
			$this->app->redirect($cancel_url, 'Error charge : '.$e->getMessage());
			$this->writeToLog($e->getMessage());
			return false;
		}

		$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
		$this->app->redirect($return_url);
		return true;
	}
}
