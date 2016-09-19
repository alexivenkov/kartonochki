<?php
# Модуль оплаты заказа с помощью сервиса Webmoney

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Webmoney
{
	private static $instance; 	# Ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Webmoney();

		return self::$instance;
	}

	/**
	* Функция инициализирует оплату
	*
	* @return string $payment_form
	*/
	public function InitiatePayment($purse, $sum, $payment_id, $payment_desc, $resultURL, $successURL)
	{
		# Формирование формы.
        $payment_form = '
        <form action="https://merchant.webmoney.ru/lmi/payment.asp" method="post">
			<input type="hidden" name="LMI_PAYEE_PURSE" value="' . $purse . '">
			<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="' . $sum . '">
			<input type="hidden" name="LMI_PAYMENT_NO" value="' . $payment_id . '">
			<input type="hidden" name="LMI_PAYMENT_DESC" value="' . $payment_desc . '">
			<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="' . base64_encode($payment_desc) . '">
			<input type="hidden" name="LMI_RESULT_URL" value="' . $resultURL . '">
			<input type="hidden" name="LMI_SUCCESS_URL" value="' . $successURL . '">
			<input type="hidden" name="LMI_SUCCESS_METHOD" value="POST">
			<input type="hidden" name="LMI_SIM_MODE" value="0">
			
            <input type="submit" name="process" value="Оплатить" class="jSaleOrder">
        </form>';

		return $payment_form;
	}

	/**
	* Функция проверяем результат оплаты
	*
	* @return boolean
	*/
	public function CheckResult($purse, $secret, $post)
	{
        if ($post['LMI_PAYEE_PURSE'] != $purse)
            return false;

        $hash = $post['LMI_PAYEE_PURSE'].$post['LMI_PAYMENT_AMOUNT'].$post['LMI_PAYMENT_NO'].$post['LMI_MODE'].$post['LMI_SYS_INVS_NO'].$post['LMI_SYS_TRANS_NO'].$post['LMI_SYS_TRANS_DATE'].$secret.$post['LMI_PAYER_PURSE'].$post['LMI_PAYER_WM'];

        if ($post['LMI_HASH'] === strtoupper(hash('sha256', $hash)))
            return true;
		else
			return false;
	}
}