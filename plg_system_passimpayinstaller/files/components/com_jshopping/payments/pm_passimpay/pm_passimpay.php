<?php
/**
* @version      1.00 2023
* @author       passimpay
* @package      Jshopping
* @copyright    Copyright (C) 2010 passimpay.io. All rights reserved.
* @license      GNU/GPL
*/
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Helper\Helper;

defined('_JEXEC') or die();

class pm_passimpay extends PaymentRoot{

    private $curlopt_sslversion = 6;
    private $webhookOrderId;
    
    function showPaymentForm($params, $pmconfigs){
        include(dirname(__FILE__)."/paymentform.php");
    }
	
	function showAdminFormParams($params){
	  $array_params = array('api_key', 'platform_id', 'payment_type', 'transaction_end_status', 'transaction_pending_status', 'transaction_failed_status');
	  foreach ($array_params as $key){
		if (!isset($params[$key])) $params[$key] = ($key === 'payment_type') ? 0 : '';
	  }
	  
	  $orders = JSFactory::getModel('orders'); //admin model
      include(dirname(__FILE__)."/adminparamsform.php");
	}

	function checkTransaction($pmconfigs, $order, $act){
        if ($act !== 'notify' || $this->webhookOrderId !== (string)$order->order_id) {
            $this->reply(400, 'Invalid notification');
        }
        $paidStatus = (int)($pmconfigs['transaction_end_status'] ?? 0);
        if ($paidStatus <= 0) {
            $this->reply(503, 'Payment status not configured');
        }
        // Повтор не меняет уже оплаченный текущий статус; ручные переходы задаёт CMS.
        if ((int)$order->order_status === $paidStatus) {
            $this->reply(200, 'OK');
        }
        $url = 'https://api.passimpay.io/v2/orderstatus';
		$platform_id = (int) $pmconfigs['platform_id'];
		$apikey = $pmconfigs['api_key'];
		$order_id = (string) $order->order_id;

		$body = ['platformId' => $platform_id, 'orderId' => $order_id];
		$json_body = json_encode($body);
		$signature_string = $platform_id . ';' . $json_body . ';' . $apikey;
		$signature = hash_hmac('sha256', $signature_string, $apikey);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'x-signature: ' . $signature,
		]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_body);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close( $curl );

		$result = is_string($result) ? json_decode($result, true) : null;
		$transaction = 0;
		$transactiondata = [];

		if ($httpCode === 200 && is_array($result) && isset($result['result']) && $result['result'] === 1)
		{
			$status = isset($result['status']) ? $result['status'] : '';
			if ($status === 'paid')
			{
				return array(1, '', $transaction, $transactiondata);
			}
			if ($status === 'error')
			{
				return array(0, 'Invalid response. Order ID '.$order->order_id, $transaction, $transactiondata);
			}
            if ($status === 'wait') {
                return array(2, "Status pending. Order ID ".$order->order_id, $transaction, $transactiondata);
            }
		}
        Helper::saveToLog('payment.log', 'Passimpay: status check failed for order ' . (int)$order->order_id);
        $this->reply(503, 'Status check failed');
    }

	function showEndForm($pmconfigs, $order){
		
		$url = 'https://api.passimpay.io/v2/createorder';
		$platform_id = (int) $pmconfigs['platform_id'];
		$apikey = $pmconfigs['api_key'];
		$order_id = (string) $order->order_id;
		$amount = $this->fixOrderTotal($order);
		$payment_type = isset($pmconfigs['payment_type']) ? (int) $pmconfigs['payment_type'] : 0;
		$currency = isset($order->currency_code_iso) ? strtoupper($order->currency_code_iso) : 'USD';

        if (filter_var($pmconfigs['platform_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false || !is_string($apikey) || $apikey === '' || !in_array($payment_type, [0, 1, 2], true)
            || (isset($pmconfigs['payment_type']) && (!is_scalar($pmconfigs['payment_type']) || !in_array((string)$pmconfigs['payment_type'], ['0', '1', '2'], true)))
            || !preg_match('/^[A-Z]{3}$/D', $currency) || !preg_match('/^[0-9]+\.[0-9]{2}$/D', $amount)) {
            $this->reply(503, 'Invalid payment configuration');
        }
		$body = [
			'platformId' => $platform_id,
			'orderId' => $order_id,
			'amount' => $amount,
			'symbol' => $currency,
			'type' => $payment_type,
		];
		$json_body = json_encode($body);
		$signature_string = $platform_id . ';' . $json_body . ';' . $apikey;
		$signature = hash_hmac('sha256', $signature_string, $apikey);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'x-signature: ' . $signature,
		]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_body);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close( $curl );

		$result = is_string($result) ? json_decode($result, true) : null;

        $parts = is_array($result) && isset($result['url']) && is_string($result['url']) ? parse_url($result['url']) : false;
        if ($httpCode === 200 && is_array($result) && isset($result['result']) && $result['result'] === 1
            && $parts && filter_var($result['url'], FILTER_VALIDATE_URL) && $parts['scheme'] === 'https'
            && !isset($parts['user']) && !isset($parts['pass'])) {
            Factory::getApplication()->redirect($result['url']);
            Factory::getApplication()->close();
        }
        $this->reply(503, 'Unable to create payment');
    }

    function getUrlParams($pmconfigs){
        $input = Factory::getApplication()->input;
        $params = ['order_id' => $input->getInt('order_id'), 'hash' => '', 'checkHash' => 0, 'checkReturnParams' => 0];
        if ($input->getCmd('act') === 'return') {
            return $params;
        }
        if ($input->getCmd('act') !== 'notify' || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->reply(405, 'Method not allowed');
        }
        $rawBody = file_get_contents('php://input', false, null, 0, 65537);
        if ($rawBody === false || strlen($rawBody) > 65536) {
            $this->reply(413, 'Invalid body');
        }
        $data = json_decode($rawBody, true);
        $platformId = (int)($pmconfigs['platform_id'] ?? 0);
        $secret = $pmconfigs['api_key'] ?? '';
        if (filter_var($pmconfigs['platform_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false || !is_string($secret) || $secret === '') {
            $this->reply(503, 'Payment not configured');
        }
        if (!$this->validateWebhook($data, $platformId)) {
            $this->reply(400, 'Invalid payload');
        }
        $signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
        $payload = json_encode($data);
        if (!is_string($signature) || !preg_match('/^[a-f0-9]{64}$/D', $signature) || $payload === false
            || !hash_equals(hash_hmac('sha256', $platformId . ';' . $payload . ';' . $secret, $secret), $signature)) {
            $this->reply(403, 'Invalid signature');
        }
        // Удерживаем блокировку до завершения штатного обновления заказа JoomShopping.
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $lock = 'pp_js_' . substr(hash('sha256', Factory::getApplication()->get('db') . ';' . $db->getPrefix() . ';' . $data['orderId']), 0, 50);
        if ((int)$db->setQuery("SELECT GET_LOCK('" . $lock . "', 0)")->loadResult() !== 1) {
            $this->reply(503, 'Order is busy');
        }
        register_shutdown_function(function () use ($db, $lock) {
            $db->setQuery("SELECT RELEASE_LOCK('" . $lock . "')")->loadResult();
        });
        $this->webhookOrderId = $data['orderId'];
        $params['order_id'] = (int)$data['orderId'];
        return $params;
    }

    function nofityFinish($pmconfigs, $order, $rescode){
        $this->reply(200, 'OK');
    }

    private function reply($code, $text){
        http_response_code($code);
        echo $text;
        Factory::getApplication()->close();
    }

	function fixOrderTotal($order){
        $total = $order->order_total;
        if ($order->currency_code_iso=='HUF'){
            $total = round($total);
        }
    return number_format($total, 2, '.', '');
    }
    private function validateWebhook($params, $platformId)
    {
        if (!is_array($params) || !isset($params['type'], $params['platformId'], $params['orderId'])
            || $params['type'] !== 'deposit' || !is_int($params['platformId']) || $params['platformId'] <= 0
            || $params['platformId'] !== $platformId || !is_string($params['orderId'])
            || strlen($params['orderId']) > 64 || !preg_match('/^[1-9][0-9]*$/D', $params['orderId'])
            || filter_var($params['orderId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            return false;
        }
        foreach (['paymentId', 'confirmations', 'destinationTag'] as $field) {
            if (array_key_exists($field, $params) && (!is_int($params[$field]) || $params[$field] < ($field === 'paymentId' ? 1 : 0))) {
                return false;
            }
        }
        foreach (['amount', 'amountReceive', 'feeService', 'feeNetwork'] as $field) {
            if (array_key_exists($field, $params) && (!is_string($params[$field]) || strlen($params[$field]) > 128
                || !preg_match('/^[0-9]+(?:\.[0-9]+)?$/D', $params[$field]))) {
                return false;
            }
        }
        foreach (['txhash', 'addressFrom', 'addressTo'] as $field) {
            if (array_key_exists($field, $params) && (!is_string($params[$field]) || strlen($params[$field]) > 512)) {
                return false;
            }
        }
        return true;
    }
}
