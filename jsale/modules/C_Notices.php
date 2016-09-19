<?php

# jSale v1.36
# http://jsale.biz

# Подключение настроек
include_once dirname(__FILE__) . '/../config.inc.php';

# Вывод ошибок
if ($config['errors'] === true)
{
	error_reporting(E_ALL); # Уровень вывода ошибок
	ini_set('display_errors', 'on'); # Вывод ошибок включён
}
# Логирование ошибок
if ($config['error_logging'] === true)
{
	ini_set("log_errors", 'on'); # Логирование включено
	ini_set("error_log", dirname(__FILE__) . '/error_log.txt'); # Путь файла с логами
}

# Если ключ в GET параметре
if (isset($_GET['secret']) && $_GET['secret'] == $config['secretWord'])
{
	if ($config['notice_order']['enabled'] === true)
	{
		# Подключение модуля работы с базой данных.
		include_once dirname(__FILE__) . '/M_DB.inc.php';
		$mDB = M_DB::Instance();
		
		# Подключение модуля работы с письмами
		include_once dirname(__FILE__) . '/M_Email.inc.php';
		$mEmail = M_Email::Instance();
		
		# Выборка всех заказов
		$orders = $mDB->GetAllItems('custom');
		
		$now = time();
		foreach ($orders as $order)
		{
			$arrays = array_merge($config['statuses']['success'], $config['statuses']['deleted'], $config['statuses']['refund'], $config['statuses']['fail']); 
			if (!in_array($order['status'], $arrays))
			{
				$days = floor( ($now - strtotime($order['date'])) / 86400);
				if (in_array($days, $config['notice_order']['period']))
				{
					# Выборка товаров заказа
					$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
					
					# Определение данных формы оплаты
					$payment = $config['payments'][$order['payment']];
					$payment['type'] = $order['payment'];

					# Определение данных способа доставки
					$delivery = $config['deliveries'][$order['delivery']];
					$delivery['cost'] = $order['delivery_cost'];

					# Определение статуса заказа
					$status = $config['statuses'][$order['status']];
					
					# Маркер, сигнализирующий создание нового заказа
					$new_order = true;

					# Мелочи
					$id_order = $order['id_custom'];
					$order_sum = $order['sum'];
					$email = $order['email'];
					$address = $order['address'];
					$name = $order['name'];
					$phone = $order['phone'];
					$lastname = $order['lastname'];
					$fathername = $order['fathername'];
					
					# Статусы успешного завершения заказа (по умолчанию "Оплачен" и "Доставлен")
					$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);

					# Подключение модуля оплаты (создание ссылок для оплаты)
					$payment_link = true;
					if (is_file(dirname(__FILE__) . '/../modules/C_Payment.php') && !in_array($order['status'], $success_statuses))
						include_once dirname(__FILE__) . '/../modules/C_Payment.php';
				
					# Генерация хеш-строки для подтверждения заказа
					if ($config['email']['confirm'] == true || $config['email']['refuse'] == true)
						$hash = $mEmail->GenerateHash($id_order, $order_sum, $config['secretWord']);
				
					# Подготовка текста письма
					$content = $mEmail->PrepareNoticeOrder($order['id_custom'], $order['email'], $order['lastname'], $order['name'], $order['fathername'], $order['phone'], $order['zip'], $order['region'], $order['city'], $order['address'], $order['comment'], $order_items, $order_sum, $payment, $delivery, $order['date'], $status, $hash, $config);
				
					# Отправка сообщения
					$from_name = (isset($config['notice_order']['name'])) ? $config['notice_order']['name'] : $config['email']['answerName'];
					$mEmail->SendEmail($order['email'], $config['email']['answer'], $config['email']['subjectNoticeOrder'], $content, $from_name, $config['encoding']);
				}
			}
		}
	}
	
	if ($config['notice_review']['enabled'] === true)
	{
		# Подключение модуля работы с базой данных.
		include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
		$mDB = M_DB::Instance();
		
		# Подключение модуля работы с письмами
		include_once dirname(__FILE__) . '/../modules/M_Email.inc.php';
		$mEmail = M_Email::Instance();
		
		# Выборка всех заказов
		if (!isset($orders))
			$orders = $mDB->GetAllItems('custom');
		
		$now = time();
		foreach ($orders as $order)
		{
			$arrays = array_merge($config['statuses']['success'], $config['statuses']['delivered']); 
			if (in_array($order['status'], $arrays))
			{
				$days = floor( ($now - strtotime($order['date'])) / 86400);
				if (in_array($days, $config['notice_review']['period']))
				{
					# Подготовка текста письма
					$content = $mEmail->PrepareNoticeReview($order['name'], $config);
				
					# Отправка сообщения
					$from_name = (isset($config['notice_review']['name'])) ? $config['notice_review']['name'] : $config['email']['answerName'];
					$mEmail->SendEmail($order['email'], $config['email']['answer'], $config['email']['subjectNoticeReview'], $content, $from_name, $config['encoding']);
				}
			}
		}
	}
	
	if ($config['notice_partner']['enabled'] === true)
	{
		# Подключение модуля работы с базой данных.
		include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
		$mDB = M_DB::Instance();
		
		# Подключение модуля работы с письмами
		include_once dirname(__FILE__) . '/../modules/M_Email.inc.php';
		$mEmail = M_Email::Instance();
		
		# Выборка всех заказов
		if (!isset($orders))
			$orders = $mDB->GetAllItems('custom');
		
		$now = time();
		foreach ($orders as $order)
		{
			$arrays = array_merge($config['statuses']['success'], $config['statuses']['delivered']); 
			if (in_array($order['status'], $arrays))
			{
				$days = floor( ($now - strtotime($order['date'])) / 86400);
				if (in_array($days, $config['notice_partner']['period']))
				{
					# Подготовка текста письма
					$content = $mEmail->PrepareNoticePartner($order['name'], $config);
				
					# Отправка сообщения
					$from_name = (isset($config['notice_partner']['name'])) ? $config['notice_partner']['name'] : $config['email']['answerName'];
					$mEmail->SendEmail($order['email'], $config['email']['answer'], $config['email']['subjectNoticePartner'], $content, $from_name, $config['encoding']);
				}
			}
		}
	}
}
else
	die;