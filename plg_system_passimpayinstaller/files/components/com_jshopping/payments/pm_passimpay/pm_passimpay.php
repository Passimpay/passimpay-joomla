<?php
/**
* @version      1.00 2023
* @author       passimpay
* @package      Jshopping
* @copyright    Copyright (C) 2010 passimpay.io. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class pm_passimpay extends PaymentRoot{

    private $curlopt_sslversion = 6;
    
    function showPaymentForm($params, $pmconfigs){
        include(dirname(__FILE__)."/paymentform.php");
    }
	
	function showAdminFormParams($params){
	  $array_params = array('api_key', 'platform_id', 'payment_type', 'transaction_end_status', 'transaction_pending_status', 'transaction_failed_status');
	  foreach ($array_params as $key){
	  	if (!isset($params[$key])) $params[$key] = ($key === 'payment_type') ? 0 : '';
	  }
	  
	  $orders = JSFactory::getModel('orders', 'JshoppingModel'); //admin model
      include(dirname(__FILE__)."/adminparamsform.php");
	}

	function checkTransaction($pmconfigs, $order, $act){
        $jshopConfig = JSFactory::getConfig();
        
        $url = 'https://api.passimpay.io/v2/orderstatus';
		$platform_id = (int) $pmconfigs['platform_id'];
		$apikey = $pmconfigs['api_key'];
		$order_id = (string) $order->order_id;

		$body = ['platformId' => $platform_id, 'orderId' => $order_id];
		$json_body = json_encode($body, JSON_UNESCAPED_SLASHES);
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
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
		$result = curl_exec($curl);
		curl_close( $curl );

		$result = json_decode($result, true);
		$transaction = 0;
		$transactiondata = [];

		if (isset($result['result']) && (int) $result['result'] === 1)
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
			return array(2, "Status pending. Order ID ".$order->order_id, $transaction, $transactiondata);
		}
		$msg = isset($result['message']) ? $result['message'] : '';
		saveToLog("payment.log", "Invalid response. Order ID ".$order->order_id.". " . $msg);
		return array(0, 'Invalid response. Order ID '.$order->order_id, $transaction, $transactiondata);
	}

	function showEndForm($pmconfigs, $order){
        $jshopConfig = JSFactory::getConfig();
        $pm_method = $this->getPmMethod();
		
		$url = 'https://api.passimpay.io/v2/createorder';
		$platform_id = (int) $pmconfigs['platform_id'];
		$apikey = $pmconfigs['api_key'];
		$order_id = (string) $order->order_id;
		$amount = $this->fixOrderTotal($order);
		$payment_type = isset($pmconfigs['payment_type']) ? (int) $pmconfigs['payment_type'] : 0; // 0=both, 1=crypto, 2=card
		$currency = isset($order->currency_code_iso) ? strtoupper($order->currency_code_iso) : 'USD';

		$body = [
			'platformId' => $platform_id,
			'orderId' => $order_id,
			'amount' => $amount,
			'symbol' => $currency,
			'type' => $payment_type,
		];
		$json_body = json_encode($body, JSON_UNESCAPED_SLASHES);
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
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
		$result = curl_exec($curl);
		curl_close( $curl );

		$result = json_decode($result, true);

		if (isset($result['result']) && (int) $result['result'] === 1 && !empty($result['url']))
		{
			header('Location: ' . $result['url']);
			exit();
		}
		die('Error create order');
	}
    
    function getUrlParams($pmconfigs){
        $params = array(); 
        $params['order_id'] = JFactory::getApplication()->input->getInt("order_id");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 0;
    return $params;
    }
    
	function fixOrderTotal($order){
        $total = $order->order_total;
        if ($order->currency_code_iso=='HUF'){
            $total = round($total);
        }else{
            $total = number_format($total, 2, '.', '');
        }
    return $total;
    }
}
