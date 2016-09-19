<?php

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

# Если API ключ в POST параметре верен
if (isset($_POST['key']) && $_POST['key'] == $config['api']['key'])
{
	# Подключение модуля работы с базой данных.
	include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
	$mDB = M_DB::Instance();

	# Обработка POST массива
	$post = array();
	foreach ($_POST as $key => $value)
	{
		if (is_string($value))
			$post[$key] = htmlspecialchars($value);
		elseif (is_array($value))
		{
			foreach ($value as $key2 => $value2)
				$post[$key][$key2] = htmlspecialchars($value2);
		}
	}
  
	# Добавление заказа
	if (isset($post['name']) && isset($post['phone']) && isset($post['orderid']) && isset($post['address']))
	{
		$params = array (
			'name' => $post['name'],
			'phone' => $post['phone'],
			'admin_comment' => 'From API: ' . $post['orderid'],
			'address' => $post['address'],
			'date' => date('Y-m-d H:i:s'),
			'sum' => $post['price'] * $post['qty']
		);
	
		$order_id = $mDB->CreateItem('custom', $params, true);
		
		$params = array (
			'id_custom' => $order_id,
			'date' => date('Y-m-d H:i:s'),
			'status' => 0
		);
		
		$mDB->CreateItem('status', $params, true);
		
		$params = array (
			'id_custom' => $order_id,
			'id_product' => $post['id'],
			'quantity' => $post['qty'],
			'product' => $post['title'],
			'unit' => 'шт.',
			'price' => $post['price']
		);
		
		$mDB->CreateItem('custom_item', $params, true);
		
		header('HTTP/1.1 200 OK');
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="utf-8"?><result><status>success</status><orderid>'.$order_id.'</orderid></result>';
		die;
	}

	# Выборка статусов
	if (isset($post['ids']))
	{
		$orders = '';
		foreach ($post['ids'] as $id)
		{
			$order = $mDB->GetItemById('custom', $id);
			
			if ($order)
				$orders .= '<order><id>'.$order['id_custom'].'</id><status>'.$config['statuses'][$order['status']].'</status><comment>'.$order['comment'].'</comment></order>';
		}

		header('HTTP/1.1 200 OK');
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="utf-8"?><result>'.$orders.'</result>';
		die;
	}
}