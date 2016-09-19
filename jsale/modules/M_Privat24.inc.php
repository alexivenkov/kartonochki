<?php
// Модуль оплаты заказа с помощью сервиса LiqPay
include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Privat24
{
	private static $instance; 	// Ссылка на экземпляр класса.

	// Получение единственного экземпляра класса.
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Privat24();

		return self::$instance;
	}

	/**
	* Функция инициализации оплаты
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($id_order, $id_merchant, $order_sum, $currencyCode, $description, $checkURL)
	{
		// Формирование формы.

        $payment_form = '
		<form action="https://api.privatbank.ua/p24api/ishop" method="post">

		<input type="hidden" name="amt" value="' . $order_sum . '"/>
		<input type="hidden" name="ccy" value="' . $currencyCode . '" />
		<input type="hidden" name="merchant" value="' . $id_merchant . '" />
		<input type="hidden" name="order" value="' . $id_order . '" />
		<input type="hidden" name="details" value="' . $description . '" />
		<input type="hidden" name="ext_details" value="" />
		<input type="hidden" name="pay_way" value="privat24" />
		<input type="hidden" name="server_url" value="'.$checkURL.'" />
		<input type="hidden" name="return_url" value="' . $checkURL . '" />
		
		<input type="submit" value="Оплатить" />
		</form>';

		return $payment_form;
	}

	public function CheckResult($post, $secret)
	{
		$signature = sha1(md5($post['payment'].$secret));
		
		if ($signature == $post['signature'])
			return $post['payment'];
		else
			return false;
	}

	public function SuccessPayment()
	{
		
	}

	public function FailurePayment()
	{
		
	}
}