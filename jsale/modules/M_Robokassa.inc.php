<?php
# Модуль оплаты заказа с помощью сервиса RoboKassa

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Robokassa
{
	private static $instance; 	# ссылка на экземпляр класса
	private $db; 			    # основная модель работы с БД

	# Получение единственного экземпляра класса.
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Robokassa();

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
	* @param string $mrh_login
	* @param string $mrh_pass1
	* @param string $inv_id
	* @param string $inv_desc
    * @param integer $out_summ
	*
	* @return string $payment_url
	*/
	public function InitiatePayment($mrh_login, $mrh_pass1, $inv_id, $inv_desc, $out_summ, $test)
	{
		# Формирование подписи.
		$crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");

        if ($test == true)
            $url = 'http://test.robokassa.ru/';
        else
            $url = 'https://merchant.roboxchange.com/';

		# Формирование URL.
		$payment_url = $url . "Index.aspx?MrchLogin=$mrh_login&".
			"OutSum=$out_summ&InvId=$inv_id&Desc=$inv_desc&SignatureValue=$crc";

		return $payment_url;
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
	public function CheckResult($pass2, $post)
	{
		$crc = strtoupper($post['SignatureValue']);
		$my_crc = strtoupper(md5("$post[OutSum]:$post[InvId]:$pass2"));

		if ($crc == $my_crc)
			return true;
		else
			return false;
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