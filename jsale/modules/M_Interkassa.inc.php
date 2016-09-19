<?php
# Модуль оплаты заказа с помощью сервиса InterKassa

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Interkassa
{
	private static $instance; 	# Ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Interkassa();

		return self::$instance;
	}

	/**
	* Функция инициализирует оплату
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($shop_id, $sum, $payment_id, $payment_desc, $currency)
	{
		# Формирование формы.
        $payment_form = '
        <form name="payment" action="https://sci.interkassa.com/" method="post" enctype="utf-8">
            <input type="hidden" name="ik_co_id" value="' . $shop_id . '">
            <input type="hidden" name="ik_am" value="' . $sum . '">
			<input type="hidden" name="ik_cur" value="'. $currency . '">
            <input type="hidden" name="ik_pm_no" value="' . $payment_id . '">
            <input type="hidden" name="ik_desc" value="' . $payment_desc . '">
            <input type="submit" name="process" value="Оплатить" class="jSaleButton">
        </form>';

		return $payment_form;
	}

	/**
	* Функция проверяем результат оплаты
	*
	* @return boolean
	*/
	public function CheckResult($shop_id, $secret_key, $post, $test_key)
	{
        if ($post['ik_co_id'] != $shop_id)
            return false;
		
		$ik_key = ($post['ik_pw_via'] == 'test_interkassa_test_xts') ? $test_key : $secret_key;	
		
		$data = array();
		foreach ($post as $key => $value)
		{
            if (!preg_match('/ik_/', $key))
                continue;
            $data[$key] = $value;
        }
		
		$ik_sign = $data['ik_sign'];
		unset($data['ik_sign']);
		
		ksort($data, SORT_STRING);
		array_push($data, $ik_key);
		
		$signString = implode(':', $data);
		$sign = base64_encode(md5($signString, true));

		if ($sign === $ik_sign && $data['ik_inv_st'] == 'success')
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