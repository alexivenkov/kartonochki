<?php
# Модуль оплаты заказа с помощью сервиса RoboKassa

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_SpryPay
{
	private static $instance; 	# ссылка на экземпляр класса
	private $db; 			    # основная модель работы с БД

	# Получение единственного экземпляра класса.
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_SpryPay();

		return self::$instance;
	}
	
	# Конструктор
	public function __construct()
	{
        $this->db = M_DB::Instance();
	}

	/**
	* Функция инициализирует оплату
	8
	* @return string $payment_form
	*/
	public function InitiatePayment($shopId, $paymentId, $sum, $currencyCode, $paymentDesc, $email, $resultURL)
	{
		# Формирование формы
		$payment_form = '
        <form name="payment" action="http://sprypay.ru/sppi/" method="post" accept-charset="utf-8" class="like-form">
            <input type="hidden" name="spShopId" value="' . $shopId . '">
            <input type="hidden" name="spShopPaymentId" value="' . $paymentId . '">
			<input type="hidden" name="spCurrency" value="'. $currencyCode . '">
            <input type="hidden" name="spAmount" value="' . $sum . '">
            <input type="hidden" name="spPurpose" value="' . $paymentDesc . '">
			<input type="hidden" name="spUserEmail" value="' . $email . '">
			<input type="hidden" name="spIpnUrl" value="' . $resultURL . '">
			<input type="hidden" name="spIpnMethod" value="1">
            <input type="submit" name="process" value="Оплатить" class="sub-btn">
        </form>';

		return $payment_form;
	}

	/**
	* Функция проверяем результат оплаты оплату
	*
	* @param string $pass2
	* @param integer $sum
	* @param string $id_order
	* @param string $crc
	*
	* @return boolean
	*/
	public function CheckResult($post, $secret)
	{
		echo $secret;
		echo '<br>';
		$spQueryFields = array('spPaymentId', 'spShopId', 'spShopPaymentId', 'spBalanceAmount', 'spAmount', 'spCurrency', 'spCustomerEmail', 'spPurpose', 'spPaymentSystemId', 'spPaymentSystemAmount', 'spPaymentSystemPaymentId', 'spEnrollDateTime', 'spHashString', 'spBalanceCurrency');

		foreach($spQueryFields as $spFieldName) if (!isset($post[$spFieldName])) exit("error в запросе с данными платежа отсутствует параметр `$spFieldName`");
	
		$localHashString = md5($post['spPaymentId'].$post['spShopId'].$post['spShopPaymentId'].$post['spBalanceAmount'].$post['spAmount'].$post['spCurrency'].$post['spCustomerEmail'].$post['spPurpose'].$post['spPaymentSystemId'].$post['spPaymentSystemAmount'].$post['spPaymentSystemPaymentId'].$post['spEnrollDateTime'].$secret);
		
		echo $localHashString;
		echo '<br>';
		echo $_POST['spHashString'];
		echo '<br>';
		
		if ($localHashString == $_POST['spHashString'])
			return true;
		else
			exit("error не совпали подписи; локальная: `$localHashString`; в запросе:`".$post['spHashString']."`");
	}

	public function CheckSuccessPayment($pass1)
	{
		$out_summ = $_REQUEST['OutSum'];
		$inv_id = $_REQUEST['InvId'];
		$crc = $_REQUEST['SignatureValue'];

		$crc = strtoupper($crc);

		$my_crc = strtoupper(md5("$out_summ:$inv_id:$pass1"));

		# проверка корректности подписи
		if ($my_crc != $crc)
			return false;

		# проверка наличия номера заказа в истории операций
		if ($this->db->GetItemById('custom', $inv_id))
			return true;
		else
			return false;
	}

	public function FailurePayment()
	{
		
	}
}