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

class Payhost extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'payhost';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.1';
        $this->author = 'Jacques Grove';
        $this->payhost_available_currencies = array('ZAR');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->need_instance = 0;
        $this->module_key = 'aaf4c5ac5c528b653a8a6012a9edb3b6';
        parent::__construct();
        $this->displayName = $this->l('Paygate Payhost (Host to host integration)');
        $this->description = $this->l('Receive payment with Paygate Payhost');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        /* Check if cURL is enabled */
        if (!extension_loaded('soap')) {
            $this->warning = $this->l('Soap extension must be enabled on your server to use this module.');
        }

        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            require( _PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php' );
        }
        $this->checkForUpdates();
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('orderConfirmation') &&
            $this->registerHook('payment') &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            Configuration::updateValue('PAYHOST_TEST_MODE', 0) &&
            Configuration::updateValue('PAYHOST_DEBUG', 1) &&
            Configuration::updateValue('PAYHOST_LIVE_LOGIN_ID', '') &&
            Configuration::updateValue('PAYHOST_LIVE_KEY', '') &&
            Configuration::updateValue('PAYHOST_TEST_LOGIN_ID', '') &&
            Configuration::updateValue('PAYHOST_TEST_KEY', '') &&
            Configuration::updateValue('PAYHOST_CARD_VISA', '') &&
            Configuration::updateValue('PAYHOST_CARD_MASTERCARD', '') &&
            Configuration::updateValue('PAYHOST_CARD_DISCOVER', '') &&
            Configuration::updateValue('PAYHOST_CARD_AX', '') &&
            Configuration::updateValue('PAYHOST_CARD_DINERSCLUB', '') &&
            Configuration::updateValue('PAYHOST_RETURN_URL', '') &&
            Configuration::updateValue('PAYHOST_NOTIFY_URL', '') &&
            Configuration::updateValue('PAYHOST_SSL_CERT', '');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PAYHOST_TEST_MODE');
        Configuration::deleteByName('PAYHOST_DEBUG');
        Configuration::deleteByName('PAYHOST_LIVE_LOGIN_ID');
        Configuration::deleteByName('PAYHOST_LIVE_KEY');
        Configuration::deleteByName('PAYHOST_TEST_LOGIN_ID');
        Configuration::deleteByName('PAYHOST_TEST_KEY');
        Configuration::deleteByName('PAYHOST_CARD_VISA');
        Configuration::deleteByName('PAYHOST_CARD_MASTERCARD');
        Configuration::deleteByName('PAYHOST_CARD_DISCOVER');
        Configuration::deleteByName('PAYHOST_CARD_AX');
        Configuration::deleteByName('PAYHOST_CARD_DINERSCLUB');
        Configuration::deleteByName('PAYHOST_RETURN_URL');
        Configuration::deleteByName('PAYHOST_NOTIFY_URL');
        Configuration::deleteByName('PAYHOST_LIVE_LOGIN_ID');
        Configuration::deleteByName('PAYHOST_LIVE_KEY');
        Configuration::deleteByName('PAYHOST_TEST_LOGIN_ID');
        Configuration::deleteByName('PAYHOST_TEST_KEY');
        Configuration::deleteByName('PAYHOST_SSL_CERT');
        return parent::uninstall();
    }

    public function hookOrderConfirmation($params)
    {
        if ($params['objOrder']->module != $this->name) {
            return;
        }

        if ($params['objOrder']->getCurrentState() != Configuration::get('PS_OS_ERROR')) {
            Configuration::updateValue('PAYHOST_CONFIGURATION_OK', true);
            $this->context->smarty->assign(array('status' => 'ok', 'id_order' => $params['objOrder']->id));
        } else {
            $this->context->smarty->assign('status', 'failed');
        }

        return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
    }

    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJQuery();
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $this->context->controller->addJqueryPlugin('fancybox');
        }

        $this->context->controller->addCSS($this->_path.'css/payhost.css');
    }

    public function getContent()
    {
        $html = '';

        if (Tools::isSubmit('submitModule')) {
            $payhost_mode = (int)Tools::getvalue('payhost_mode');
            if ($payhost_mode == 0) {
                Configuration::updateValue('PAYHOST_MODE', 0);
            } elseif ($payhost_mode == 1) {
                Configuration::updateValue('PAYHOST_MODE', 1);
            }
            Configuration::updateValue('PAYHOST_DEBUG', Tools::getvalue('payhost_debug'));
            Configuration::updateValue('PAYHOST_LIVE_LOGIN_ID', Tools::getvalue('payhost_live_login_id'));
            Configuration::updateValue('PAYHOST_LIVE_KEY', Tools::getvalue('payhost_live_key'));
            Configuration::updateValue('PAYHOST_TEST_LOGIN_ID', Tools::getvalue('payhost_test_login_id'));
            Configuration::updateValue('PAYHOST_TEST_KEY', Tools::getvalue('payhost_test_key'));
            Configuration::updateValue('PAYHOST_CARD_VISA', Tools::getvalue('payhost_card_visa'));
            Configuration::updateValue('PAYHOST_CARD_MASTERCARD', Tools::getvalue('payhost_card_mastercard'));
            Configuration::updateValue('PAYHOST_CARD_DISCOVER', Tools::getvalue('payhost_card_discover'));
            Configuration::updateValue('PAYHOST_CARD_AX', Tools::getvalue('payhost_card_ax'));
            Configuration::updateValue('PAYHOST_CARD_DINERSCLUB', Tools::getvalue('payhost_card_dinersclub'));
            Configuration::updateValue('PAYHOST_NOTIFY_URL', Tools::getvalue('payhost_notify_url'));
            Configuration::updateValue('PAYHOST_RETURN_URL', Tools::getvalue('payhost_return_url'));
            Configuration::updateValue('PAYHOST_SSL_CERT', Tools::getvalue('payhost_ssl_cert'));
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
        }

        $currencies = Currency::getCurrencies(false, true);
        $order_states = OrderState::getOrderStates((int)$this->context->cookie->id_lang);

        $this->context->smarty->assign(array(
            'available_currencies' => $this->payhost_available_currencies,
            'currencies' => $currencies,
            'module_dir' => $this->_path,
            'order_states' => $order_states,
            'PAYHOST_DEBUG' => (bool)Configuration::get('PAYHOST_DEBUG'),
            'PAYHOST_MODE' => (bool)Configuration::get('PAYHOST_MODE'),
            'PAYHOST_LIVE_LOGIN_ID' => Configuration::get('PAYHOST_LIVE_LOGIN_ID'),
            'PAYHOST_LIVE_KEY' => Configuration::get('PAYHOST_LIVE_KEY'),
            'PAYHOST_TEST_LOGIN_ID' => Configuration::get('PAYHOST_TEST_LOGIN_ID'),
            'PAYHOST_TEST_KEY' => Configuration::get('PAYHOST_TEST_KEY'),
            'PAYHOST_CARD_VISA' => Configuration::get('PAYHOST_CARD_VISA'),
            'PAYHOST_CARD_MASTERCARD' => Configuration::get('PAYHOST_CARD_MASTERCARD'),
            'PAYHOST_CARD_DISCOVER' => Configuration::get('PAYHOST_CARD_DISCOVER'),
            'PAYHOST_CARD_AX' => Configuration::get('PAYHOST_CARD_AX'),
            'PAYHOST_CARD_DINERSCLUB' => Configuration::get('PAYHOST_CARD_DINERSCLUB'),
            'PAYHOST_CARD_SHOP' => Configuration::get('PAYHOST_CARD_SHOP'),
            'PAYHOST_NOTIFY_URL' => Configuration::get('PAYHOST_NOTIFY_URL'),
            'PAYHOST_RETURN_URL' => Configuration::get('PAYHOST_RETURN_URL'),
            'PAYHOST_SSL_CERT' => Configuration::get('PAYHOST_SSL_CERT'),
        ));

        return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/configuration.tpl');
    }

    public function hookPayment($params)
    {
        $currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);

        if (!Validate::isLoadedObject($currency)) {
            return false;
        }

        $isFailed = Tools::getValue('payhosterror');
        $cards = array();
        $cards['visa'] = Configuration::get('PAYHOST_CARD_VISA') == 'on';
        $cards['mastercard'] = Configuration::get('PAYHOST_CARD_MASTERCARD') == 'on';
        $cards['discover'] = Configuration::get('PAYHOST_CARD_DISCOVER') == 'on';
        $cards['ax'] = Configuration::get('PAYHOST_CARD_AX') == 'on';
        $cards['dinersclub'] = Configuration::get('PAYHOST_CARD_DINERSCLUB') == 'on';
        $ssl_cert = Configuration::get('PAYHOST_SSL_CERT');
        $url = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/';

        $this->context->smarty->assign('x_invoice_num', (int)$params['cart']->id);
        $this->context->smarty->assign('cards', $cards);
        $this->context->smarty->assign('isFailed', $isFailed);
        $this->context->smarty->assign('new_base_dir', $url);
        $this->context->smarty->assign('currency', $currency);
        $this->context->smarty->assign('ssl_cert', $ssl_cert);
        $this->context->controller->addCSS($this->_path.'views/css/payhost.css');
        $this->context->controller->addCSS($this->_path.'views/css/creditCardTypeDetector.css');
        return $this->display(__FILE__, 'views/templates/hook/payhost.tpl');
    }

    public function hookHeader()
    {

        if (_PS_VERSION_ < '1.5') {
            Tools::addJS(_PS_JS_DIR_ . 'jquery/jquery.validate.creditcard2-1.0.1.js');
        } else {
            $this->context->controller->addJqueryPlugin('validate-creditcard');
        }

    }

    private function checkForUpdates()
    {
        // Used by PrestaShop 1.3 & 1.4
        if (version_compare(_PS_VERSION_, '1.5', '<') && self::isInstalled($this->name)) {
            foreach (array('1.4.8', '1.4.11') as $version) {
                $file = dirname(__FILE__) . '/upgrade/install-' . $version . '.php';
                if (Configuration::get('PAYHOST') < $version && file_exists($file)) {
                    include_once($file);
                    call_user_func('upgrade_module_' . str_replace('.', '_', $version), $this);
                }
            }
        }
    }

    public function checkGatewayReturn($pay_request_id)
    {
        $mode = Tools::safeOutput(Configuration::get('PAYHOST_MODE'));
        $cart           = Context::getContext()->cart;
        $params = array();

        if ($mode == 0) {
            $params['QueryRequest']['Account']['PayGateId']
                = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_LOGIN_ID'));
            $params['QueryRequest']['Account']['Password']
                = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_KEY'));
        } else {
            $params['QueryRequest']['Account']['PayGateId']
                = Tools::safeOutput(Configuration::get('PAYHOST_TEST_LOGIN_ID'));
            $params['QueryRequest']['Account']['Password']
                = Tools::safeOutput(Configuration::get('PAYHOST_TEST_KEY'));
        }
        $params['QueryRequest']['PayRequestId'] = Tools::safeOutput($pay_request_id);
        $url = 'https://secure.paygate.co.za/payhost/process.trans?wsdl';
        $client = new SoapClient($url);
        $result = $client->SingleFollowUp($params);
        $amount = $result->QueryResponse->Status->Amount;
        $amount_paid    = number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '');
        if ($amount == $amount_paid) {
            $this->processSuccessfulPayment($pay_request_id);
        } else {
            $nomatch = 1;
            $this->processDeclinedPayment($pay_request_id, $nomatch);
        }
    }

    public function checkAmountReturn($pay_request_id)
    {
        $mode = Tools::safeOutput(Configuration::get('PAYHOST_MODE'));
        $cart           = Context::getContext()->cart;
        $params = array();

        if ($mode == 0) {
            $params['QueryRequest']['Account']['PayGateId']
              = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_LOGIN_ID'));
            $params['QueryRequest']['Account']['Password']
              = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_KEY'));
        } else {
            $params['QueryRequest']['Account']['PayGateId']
              = Tools::safeOutput(Configuration::get('PAYHOST_TEST_LOGIN_ID'));
            $params['QueryRequest']['Account']['Password']
              = Tools::safeOutput(Configuration::get('PAYHOST_TEST_KEY'));
        }
        $params['QueryRequest']['PayRequestId'] = Tools::safeOutput($pay_request_id);
        $url = 'https://secure.paygate.co.za/payhost/process.trans?wsdl';
        $client = new SoapClient($url);
        $result = $client->SingleFollowUp($params);
        $amount = $result->QueryResponse->Status->Amount;
        $amount = number_format((float)$amount / 100, 2, '.', '');
        $amount_paid    = number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '');

        if ($amount == $amount_paid) {
            return $amount;
        } else {
            return 0;
        }
    }

    public function processSuccessfulPayment($pay_request_id)
    {
        $mode = Tools::safeOutput(Configuration::get('PAYHOST_MODE'));
        $cart           = Context::getContext()->cart;
        $customer       = new Customer((int)$cart->id_customer);
        $amount_paid    = number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '');
        $sc = $customer->secure_key;
        $this->validateOrder($cart->id, 2, $amount_paid, "Paygate Payhost", null, null, null, false, $sc);
        $ref = $this->currentOrderReference;
        $params = array();

        if ($mode == 0) {
            $params['QueryRequest']['Account']['PayGateId']
                = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_LOGIN_ID'));
            $params['QueryRequest']['Account']['Password']
                = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_KEY'));
        } else {
            $params['QueryRequest']['Account']['PayGateId']
                = Tools::safeOutput(Configuration::get('PAYHOST_TEST_LOGIN_ID'));
            $params['QueryRequest']['Account']['Password']
                = Tools::safeOutput(Configuration::get('PAYHOST_TEST_KEY'));
        }
        $params['QueryRequest']['PayRequestId'] = Tools::safeOutput($pay_request_id);
        $url = 'https://secure.paygate.co.za/payhost/process.trans?wsdl';
        $client = new SoapClient($url);
        $result = $client->SingleFollowUp($params);
        $result_code = $result->QueryResponse->Status->ResultCode;
        $transaction_id = $result->QueryResponse->Status->TransactionId;
        $card_brand = $result->QueryResponse->Status->PaymentType->Detail;
        if ($result_code == 990017) {
                $this->reportSuccess($transaction_id, $ref, $card_brand);
        }
        
        $url   = 'index.php?controller=order-confirmation&';
        if (_PS_VERSION_ < '1.5') {
            $url = 'order-confirmation.php?';
        }
        $auth_order = new Order($this->currentOrder);
        $id = $this->id;
        $cid = $cart->id;
        $sc = $auth_order->secure_key;
        Tools::redirect($url.'id_module='.$id.'&id_cart='.$cid.'&key='.$sc.'&order_reference='.$ref);
    }

    public function processDeclinedPayment($pay_request_id, $nomatch = 0)
    {
        $mode = Tools::safeOutput(Configuration::get('PAYHOST_MODE'));
        $params = array();
        if ($mode == 0) {
            $params['QueryRequest']['Account']['PayGateId']
                = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_LOGIN_ID'));
            $params['QueryRequest']['Account']['Password']
                = Tools::safeOutput(Configuration::get('PAYHOST_LIVE_KEY'));
        } else {
            $params['QueryRequest']['Account']['PayGateId']
                = Tools::safeOutput(Configuration::get('PAYHOST_TEST_LOGIN_ID'));
            $params['QueryRequest']['Account']['Password']
                = Tools::safeOutput(Configuration::get('PAYHOST_TEST_KEY'));
        }
        $params['QueryRequest']['PayRequestId'] = Tools::safeOutput($pay_request_id);
        $url = 'https://secure.paygate.co.za/payhost/process.trans?wsdl';
        $client = new SoapClient($url);
        $result = $client->SingleFollowUp($params);
        $result_code = $result->QueryResponse->Status->ResultCode;

        if ($nomatch == 1) {
            $result_code = 999999;
        }
        
        switch ($result_code) {
            case 900001:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('Call for approval.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900002:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card you used has expired.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900003:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card you used has insufficient funds available.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900004:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card number you used is invalid.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900005:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The connection to your bank has timed out.  Please try again.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900006:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card type you used is invalid or not enabled.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900007:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card you used has been declined.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900009:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card you used has been reported as lost.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900010:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card number you used has the wrong length or is otherwise invalid.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900011:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card you used has been flagged for fraudulent activities.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900012:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card you used has been reported as stolen.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900013:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card that you used is restricted.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900014:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card you used has been flagged for Excessive Card Usage.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900015:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The card you used has been blacklisted.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900017:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The amount requested and the amount paid do not match.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900019:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('Card vault out of scope.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900207:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The transaction has been declined.  3DSecure Authorization has failed.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 900208:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('Your bank has requested 3DSecure authentication,
                but the card you used has not been enrolled for 3DSecure Authentication.  Please contact your bank.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 990020:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('Authorization for this transaction has been declined.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 991001:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The expiry date you entered is incorrect.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 991002:
                $error_message = $result->QueryResponse->Status->TransactionStatusDescription;
                $friendly_message = $this->l('The amount for this transaction is invalid.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
            case 999999:
                $error_message = "The cart amount and payment amounts do not match.";
                $friendly_message = $this->l('The cart amount and payment amounts do not match.');
                $this->reportError($result_code, $error_message, $friendly_message);
                break;
        }
    }

    public function reportError($result_code, $friendly_message)
    {
        $checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ?
            'order-opc' : 'order';
        $url = _PS_VERSION_ >= '1.5' ?
            'index.php?controller=' . $checkout_type . '&' : $checkout_type . '.php?';
        $url .= 'step=3&cgv=1&payhosterror=1&friendly_message=' . $friendly_message . '$error_code=' . $result_code;

        if (!isset($_SERVER['HTTP_REFERER']) || strstr($_SERVER['HTTP_REFERER'], 'order')) {
            Tools::redirect($url);
        } elseif (strstr($_SERVER['HTTP_REFERER'], '?')) {
            Tools::redirect($_SERVER['HTTP_REFERER'].'&message='.$friendly_message.'&error_code='.$result_code, '');
        } else {
            Tools::redirect($_SERVER['HTTP_REFERER'].'?message='.$friendly_message.'&error_code='.$result_code, '');
        }
    }

    public function reportSuccess($transaction_id, $ref, $card_brand)
    {
        $data = array(
            'transaction_id' => pSQL($transaction_id),
            'card_number' => pSQL("XXXX-XXXX-XXXX-XPCI"),
            'card_brand' => pSQL($card_brand)
        );
        Db::getInstance()->update('order_payment', $data, 'order_reference = "'.$ref.'"');
    }

    public function isSecure()
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }
}
