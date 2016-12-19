<?php
# Контроллер работы с базой данных

# Подключение модуля работы с базой данных.
include_once dirname(__FILE__) . '/M_Orders.inc.php';
$mOrders = M_Orders::Instance();

# Если заказ отправлен, то добавление его в базу данных
if (isset($new_order))
{
	$date = date("Y-m-d H:i:s");
    $status = 0;
    $juridical = (isset($juridical)) ? $juridical : '';
	$id_user = (isset($user['id_user'])) ? $user['id_user'] : '';
	$yandex_payment_type = (isset($yandex_payment_type)) ? $yandex_payment_type : '';
	#$domain = $_SERVER['HTTP_REFERER'];

	# Добавление заказа в БД
	$id_order = $mOrders->CreateOrder($lastname, $name, $fathername, $email, $phone, $zip, $country, $region, $city, $address, $comment, $payment['type'], $juridical, $delivery['type'], $delivery['cost'], $date, $order_sum, $status, $id_user, $utm, $source, $product['form_config'], $_SERVER['REMOTE_ADDR'], $yandex_payment_type, $product['domain']);
	
	# Логирование статуса
	$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), 0, true);
	
	foreach ($products as $k => $product)
	{
		$product['param1'] = ($product['param1'] == null) ? '' : $product['param1'];
		$product['param2'] = ($product['param2'] == null) ? '' : $product['param2'];
		$product['param3'] = ($product['param3'] == null) ? '' : $product['param3'];

		# Расчёт комиссии партнёра
		if ($config['partner']['enabled'] === true)
		{
			if ($config['partner']['rate_product'] === true)
			{
				$tmp_product = $mDB->GetItemByCode('product', $product['code']);
				$partner_rate = (isset($tmp_product['partner_rate'])) ? $tmp_product['partner_rate'] : $config['partner']['percent']['level_1'];
			}
			else
				$partner_rate = $config['partner']['percent']['level_1'];
		}
		else
			$partner_rate = 0;

		$products[$k]['commission'] = number_format( $product['subtotal'] * $partner_rate / 100 , 2, '.', '');

		# Добавление элемента заказа в БД
		$mOrders->CreateOrderItem($id_order, $product['code'], $product['title'], $product['qty'], $product['price'], $product['discount'], $product['unit'], $product['param1'], $product['param2'], $product['param3'], $partner_rate);
	}
}