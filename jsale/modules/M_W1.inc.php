<?php
# Модуль оплаты заказа с помощью сервиса InterKassa

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_W1
{
	private static $instance; 	# Ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_W1();

		return self::$instance;
	}

	/**
	* Функция инициализирует оплату
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($shop_id, $sum, $currency, $order_id, $user_email, $description, $successURL, $failURL, $secret_key, $encoding)
	{
		# Подставляем валюты
		if ($currency == 'RUB')
			$currency_code = '643';
		elseif ($currency == 'USD')
			$currency_code = '840';
		elseif ($currency == 'EUR')
			$currency_code = '978';
		elseif ($currency == 'UAH')
			$currency_code = '980';
		elseif ($currency == 'ZAR')
			$currency_code = '710';
		elseif ($currency == 'KZT')
			$currency_code = '398';
		elseif ($currency == 'BYB')
			$currency_code = '974';
	
		# Массив данных
		$params = array();
		$params['WMI_MERCHANT_ID'] = $shop_id;
		$params['WMI_PAYMENT_AMOUNT'] = $sum;
		$params['WMI_CURRENCY_ID'] = $currency_code;
		$params['WMI_PAYMENT_NO'] = $order_id;
		$params['WMI_DESCRIPTION'] = base64_encode($description);
		$params['WMI_SUCCESS_URL'] = $successURL;
		$params['WMI_FAIL_URL'] = $failURL;
		$params['WMI_RECIPIENT_LOGIN'] = $user_email;
		
		# Сортировка массива
		uksort($params, 'strcasecmp');
		
		# Формирование строки данных
		$form_params = '';
		foreach($params as $param)
		{
			if ($encoding == 'utf-8')
				$param = iconv('utf-8', 'windows-1251', $param);
			$form_params .= $param;
		}
		
		# Формирование подписи		
		$sign = base64_encode(pack("H*", md5($form_params . $secret_key)));
	
		# Формирование формы.
        $payment_form = '
		<form method="post" action="https://www.walletone.com/checkout/default.aspx" accept-charset="UTF-8">
			<input type="hidden" name="WMI_MERCHANT_ID"     value="'.$shop_id.'"/>
			<input type="hidden" name="WMI_PAYMENT_AMOUNT"  value="'.$sum.'"/>
			<input type="hidden" name="WMI_CURRENCY_ID"     value="'.$currency_code.'"/>
			<input type="hidden" name="WMI_PAYMENT_NO"      value="'.$order_id.'"/>
			<input type="hidden" name="WMI_DESCRIPTION"     value="'.base64_encode($description).'"/>
			<input type="hidden" name="WMI_SUCCESS_URL"     value="'.$successURL.'"/>
			<input type="hidden" name="WMI_FAIL_URL"        value="'.$failURL.'"/>
			<input type="hidden" name="WMI_RECIPIENT_LOGIN" value="'.$user_email.'"/>
			<input type="hidden" name="WMI_SIGNATURE"       value="'.$sign.'"/>
			<input type="submit" class="jSaleButton" />
		</form>';

		return $payment_form;
	}

	/**
	* Функция проверяем результат оплаты
	*
	* @return boolean
	*/
	public function CheckResult($post, $shop_id, $secret_key)
	{
		# Проверка наличия необходимых параметров в POST-запросе
		if (!isset($post['WMI_SIGNATURE']))
			$this->PrintAnswer('Retry', 'Отсутствует параметр WMI_SIGNATURE');
		
		if (!isset($post['WMI_PAYMENT_NO']))
			$this->PrintAnswer('Retry', 'Отсутствует параметр WMI_PAYMENT_NO');
		
		if (!isset($post['WMI_ORDER_STATE']))
			$this->PrintAnswer('Retry', 'Отсутствует параметр WMI_ORDER_STATE');
		
		# Извлечение всех параметров POST-запроса, кроме WMI_SIGNATURE
		$params = array();
		foreach($post as $name => $value)
			if ($name !== 'WMI_SIGNATURE') $params[$name] = $value;
			
		# Сортировка массива по именам ключей в порядке возрастания и формирование сообщения, путем объединения значений формы
		uksort($params, 'strcasecmp'); $values = '';
		 
		foreach($params as $name => $value)
		{
			# Конвертация из текущей кодировки (UTF-8) - необходима только если кодировка магазина отлична от Windows-1251
			if ($encoding == 'utf-8')
				$value = iconv('utf-8', 'windows-1251', $value);
			$values .= $value;
		}

		# Формирование подписи для сравнения ее с параметром WMI_SIGNATURE
		$signature = base64_encode(pack("H*", md5($values . $secret_key)));
		 
		#Сравнение полученной подписи с подписью W1
		if ($signature == $post['WMI_SIGNATURE'])
		{
			if (strtoupper($post['WMI_ORDER_STATE']) == 'ACCEPTED')
				# Пометить заказ, как «Оплаченный» в системе учета магазина			 
				return true;
			else
				# Случилось что-то странное, пришло неизвестное состояние заказа
				PrintAnswer('Retry', 'Неверное состояние '. $post['WMI_ORDER_STATE']);
		}
		else
			# Подпись не совпадает, возможно вы поменяли настройки интернет-магазина
			PrintAnswer('Retry', 'Неверная подпись ' . $post['WMI_SIGNATURE']);
	}
	
	public function PrintAnswer($result, $description)
	{
		print "WMI_RESULT=" . strtoupper($result) . "&";
		print "WMI_DESCRIPTION=" .urlencode($description);
		die();
	}

	public function SuccessPayment()
	{
		
	}

	public function FailurePayment()
	{
		
	}
}