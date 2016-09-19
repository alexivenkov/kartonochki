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

# Простейшая авторизация
include_once dirname(__FILE__) . '/../modules/M_Admin.inc.php';
$mAdmin = M_Admin::Instance();

session_start();
if (!$mAdmin->CheckLogin())
	die;

if ($_SESSION['access_type'] != 'admin')
	die;

# Кодировка.
header('Content-type: text/html; charset=' . $config['encoding']);

# Формирование GET запроса (на случай PHP как CGI)
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

# Подключение меню
include_once dirname(__FILE__) . '/_menu.php';

# Подключение необходимых модулей
include_once dirname(__FILE__) . '/../modules/M_Orders.inc.php';
$mOrders = M_Orders::Instance();

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	# Количество заказов в списке и множественная смена статусов заказов
	if (isset($_POST['reportList']))
	{
		$_SESSION['reportList'] = $_POST['reportList'];
		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/report_2.php');
		die;
	}

	# Фильтр
	if (isset($_POST['search_submit']) || isset($_POST['refunds']) || isset($_POST['was_refunds']))
	{
		list($day, $month, $year) = explode('.', $_POST['date_from']);
		$date_from = "$year-$month-$day";

		list($day, $month, $year) = explode('.', $_POST['date_to']);
		$date_to = "$year-$month-$day";
		
		unset($day, $month, $year);
	}
}

# Вывод списка заказов

# Количество дней в списке
$reportList = (isset($_SESSION['reportList'])) ? $_SESSION['reportList'] : $config['admin']['report2List'];

# Заглушка для уникалов
if (isset($date_to) && strtotime($date_to) > time())
	$date_to = date('Y-m-d');

if (isset($date_from) && strtotime($date_from) > time())
	$date_from = date('Y-m-d');

if (isset($date_from) && isset($date_to) && strtotime($date_from) > strtotime($date_to))
	$date_from = date('Y-m-d');

# Вывод дней
if (isset($date_from) && (isset($date_to)))
	$count = (strtotime($date_to) - strtotime($date_from)) / (3600 * 24);
else
	$count = $reportList - 1;

$date_from = $date_from_form = (isset($date_from)) ? $date_from : date("Y-m-d", time(0, 0, 0, date('m'), date('d') - $reportList + 1,  date('Y')));
$date_to = $date_to_form = (isset($date_to)) ? $date_to : date("Y-m-d", time(0, 0, 0, date('m'), date('d'),  date('Y')));

