<?php
/**
 * 2016 Jacques Grove
 *
 * @author    Jacques Grove
 * @copyright Jacques Grove Professional Web Development
 * @version   1.0.1
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * http://www.jacquesgrove.co.za
 */

include(dirname(__FILE__). '/../../config/config.inc.php');
include(dirname(__FILE__). '/../../init.php');
include(dirname(__FILE__). '/payhost.php');

$debug = Tools::safeOutput(Configuration::get('PAYHOST_DEBUG'));
$payhost = new Payhost();
$pay_request_id = Tools::getValue('PAY_REQUEST_ID');
$transaction_status = Tools::getValue('TRANSACTION_STATUS');
$result_code = Tools::getValue('RESULT_CODE');
$x_card_num = Tools::getValue('x_card_num');
$x_exp_date_m = Tools::getValue('x_exp_date_m');
$x_exp_date_y = Tools::getValue('x_exp_date_y');
$x_card_code = Tools::getValue('x_card_code');
$x_invoice_num = Tools::getValue('x_invoice_num');
$cardType = Tools::getValue('cardType');
$name = Tools::getValue('name');
$cart = Context::getContext()->cart;

if (!isset($x_invoice_num)) {
    if ($debug) {
        Logger::addLog('Missing x_invoice_num', 4);
    }
    die('An unrecoverable error occurred: Missing parameter');
}

if (!Validate::isLoadedObject($cart)) {
    if ($debug) {
        Logger::addLog('Cart loading failed for cart ' . $x_invoice_num, 4);
    }
    die('An unrecoverable error occurred with the cart ' . $x_invoice_num);
}

if ($cart->id != $x_invoice_num) {
    if ($debug) {
        Logger::addLog('Conflict between cart id order and customer cart id');
    }
    die('An unrecoverable conflict error occurred with the cart ' . $x_invoice_num);
}

$customer = new Customer($cart->id_customer);
$invoiceAddress = new Address($cart->id_address_invoice);
$currency = new Currency($cart->id_currency);

if (!Validate::isLoadedObject($customer)
    || !Validate::isLoadedObject($invoiceAddress)
       && !Validate::isLoadedObject($currency)) {
    if ($debug) {
        Logger::addLog('Issue loading customer, address and/or currency data');
    }
    die('An unrecoverable error occurred while retrieving your data');
}

if ($payhost->isSecure()) {
    $prefix = 'https://';
} else {
    $prefix = 'http://';
}

$notify = $prefix.$_SERVER['HTTP_HOST'].'/modules/payhost/notify.php';
$return = $prefix.$_SERVER['HTTP_HOST'].'/modules/payhost/validation.php';
$mode = Tools::safeOutput(Configuration::get('PAYHOST_MODE'));

if (Tools::strlen($x_exp_date_m) == 1) {
    $month = sprintf("%02d", $x_exp_date_m);
} else {
    $month = $x_exp_date_m;
}

$params = array();
if ($mode == 0) {
    $params['CardPaymentRequest']['Account']['PayGateId']
        = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_LOGIN_ID'));
    $params['CardPaymentRequest']['Account']['Password']
        = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_KEY'));
} else {
    $params['CardPaymentRequest']['Account']['PayGateId']
        = Tools::safeOutput(Configuration::get('PAYHOST_TEST_LOGIN_ID'));
    $params['CardPaymentRequest']['Account']['Password']
        = Tools::safeOutput(Configuration::get('PAYHOST_TEST_KEY'));
}

$params['CardPaymentRequest']['Customer']['FirstName'] = Tools::safeOutput($customer->firstname);
$params['CardPaymentRequest']['Customer']['LastName'] = Tools::safeOutput($customer->lastname);
$params['CardPaymentRequest']['Customer']['Email'] = $customer->email;
$params['CardPaymentRequest']['CardNumber'] = Tools::safeOutput($x_card_num);
$params['CardPaymentRequest']['CardExpiryDate'] = Tools::safeOutput($month . $x_exp_date_y);
$params['CardPaymentRequest']['CVV'] = Tools::safeOutput($x_card_code);
$params['CardPaymentRequest']['BudgetPeriod'] = 0;
$params['CardPaymentRequest']['Redirect']['NotifyUrl'] = $notify;
$params['CardPaymentRequest']['Redirect']['ReturnUrl'] = $return;
$params['CardPaymentRequest']['Redirect']['Target'] = '_parent';
$params['CardPaymentRequest']['Order']['MerchantOrderId'] = $x_invoice_num;
$params['CardPaymentRequest']['Order']['Currency'] = 'ZAR';
$params['CardPaymentRequest']['Order']['Amount']=number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '') * 100;
$url = 'https://secure.paygate.co.za/payhost/process.trans?wsdl';
$client = new SoapClient($url);

$result = $client->SinglePayment($params);


if ($result->CardPaymentResponse->Status->StatusName == 'ThreeDSecureRedirectRequired') {
    // Redirects to 3DSecure / Verified by Visa
    if ($debug) {
        Logger::addLog('3Dsecure Authentication required for ' . $x_invoice_num, 1);
    }
    $data = array();
    $data['type'] = 'ThreeDSecure';
    $data['url'] = $result->CardPaymentResponse->Redirect->RedirectUrl;
    $data['value1'] = $result->CardPaymentResponse->Redirect->UrlParams[0]->value;
    $data['value2'] = $result->CardPaymentResponse->Redirect->UrlParams[1]->value;
    $data['value3'] = $result->CardPaymentResponse->Redirect->UrlParams[2]->value;
    $data['errors'] = '';
    $data['success'] = 1;
    echo Tools::jsonEncode($data);

} elseif ($result->CardPaymentResponse->Status->StatusName == 'Completed'
          && $result->CardPaymentResponse->Status->ResultCode == 990017) {
    // no redirect required
    $PayRequestId = $result->CardPaymentResponse->Status->PayRequestId;
    // check gateway payment amount, and if not a match with cart amount return 0 else return amount paid at gateway
    $amount_paid = $payhost->checkAmountReturn($PayRequestId);
    // If the return is "0" then there is no match
    if ($amount_paid != 0) {
        $customer    = new Customer((int) $cart->id_customer);
        $sc          = $customer->secure_key;
        $payhost->validateOrder(
            (int) $cart->id,
            Configuration::get('PS_OS_PAYMENT'),
            $amount_paid,
            "Paygate Payhost",
            null,
            null,
            null,
            false,
            $sc
        );
        $auth_order       = new Order($payhost->currentOrder);
        $data            = array();
        $data['type']    = 'Complete';
        $data['errors']  = '';
        $data['success'] = 1;
        $data['url']     = 'index.php?controller=order-confirmation&';
        $data['id']      = $payhost->id;
        $data['cid']     = $cart->id;
        $data['ao']      = $auth_order->secure_key;
        $data['ref']     = $payhost->currentOrderReference;
        echo Tools::jsonEncode($data);
    } else {
        echo "The cart amount and gateway amount paid do not match.";
    }

    if ($debug) {
        Logger::addLog('Transaction successful for ' . $x_invoice_num, 1);
    }

} elseif ($result->CardPaymentResponse->Status->StatusName == 'Completed'
          && ($result->CardPaymentResponse->Status->TransactionStatusCode == 0
              || $result->CardPaymentResponse->Status->TransactionStatusCode == 2)
) {
    // Payment has been declined
    $payhost->processDeclinedPayment($result->CardPaymentResponse->Status->ResultCode);
}
