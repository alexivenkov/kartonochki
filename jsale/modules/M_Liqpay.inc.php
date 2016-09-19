<?php
# Модуль оплаты заказа с помощью сервиса LiqPay

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Liqpay
{
	private static $instance; 	# ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Liqpay();

		return self::$instance;
	}

	/**
	* Функция инициализирует оплату
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($returnURL, $resultURL, $shop_id, $order_id, $sum, $currencyCode, $productDesc, $private_key, $test)
	{
		$signature = base64_encode( sha1( $private_key . $sum . $currencyCode . $shop_id . $order_id . 'buy' . $productDesc . $returnURL . $resultURL, 1 ));
	
		$payment_form = '
        <form method="POST" accept-charset="utf-8" action="https://www.liqpay.com/api/pay">
		<input type="hidden" name="public_key" value="'.$shop_id.'" />
		<input type="hidden" name="amount" value="'.$sum.'" />
		<input type="hidden" name="currency" value="'.$currencyCode.'" />
		<input type="hidden" name="description" value="'.$productDesc.'" />
		<input type="hidden" name="type" value="buy" />
		<input type="hidden" name="sandbox" value="'.$test.'" />
		<input type="hidden" name="pay_way" value="card,delayed" />
		<input type="hidden" name="server_url" value="'.$resultURL.'" />
		<input type="hidden" name="result_url" value="'.$returnURL.'" />
		<input type="hidden" name="order_id" value="'.$order_id.'" />
		<input type="hidden" name="signature" value="'.$signature.'" />
		<input type="hidden" name="language" value="ru" />
		<input type="submit" class="jSaleButton">
		</form>';

		return $payment_form;
	}

	/**
	* Функция проверки результата оплаты
	*
	* @return boolean
	*/
	public function CheckResult($private_key)
	{

		$path = dirname(__FILE__) . '/../files/liqpay.txt';
		$content = file_get_contents($path);

		foreach ($_POST as $key => $val)
			$content .= "$key => $val \n";

		file_put_contents($path, $content . "\n");
	
		$sign = base64_encode( sha1( $private_key . $_POST['amount'] . $_POST['currency'] . $_POST['public_key'] . $_POST['order_id'] . $_POST['type'] . $_POST['description'] . $_POST['status'] . $_POST['transaction_id'] . $_POST['sender_phone'], 1 ));

		if ($sign == $_POST['signature'] && $_POST['status'] == 'success' || $sign == $_POST['signature'] && $_POST['status'] == 'sandbox')
			return true;
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