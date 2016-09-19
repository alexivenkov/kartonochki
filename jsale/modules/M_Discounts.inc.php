<?php
# Модуль обработки заказа и отправки заказа на почту

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Discounts
{
	private static $instance; 	# Ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Discounts();

		return self::$instance;
	}
	
	# Подсчёт скидки
	public function CountDiscount($sum, $discounts)
	{
        $mark = 0;
		
		foreach ($discounts as $key => $discount)
		{
			if ($sum >= $key)
				$user['discount'] = $discount;

			if ($mark != 1)
			{
				if ($sum < $key)
				{
					$user['next_discount'] = $discount;
					$user['next_sum'] = $key;
					$mark = 1;
				}
			}
		}
		
		$user['discount'] = (isset($user['discount'])) ? $user['discount'] : 0;
		$user['next_sum'] = (isset($user['next_sum'])) ? $user['next_sum'] : 0;
		$user['next_discount'] = (isset($user['next_discount'])) ? $user['next_discount'] : 0;

		return $user;
	}
	
	# Подсчёт скидки по промо-коду
	public function CountCodeDiscount($code, $codes)
	{
		$discount = (isset($codes[$code])) ? $codes[$code] : 0;
		
		return $discount;
	}
}