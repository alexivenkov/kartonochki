<?php

# jSale v1.431
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

if ($config['author']['enabled'] !== true)
{
	echo 'Авторский кабинет отключён администратором.';
	die;
}

# Подключение модулей
include_once dirname(__FILE__) . '/../modules/M_Authors.inc.php';
$mAuthors = M_Authors::Instance();

# Кодировка
header('Content-type: text/html; charset=' . $config['encoding']);

# Формирование GET запроса (на случай PHP как CGI)
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

# Авторизация
session_start();
if (!$mAuthors->CheckLogin())
	die;

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

if ($config['database']['enabled'] === false)
	$message = 'Использование базы данных отключено в настройках. Админка в данном случае бесполезна и будет выдавать ошибки.';

# Выборка данных по партнёрку из БД
$author = $mDB->GetItemByID('author', $_SESSION['id_author']);

# Подсчёт статистики
$products = $mDB->GetItemsByParam('product', 'author', $_SESSION['id_author']);
$total_count = $paid_count = $paid_sum = 0;
$all_orders = array();
$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);

foreach ($products as $product)
{
	# Все пункты заказов с привязанными товарами
	$order_items = $mDB->GetItemsByParam('custom_item', 'id_product', $product['code']);
	
	foreach ($order_items as $order_item)
	{
		# Все заказы с привязанными товарами
		$orders = $mDB->GetItemsByParam('custom', 'id_custom', $order_item['id_custom']);
		
		$total_count++;
		
		# Оплаченные заказы
		foreach ($orders as $id => $order)
		{
			$commission = 0;
			if (in_array($order['status'], $success_statuses))
			{
				$paid_count++;
				$partner_sum = $partner2_sum = 0;
				
				# Учёт скидки
				if ($config['discounts']['fixed'] === true)
					$real_paid_sum = $order_item['quantity'] * $order_item['price'] - $order_item['discount'];
				else
					$real_paid_sum = $order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100);
					
				# Учёт отчислений партнёру
				if ($config['partner']['enabled'] === true && $order['id_partner'] != '0')
				{
					$partner = $mDB->GetItemById('partner', $order['id_partner']);
					
					if (isset($partner) && !empty($partner))
					{
						$partner_sum = $real_paid_sum * ($order_item['partner_rate'] / 100);
						
						if ($config['partner']['levels'] === 2 && $partner['referer'] != '0')
						{
							$partner2_sum = $real_paid_sum * ($config['partner']['percent']['level_2'] / 100);
						}
					}
				}
				
				$paid_sum += $commission = $real_paid_sum - $partner_sum - $partner2_sum;
			}
			
			$orders[$id]['items'] = array($order_item);
			$orders[$id]['commission'] = number_format( $commission * ($config['author']['percent'] / 100), 2, '.', '');
		}
		$all_orders = array_merge($all_orders, $orders);
	}
}
$paid_sum = number_format($paid_sum * $config['author']['percent'] / 100, 2, '.', '');
	
# Вывод дизайна
include_once dirname(__FILE__) . '/_menu.php';
include_once dirname(__FILE__) . '/../design/authorStat.tpl.php';