if ($date_from == $date_to)
{
	$date_next = date("Y-m-d", strtotime($date_to) - ($count - 1) * 24 * 3600);
	$date_stat = $date_from = date("Y-m-d", strtotime($date_to) - $count * 24 * 3600);
	$count--;
	$clicks = $rate = 0;
	$days[$count]['clicks'] = $days[$count]['rate'] = $days[$count]['price'] = 0;
	
	# дата
	$days[$count]['date'] = $date_stat;
	
	if ($config['yandex']['enabled'] === true)
	{
		include dirname(__FILE__) . '/../modules/C_Yandex.inc.php';
	
		# клики
		$days[$count]['clicks'] = $clicks;
		# расход
		$days[$count]['rate'] = number_format($rate * $config['yandex']['currency_rate'], 2, '.', '');
		# ср.цена
		$days[$count]['price'] = ($clicks != 0) ? number_format($rate / $clicks * $config['yandex']['currency_rate'], 2, '.', '') : 0;
	}
	
	# Выбор заказов
	$params = array ('date >=' , 'date <=');
	$values = array ("'$date_stat'" , "'$date_next'");
	$search_type = array('AND', 'AND');
	
	foreach ($config['statuses']['fail'] as $status)
	{
		$params[] = 'status !=';
		$values[] = $status;
		$search_type[] = 'AND';
	}
	
	if (isset($_POST['refunds']) && $_POST['refunds'] == 'count')
	{}
	else
	{
		$params[] = 'status !=';
		$values[] = $config['statuses']['refund'][0];
		$search_type[] = 'AND';		
	}

	$params[] = 'status !=';
	$values[] = $config['statuses']['deleted'][0];
	$search_type[] = 'AND';
	
	$orders[$date_from] = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);
	
	# заказы
	$order = $days[$count]['orders'] = count($orders[$date_from]);
	
	# цена 1 заказа
	$days[$count]['order_price'] = ($order) ? $rate / $order * $config['yandex']['currency_rate'] : 0;
	
	# Дефолтные значения
	$days[$count]['author_sum'] = $days[$count]['manager_sum'] = $days[$count]['partner_sum'] = $days[$count]['partner2_sum'] = $days[$count]['product_cost']= 0;
	
	# Расчёт отчислений и прибыли
	if (count($orders[$date_from]) > 0)
	{
		$turnover = $real_profit = $profit_without_ads = $sum = $delivery_cost = $real_profit_sum = 0;
		$author_sum = $manager_sum = $profit_sum = 0;
		foreach ($orders[$date_from] as $order)
		{
			# Сумма заказа
			$sum += $order['sum'];
			
			# Стоимость доставки
			$delivery_cost += $order['delivery_cost'];
		
			# Оборот
			$turnover += $order['sum']/* - $order['delivery_cost']*/;
			
			# Позиции заказа
			$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
			
			# Выборка данных по партнёру
			if ($config['partner']['enabled'] === true && $order['id_partner'] != '0')
				$partner = $mDB->GetItemById('partner', $order['id_partner']);
			else
				$partner = null;

			foreach ($order_items as $order_item)
			{
				$product_cost = $partner_sum = $partner2_sum = 0;
				$product = $mDB->GetItemByCode('product', $order_item['id_product']);

				# Себестоимость товара
				$days[$count]['product_cost'] += $product_cost += $product['cost_price'] * $order_item['quantity'];
				
				if ($config['discounts']['fixed'] === true)
					$order_item_sum = $order_item['quantity'] * $order_item['price'] - $order_item['discount'];
				else
					$order_item_sum = $order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100);
				
				# Партнёрские отчисления
				if (isset($partner) && !empty($partner))
				{
					$partner_sum += $order_item_sum * $order_item['partner_rate'] / 100;
					
					if ($config['partner']['levels'] === 2 && $partner['referer'] != '0')
					{
						$partner2_sum += $order_item_sum * $config['partner']['percent']['level_2'] / 100;
					}
				}
				
				# Отчисления автору
				if ($config['author']['enabled'] === true)
				{
					if (isset($product['author']) && $product['author'] != '0')
					{
						$author = $mDB->GetItemById('author', $product['author']);
						
						if ($author)
							$author_sum += ($order_item_sum - $partner_sum - $partner2_sum) * ($config['author']['percent'] / 100);
					}
				}

				# Отчисления менеджеру
				if ($config['manager']['enabled'] === true)
				{
					if (isset($product['manager']) && $product['manager'] != '0')
					{
						$manager = $mDB->GetItemById('manager', $product['manager']);

						if ($manager)
							$manager_sum += ($order_item_sum - $partner_sum - $partner2_sum - $author_sum) * ($config['manager']['percent'] / 100);
					}
				}
			}
			
			# Сохранение в переменные
			$days[$count]['partner_sum'] += $partner_sum;
			$days[$count]['partner2_sum'] += $partner2_sum;
			$days[$count]['author_sum'] += $author_sum;
			$days[$count]['manager_sum'] += $manager_sum;
			
			# Прибыль за вычетом комиссионных отчислений
			$profit_sum = $order['sum'] - $order['delivery_cost'] - $partner_sum - $partner2_sum - $author_sum - $manager_sum;
			
			# Подсчёт стоимости рекламы
			$ads = ($config['yandex']['enabled'] === true) ? $days[$count]['rate'] / count($orders[$date_from]) : 0;
			
			# Чистая прибыль
			if ($order['status'] == $config['statuses']['refund'][0])
			{
				$refund = $mDB->GetItemsByParam('refund', 'id_custom', $order['id_custom']);
				$refund = ($refund) ? $refund[0]['refund'] : 0;
			
				$real_profit = 0 - $refund - $ads;
			}
			else
				$real_profit = $profit_sum - $ads - $product_cost;
			
			# Реальная прибыль
			$real_profit_sum += $real_profit;
			
			# Прибыль без учёта рекламы
			$profit_without_ads += $real_profit + $ads;
		}
	}
	else
	{
		$sum = $delivery_cost = $turnover = $real_profit_sum = 0;
		$real_profit = 0 - $days[$count]['rate'];
		$profit_without_ads = 0;
	}
	
	# сумма заказов
	$days[$count]['sum'] = $sum;
	
	# стоимость доставки
	$days[$count]['delivery_cost'] = $delivery_cost;
	
	# оборот за 1 день
	$days[$count]['turnover'] = $turnover;
	
	# оборот за 1 день без рекламы
	$days[$count]['profit_without_ads'] = $profit_without_ads;
	
	# чистая прибыль
	$days[$count]['real_profit'] = number_format($real_profit_sum, 2, '.', '');
}
else
{
	# Подсчёт всех заказов
	$params[] = 'status !=';
	$values[] = $config['statuses']['deleted'][0];
	$search_type[] = 'AND';

	$all_orders = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);

	# Всего заказов
	$all_orders = count($all_orders);

	while ($date_from != $date_to)
	{
		$date_next = date("Y-m-d", strtotime($date_to) - ($count - 1) * 24 * 3600);
		$date_stat = $date_from = date("Y-m-d", strtotime($date_to) - $count * 24 * 3600);
		$count--;
		$clicks = $rate = 0;
		
		# дата
		$days[$count]['date'] = $date_stat;
		
		if ($config['yandex']['enabled'] === true)
		{
			include dirname(__FILE__) . '/../modules/C_Yandex.inc.php';
			
			# клики
			$days[$count]['clicks'] = $clicks;
			# расход
			$days[$count]['rate'] = number_format($rate * $config['yandex']['currency_rate'], 2, '.', '');
			# ср.цена
			$days[$count]['price'] = ($clicks != 0) ? number_format($rate / $clicks * $config['yandex']['currency_rate'], 2, '.', '') : 0;
		}
		else
			$days[$count]['clicks'] = $days[$count]['rate'] = $days[$count]['price'] = 0;

		# Выбор заказов
		$params = array ('date >=' , 'date <=');
		$values = array ("'$date_stat'" , "'$date_next'");
		$search_type = array('AND', 'AND');
		
		foreach ($config['statuses']['fail'] as $status)
		{
			$params[] = 'status !=';
			$values[] = $status;
			$search_type[] = 'AND';
		}

		if (isset($_POST['refunds']) && $_POST['refunds'] == 'count')
		{}
		else
		{
			$params[] = 'status !=';
			$values[] = $config['statuses']['refund'][0];
			$search_type[] = 'AND';		
		}

		$params[] = 'status !=';
		$values[] = $config['statuses']['deleted'][0];
		$search_type[] = 'AND';
		
		$orders[$date_from] = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);
		
		# заказы
		$order = $days[$count]['orders'] = count($orders[$date_from]);
		
		# цена 1 заказа
		$days[$count]['order_price'] = ($order) ? $rate / $order * $config['yandex']['currency_rate'] : 0;
		
		# Дефолтные значения
		$days[$count]['author_sum'] = $days[$count]['manager_sum'] = $days[$count]['partner_sum'] = $days[$count]['partner2_sum'] = $days[$count]['product_cost'] = 0;
		
		# Расчёт отчислений и прибыли
		if (count($orders[$date_from]) > 0)
		{
			$turnover = $real_profit = $profit_without_ads = $sum = $delivery_cost = $real_profit_sum = 0;
			$author_sum = $manager_sum = $profit_sum = 0;
			foreach ($orders[$date_from] as $order)
			{
				# Сумма заказа
				$sum += $order['sum'];
				
				# Стоимость доставки
				$delivery_cost += $order['delivery_cost'];
			
				# Оборот
				$turnover += $order['sum']/* - $order['delivery_cost']*/;
				
				# Позиции заказа
				$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
				
				# Выборка данных по партнёру
				if ($config['partner']['enabled'] === true && $order['id_partner'] != '0')
					$partner = $mDB->GetItemById('partner', $order['id_partner']);
				else
					$partner = null;

				foreach ($order_items as $order_item)
				{
					$product_cost = $partner_sum = $partner2_sum = 0;
					$product = $mDB->GetItemByCode('product', $order_item['id_product']);

					# Себестоимость товара
					$days[$count]['product_cost'] = $product_cost += $product['cost_price'] * $order_item['quantity'];
					
					if ($config['discounts']['fixed'] === true)
						$order_item_sum = $order_item['quantity'] * $order_item['price'] - $order_item['discount'];
					else
						$order_item_sum = $order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100);
					
					# Партнёрские отчисления
					if (isset($partner) && !empty($partner))
					{
						$partner_sum += $order_item_sum * $order_item['partner_rate'] / 100;
						
						if ($config['partner']['levels'] === 2 && $partner['referer'] != '0')
						{
							$partner2_sum += $order_item_sum * $config['partner']['percent']['level_2'] / 100;
						}
					}
					
					# Отчисления автору
					if ($config['author']['enabled'] === true)
					{
						if (isset($product['author']) && $product['author'] != '0')
						{
							$author = $mDB->GetItemById('author', $product['author']);
							
							if ($author)
								$author_sum += ($order_item_sum - $partner_sum - $partner2_sum) * ($config['author']['percent'] / 100);
						}
					}

					# Отчисления менеджеру
					if ($config['manager']['enabled'] === true)
					{
						if (isset($product['manager']) && $product['manager'] != '0')
						{
							$manager = $mDB->GetItemById('manager', $product['manager']);

							if ($manager)
								$manager_sum += ($order_item_sum - $author_sum - $partner_sum - $partner2_sum) * ($config['manager']['percent'] / 100);
						}
					}
				}
				
				# Сохранение в переменные
				$days[$count]['partner_sum'] += $partner_sum;
				$days[$count]['partner2_sum'] += $partner2_sum;
				$days[$count]['author_sum'] += $author_sum;
				$days[$count]['manager_sum'] += $manager_sum;
				
				# Прибыль за вычетом комиссионных отчислений
				$profit_sum = $order['sum'] - $order['delivery_cost'] - $manager_sum - $author_sum - $partner_sum - $partner2_sum;
				
				# Подсчёт стоимости рекламы
				$ads = ($config['yandex']['enabled'] === true) ? $days[$count]['rate'] / count($orders[$date_from]) : 0;
				
				# Чистая прибыль
				if ($order['status'] == $config['statuses']['refund'][0])
				{
					$refund = $mDB->GetItemsByParam('refund', 'id_custom', $order['id_custom']);
					$refund = ($refund) ? $refund[0]['refund'] : 0;
				
					$real_profit = 0 - $refund - $ads;
				}
				else
					$real_profit = $profit_sum  - $ads - $product_cost;

				# Реальная прибыль
				$real_profit_sum += $real_profit;
				
				# Прибыль без учёта рекламы
				$profit_without_ads += $real_profit + $ads;
			}
		}
		else
		{
			$sum = $delivery_cost = $turnover = $real_profit_sum = 0;
			$real_profit = 0 - $days[$count]['rate'];
			$profit_without_ads = 0;
		}
		
		# сумма заказов
		$days[$count]['sum'] = $sum;
		
		# стоимость доставки
		$days[$count]['delivery_cost'] = $delivery_cost;
		
		# оборот за 1 день
		$days[$count]['turnover'] = $turnover;
		
		# оборот за 1 день без рекламы
		$days[$count]['profit_without_ads'] = $profit_without_ads;
		
		# чистая прибыль
		$days[$count]['real_profit'] = number_format($real_profit_sum, 2, '.', '');
	}
}

# Подсчёт возвратов и заказов
$orders_count = $refund_count = 0;
foreach ($orders as $order_date)
	foreach ($order_date as $order)
	{
		if ($order['status'] == $config['statuses']['refund'][0])
			$refund_count = (isset($refund_count)) ? $refund_count + 1 : 1;
		$orders_count++;
	}

# Процент возвратов
if (isset($orders_count) && $orders_count > 0)
	$refund_percent = number_format($refund_count / $orders_count * 100, 2, '.', '');
else
	$refund_count = $refund_percent = 0;

# Статусы заказов
$statuses = $config['statuses'];

include_once dirname(__FILE__) . '/../design/adminReport_2.tpl.php';