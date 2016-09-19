<?php

# jSale v1.431
# http://jsale.biz

# Модуль для изменения способа оплаты

# Работает только при наличии переменных id_order и hash
if (isset($_GET['id_order']) && isset($_GET['hash']))
{
	include_once dirname(__FILE__) . '/../config.inc.php';
	include_once dirname(__FILE__) . '/../modules/M_Email.inc.php';
	$mEmail = M_Email::Instance();

	# Обработка GET параметров
	$id_order = intval($_GET['id_order']);
	$hash = htmlspecialchars($_GET['hash']);

	# Выбор заказа и его элементов
	$order = $mDB->GetItemById('custom', $id_order);
	$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $id_order);
	
	$order_sum = $order['sum'];
	$order_delivery = $order['delivery'];
	$order_payment = $order['payment'];
	$yandex_payment_type = $order['payment_ym'];

	# Проверка хеш-строки
	if ($mEmail->CheckHash($hash, $id_order, 'CHANGE', $config['secretWord']) == false)
		die;

	# Подключение дополнительных настроек
	if (isset($order['config']) && !empty($order['config']) && is_file(dirname(__FILE__) . '/../config' . $order['config'] . '.inc.php'))
		include_once dirname(__FILE__) . '/../config' . $order['config'] . '.inc.php';

	# Обработка POST запроса
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		# Обработка данных
		$order_payment = htmlspecialchars($_POST['order_payment']);
		if ($config['payments']['yandex_eshop']['enabled'] === true)
			$yandex_payment_type = (isset($_POST['yandex_payment_type'])) ? htmlentities($_POST['order_payment_ym'], ENT_QUOTES) : key($config['payments']['yandex_eshop']['types']);
		else
			$yandex_payment_type = '';
		
		$order_delivery = htmlspecialchars($_POST['order_delivery']);
		$configs = $config;
		foreach ($order as $var => $val)
			$$var = $val;
		$config = $configs;

		# Определение данных формы оплаты
		$payment = $config['payments'][$order_payment];
		$payment['type'] = $order_payment;

		# Определение данных способа доставки
		if (isset($config['payment2delivery'][$order_payment]) && !in_array($order_delivery, $config['payment2delivery'][$order_payment]))
			$order_delivery = $config['payment2delivery'][$order_payment][0];

		$delivery = $config['deliveries'][$order_delivery];
		$delivery['type'] = $order_delivery;
		
		# Определение статуса заказа
		$status = $config['statuses'][$order['status']];
		
		# Маркеры
		$new_order = $payment_link = true;
		
		# Мелочи
		$email = $order['email'];
		$address = $order['address'];
		$name = $order['name'];
		$lastname = $order['lastname'];
		$fathername = $order['fathername'];
		
		# Подсчёт суммы заказа
		$order_sum = $subtotal = 0;
		
		# Учёт скидки		
		foreach ($order_items as $key => $order_item)
		{
			if ($config['discounts']['fixed'] === true)
				$order_sum += $order_item['quantity'] * ($order_item['price'] - max($payment['discount'], $order_item['discount']));
			else
				$order_sum += $order_item['quantity'] * $order_item['price'] * (1 - max($payment['discount'], $order_item['discount']) / 100);
			
			$subtotal += $order_item['quantity'] * $order_item['price'];
		}

		# Добавление стоимости доставки
		$delivery['cost'] = (isset($delivery['free']) && $delivery['free'] != '' && $subtotal > $delivery['free'] || isset($payment['free_delivery']) && $payment['free_delivery'] === true) ? 0 : $delivery['cost'];
		$order_sum += $delivery['cost'];
		
		# Статусы успешного завершения заказа (по умолчанию "Оплачен" и "Доставлен")
		$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);
		
		if (isset($_POST['save']))
		{
			# Подключение модуля оплаты (создание ссылок для оплаты)
			if (is_file(dirname(__FILE__) . '/../modules/C_Payment.php'))
				include_once dirname(__FILE__) . '/../modules/C_Payment.php';

			# Сохранение заказа
			$params = array ('payment' => $order_payment, 'payment_ym' => $yandex_payment_type,'delivery' => $order_delivery, 'delivery_cost' => $delivery['cost'], 'sum' => $order_sum);

			$mDB->EditItemById('custom', $params, $id_order, true);

			# Отправка изменений на почту
			$emailSubjectOrder = str_replace('№№', '№' . $id_order, $config['email']['subjectOrder']);
			$content = $mEmail->PrepareChangeStatus($id_order, $email, $lastname, $name, $fathername, $phone, $zip, $country, $region, $city, $address, $comment, $order_items, $order_sum, $payment, $yandex_payment_type, $delivery, $order['date'], $config, $status, null, null, $order['config']);

			$mEmail->SendEmail($email, $config['email']['answer'], $emailSubjectOrder, $content, $config['email']['answerName'], $config['encoding']);

			ob_start();
			$echo = '<div class="jSaleWrapper"><div class="jSaleForm"><br>
			<link type="text/css" rel="stylesheet" href="'.$config['sitelink'].$config['dir'].'css/jsale.css"></link>';
			if (isset($payment['link']))
				$echo .= '<p class="submit">Теперь вы можете оплатить заказ с помощью этой ссылки<br><br><a href="'.$payment['link'].'" class="jSaleButton" style="text-decoration: none;">Оплатить</a></p>
				<br><p class="t-center"><a href="">Изменить метод оплаты</a></p>';
			else
				$echo .= '<p class="t-center">Метод оплаты изменён. Проверьте почту.</p>
				<br><p class="t-center"><a href="">Изменить метод оплаты</a></p>';
			$echo .= '</div></div>';
			echo $echo;
			die;
		}
	}
	
	ob_start();
	include_once dirname(__FILE__) . '/../design/changePayment.tpl.php';
	$echo = ob_get_clean();
	echo $echo;
}