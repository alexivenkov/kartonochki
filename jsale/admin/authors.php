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
	
# Определение текущей страницы
if (isset($_GET['page']))
    $navi['page'] = $_GET['page'];
else
    $navi['page'] = 1;

# Определение текущего заказа
if (isset($_GET['author']))
    $id_author = $_GET['author'];
	
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
	# Удаление заказа
	if (isset($_POST['author_delete']))
	{
		$mDB->DeleteItemById('author', $_POST['author_id']);
		header('Location: ' . $_SESSION['referer']);
		die;
	}
	# Отмена редактирования заказа
	elseif (isset($_POST['author_cancel']))
	{
		header('Location: ' . $_SESSION['referer']);
		die;
	}	
	# Редактирование
	elseif (isset($_POST['author_edit']))
	{
		$params = array ('email' => $_POST['author_email'], 'name' => $_POST['author_name'], 'payment' => $_POST['author_payment']);
		
		if ($_POST['author_id'] == '')
			# Создание автора
			$mDB->CreateItem('author', $params);
		else
			# Редактирование автора
			$mDB->EditItemById('author', $params, $_POST['author_id']);
		
		header('Location: ' . $_SESSION['this_page']);
	}
	# Выплата 
	elseif (isset($_POST['author_payment']))
	{
		$params = array ('paid' => $_POST['for_payment'] + $_POST['paid']);
		$mDB->EditItemById('author', $params, $_POST['author_id']);
		header('Location: ' . $_SESSION['this_page']);
	}
}
else
{
	$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);
	if (isset($id_author))
	{
		if ($id_author != 'new')
		{
			# Выборка данных по автору из БД
			$author = $mDB->GetItemByID('author', $id_author);
			
			if ($author['paid'] == '')
				$author['paid'] = '0.00';

			# Подсчёт статистики
			$products = $mDB->GetItemsByParam('product', 'author', $author['id_author']);
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
				
		}
		else
		{
			$author = array ('id_author' => '', 'name' => '', 'email' => '', 'payment' => '', 'paid' => '0.00');
			$products = $all_orders = array();
			$paid_sum = $paid_count = $total_count = 0;
		}

		# Вывод дизайна
		include_once dirname(__FILE__) . '/_menu.php';
		include_once dirname(__FILE__) . '/../design/adminAuthorView.tpl.php';
	}
	else
	{
		# Выборка данных по партнёрам из БД
		if (isset($_GET['pay']))
		{
			$authors = $mDB->GetAllItems('author');
		}
		else
		{
			$navi = $mDB->Paginate('author', $navi['page'], $config['admin']['authorsList']);
			$authors = $mDB->GetPaginatedList('author', $navi['start'], $config['admin']['authorsList']);
		}

		# Подсчёт статистики
		foreach ($authors as $key => $author)
		{
			$total_count = $paid_count = $paid_sum = 0;

			# Все привязанные товары
			$products = $mDB->GetItemsByParam('product', 'author', $author['id_author']);

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
					foreach ($orders as $order)
					{
						if (in_array($order['status'], $success_statuses))
						{
							$paid_count++;
							$partner_sum = $partner2_sum = 0;
							
							foreach ($order_items as $item)
							{
								if ($item['id_custom'] == $order['id_custom'])
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
									
									$paid_sum += $real_paid_sum - $partner_sum - $partner2_sum;
								}
							}
						}
					}
				}
			}

			$authors[$key]['products'] = count($products);
			$authors[$key]['total_count'] = $total_count;
			$authors[$key]['paid_count'] = $paid_count;
			$authors[$key]['paid_sum'] = number_format( $paid_sum * $config['author']['percent'] / 100, 2, '.', '');
		}
		
		ob_start();
		include_once dirname(__FILE__) . '/../design/adminPagination.tpl.php';
		$pagination = ob_get_clean();
		
		# Вывод дизайна
		include_once dirname(__FILE__) . '/_menu.php';
		if (isset($_GET['pay']))
			include_once dirname(__FILE__) . '/../design/adminAuthorsPay.tpl.php';
		else
			include_once dirname(__FILE__) . '/../design/adminAuthorsList.tpl.php';
	}
}