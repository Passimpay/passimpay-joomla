<?php
/**
* @version      1.00 2023
* @author       passimpay
* @package      Jshopping
* @copyright    Copyright (C) 2010 passimpay.io. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

class pm_passimpay extends PaymentRoot
{

    private $curlopt_sslversion = 6;
    
    public function showPaymentForm($params, $pmconfigs)
	{
        include_once(dirname(__FILE__)."/paymentform.php");
    }
	
	public function showAdminFormParams($params)
	{
	  $array_params = array('api_key', 'platform_id', 'transaction_end_status', 'transaction_pending_status', 'transaction_failed_status');
	  foreach ($array_params as $key){
	  	if (!isset($params[$key])) $params[$key] = '';
	  }
	  
	  $orders = JSFactory::getModel('orders', 'JshoppingModel'); //admin model
      include_once(dirname(__FILE__)."/adminparamsform.php");
	}

	public function checkTransaction($pmconfigs, $order, $act)
	{
        $jshopConfig = JSFactory::getConfig();
        
        $url = 'https://api.passimpay.io/orderstatus';
		$platform_id = $pmconfigs['platform_id']; // Platform ID
		$apikey = $pmconfigs['api_key']; // Secret key
		$order_id = $order->order_id; // Payment ID of your platform

		$payload = http_build_query(['platform_id' => $platform_id, 'order_id' => $order_id]);
		$hash = hash_hmac('sha256', $payload, $apikey);

		$data = [
			'platform_id' => $platform_id,
			'order_id' => $order_id,
			'hash' => $hash,
		];

		$post_data = http_build_query($data);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		$result = curl_exec($curl);
		curl_close( $curl );

		$result = json_decode($result, true);

		$transaction = 0;
		$transactiondata = [];
		// Response options
		// In case of success
		if (isset($result['result']) && $result['result'] == 1)
		{
			if ($result['status'] == 'paid')
			{
				return array(1, '', $transaction, $transactiondata);
			}
			elseif ($result['status'] == 'error')
			{
				return array(0, 'Invalid response. Order ID '.$order->order_id, $transaction, $transactiondata);
			}
			else
			{
				return array(2, "Status pending. Order ID ".$order->order_id, $transaction, $transactiondata);
			}
		}
		// In case of an error
		else
		{
			saveToLog("payment.log", "Invalid response. Order ID ".$order->order_id.". " . $result['message']);
			return array(0, 'Invalid response. Order ID '.$order->order_id, $transaction, $transactiondata);
		}
        
	}

	public function showEndForm($pmconfigs, $order)
	{
        $jshopConfig = JSFactory::getConfig();
        $pm_method = $this->getPmMethod();
		
		$url = 'https://api.passimpay.io/createorder';
		$platform_id = $pmconfigs['platform_id']; // Platform ID
		$apikey = $pmconfigs['api_key']; // Secret key
		$order_id = $order->order_id; // Payment ID of your platform
		$amount = $this->fixOrderTotal($order);

		$payload = http_build_query(['platform_id' => $platform_id, 'order_id' => $order_id, 'amount' => $amount]);
		$hash = hash_hmac('sha256', $payload, $apikey);

		$data = [
			'platform_id' => $platform_id,
			'order_id' => $order_id,
			'amount' => $amount,
			'hash' => $hash,
		];

		$post_data = http_build_query($data);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		$result = curl_exec($curl);
		curl_close( $curl );

		$result = json_decode($result, true);

		// Response options
		// In case of success
		if (isset($result['result']) && $result['result'] == 1)
		{
			header('Location: ' . $result['url']);
			exit();
		}
		// In case of an error
		else
		{
			die('Error create order');
		}
		
		die();
	}
    
    public function getUrlParams($pmconfigs)
	{
        $params = array(); 
        $params['order_id'] = JFactory::getApplication()->input->getInt("order_id");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 0;
    return $params;
    }
    
	public function fixOrderTotal($order)
	{
        $total = $order->order_total;
        if ($order->currency_code_iso=='HUF'){
            $total = round($total);
        }else{
            $total = number_format($total, 2, '.', '');
        }
    return $total;
    }
}
