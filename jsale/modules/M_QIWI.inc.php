<?php
# Модуль оплаты заказа с помощью сервиса QIWI

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_QIWI
{
	private static $instance; 	# Ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_QIWI();

		return self::$instance;
	}

	/**
	* Функция инициализирует оплату
	*
	* @return string $payment_link
	*/
	public function InitiatePayment($shop_id, $rest_id, $password, $phone, $sum, $currency, $payment_desc, $id_order, $successURL, $failURL)
	{
		$url = "https://w.qiwi.com/api/v2/prv/$shop_id/bills/$id_order";
		$sum = number_format($sum, 2, '.', '');
		$requestType = 'PUT';
		$loginPass = $rest_id . ':' . $password;

		$parameters = array(
			'user' => 'tel:' . $phone,
			'amount' => $sum,
			'ccy' => $currency,
			'comment' => $payment_desc,
			'pay_source' => 'qw',
			'lifetime' => date('c', time() + 3600 * 12),
			'prv_name' => 'QIWI',
		);
		
		$headers = array(
			"Accept: text/json",
			"Content-Type: application/x-www-form-urlencoded; charset=utf-8"
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $loginPass);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

		$httpResponse = curl_exec($ch);

		if (!$httpResponse) {
			echo curl_error($ch).'('.curl_errno($ch).')';
			return false;
		}
		$httpResponseAr = json_decode($httpResponse);
		
		if ($httpResponseAr->response->result_code == 0)
			return 'https://w.qiwi.com/order/external/main.action?shop='.$shop_id.'&transaction='.$id_order.'&successUrl='.$successURL.'&failUrl='.$failURL.'&qiwi_phone='.$phone;
	}
	
	/**
	* Функция проверяем состояние счёта
	*
	* @return boolean
	*/
	public function CheckPayment($shop_id, $rest_id, $password, $id_order)
	{
		$url = "https://w.qiwi.com/api/v2/prv/$shop_id/bills/$id_order";
		$requestType = 'GET';
		$loginPass = $rest_id . ':' . $password;

		$parameters = array();
		
		$headers = array(
			"Accept: text/json",
			"Content-Type: application/x-www-form-urlencoded; charset=utf-8"
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $loginPass);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

		$httpResponse = curl_exec($ch);
		if (!$httpResponse) {
			echo curl_error($ch).'('.curl_errno($ch).')';
			return false;
		}
		$httpResponseAr = json_decode($httpResponse);
		
		if ($httpResponseAr->response->result_code == 0 && $httpResponseAr->response->bill->status == 'paid')
			return $httpResponseAr->response->bill;
		else
			return false;
	}
	
	/**
	* Функция проверяем результат оплаты
	*
	* @return boolean
	*/
	public function CheckResult($password, $data, $headers)
	{
        if ($data['status'] != 'paid')
		{
			header('HTTP/1.1 200 OK');
			header('Content-Type: text/xml');
			echo '<?xml version="1.0"?><result><result_code>0</result_code></result>';
			die;
		}

        $data_str = $data['amount'].'|'.$data['bill_id'].'|'.$data['ccy'].'|'.$data['command'].'|'.$data['comment'].'|'.$data['error'].'|'.$data['prv_name'].'|'.$data['status'].'|'.$data['user'];

        if ($headers['X-API-SIGNATURE'] === base64_encode(hash_hmac('sha1', $data_str, $password, true)))
            return true;
		else
			return false;
	}
	
	public function ApacheRequestHeaders()
	{
	   $arh = array();
	   $rx_http = '/\AHTTP_/';
	   foreach($_SERVER as $key => $val) {
		 if( preg_match($rx_http, $key) ) {
		   $arh_key = preg_replace($rx_http, '', $key);
		   $rx_matches = array();
		   // do some nasty string manipulations to restore the original letter case
		   // this should work in most cases
		   $rx_matches = explode('_', $arh_key);
		   if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
			 foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
			 $arh_key = implode('-', $rx_matches);
		   }
		   $arh[$arh_key] = $val;
		 }
	   }
	   return( $arh );
	}
}