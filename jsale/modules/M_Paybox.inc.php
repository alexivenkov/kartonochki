<?php
# Модуль оплаты заказа с помощью сервиса Paybox
include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Paybox
{
	private static $instance; 	// Ссылка на экземпляр класса.

	// Получение единственного экземпляра класса.
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Paybox();

		return self::$instance;
	}

	/**
	* Функция инициализации оплаты
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($orderID, $merchantID, $secretKey, $sum, $currencyCode, $description, $email, $phone, $successURL, $failURL, $resultURL, $test)
	{
		# Данные запроса
		$string_data = $array_data = array(
			'pg_merchant_id' => $merchantID,
            'pg_order_id' => $orderID,
            'pg_amount' => round($sum, 2),
			'pg_currency' => $currencyCode,
            'pg_description' => $description,
            'pg_user_contact_email' => $email,
            'pg_user_phone' => trim($phone, '+'),
            'pg_language' => 'ru',
            'pg_salt' => $this->makeSalt(),
            'pg_result_url' => $resultURL,
            'pg_request_method' => 'POST',
            'pg_success_url' => $successURL,
            'pg_success_url_method' => 'AUTOPOST',
            'pg_failure_url' => $failURL,
            'pg_failure_url_method' => 'AUTOPOST',
            'pg_testing_mode' => $test
		);
		
		# Генерация подписи
		ksort($array_data);
		array_unshift($array_data, 'payment.php');
		array_push($array_data, $secretKey);
		$array_data = join(';', $array_data);
		$pg_sig = md5($array_data);
	
		# Форма
		$form = '<form action="https://paybox.kz/payment.php" method="post">';
		foreach ($string_data as $var => $val)
			$form .= '<input type="hidden" name="'.$var.'" value="'.$val.'">';
		$form .= '<input type="hidden" name="pg_sig" value="'.$pg_sig.'">
		<input type="submit" value="Оплатить сейчас" class="jSaleButton">
		</form>';

		return $form;
	}

	/**
	* Функция проверки результата оплаты
	*
	* @return boolean
	*/

	public function CheckResult($post, $secretKey)
	{
		# Проверка статуса
		if ($post['pg_result'] != 1)
			return false;
		
		# Сохранение подписи в переменную
		$pg_sig = $post['pg_sig'];
		unset($post['pg_sig']);

		# Генерация подписи
		ksort($post);
		array_unshift($post, 'C_Payment.php');
		array_push($post, $secretKey);
		$string_post = join(';', $post);		
		$signature = md5($string_post);
		
		# Проверка подписи
		if ($signature == $pg_sig)
			return true;
	}

	public function SuccessPayment()
	{
		
	}

	public function FailurePayment()
	{
		
	}
	
	public function SuccessResponse($secretKey)
	{
		# Данные ответа
		$string_data = $array_data = array(
			'pg_status' => 'ok',
            'pg_salt' => $this->makeSalt()
		);
		
		# Генерация подписи
		ksort($array_data);
		array_unshift($array_data, 'C_Payment.php');
		array_push($array_data, $secretKey);
		$array_data = join(';', $array_data);
		$string_data['pg_sig'] = md5($array_data);
		
		# Вывод ответа
		$xml = '<?xml version="1.0" encoding="utf-8"?>
		<response>';
		foreach ($string_data as $var => $val)
			$xml .= '<'.$var.'>'.$val.'</'.$var.'>';
		$xml .= '</response>';
		
		
		return $xml;
	}
	
	private function makeSalt($length=50)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
            $randomString .= $characters[rand(0, $charactersLength - 1)];

        return $randomString;
    }
}