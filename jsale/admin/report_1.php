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

# Определение текущей страницы
if (isset($_GET['page']))
    $navi['page'] = $_GET['page'];
else
    $navi['page'] = 1;

# Определение текущего заказа
if (isset($_GET['order']))
    $id_order = $_GET['order'];

# Определение статуса заказов для фильтра
$status = (isset($_GET['status']) && $_GET['status'] != 'all') ? intval($_GET['status']) : 'all';

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	# Количество заказов в списке и множественная смена статусов заказов
	if (isset($_POST['ordersList']))
	{
		$_SESSION['ordersList'] = $_POST['ordersList'];

		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/report_1.php');
		die;
	}

	# Фильтр
	if (isset($_POST['search_submit']) || isset($_POST['refunds']) || isset($_POST['was_refunds']))
	{
		if (isset($_POST['refunds']))
			$_POST['refunds'] = 'count';
		elseif (isset($_POST['was_refunds']) && !isset($_POST['refunds']))
			unset($_POST['refunds']);
	
		$date_from = $date_from_form = (!empty($_POST['date_from'])) ? $_POST['date_from'] : date('d.m.Y', time());
		$date_to = $date_to_form = (!empty($_POST['date_to'])) ? $_POST['date_to'] : date('d.m.Y', time());
		
		# Красивые даты
		list($day, $month, $year) = explode('.', $date_from);
		$date_from = "$year-$month-$day";

		list($day, $month, $year) = explode('.', $date_to);
		$date_to = "$year-$month-$day";
		
		unset($day, $month, $year);

		$date_to =  date("Y-m-d", strtotime($date_to) + 24 * 3600);
		
		# Выбор заказов
		$params = array ('date >=' , 'date <=');
		$values = array ("'$date_from'" , "'$date_to'");
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
	
		$orders = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);

		# Выбор позиций товаров
		$orders_items = array();
		foreach ($orders as $order)
			$order_items[$order['id_custom']] = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
			
		# Подсчёты
		$orders_qty = 0;
		foreach ($orders as $i => $order)
		{
			$date_stat = date("Y-m-d", strtotime($order['date']));
			$date_next = date("Y-m-d", strtotime($order['date']) + 24 * 3600);
			
			if ($config['yandex']['enabled'] === true)
				include dirname(__FILE__) . '/../modules/C_Yandex.inc.php';
			
			# Выбор заказов за день
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

			$orders_by_day = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);;
				
			# Подсчёт стоимости рекламы
			if ($config['yandex']['enabled'] === true)
				$orders[$i]['ads'] = $ads = $rate / count($orders_by_day) * $config['yandex']['currency_rate'];
			else
				$orders[$i]['ads'] = $ads = 0;
			
			# Выборка данных по партнёру
			if ($config['partner']['enabled'] === true && $order['id_partner'] != '0')
				$partner = $mDB->GetItemById('partner', $order['id_partner']);
				
			# Дефолтные значения
			$author_sum = $partner_sum = $partner2_sum = $manager_sum = $profit_sum = $product_cost = 0;
			$orders[$i]['author_sum'] = $orders[$i]['manager_sum'] = $orders[$i]['partner_sum'] = $orders[$i]['partner2_sum'] = $orders[$i]['product_cost'] = 0;
			
			# Товары
			foreach ($order_items[$order['id_custom']] as $order_item)
			{
				$orders_qty += $order_item['quantity'];
				$orders[$i]['quantity'] = $order_item['quantity'];
				$product = $mDB->GetItemByCode('product', $order_item['id_product']);
				
				# Себестоимость товара
				$orders[$i]['product_cost'] = $product_cost += $product['cost_price'] * $order_item['quantity'];

				# Подсчёт прибыли без учёта отчислений
				$orders[$i]['profit'] = $profit = $order['sum'] - $order['delivery_cost'] - $product_cost;
				
				if ($config['discounts']['fixed'] === true)
					$order_item_sum = $order_item['quantity'] * $order_item['price'] - $order_item['discount'];
				else
					$order_item_sum = $order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100);

				if (isset($partner) && !empty($partner))
				{
					$orders[$i]['partner_sum'] = $partner_sum = $order_item_sum * $order_item['partner_rate'] / 100;
					
					if ($config['partner']['levels'] === 2 && $partner['referer'] != '0')
					{
						$orders[$i]['partner2_sum'] = $partner2_sum = $order_item_sum * $config['partner']['percent']['level_2'] / 100;
					}
				}

				if ($config['author']['enabled'] === true)
				{
					if (isset($product['author']) && $product['author'] != '0')
					{
						$author = $mDB->GetItemById('author', $product['author']);
						
						if ($author)
							$orders[$i]['author_sum'] = $author_sum += ($order_item_sum - $partner_sum - $partner2_sum) * ($config['author']['percent'] / 100);
					}
				}

				if ($config['manager']['enabled'] === true)
				{
					if (isset($product['manager']) && $product['manager'] != '0')
					{
						$manager = $mDB->GetItemById('manager', $product['manager']);

						if ($manager)
							$orders[$i]['manager_sum'] = $manager_sum += ($order_item_sum - $partner_sum - $partner2_sum - $author_sum) * ($config['manager']['percent'] / 100);
					}
				}
			}
			
			# Прибыль за вычетом комиссионных отчислений
			$orders[$i]['profit_sum'] = $profit_sum = $order['sum'] - $manager_sum - $author_sum - $partner_sum - $partner2_sum;
		
			# Расчёт чистой прибыли
			if ($order['status'] == $config['statuses']['refund'][0])
			{
				# Стоимость возврата
				$refund = $mDB->GetItemsByParam('refund', 'id_custom', $order['id_custom']);
				$refund = ($refund) ? $refund[0]['refund'] : 0;
			
				$orders[$i]['real_profit'] = 0 - $ads - $order['delivery_cost'] - $refund;
			}
			else
				$orders[$i]['real_profit'] = $profit_sum - $ads - $order['delivery_cost'] - $product_cost;
		}
		
		$orders_count = count($orders);
		
		# Подсчёт всех заказов
		$params = array('status !=');
		$values = array($config['statuses']['deleted'][0]);
		$search_type = array('AND');
		
		$all_orders = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);
		
		# Всего заказов
		$all_orders = count($all_orders);
		
		# Определение статуса заказов
		foreach ($orders as $i => $order)
			$orders[$i]['status'] = $config['statuses'][$order['status']];

		$current_category['code'] = '';

		$statuses = $config['statuses'];

		include_once dirname(__FILE__) . '/../design/adminReport_1.tpl.php';
	}
}
# Вывод списка заказов
else
{
	# Количество заказов в списке
	if (isset($_SESSION['ordersList']))
		$ordersList = $_SESSION['ordersList'];
	else
		$ordersList = $config['admin']['ordersList'];

	# Выбор всех заказов
	if ($ordersList === 'all')
	{
		$orders = $mOrders->GetAllOrders($config['statuses'], true);
		rsort($orders);
	}
	# Выбор всех заказов с учётом пагинации
	elseif ($ordersList !== 'all')
	{
		# Выборка данных
		$navi = $mOrders->Paginate($navi['page'], $ordersList, $config['statuses'], true);
		$orders = $mOrders->GetPaginatedList($navi['start'], $ordersList, $config['statuses'], true);
	}
	
	# Подсчёт всех заказов
	$params[] = 'status !=';
	$values[] = $config['statuses']['deleted'][0];
	$search_type[] = 'AND';
	
	$all_orders = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);
	
	# Всего заказов
	$all_orders = count($all_orders);

	# Выбор позиций товаров
	$orders_items = array();
	foreach ($orders as $order)
		$order_items[$order['id_custom']] = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
		
	# Подсчёты
	$orders_qty = 0;
	foreach ($orders as $i => $order)
	{
		$date_stat = date("Y-m-d", strtotime($order['date']));
		$date_next = date("Y-m-d", strtotime($order['date']) + 24 * 3600);
		
		if ($config['yandex']['enabled'] === true)
			include dirname(__FILE__) . '/../modules/C_Yandex.inc.php';
		
		# Выбор заказов за день
		$params = array ('date >=' , 'date <=');
		$values = array ("'$date_stat'" , "'$date_next'");
		$search_type = array('AND', 'AND');
		
		foreach ($config['statuses']['fail'] as $value)
		{
			$params[] = 'status !=';
			$values[] = $value;
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

		$orders_by_day = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);
		$count_orders_by_day = (count($orders_by_day) == 0) ? 1 : count($orders_by_day);

		# Подсчёт стоимости рекламы
		if ($config['yandex']['enabled'] === true)
			$orders[$i]['ads'] = $ads = $rate / $count_orders_by_day * $config['yandex']['currency_rate'];
		else
			$orders[$i]['ads'] = $ads = 0;
			
		# Выборка данных по партнёру
		if ($config['partner']['enabled'] === true && $order['id_partner'] != '0')
			$partner = $mDB->GetItemById('partner', $order['id_partner']);
		else
			$partner = null;
			
		# Дефолтные значения
		$author_sum = $partner_sum = $partner2_sum = $manager_sum = $profit_sum = $product_cost = 0;
		$orders[$i]['quantity'] = $orders[$i]['profit'] = $orders[$i]['author_sum'] = $orders[$i]['manager_sum'] = $orders[$i]['partner_sum'] = $orders[$i]['partner2_sum'] = $orders[$i]['product_cost'] = 0;
		
		# Товары
		foreach ($order_items[$order['id_custom']] as $order_item)
		{
			$orders_qty += $order_item['quantity'];
			$orders[$i]['quantity'] = $order_item['quantity'];
			$product = $mDB->GetItemByCode('product', $order_item['id_product']);
			
			# Себестоимость товара
			$orders[$i]['product_cost'] = $product_cost += $product['cost_price'] * $order_item['quantity'];

			# Подсчёт прибыли без учёта отчислений
			$orders[$i]['profit'] = $profit = $order['sum'] - $order['delivery_cost'] - $product_cost;
			
			if ($config['discounts']['fixed'] === true)
				$order_item_sum = $order_item['quantity'] * $order_item['price'] - $order_item['discount'];
			else
				$order_item_sum = $order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100);
			
			if (isset($partner) && !empty($partner))
			{
				$orders[$i]['partner_sum'] = $partner_sum = $order_item_sum * ($order_item['partner_rate'] / 100);
				
				if ($config['partner']['levels'] === 2 && $partner['referer'] != '0')
				{
					$orders[$i]['partner2_sum'] = $partner2_sum = $order_item_sum * $config['partner']['percent']['level_2'] / 100;
				}
			}

			if ($config['author']['enabled'] === true)
			{
				if (isset($product['author']) && $product['author'] != '0')
				{
					$author = $mDB->GetItemById('author', $product['author']);
					
					if ($author)
						$orders[$i]['author_sum'] = $author_sum += ($order_item_sum - $partner_sum - $partner2_sum) * ($config['author']['percent'] / 100);
				}
			}

			if ($config['manager']['enabled'] === true)
			{
				if (isset($product['manager']) && $product['manager'] != '0')
				{
					$manager = $mDB->GetItemById('manager', $product['manager']);

					if ($manager)
						$orders[$i]['manager_sum'] = $manager_sum += ($order_item_sum - $partner_sum - $partner2_sum - $author_sum) * ($config['manager']['percent'] / 100);
				}
			}
		}
		
		# Прибыль за вычетом комиссионных отчислений
		$orders[$i]['profit_sum'] = $profit_sum = $order['sum'] - $manager_sum - $author_sum - $partner_sum - $partner2_sum;
	
		# Расчёт чистой прибыли
		if ($order['status'] == $config['statuses']['refund'][0])
		{
			# Стоимость возврата
			$refund = $mDB->GetItemsByParam('refund', 'id_custom', $order['id_custom']);
			$refund = ($refund) ? $refund[0]['refund'] : 0;
		
			$orders[$i]['real_profit'] = 0 - $ads - $order['delivery_cost'] - $refund;
		}
		else
			$orders[$i]['real_profit'] = $profit_sum - $ads - $order['delivery_cost'] - $product_cost;
	}

	$orders_count = count($orders);
	
	# Определение статуса заказов
	foreach ($orders as $i => $order)
		$orders[$i]['status'] = $config['statuses'][$order['status']];
		
	ob_start();
	include_once dirname(__FILE__) . '/../design/adminPagination.tpl.php';
	$pagination = ob_get_clean();
	
	# Статусы заказов
	$statuses = $config['statuses'];

	include_once dirname(__FILE__) . '/../design/adminReport_1.tpl.php';
}