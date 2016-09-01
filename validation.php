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


$payhost = new Payhost();
$pay_request_id = Tools::getValue('PAY_REQUEST_ID');
$transaction_status = Tools::getValue('TRANSACTION_STATUS');
$result_code = Tools::getValue('RESULT_CODE');

if ($transaction_status == 1) {
    $payhost->checkGatewayReturn($pay_request_id);

} elseif ($transaction_status == 0 || $transaction_status == 2) {
    $payhost->processDeclinedPayment($pay_request_id);
}
