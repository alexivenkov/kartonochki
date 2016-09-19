<?php
# Модуль оплаты заказа с помощью сервиса PayPal

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_PayPal
{
	private static $instance; 	// Ссылка на экземпляр класса.

	// Получение единственного экземпляра класса.
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_PayPal();

		return self::$instance;
	}

	/**
	* Функция инициализации оплаты
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($checkURL, $successURL, $cancelURL, $email, $description, $id_product, $price, $us_price = null, $currencyCode, $test, $id_order, $customer_info = null)
	{
		// Формирование формы.
		if ($test == true)
			$form_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		else
			$form_url = 'https://www.paypal.com/cgi-bin/webscr';

        $payment_form = '
		<form action="' . $form_url . '" method="post" accept-charset="UTF-8">

		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="' . $email . '">
		<input type="hidden" name="item_name" value="' . $description . '">
		<input type="hidden" name="item_number" value="' . $id_product . '">
		<input type="hidden" name="amount" value="' . $price . '">
		<input type="hidden" name="no_shipping" value="1">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="' . $currencyCode . '">
		<input type="hidden" name="lc" value="RU">
		<input type="hidden" name="bn" value="PP-BuyNowBF">
		<input type="hidden" name="return" value="' . $successURL . '"> 
		<input type="hidden" name="cancel_return" value="' . $cancelURL . '">
		<input type="hidden" name="rm" value="2">
		<input type="hidden" name="charset" value="utf-8">
		<input type="hidden" name="notify_url" value="'.$checkURL.'" /> 
		<input type="hidden" name="invoice" value="'.$id_order.'"/>
		<input type="hidden" name="custom" value="'.$us_price.'"/>';

		if (is_array($customer_info))
			foreach ($customer_info as $name => $value)
				$payment_form .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
		
		$payment_form .= '
		<input type="submit" value="Купить сейчас" class="jSaleButton">
		</form>';

		return $payment_form;
	}

	/**
	* Функция проверки результата оплаты
	*
	* @return boolean
	*/

	public function CheckResult($test)
	{
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) 
		{
		    $value = urlencode(stripslashes($value));
		    $req .= "&$key=$value";
		}

		$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n";

		if ($test == true)
		{
			$header .= "Host: www.sandbox.paypal.com\r\n";
			$header .= "Connection: close\r\n";
			$header .= "User-Agent: JSale\r\n\r\n";
			$fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
		}
		else
		{
			$header .= "Host: www.paypal.com\r\n";
			$header .= "Connection: close\r\n";
			$header .= "User-Agent: JSale\r\n\r\n";
			$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);
		}

		if (!$fp) 
		{
		    // HTTP ERROR
		    return false;
		} 
		else 
		{
		    fputs ($fp, $header . $req);
		    while (!feof($fp))
		        $res = fgets ($fp, 1024);
			fclose($fp);

		    if (strcmp (trim($res), 'VERIFIED') == 0)
				return true;
			else
				return false;
		}
	}
	
	public function SuccessPayment()
	{
		
	}

	public function FailurePayment()
	{
		
	}
}