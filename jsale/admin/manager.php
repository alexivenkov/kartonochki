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

# Кодировка
header('Content-type: text/html; charset=' . $config['encoding']);

# Формирование GET запроса (на случай PHP как CGI)
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

if ($config['database']['enabled'] == false)
	$message = 'Использование базы данных отключено в настройках. Админка в данном случае бесполезна и будет выдавать ошибки.';

# Определение текущего заказа
if (isset($_SESSION['id_manager']))
    $id_manager = $_SESSION['id_manager'];
else
	die;
	
# Сохранение прошлой и текущей страницы
if ($_SERVER['REQUEST_METHOD'] != 'POST')
{
	$_SESSION['referer'] = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$_SESSION['this_page'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
if (!isset($_SESSION['referer']) && isset($_SESSION['this_page']))
	$_SESSION['referer'] = $_SESSION['this_page'];

# Подключение модуля работы с базой данных.
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
}
else
{
	$success_statuses = array_merge($config['statuses']['confirmed'], $config['statuses']['sent'], $config['statuses']['delivered'], $config['statuses']['success'], $config['statuses']['refund'], $config['statuses']['fail']);
	if (isset($id_manager))
	{
		if ($id_manager != 'new')
		{
			# Выборка данных по автору из БД
			$manager = $mDB->GetItemByID('manager', $id_manager);
			
			if ($manager['paid'] == '')
				$manager['paid'] = '0.00';

			if ($config['manager']['type'] == 'product')
			{
				# Подсчёт статистики
				$products = $mDB->GetItemsByParam('product', 'manager', $manager['id_manager']);
				$total_count = $paid_count = $paid_sum = 0;
				$all_orders = array();
				
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
							
							# Учёт отчислений партнёру
							if (in_array($order['status'], $success_statuses))
							{
								$paid_count++;
								$partner_sum = $partner2_sum = $author_sum = 0;

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

								# Учёт отчислений автору
								if ($config['author']['enabled'] === true && isset($product['author']) && $product['author'] != '0')
								{
									$author = $mDB->GetItemById('author', $product['author']);
									
									if ($author)
										$author_sum = ($real_paid_sum - $partner_sum - $partner2_sum) * ($config['author']['percent'] / 100);
								}
								
								$paid_sum += $commission = $real_paid_sum - $partner_sum - $partner2_sum - $author_sum;
							}

							$orders[$id]['items'] = array($order_item);
							$orders[$id]['commission'] = number_format( $commission * ($config['manager']['percent'] / 100), 2, '.', '');
						}

						$all_orders = array_merge($all_orders, $orders);
					}
				}
				
				$paid_sum = number_format( $paid_sum * $config['manager']['percent'] / 100, 2, '.', '');
			}
			elseif ($config['manager']['type'] == 'order')
			{
				# Подсчёт статистики
				$total_count = $paid_count = $paid_sum = 0;
				$all_orders = array();
				
				# Все заказы с привязанным менеджером
				$all_orders = $mDB->GetItemsByParam('custom', 'id_manager', $manager['id_manager']);
				$total_count = count($all_orders);
				
				foreach ($all_orders as $id => $order)
				{
					$commission = 0;
							
					$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
					$paid_count++;
					$partner_sum = $partner2_sum = $author_sum = 0;

					foreach ($order_items as $order_item)
					{
						# Учёт отчислений партнёру
						if (in_array($order['status'], $success_statuses))
						{
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

							# Учёт отчислений автору
							if ($config['author']['enabled'] === true && isset($product['author']) && $product['author'] != '0')
							{
								$author = $mDB->GetItemById('author', $product['author']);
								
								if ($author)
									$author_sum = ($real_paid_sum - $partner_sum - $partner2_sum) * ($config['author']['percent'] / 100);
							}
						
							$paid_sum += $commission = $real_paid_sum - $partner_sum - $partner2_sum - $author_sum;
						}
						$all_orders[$id]['items'][] = $order_item;
					}
					$all_orders[$id]['commission'] = number_format( $commission * ($config['manager']['percent'] / 100), 2, '.', '');
				}
				
				$paid_sum = number_format( $paid_sum * $config['manager']['percent'] / 100, 2, '.', '');
			}
		}
		else
		{
			$manager = array ('id_manager' => '', 'name' => '', 'email' => '', 'password' => '', 'payment' => '', 'paid' => '0.00');
			$products = $all_orders = array();
			$paid_sum = $paid_count = $total_count = 0;
		}

		# Вывод дизайна
		include_once dirname(__FILE__) . '/_menu.php';
		include_once dirname(__FILE__) . '/../design/adminManagerView.tpl.php';
	}
}