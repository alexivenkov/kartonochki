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
if (isset($_GET['partner']))
    $id_partner = $_GET['partner'];
	
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
	if (isset($_POST['partner_delete']))
	{
		$mDB->DeleteItemById('partner', $_POST['partner_id']);
		header('Location: ' . $_SESSION['referer']);
		die;
	}
	# Отмена редактирования заказа
	elseif (isset($_POST['partner_cancel']))
	{
		header('Location: ' . $_SESSION['referer']);
		die;
	}	
	# Выплата 
	elseif (isset($_POST['partner_payment']))
	{
		$params = array ('paid' => $_POST['for_payment'] + $_POST['paid']);
		$mDB->EditItemById('partner', $params, $_POST['partner_id']);
		header('Location: ' . $_SESSION['this_page']);
	}
}
else
{
	$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);
	if (isset($id_partner))
	{
		# Выборка данных по партнёру из БД
		$partner = $mDB->GetItemByID('partner', $id_partner);

		# Подсчёт статистики
		$orders = $mDB->GetItemsByParam('custom', 'id_partner', $id_partner);
		$total_count = $paid_count = $paid_sum = $commission = 0;
		if (is_array($orders))
		{
			foreach ($orders as $i => $order)
			{
				$total_count++;
				$orders[$i]['items'] = $order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
				
				if (in_array($order['status'], $success_statuses))
				{
					$paid_count++;
					
					$order_item_sum = $order_commission = 0;
					foreach ($order_items as $order_item)
					{
						if ($config['discounts']['fixed'] === true)
							$paid_sum += $order_item_sum = $order_item['quantity'] * $order_item['price'] - $order_item['discount'];
						else
							$paid_sum += $order_item_sum = $order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100);
						
						$order_commission += $order_item_sum * $order_item['partner_rate'] / 100;
					}
					$commission += $order_commission;
					$orders[$i]['commission'] = $order_commission;
				}
				else
					$orders[$i]['commission'] = 0;
			}
		}

		$referers = $mDB->GetItemsByParam('partner', 'referer', $id_partner);
		$referers_sum = $referers_count = 0;
		if (is_array($referers))
		{
			foreach ($referers as $referer)
			{
				$referers_count++;
				$referers_sum += $referer['paid'];
			}
		}
		$referers_profit = number_format($referers_sum * $config['partner']['percent']['level_2'] / 100, 2, '.', '');
			
		# Вывод дизайна
		include_once dirname(__FILE__) . '/_menu.php';
		include_once dirname(__FILE__) . '/../design/adminPartnerView.tpl.php';
	}
	else
	{
		# Выборка данных по партнёрам из БД
		if (isset($_GET['pay']))
		{
			$partners = $mDB->GetAllItems('partner');
		}
		else
		{
			$navi = $mDB->Paginate('partner', $navi['page'], $config['admin']['partnersList']);
			$partners = $mDB->GetPaginatedList('partner', $navi['start'], $config['admin']['partnersList']);
		}
		
		# Подсчёт статистики
		foreach ($partners as $key => $partner)
		{
			$orders = $mDB->GetItemsByParam('custom', 'id_partner', $partner['id_partner']);
			$total_count = $paid_count = $paid_sum = $commission = 0;
			if (is_array($orders))
			{
				foreach ($orders as $i => $order)
				{
					$orders[$i]['items'] = $order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
					$total_count++;

					if (in_array($order['status'], $success_statuses))
					{
						$paid_count++;
						
						$order_item_sum = 0;
						$order_commission = 0;
						foreach ($order_items as $order_item)
						{
							if ($config['discounts']['fixed'] === true)
								$paid_sum += $order_item_sum = $order_item['quantity'] * $order_item['price'] - $order_item['discount'];
							else
								$paid_sum += $order_item_sum = $order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100);
							
							$order_commission += $order_item_sum * $order_item['partner_rate'] / 100;
						}
						$commission += $order_commission;
						$orders[$i]['commission'] = $order_commission;
					}
				}
			}
			$partners[$key]['total_count'] = $total_count;
			$partners[$key]['paid_count'] = $paid_count;
			$partners[$key]['paid_sum'] = $commission;

			$referers = $mDB->GetItemsByParam('partner', 'referer', $partner['id_partner']);
			$referers_sum = $referers_count = 0;
			if (is_array($referers))
			{
				foreach ($referers as $referer)
				{
					$referers_count++;
					$referers_sum += $referer['paid'];
				}
			}
			
			$partners[$key]['referers_sum'] = number_format($referers_sum * $config['partner']['percent']['level_2'] / 100, 2, '.', '');
			$partners[$key]['referers_count'] = $referers_count;
		}
		
		ob_start();
		include_once dirname(__FILE__) . '/../design/adminPagination.tpl.php';
		$pagination = ob_get_clean();
		
		# Вывод дизайна
		include_once dirname(__FILE__) . '/_menu.php';
		if (isset($_GET['pay']))
			include_once dirname(__FILE__) . '/../design/adminPartnersPay.tpl.php';
		else
			include_once dirname(__FILE__) . '/../design/adminPartnersList.tpl.php';
	}
}