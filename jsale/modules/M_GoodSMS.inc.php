<?php
class SmsServiceApi {
	private $api_uid = null;
	private $api_key = null;
	
	/**
	 * Адрес API
	 * @var unknown_type
	 */
	const API_URL = 'http://api.cabinet.goodsms.ru/';
	const API_UA = 'SmsServiceApi/1.0';
	
	function __construct($api_uid, $api_key) {
		$this->api_uid = $api_uid;
		$this->api_key = $api_key;
	}
	public function send ($api_method, $api_params=null) {
		if (!$api_params) {
			$api_params = array();
		}
		# формируем запрос
		$api_params['uid'] = $this->api_uid;
		$api_params['sig'] = $this->sig($api_params, $api_method);
		
		$result = self::post(self::API_URL.$api_method, $api_params);
		
		if ($result !== false) {
			return json_decode($result, true);
		}
		
		return false;
	}
	/**
	 * POST запрос
	 * 
	 * @param unknown_type $url
	 * @param unknown_type $vars
	 * @param unknown_type $timeout
	 */
	private static function post($url, $vars) {	
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($vars, '', '&'));
		curl_setopt($ch, CURLOPT_USERAGENT, self::API_UA);
		
		$result = curl_exec($ch);
		$error = curl_errno($ch);
		curl_close($ch);
		
		if ($error != 0 || empty($result)) {	
			return false;
		}
		
		return $result;
	}
	/**
	 * Подпись параметров для передачи в АПИ
	 * 
	 * @param array $api_params
	 * @param string $api_method
	 */
	public function sig ($api_params, $api_method) {
	  $params = $this->getSigParamsString($api_params);
	  return md5($this->api_uid . $api_method . $params . $this->api_key);
	}
	/**
	 * Сбор параметров в строку
	 * 
	 * @param array $api_params
	 * @param mixed $parent_key - родительский ключ для рекурсии
	 */
	private function getSigParamsString($api_params, $parent_key=null) {
		$params = '';
		ksort($api_params, SORT_STRING);
		
		foreach ($api_params as $key => $value) {
	  		if ($parent_key !== null || ($key != 'uid' && $key != 'method' && $key != 'sig')) {
	  			if (is_array($value)) {
	  				if ($parent_key) {
	  					$key = $parent_key."[$key]";
	  				}
	  				$params .= $this->getSigParamsString($value, $key);
	  			} else {
	  				if ($parent_key) {
	  					$params .= $parent_key."[$key]=$value";
	  				} else {
	    				$params .= "$key=$value";
	  				}
	  			}
			}
		}
		
		return $params;
	}
}