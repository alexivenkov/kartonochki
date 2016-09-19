<?php
# Модуль оплаты заказа с помощью сервиса InterKassa

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_RBKmoney
{
	private static $instance; 	# Ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_RBKmoney();

		return self::$instance;
	}

	/**
	* Функция инициализирует оплату
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($shop_id, $sum, $currency, $order_id, $user_email, $description, $succesURL, $failURL, $secret_key)
	{
		# Генеарация проверочной строки
		$hash = $shop_id . '::' . 
				$sum . '::' . 
				$currency . '::' . 
				$user_email . '::' . 
				$description . '::' . 
				$order_id . '::::' . 
				$secret_key;
	
		# Формирование формы.
        $payment_form = '
        <form action="https://rbkmoney.ru/acceptpurchase.aspx" method="post" name="pay" target="_blank">
            <input type="hidden" name="eshopId" value="' . $shop_id . '">
            <input type="hidden" name="recipientAmount" value="' . $sum . ' ">
            <input type="hidden" name="recipientCurrency" value="' . $currency . '">
			
			<input type="hidden" name="orderId" value="' . $order_id . '">
			<input type="hidden" name="user_email" value="' . $user_email . '">
			<input type="hidden" name="serviceName" value="' . $description . '">
			
			<input type="hidden" name="language" value="ru">
			<input type="hidden" name="successUrl" value="' . $succesURL . '">
			<input type="hidden" name="failUrl" value="' . $failURL . '">

			<input type="hidden" name="hash" value="' . md5($hash) . '">
            <input type="submit" name="button" value="Оплатить" class="jSaleOrder">
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
        if ($post['eshopId'] != $shop_id)
            return false;

        $hash = $post['eshopId'].'::'.
				$post['orderId'].'::'.
				$post['serviceName'].'::'.
				$post['eshopAccount'].'::'.
				$post['recipientAmount'].'::'.
				$post['recipientCurrency'].'::'.
				$post['paymentStatus'].'::'.
				$post['userName'].'::'.
				$post['userEmail'].'::'.
				$post['paymentData'].
				$secret_key;

        if ($post['hash'] === strtolower(md5($hash)) && $post['paymentStatus'] == '3')
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