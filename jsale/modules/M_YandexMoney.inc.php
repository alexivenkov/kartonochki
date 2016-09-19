<?php
# Модуль оплаты заказа с помощью сервиса Yandex.Money

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_YandexMoney
{
	private static $instance; 	# Ссылка на экземпляр класса
	private $db; 			    # Основная модель работы с БД

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_YandexMoney();

		return self::$instance;
	}
	
	# Конструктор
	public function __construct()
	{
        $this->db = M_DB::Instance();
	}

	/**
	* Функция инициализирует оплату
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($purse, $sum, $payment_id, $payment_desc, $sitename, $successURL)
	{
		$payment_form = '<form action="https://money.yandex.ru/quickpay/confirm.xml" method="post">

		<input type="hidden" value="'.$purse.'" name="receiver">
		<input type="hidden" value="'.$payment_id.'" name="label">
		<input type="hidden" value="'.$payment_desc.'" name="FormComment">
		<input type="hidden" value="Оплата заказа №'.$payment_id.'" name="short-dest">
		<input type="hidden" value="false" name="writable-targets">
		<input type="hidden" value="false" name="writable-sum">
		<input type="hidden" value="false" name="comment-needed">
		<input type="hidden" value="shop" name="quickpay-form">
		<input type="hidden" value="'.$payment_desc.'" name="targets">
		<input type="hidden" value="'.$sum.'" name="sum">
		<input type="hidden" value="'.$successURL.'" name="successURL">
		<input type="submit" value="Купить сейчас" name="submit-button" class="jSaleButton">

		</form>';

		return $payment_form;
	}
	
	public function InitiatePaymentCards($purse, $sum, $payment_id, $payment_desc, $sitename, $successURL)
	{
		$payment_form = '<form action="https://money.yandex.ru/quickpay/confirm.xml" method="post">

		<input type="hidden" value="'.$purse.'" name="receiver">
		<input type="hidden" value="'. $payment_id .'" name="label">
		<input type="hidden" value="'.$sitename.'" name="FormComment">
		<input type="hidden" value="Оплата заказа №'.$payment_id.'" name="short-dest">
		<input type="hidden" value="false" name="writable-targets">
		<input type="hidden" value="false" name="writable-sum">
		<input type="hidden" value="false" name="comment-needed">
		<input type="hidden" value="shop" name="quickpay-form">
		<input type="hidden" value="'.$payment_desc.'" name="targets">
		<input type="hidden" value="'.$sum.'" name="sum">
		<input type="hidden" name="paymentType" value="AC">
		<input type="hidden" value="'.$successURL.'" name="successURL">
		<input type="submit" value="Купить сейчас" name="submit-button" class="jSaleButton">

		</form>';

		return $payment_form;
	}

	/**
	* Функция проверяем результат оплаты
	*
	* @return boolean
	*/
	public function CheckResult($shop_id, $secret_key, $token, $payment_desc, $post)
	{
		require_once dirname(__FILE__) . '/ym/lib/YandexMoney.php';
		
		if	($post['codepro'] != 'false')
			return false;

        $str  = $post['notification_type'] . '&' .
				$post['operation_id'] . '&' .
				$post['amount'] . '&' .
				$post['currency'] . '&' .
				$post['datetime'] . '&' .
				$post['sender'] . '&' .
				$post['codepro'] . '&' .
				$secret_key . '&' .
				$post['label'];

		if (sha1($str) != $post['sha1_hash'])
			return false;

		$ym = new YandexMoney($shop_id);

		$resp = $ym->operationDetail($token, $post['operation_id']);

		$id_order = $post['label'];
		
		if ($resp->isSuccess())
			return $id_order;
		else
			return false;
	}
	
	/**
	* Функция инициализирует оплату
	*
	* @return string $payment_form
	*/
	
	public function InitiatePaymentEShop($shopid, $scid, $phone, $sum, $orderId, $paymentType, $successURL, $failURL, $paymentDesc, $email, $name, $address, $test = false)
	{
		$url = ($test === true) ? 'https://demomoney.yandex.ru/eshop.xml' : 'https://money.yandex.ru/eshop.xml';
	
		$payment_form = '<form action="'.$url.'" method="post">

		<input type="hidden" value="'.$shopid.'" name="shopId"/>
		<input type="hidden" value="'.$scid.'" name="scid"/>
		<input type="hidden" value="'.$sum.'" name="sum"/>
		<input type="hidden" value="'.$phone.'" name="customerNumber"/>
		
		<input type="hidden" value="'.$orderId.'" name="orderNumber"/>
		<input type="hidden" value="'.$paymentType.'" name="paymentType"/>
		<input type="hidden" value="'.$successURL.'" name="shopSuccessURL"/>
		<input type="hidden" value="'.$failURL.'" name="shopFailURL"/>
		
		<input type="hidden" value="'.$phone.'" name="cps_phone"/>
		<input type="hidden" value="'.$email.'" name="cps_email"/>
		<input type="hidden" value="'.$email.'" name="custEMail"/>
		<input type="hidden" value="'.$name.'" name="custName"/>
		<input type="hidden" value="'.$address.'" name="custAddr"/>
		<input type="hidden" value="'.$paymentDesc.'" name="OrderDetails"/>
		
		<input type="submit" value="Купить сейчас" class="jSaleButton"/>

		</form>';

		return $payment_form;
	}
	
	/**
	* Функция выполняет проверку корректности данных перед оплатой
	*
	* @return string
	*/
	public function CheckOrderEShop($post, $shopId, $scid, $pass)
	{
		$hash = $post['action'].';'.$post['orderSumAmount'].';'.$post['orderSumCurrencyPaycash'].';'.$post['orderSumBankPaycash'].';'.$post['shopId'].';'.$post['invoiceId'].';'.$post['customerNumber'].';'.$pass;
	
		if ($shopId == $post['shopId'] && $scid == $post['scid'] && $post['md5'] == strtoupper(md5($hash)))
		{
			$order = $this->db->GetItemById('custom', $post['orderNumber']);
	
			if ($order && $post['orderSumAmount'] == $order['sum'])
				return '<?xml version="1.0" encoding="UTF-8"?><checkOrderResponse performedDatetime="'.date('c').'" code="0" invoiceId="'.$post['invoiceId'].'" shopId="'.$shopId.'"/>';
		}

		return false;
	}
	
	/**
	* Функция выполняет проверку корректности данных перед оплатой
	*
	* @return string
	*/
	public function CheckResultEShop($post, $shopId, $scid, $pass)
	{
		$hash = $post['action'].';'.$post['orderSumAmount'].';'.$post['orderSumCurrencyPaycash'].';'.$post['orderSumBankPaycash'].';'.$post['shopId'].';'.$post['invoiceId'].';'.$post['customerNumber'].';'.$pass;
	
		if ($shopId == $post['shopId'] && $scid == $post['scid'] && $post['md5'] == strtoupper(md5($hash)))
		{
			$order = $this->db->GetItemById('custom', $post['orderNumber']);
	
			if ($order && $post['orderSumAmount'] == $order['sum'])
				return '<?xml version="1.0" encoding="UTF-8"?><paymentAvisoResponse performedDatetime="'.date('c').'" code="0" invoiceId="'.$post['invoiceId'].'" shopId="'.$shopId.'"/>';
		}

		return false;
	}
}