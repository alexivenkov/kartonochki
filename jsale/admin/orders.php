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

# Настройки для электронных товаров
if (is_file(dirname(__FILE__) . '/../config2.inc.php'))
{
	$admin = true; # Маркер админки
	include_once dirname(__FILE__) . '/../config2.inc.php';
}

# Простейшая авторизация
include_once dirname(__FILE__) . '/../modules/M_Admin.inc.php';
$mAdmin = M_Admin::Instance();
if (is_file(dirname(__FILE__) . '/../modules/M_Files.inc.php'))
{
	include_once dirname(__FILE__) . '/../modules/M_Files.inc.php';
	$mFiles = M_Files::Instance();
}

session_start();
if (!$mAdmin->CheckLogin())
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

# Определение тега для фильтра
$tag = (isset($_GET['tag'])) ? $_GET['tag'] : 'all';
$order_tags = explode(',', $tag);
$domain = (isset($_GET['domain'])) ? $_GET['domain'] : 'all';
$order_domains = explode(',', $domain);
#var_dump($order_tags);
#var_dump($order_domains);

# Сохранение прошлой и текущей страницы
if ($_SERVER['REQUEST_METHOD'] != 'POST')
{
	if (isset($_SESSION['is_save']))
	{
		$_SESSION['this_page'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		unset($_SESSION['is_save']);
	}
	else
	{
		$_SESSION['referer'] = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$_SESSION['this_page'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
}
if (!isset($_SESSION['referer']) && isset($_SESSION['this_page']))
	$_SESSION['referer'] = $_SESSION['this_page'];

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	# Обновление ВСЕХ статусов посылок
	if (isset($_POST['update_all_tracks']))
	{
		# Отслеживание посылок
		if ($config['track']['enabled'] === true && is_file(dirname(__FILE__) . '/../modules/C_Track.inc.php'))
		{	
			$tracks = $track = array();
			
			# Выбор всех треков
			$all_tracks = $mDB->GetAllItems('track');

			# Перебор треков
			foreach ($all_tracks as $track)
			{
				# Заполняем массив $tracks
				$tracks[$track['id_custom']] = $track;
				
				# Если трек не пустой
				if ($track['track'] != '')
				{
					# Сохраняем данные во временные переменные
					$track_id = $track['track'];
					$id_track = $track['id_track'];
					$id = $track['id_custom'];
					
					# Подключаем модуль для отслеживания треков
					include dirname(__FILE__) . '/../modules/C_Track.inc.php';

					# Сохраняем последний статус
					$tracks[$track['id_custom']]['status'] = $track_status;
				}
				# Или удаляем трек и его статусы
				else
				{
					$mDB->DeleteItemsByParam('track', 'id_track', $track['id_track']);
					$mDB->DeleteItemsByParam('track_info', 'id_track', $track['id_track']);
				}
				
				# Если есть данные по треку, обновляем его статусы
				if (isset($track_data[$track['id_custom']]))
				{
					$mDB->DeleteItemsByParam('track_info', 'id_track', $id_track);
					foreach ($track_data[$track['id_custom']] as $track_item)
						$mDB->CreateItem('track_info', $track_item);
						
					$order = $mDB->GetItemsByParam('custom', 'id_custom', $track['id_custom']);
					if ($order)
						$order = $order[0];

					$id_order = $track['id_custom'];
						
					# Автоматическая смена статуса заказа
					if ($config['yandex']['status_change'] === true)
					{
						# Выборка статусов заказа и последнего статуса
						$orders_statuses = $mDB->GetItemsByParam('status', 'id_custom', $id_order);
						if (is_array($orders_statuses))
							sort($orders_statuses);
						else
							$orders_statuses = array();
						
						$statuses = array();
						foreach ($orders_statuses as $status)
							$statuses[] = $status['status'];
					
						# Вручение
						if (in_array('Вручение', $track_types) && !array_intersect($config['statuses']['success'], $statuses) && !array_intersect($config['statuses']['refund'], $statuses))
						{
							$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['success'][0]);
							$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['success'][0]);
						}
						# Доставлено
						elseif (in_array('Прибыло в место вручения', $track_operations) /*$track_status == 'Прибыло в место вручения'*/ && !in_array($config['statuses']['delivered'][0], $statuses))
						{
							$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['delivered'][0]);
							$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['delivered'][0]);
							
							# Подключение модуля отправки SMS уведомления
							if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php') && $config['sms']['enabled'] === true)
							{
								if ($config['sms']['trackDelivered2customer'] === true)
								{
									# Выбор заказа и его элементов
									$order = $mDB->GetItemById('custom', $id_order);
									$phone = $order['phone'];
								
									# Отправка SMS
									$sms_type = 'trackDelivered2customer';
									include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
								}
							}
						}
						# Возврат
						elseif (in_array('Возврат', $track_types) && !array_intersect($config['statuses']['refund'], $statuses))
						{
							$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['refund'][0]);
							$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['refund'][0]);
						}
						# Отправлено
						elseif (isset($order['status']) && in_array($order['status'], array(0, 1)) && !array_intersect($config['statuses']['success'], $statuses) && !array_intersect($config['delivered']['success'], $statuses) && !array_intersect($config['refund']['success'], $statuses) && !array_intersect(array(1, 2), $statuses))
						{
							$mDB->ChangeStatusByID('custom', $id_order, 2);
							$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), 2);
							
							# Подключение модуля отправки SMS уведомления
							if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php') && $config['sms']['enabled'] === true)
							{
								if ($config['sms']['trackSent2customer'] === true)
								{
									# Выбор заказа и его элементов
									$order = $mDB->GetItemById('custom', $id_order);
									$phone = $order['phone'];
								
									# Отправка SMS
									$sms_type = 'trackSent2customer';
									include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
								}
							}
						}
					}
				}
			}
		}

		header('Location: ' . $_SESSION['this_page']);
		die;
	}
	# Обновление статусов посылок
	elseif (isset($_POST['update_tracks']))
	{
		# Отслеживание посылок
		if ($config['track']['enabled'] === true && is_file(dirname(__FILE__) . '/../modules/C_Track.inc.php'))
		{
			$tracks = $track = array();
			$tracks_for_update = (!empty($_POST['id_order'])) ? $_POST['id_order'] : array();
			$orders = $mDB->GetAllItems('custom');

			foreach ($orders as $order)
			{
				if (in_array($order['id_custom'], $tracks_for_update))
				{
					$track = $mDB->GetItemsByParam('track', 'id_custom', $order['id_custom']);

					if ($track && $track[0]['track'] != '')
					{
						$tracks[$order['id_custom']] = $track[0];
						
						if ($track[0]['track'] != '')
						{
							$track_id = $track[0]['track'];
							$id_track = $track[0]['id_track'];
							$id = $order['id_custom'];
							
							include dirname(__FILE__) . '/../modules/C_Track.inc.php';
							
							$tracks[$order['id_custom']]['status'] = $track_status;
						}
						
						if (isset($track_data[$order['id_custom']]))
						{
							$mDB->DeleteItemsByParam('track_info', 'id_track', $id_track);
							foreach ($track_data[$order['id_custom']] as $track)
								$mDB->CreateItem('track_info', $track);
								
							$id_order = $order['id_custom'];
							
							# Автоматическая смена статуса заказа
							if ($config['yandex']['status_change'] === true)
							{
							
								# Выборка статусов заказа и последнего статуса
								$orders_statuses = $mDB->GetItemsByParam('status', 'id_custom', $id_order);
								if (is_array($orders_statuses))
									sort($orders_statuses);
								else
									$orders_statuses = array();
								
								$statuses = array();
								foreach ($orders_statuses as $status)
									$statuses[] = $status['status'];
							
								# Вручение
								if (in_array('Вручение', $track_types) && !array_intersect($config['statuses']['success'], $statuses) && !array_intersect($config['statuses']['refund'], $statuses))
								{
									$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['success'][0]);
									$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['success'][0]);
								}
								# Доставлено
								elseif (in_array('Прибыло в место вручения', $track_operations) /*$track_status == 'Прибыло в место вручения'*/ && !array_intersect($config['statuses']['delivered'], $statuses))
								{
									$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['delivered'][0]);
									$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['delivered'][0]);
									
									# Подключение модуля отправки SMS уведомления
									if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php') && $config['sms']['enabled'] === true)
									{
										if ($config['sms']['trackDelivered2customer'] === true)
										{
											# Выбор заказа и его элементов
											$order = $mDB->GetItemById('custom', $id_order);
											$phone = $order['phone'];
										
											# Отправка SMS
											$sms_type = 'trackDelivered2customer';
											include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
										}
									}
								}
								# Возврат
								elseif (in_array('Возврат', $track_types) && !array_intersect($config['statuses']['refund'], $statuses))
								{
									$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['refund'][0]);
									$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['refund'][0]);
								}
								# Отправлено
								elseif (isset($order['status']) && in_array($order['status'], array(0, 1)) && !array_intersect($config['statuses']['success'], $statuses) && !array_intersect($config['delivered']['success'], $statuses) && !array_intersect($config['refund']['success'], $statuses) && !array_intersect(array(1, 2), $statuses))
								{
									$mDB->ChangeStatusByID('custom', $id_order, 2);
									$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), 2);
									
									# Подключение модуля отправки SMS уведомления
									if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php') && $config['sms']['enabled'] === true)
									{
										if ($config['sms']['trackSent2customer'] === true)
										{
											# Выбор заказа и его элементов
											$order = $mDB->GetItemById('custom', $id_order);
											$phone = $order['phone'];
										
											# Отправка SMS
											$sms_type = 'trackSent2customer';
											include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
										}
									}
								}
							}
						}
					}
				}
			}
		}

		header('Location: ' . $_SESSION['this_page']);
		die;
	}
	# Вывод на печать
	elseif (isset($_POST['print_1']) || isset($_POST['print_2']) || isset($_POST['print_3']) || isset($_POST['print_4']) || isset($_POST['print_5']))
	{
		if (isset($_POST['id']))
		{
			$_SESSION['print'] = array();
			foreach ($_POST['id'] as $key => $id_custom)
			{
				# Собираем данные
				$order = $mDB->GetItemsByParam('custom', 'id_custom', $id_custom);
				$order = $order[0];
				
				$sum = $order['sum'];
				$sum = number_format($sum, 2, '.', '');
				$customer_fio = $order['lastname'] . ' ' . $order['name'] . ' ' . $order['fathername'];
				$customer_address1 = $order['region'] . ' ' . $order['city'];
				$customer_address2 = $order['address'];
				$customer_zip = $order['zip'];
				$customer_phone = $order['phone'];
				
				if (isset($_POST['print_1']))
				{
					# Подключаем шаблон
					ob_start();
					include dirname(__FILE__) . '/../design/printForm1.tpl.php';
					$_SESSION['print'][$id_custom] = ob_get_clean();
				}
				
				if (isset($_POST['print_3']))
				{
					# Подключаем шаблон
					ob_start();
					include dirname(__FILE__) . '/../design/printForm3.tpl.php';
					$_SESSION['print'][$id_custom] = ob_get_clean();
				}
				
				if (isset($_POST['print_4']))
				{
					# Подключаем шаблон
					ob_start();
					include dirname(__FILE__) . '/../design/printForm4.tpl.php';
					$_SESSION['print'][$id_custom] = ob_get_clean();
				}
				
				if (isset($_POST['print_5']))
				{
					$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $id_custom);
					
					# Подключаем шаблон
					ob_start();
					include dirname(__FILE__) . '/../design/printForm5.tpl.php';
					$_SESSION['print'][$id_custom] = ob_get_clean();
				}
				
				# Готовим данные
				$print_temp[$key]['sum'] = $order['sum'];
				$print_temp[$key]['fio'] = $order['lastname'] . ' ' . $order['name'] . ' ' . $order['fathername'];
				$print_temp[$key]['address'] = $order['region'] . ' ' . $order['city'] . ' ' . $order['address'];
				$print_temp[$key]['zip'] = $order['zip'];
				$print_temp[$key]['phone'] = $order['phone'];
			}
			
			if (isset($_POST['print_2']))
			{
				# Подключаем шаблон
				$print_temp = array_chunk($print_temp, 4);
				foreach ($print_temp as $key2 => $prints)
				{
					ob_start();
					include dirname(__FILE__) . '/../design/printForm2.tpl.php';
					$_SESSION['print']['_' . $key2] = ob_get_clean();
				}
			}
			else
				unset($print_temp);
		}
		
		header('Location: ' . $_SESSION['this_page']);
		die;
	}
	# Фильтр
	elseif (isset($_POST['search_submit']))
	{
		$pre_search = (isset($_POST['search']))?$_POST['search']:array();
		$enabled = array_unique(array_keys($pre_search));
		$search_type = array();
		foreach ($enabled as $field)
		{
			if (isset($pre_search[$field]) && (is_array($pre_search[$field]) || !preg_match('|^(\s*$)|', $pre_search[$field])))
			{
				if ($field == 'product' )
				{
					foreach ($pre_search['product'] as $key => $val)
					{
						if (!empty($val))
						{
							$prod_params[] = '`' . $key . '` =';
							$prod_values[] = '\'' . $val . '\'';
							$prod_search_type[] = 'AND';
						}
					}
					if (isset($prod_params) && !empty($prod_params))
					{
						$tmp_params = $mDB->SearchItemsByParamArray('custom_item', $prod_params, $prod_values, $prod_search_type);

						$i = 0;
						foreach ($tmp_params as $key => $val)
						{
							$pre = ($i == 0)? ' ( ': '';
							if($i < count($tmp_params)-1)
							{
								$search_type[] = 'OR';
								$app = '';
							}
							else
							{
								$search_type[] = 'AND';
								$app = ' ) ';
							}
							$params[] = $pre . '`id_custom` =';
							$values[] = '\'' . $val['id_custom'] . '\'' . $app;

							$i++;
						}

						if (count($tmp_params) == 0)
						{
							$params[] = '`id_custom` =';
							$values[] = '\'false_product_dummy_string_for_search_result_prevention\'';
							$search_type[] = 'AND';
						}
					}
					else
					{
						unset($pre_search['product']);
					}
				}
				elseif ($field == 'track' )
				{
					$tracks = $mDB->GetItemsByParamLike('track', 'track', $pre_search['track']);
					
					foreach ($tracks as $track)
					{
						$params[] = '`id_custom` = ';
						$values[] = '\'' . $track['id_custom'] . '\'';
						$search_type[] = 'OR';
					}
				}
				elseif ($field == 'qty_items' )
				{
					$search_qty_items = intval($pre_search['qty_items']);
					
					$all_orders = $mDB->GetAllItems('custom');
					
					foreach ($all_orders as $i => $order)
					{
						$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
						
						if (count($order_items) == $search_qty_items)
						{
							$params[] = '`id_custom` = ';
							$values[] = '\'' . $order['id_custom'] . '\'';
							$search_type[] = 'OR';
						}
					}
						
					$pre_search['qty_items'] = $search_qty_items;
				}
				elseif ($field == 'date_from')
				{
					$params[] = '`date` >= ';
					$date_from = date("Y-m-d H:i:s", strtotime($pre_search['date_from']));
					$values[] = '\'' . $date_from . '\'';
					$search_type[] = 'AND';
				}
				elseif ($field == 'date_to')
				{
					$params[] = '`date` <=';
					$date_to =  date("Y-m-d H:i:s", strtotime($pre_search['date_to']) + 24 * 3600);
					$values[] = '\'' . $date_to . '\'';
					$search_type[] = 'AND';
				}
				elseif (preg_match('|(.*)_from|', $field, $tmp))
				{
					$tmp = $tmp[1];
					if (isset($pre_search[$tmp . '_from']) && preg_match('|[0-9]+|', $pre_search[$tmp . '_from']))
					{
						$params[] = '`' . $tmp . '` >= ';
						$values[] = number_format($pre_search[$tmp . '_from'], 2, '.', '');
					}
					$search_type[] = 'AND';
					unset($tmp);
				}
				elseif (preg_match('|(.*)_to|', $field, $tmp))
				{
					$tmp = $tmp[1];
					if (isset($pre_search[$tmp . '_to']) && preg_match('|[0-9]+|', $pre_search[$tmp . '_to']))
					{
						$params[] = '`' . $tmp . '` <= ';
						$values[] = number_format($pre_search[$tmp . '_to'], 2, '.', '');
					}
					$search_type[] = 'AND';
					unset($tmp);
				}
				elseif ($field == 'status' || $field == 'payment' || $field == 'delivery')
				{
					$i = 0;
					foreach ($pre_search[$field] as $num => $stat)
					{
						$pre = ($i == 0)? ' ( ': '';
						if (count($pre_search[$field])>1 && $i < count($pre_search[$field])-1)
						{
							$search_type[] = 'OR';
							$app = '';
						}
						else
						{
							$search_type[] = 'AND';
							$app = ' ) ';
						}
						$params[] = $pre . '`' . $field . '` =';
						$values[] = '\'' . $stat . '\'' . $app;

						$i++;
					}
				}
				elseif ($field != 'phone' && $field != 'zip' && $field != 'id_custom')
				{
					$params[] = '`' . $field . '` LIKE';
					$values[] = '\'%%' . $pre_search[$field] . '%%\'';
					$search_type[] = 'AND';
				}
				else
				{
					$params[] = '`' . $field . '` =';
					$values[] = '\'' . $pre_search[$field] . '\'';
					$search_type[] = 'AND';
				}
			}
		}
		if (isset($values) && count($values)>0)
		{
			$orders = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);
			
			if (isset($orders) && count($orders) > 0)
			{
				$pre_search['total_sum'] = 0;
				foreach ($orders as $i => $order)
				{
					$pre_search['total_sum'] += $order['sum'];

					foreach ($config['statuses'] as $j => $status)
					{
						if (!isset($pre_search['status_sum'][$j]))
							$pre_search['status_sum'][$j] = 0;
						if (!isset($pre_search['status_num'][$j]))
							$pre_search['status_num'][$j] = 0;
						if ($order['status'] == $j)
						{
							$pre_search['status_sum'][$j] += $order['sum'];
							$pre_search['status_sum'][$j] = number_format($pre_search['status_sum'][$j], 2, '.', '');

							$pre_search['status_num'][$j]++;
						}
					}
				}
				$pre_search['total_sum'] = number_format($pre_search['total_sum'], 2, '.', '');
				$_SESSION['success_message'] = 'Найдено результатов: ' . count($orders) . ' на сумму ' . $pre_search['total_sum'] . ' ' . $config['currency'];
			}
			elseif (!isset($orders) || count($orders) == 0)
				$_SESSION['search_massage'] = 'Ничего не найдено';
		}
		else
			$_SESSION['search_massage'] = 'Введите данные для поиска';

		$_SESSION['search']['res'] = (isset($orders))? $orders : null;
		$_SESSION['search']['form'] = $pre_search;

		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php');
		die;
	}
	# Удаление заказа
	elseif (isset($_POST['order_delete']))
	{
		$mDB->ChangeStatusById('custom', $_POST['order_id'], $config['statuses']['deleted'][0]);
		$mDB->SaveStatus($_POST['order_id'], date('Y-m-d H:i:s'), $config['statuses']['deleted'][0]);
		header('Location: ' . $_SESSION['referer']);
		die;
	}
	# Отмена редактирования заказа
	elseif (isset($_POST['order_cancel']))
	{
		header('Location: ' . $_SESSION['referer']);
		die;
	}
	# Редактирование заказа
	elseif (isset($_POST['order_edit']) || isset($_POST['order_save']))
    {
		$order_sum = 0;
		
		# Красивая дата
		list($date, $time) = explode(' ', $_POST['order_date']);
		list($day, $month, $year) = explode('.', $date);
		list($hour, $minute, $second) = explode(':', $time);
		$_POST['order_date'] = "$year-$month-$day $hour:$minute:$second";
		unset($day, $month, $year, $date, $time, $hour, $minute, $second);
		
		# Оплата ЯКассой
		$order_payment_ym = (isset($_POST['order_payment_ym'])) ? $_POST['order_payment_ym'] : '';

		# Создание нового заказа
		if ($_POST['order_id'] == '')
		{
			if (!$id_order = $mOrders->CreateOrder($_POST['order_lastname'], $_POST['order_name'], $_POST['order_fathername'], $_POST['order_email'], $_POST['order_phone'], $_POST['order_zip'], $_POST['order_country'], $_POST['order_region'], $_POST['order_city'], stripslashes($_POST['order_address']), stripslashes($_POST['order_comment']), $_POST['order_payment'], '', $_POST['order_delivery'], $_POST['order_delivery_cost'], $_POST['order_date'], $order_sum, $_POST['order_status'], '', '', '', '', '', $order_payment_ym))
				die('<p>Данные не были сохранены. Проверьте корректность заполнения полей.</p>');
				
			$is_new_order = true;
		}

		# Запись статуса в БД
		if ($_POST['order_status'] != $_POST['order_last_status'] || $_POST['order_last_status'] == '')
			$mDB->SaveStatus($_POST['order_id'], date('Y-m-d H:i:s'), $_POST['order_status']);
		
        # Редактирование позиций заказа
        $orderItemID = (isset($_POST['orderItemID'])) ? $_POST['orderItemID'] : '';
		$orderItemProduct = (isset($_POST['orderItemProduct'])) ? $_POST['orderItemProduct'] : '';
        $orderItemName = (isset($_POST['orderItemName'])) ? $_POST['orderItemName'] : '';
        $orderItemQty = (isset($_POST['orderItemQty'])) ? $_POST['orderItemQty'] : '';
        $orderItemPrice = (isset($_POST['orderItemPrice'])) ? $_POST['orderItemPrice'] : '';
		$orderItemDiscount = (isset($_POST['orderItemDiscount'])) ? $_POST['orderItemDiscount'] : '';
		$orderItemUnit = (isset($_POST['orderItemUnit'])) ? $_POST['orderItemUnit'] : '';
        $orderItemSize = (isset($_POST['orderItemSize'])) ? $_POST['orderItemSize'] : '';
        $orderItemColor = (isset($_POST['orderItemColor'])) ? $_POST['orderItemColor'] : '';
        $orderItemParam = (isset($_POST['orderItemParam'])) ? $_POST['orderItemParam'] : '';
		
        if (!empty($orderItemID))
        {
            foreach ($orderItemID as $key => $order)
            {
                $mOrders->EditOrderItem($orderItemID[$key], $id_order, $orderItemProduct[$key], stripslashes($orderItemName[$key]), $orderItemQty[$key], $orderItemPrice[$key], $orderItemDiscount[$key], $orderItemUnit[$key], stripslashes($orderItemSize[$key]), stripslashes($orderItemColor[$key]), stripslashes($orderItemParam[$key]));

				if ($config['discounts']['fixed'] === true)
					$order_sum += $orderItemQty[$key] * ($orderItemPrice[$key] - $orderItemDiscount[$key]);
				else
					$order_sum += $orderItemQty[$key] * $orderItemPrice[$key] * (1 - $orderItemDiscount[$key] / 100);
            }
        }
		
		# Редактирование или добавление возвратов
		if (isset($_POST['order_refund_cost']))
		{
			if (empty($_POST['order_refund_cost']))
				$_POST['order_refund_cost'] = 0;
			
			if ($mDB->IssetItemByParam('refund', 'id_custom', $_POST['order_id']))
			{
				if ($_POST['order_refund_cost'] != 0)
				{
					$params = array (
						'refund' => $_POST['order_refund_cost']
					);
				
					$mDB->EditItemsByParam('refund', $params, 'id_custom', $_POST['order_id']);
				}
				else
					$mDB->DeleteItemsByParam('refund', 'id_custom', $_POST['order_id']);
			}
			else
			{
				if ($_POST['order_refund_cost'] != 0)
				{
					$params = array (
						'id_custom' => $_POST['order_id'],
						'refund' => $_POST['order_refund_cost']
					);
					$mDB->CreateItem('refund', $params);
				}
			}
		}
	
		# Добавление позиций заказа
		if (!empty($_POST['new_orderItemProduct']))
		{
            $new_orderItemProduct = $_POST['new_orderItemProduct'];
            $new_orderItemName = $_POST['new_orderItemName'];
            $new_orderItemQty = $_POST['new_orderItemQty'];
            $new_orderItemPrice = $_POST['new_orderItemPrice'];
			$new_orderItemDiscount = $_POST['new_orderItemDiscount'];
			$new_orderItemUnit = $_POST['new_orderItemUnit'];
            $new_orderItemSize = $_POST['new_orderItemSize'];
            $new_orderItemColor = $_POST['new_orderItemColor'];
            $new_orderItemParam = $_POST['new_orderItemParam'];

            if (!empty($new_orderItemProduct))
            {
                foreach ($new_orderItemProduct as $key => $orderItem)
                {
					if ($new_orderItemProduct[$key] != '')
					{
						# Выбираем товар из БД
						$product = $mDB->GetItemByCode('product', $new_orderItemProduct[$key]);
						
						# Добавляем товары комплекта
						if (isset($product) && is_array($product) && isset($product['bandle_products']) && $product['bandle_products'] != '')
						{
							$bandle_products = explode('|', $product['bandle_products']);
							foreach ($bandle_products as $bandle_product)
							{
								$product_for_add = $mDB->GetItemByParam('product', 'code', $bandle_product);
								$product_for_add['price'] = '0.00';
								$product_for_add['discount'] = '0';
								#array_push($products, $product_for_add);
								
								# Расчёт комиссии партнёра
								if ($config['partner']['enabled'] === true)
								{
									if ($config['partner']['rate_product'] === true)
										$partner_rate = (isset($product_for_add['partner_rate'])) ? $product_for_add['partner_rate'] : $config['partner']['percent']['level_1'];
									else
										$partner_rate = $config['partner']['percent']['level_1'];
								}
								else
									$partner_rate = 0;
								
								$mOrders->CreateOrderItem($id_order, $product_for_add['code'], $product_for_add['title'], $product_for_add['qty'], $product_for_add['price'], $product_for_add['discount'], $product_for_add['unit'], $product_for_add['param1'], $product_for_add['param2'], $product_for_add['param3'], $partner_rate);
							}
						}
						
						$size = (isset($new_orderItemSize[$key])) ? $new_orderItemSize[$key] : '';
						$color = (isset($new_orderItemColor[$key])) ? $new_orderItemColor[$key] : '';
						$param = (isset($new_orderItemParam[$key])) ? $new_orderItemParam[$key] : '';
						
						# Расчёт комиссии партнёра
						if ($config['partner']['enabled'] === true)
						{
							if ($config['partner']['rate_product'] === true)
								$partner_rate = (isset($product['partner_rate'])) ? $product['partner_rate'] : $config['partner']['percent']['level_1'];
							else
								$partner_rate = $config['partner']['percent']['level_1'];
						}
						else
							$partner_rate = 0;
					
						$mOrders->CreateOrderItem($id_order, $new_orderItemProduct[$key], $new_orderItemName[$key], $new_orderItemQty[$key], $new_orderItemPrice[$key], $new_orderItemDiscount[$key], $new_orderItemUnit[$key], $size, $color, $param, $partner_rate);
						
						if ($config['discounts']['fixed'] === true)
							$order_sum += $new_orderItemQty[$key] * ($new_orderItemPrice[$key] - $new_orderItemDiscount[$key]);
						else
							$order_sum += $new_orderItemQty[$key] * $new_orderItemPrice[$key] * (1 - $new_orderItemDiscount[$key] / 100);
					}
                }
            }
        }

		# Подсчёт суммы заказа
		$order_sum = $order_sum + $_POST['order_delivery_cost'];

        # Приведение суммы к нормальному виду
        $order_sum = number_format($order_sum, 2, '.', '');
		
		# Редактирование данных заказа - оригинальная настройка
		        if (!$mOrders->EditOrder($id_order,
            $_POST['order_lastname'],
            $_POST['order_name'],
            $_POST['order_fathername'],
            $_POST['order_email'],
            $_POST['order_phone'],
            $_POST['order_zip'],
            $_POST['order_country'],
            $_POST['order_region'],
            $_POST['order_city'],
            stripslashes($_POST['order_address']),
            stripslashes($_POST['order_comment']),
            $_POST['order_payment'],
            '', // todo
            $_POST['order_delivery'],
            $_POST['order_delivery_cost'],
            $_POST['order_date'],
            $order_sum,
            $_POST['order_status'],
            $_POST['order_admin_comment'],
            $order_payment_ym,
            $_POST['order_manager'],
            (float) $_POST['manager_bonus'],
            $_POST['pvz_address'])
        ) {
            die('<p>Данные не были сохранены. Проверьте корректность заполнения полей.</p>');
        }

		# Редактирование тегов
		if (isset($_POST['tags']) && is_array($_POST['tags']))
		{
			foreach ($_POST['tags'] as $key => $tag)
				$_POST['tags'][$key] = '['.$tag.']';
			$tags = implode(',', $_POST['tags']);
			$params = array ('tags' => $tags);
			$mDB->EditItemById('custom', $params, $id_order);
		}
		else
		{
			$params = array ('tags' => '');
			$mDB->EditItemById('custom', $params, $id_order);
		}
		
		# Добавление трека посылки
		if ($config['track']['enabled'] === true && isset($_POST['order_track']))
		{
			$track = $mDB->GetItemsByParam('track', 'id_custom', $id_order);
			$track = ($track) ? $track[0] : false;
		
			$params = array (
				'id_custom' => $id_order,
				'track' => $_POST['order_track']
			);
		
			# Изменяем трек
			if ($track && !empty($_POST['order_track']))
			{
				# Редактирование записи
				$mDB->EditItemsByParam('track', $params, 'id_custom', $id_order);
				
				# Задаём данные для обновления статусов
				$track_id = $_POST['order_track'];
				$id_track = $track['id_track'];
				$id = $id_order;
				
				# Обновление статусов
				include dirname(__FILE__) . '/../modules/C_Track.inc.php';
			}
			# Удаляем трек
			elseif ($track && empty($_POST['order_track']))
			{
				$mDB->DeleteItemsByParam('track', 'id_track', $track['id_track']);
				$mDB->DeleteItemsByParam('track_info', 'id_track', $track['id_track']);
			}
			# Создаём новый трек
			elseif (!$track && !empty($_POST['order_track']))
			{
				# Создание трека
				$track_id = $_POST['order_track'];
				$id_track = $mDB->CreateItem('track', $params);
				$id = $id_order;
				
				# Обновление статусов
				include dirname(__FILE__) . '/../modules/C_Track.inc.php';
			}
			
			# Обновляем статусы треков
			if (isset($track_data[$id_order]))
			{
				$mDB->DeleteItemsByParam('track_info', 'id_track', $id_track);
				foreach ($track_data[$id_order] as $track)
					$mDB->CreateItem('track_info', $track);
				
				# Автоматическая смена статуса заказа
				if ($config['yandex']['status_change'] === true)
				{
					# Выборка статусов заказа и последнего статуса
					$orders_statuses = $mDB->GetItemsByParam('status', 'id_custom', $id_order);
					if (is_array($orders_statuses))
						sort($orders_statuses);
					else
						$orders_statuses = array();
					
					$statuses = array();
					foreach ($orders_statuses as $status)
						$statuses[] = $status['status'];
				
					# Вручение
					if (in_array('Вручение', $track_types) && !array_intersect($config['statuses']['success'], $statuses) && !array_intersect($config['statuses']['refund'], $statuses))
					{
						$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['success'][0]);
						$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['success'][0]);
					}
					# Доставлено
					elseif (in_array('Прибыло в место вручения', $track_operations) /*$track_status == 'Прибыло в место вручения'*/ && !in_array($config['statuses']['delivered'][0], $statuses))
					{
						$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['delivered'][0]);
						$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['delivered'][0]);
						
						# Подключение модуля отправки SMS уведомления
						if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php') && $config['sms']['enabled'] === true)
						{
							if ($config['sms']['trackDelivered2customer'] === true)
							{
								# Выбор заказа и его элементов
								$order = $mDB->GetItemById('custom', $id_order);
								$phone = $order['phone'];
							
								# Отправка SMS
								$sms_type = 'trackDelivered2customer';
								include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
							}
						}
					}
					# Возврат
					elseif (in_array('Возврат', $track_types) && !array_intersect($config['statuses']['refund'], $statuses))
					{
						$mDB->ChangeStatusByID('custom', $id_order, $config['statuses']['refund'][0]);
						$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['refund'][0]);
					}
					# Отправлено
					elseif (isset($order['status']) && in_array($order['status'], array(0, 1)) && !array_intersect($config['statuses']['success'], $statuses) && !array_intersect($config['delivered']['success'], $statuses) && !array_intersect($config['refund']['success'], $statuses) && !array_intersect(array(1, 2), $statuses))
					{
						$mDB->ChangeStatusByID('custom', $id_order, 2);
						$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), 2);
						
						# Подключение модуля отправки SMS уведомления
						if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php') && $config['sms']['enabled'] === true)
						{
							if ($config['sms']['trackSent2customer'] === true)
							{
								# Выбор заказа и его элементов
								$order = $mDB->GetItemById('custom', $id_order);
								$phone = $order['phone'];
							
								# Отправка SMS
								$sms_type = 'trackSent2customer';
								include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
							}
						}
					}
				}
			}
		}
		
		# Загрузка файла
		if (!empty($_FILES['order_upload_admin']) && !empty($_FILES['order_upload_admin']['tmp_name']) && $config['admin']['upload']['enabled'] === true)
		{
			# Подготовка загрузки
			$upload_file = array();
			$file_name = explode('.', $_FILES['order_upload_admin']['name']);
			$upload_file['name'] =  $id_order. '.' . end($file_name);
			$upload_file['tmp_name'] = $_FILES['order_upload_admin']['tmp_name'];
			$upload_file['size'] = $_FILES['order_upload_admin']['size'];
			$upload_file['type'] = $_FILES['order_upload_admin']['type'];
			$upload_file['error'] = $_FILES['order_upload_admin']['error'];
			
			$path = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $config['admin']['upload']['dir'] . '/';

			# Загрузка нового файла
			$mFiles->UploadFile($upload_file, $path, true);
			unset($upload_file);
		}
		
		# Добавление с список рассылки SmartResponder
		if ($config['smart']['enabled'] === true && is_file(dirname(__FILE__) . '/../modules/C_SmartResponder.inc.php'))
			include_once dirname(__FILE__) . '/../modules/C_SmartResponder.inc.php';
		
		# Подключение модуля отправки SMS уведомления
		if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php') && $config['sms']['enabled'] === true)
		{
			if ($config['sms']['sdekSent2customer']['enabled'] === true)
			{
				if ($_POST['order_status'] == $config['statuses']['sent'][0] && $_POST['order_delivery'] == $config['sms']['sdekSent2customer']['delivery'] && !empty($_POST['order_track']))
				{
					# Отправка SMS
					$sms_type = 'sdekSent2customer';
					$phone = $_POST['order_phone'];
					include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
				}
			}
			
			if ($config['sms']['status2customer'] === true && isset($_POST['send_sms']))
			{
				# Выбор заказа и его элементов
				$order = $mDB->GetItemById('custom', $id_order);
			
				# Отправка SMS
				$sms_type = 'status2customer';
				$phone = $order['phone'];
				include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
			}
		}

        # Отправка уведомления об изменении статуса покупателю
        if (isset($_POST['send_notice']))
        {
			# Выбор заказа и его элементов
			$order = $mDB->GetItemById('custom', $id_order);
			$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $id_order);
			
			# Уникальные настройки
			if ($order['config'] && is_file(dirname(__FILE__) . '/../config'.$order['config'].'.inc.php'))
			{
				$admin = true; # Маркер админки
				include_once dirname(__FILE__) . '/../config'.$order['config'].'.inc.php';
			}

			# Подключение модуля работы с письмами
			include_once dirname(__FILE__) . '/../modules/M_Email.inc.php';
			$mEmail = M_Email::Instance();

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
			$email = $order['email'];
            $address = $order['address'];
            $name = $order['name'];
			$lastname = $order['lastname'];
			$fathername = $order['fathername'];
			
			# Статусы успешного завершения заказа (по умолчанию "Оплачен" и "Доставлен")
			$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);

            # Подключение модуля оплаты (создание ссылок для оплаты)
			if (is_file(dirname(__FILE__) . '/../modules/C_Payment.php') && !in_array($order['status'], $success_statuses))
				include_once dirname(__FILE__) . '/../modules/C_Payment.php';
				
			# Подключение модуля работы с файлами (создание ссылки на скачивание)
            if ($config['download']['enabled'] === true && is_file(dirname(__FILE__) . '/../modules/C_Files.inc.php') && in_array($order['status'], $success_statuses))
                include_once dirname(__FILE__) . '/../modules/C_Files.inc.php';
			
			# Подстановка данных партнёра
			if ($order['id_partner'] != '0')
			{
				$partner = $mDB->GetItemById('partner', $order['id_partner']);
				$partner['commission'] = $order_sum * $config['partner']['percent']['level_1'] / 100;
			}
			else
				$partner = '';
			
			# Генерация хеш-строки
			$hash = ($config['admin']['upload']['enabled'] === true) ? $mEmail->GenerateHash($id_order, 'DOWNLOAD', $config['secretWord']) : '';

			# Подготовка текста письма
			$adminContent = $mEmail->PrepareChangeStatus($order['id_custom'], $order['email'], $order['lastname'], $order['name'], $order['fathername'], $order['phone'], $order['zip'], $order['country'], $order['region'], $order['city'], $order['address'], $order['comment'], $order_items, $order_sum, $payment, $order['payment_ym'], $delivery, $order['date'], $config, $status, $partner, true, $order['config'], $hash);
			$customerContent = $mEmail->PrepareChangeStatus($order['id_custom'], $order['email'], $order['lastname'], $order['name'], $order['fathername'], $order['phone'], $order['zip'], $order['country'], $order['region'], $order['city'], $order['address'], $order['comment'], $order_items, $order_sum, $payment, $order['payment_ym'], $delivery, $order['date'], $config, $status, $partner, null, $order['config'], $hash);

			# Подстановка номера заказа в тему письма
			$emailSubjectStatus = str_replace('№№', '№' . $id_order, $config['email']['subjectStatus']);
			
			# Отправка письма владельцу и покупателю
			$mEmail->SendEmail($order['email'], $config['email']['answer'], $emailSubjectStatus, $customerContent, $config['email']['answerName'], $config['encoding']);
			$mEmail->SendEmail($config['email']['receiver'], $config['email']['answer'], $emailSubjectStatus, $adminContent, $config['email']['answerName'], $config['encoding']);
        }
		
		if (isset($is_new_order))
		{
			header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php');
		}
		elseif (isset($_POST['order_last_status']) && $_POST['order_last_status'] == '' || isset($_POST['order_save']))
		{
			header('Location: ' . $_SESSION['this_page']);
			$_SESSION['is_save'] = true;
		}
		else
			header('Location: ' . $_SESSION['referer']);
        die;
    }
	# Множественная смена статусов заказов
	elseif (isset($_POST['change_status']))
	{
		if (isset($_POST['id']))
		{
			foreach ($_POST['id'] as $id_custom)
			{
				$mDB->ChangeStatusById('custom', $id_custom, $_POST['status']);
				$mDB->SaveStatus($id_custom, date('Y-m-d H:i:s'), $_POST['status']);
			}
		}
		
		header('Location: ' . $_SESSION['this_page']);
		die;
	}
	# Количество заказов в списке
	elseif (isset($_POST['ordersList']))
	{
		$_SESSION['ordersList'] = $_POST['ordersList'];
		
		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php');
		die;
	}
	# Обновление тегов
	if (isset($_POST['status']))
	{
		if (isset($_POST['tag']) || isset($_POST['domain']))
		{
			$tags = (isset($_POST['tag']) && !empty($_POST['tag'])) ? ((is_array($_POST['tag'])) ? implode(',', $_POST['tag']) : $_POST['tag']) : '';
			$domains = (isset($_POST['domain']) && !empty($_POST['domain'])) ? ((is_array($_POST['domain'])) ? implode(',', $_POST['domain']) : $_POST['domain']) : '';
			header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php?status=' . $_POST['status'] . '&tag=' . $tags . '&domain=' . $domains);
		}
		else
			header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php?status=' . $_POST['status']);
		die;
	}
}
else
{
    # Обработка GET запроса
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        # Удаление товара
        if (isset($_GET['delete_order_item']))
        {
            $mDB->DeleteItemById('custom_item', $_GET['delete_order_item']);
			
            header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php?order=' . $_GET['from_order']);
            die;
        }
		
		# Скачать файл
        if (isset($_GET['download_file']) && isset($_GET['dir']))
        {
			$path = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $_GET['dir'] . '/';

			# Убиваем скрипт, если кто-то пытается ввести кривой путь
			if (strstr('/', $_GET['download_file']) !== false)
				die;

			# Скачивание файла
			if ($mFiles->DownloadFile($_GET['download_file'], $path))
				die;
        }
		
        # Удаление файла
        if (isset($_GET['delete_file']) && isset($_GET['dir']))
        {
            $file = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $_GET['dir'] . '/' . $_GET['delete_file'];
            if (is_file($file))
                unlink($file);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die;
        }
		
		# Отправка письма об изменении статуса покупателю
        if (isset($_GET['send_order']))
        {
			# Выбор заказа и его элементов
			$order = $mDB->GetItemById('custom', $_GET['send_order']);
			$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $_GET['send_order']);
			
			# Уникальные настройки
			if ($order['config'] && is_file(dirname(__FILE__) . '/../config'.$order['config'].'.inc.php'))
			{
				$admin = true; # Маркер админки
				include_once dirname(__FILE__) . '/../config'.$order['config'].'.inc.php';
			}

			# Подключение модуля работы с письмами
			include_once dirname(__FILE__) . '/../modules/M_Email.inc.php';
			$mEmail = M_Email::Instance();

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
			$email = $order['email'];
            $address = $order['address'];
            $name = $order['name'];
			$lastname = $order['lastname'];
			$fathername = $order['fathername'];
			
			# Статусы успешного завершения заказа (по умолчанию "Оплачен" и "Доставлен")
			$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);

            # Подключение модуля оплаты (создание ссылок для оплаты)
			if (is_file(dirname(__FILE__) . '/../modules/C_Payment.php') && !in_array($order['status'], $success_statuses))
				include_once dirname(__FILE__) . '/../modules/C_Payment.php';
				
			# Подключение модуля работы с файлами (создание ссылки на скачивание)
            if ($config['download']['enabled'] === true && is_file(dirname(__FILE__) . '/../modules/C_Files.inc.php') && in_array($order['status'], $success_statuses))
                include_once dirname(__FILE__) . '/../modules/C_Files.inc.php';
			
			# Подстановка данных партнёра
			if ($order['id_partner'] != '0')
			{
				$partner = $mDB->GetItemById('partner', $order['id_partner']);
				$partner['commission'] = $order['sum'] * $config['partner']['percent']['level_1'] / 100;
			}
			else
				$partner = '';
				
			# Генерация хеш-строки
			$hash = ($config['admin']['upload']['enabled'] === true) ? $mEmail->GenerateHash($order['id_custom'], 'DOWNLOAD', $config['secretWord']) : '';

			# Подготовка текста письма
			$adminContent = $mEmail->PrepareChangeStatus($order['id_custom'], $order['email'], $order['lastname'], $order['name'], $order['fathername'], $order['phone'], $order['zip'], $order['country'], $order['region'], $order['city'], $order['address'], $order['comment'], $order_items, $order['sum'], $payment, $order['payment_ym'], $delivery, $order['date'], $config, $status, $partner, true, $order['config'], $hash);

			$customerContent = $mEmail->PrepareChangeStatus($order['id_custom'], $order['email'], $order['lastname'], $order['name'], $order['fathername'], $order['phone'], $order['zip'], $order['country'], $order['region'], $order['city'], $order['address'], $order['comment'], $order_items, $order['sum'], $payment, $order['payment_ym'], $delivery, $order['date'], $config, $status, $partner, null, $order['config'], $hash);

			# Подстановка номера заказа в тему письма
			$emailSubjectStatus = str_replace('№№', '№' . $_GET['send_order'], $config['email']['subjectStatus']);
			
			# Отправка письма владельцу и покупателю
			$mEmail->SendEmail($order['email'], $config['email']['answer'], $emailSubjectStatus, $customerContent, $config['email']['answerName'], $config['encoding']);
			$mEmail->SendEmail($config['email']['receiver'], $config['email']['answer'], $emailSubjectStatus, $adminContent, $config['email']['answerName'], $config['encoding']);
			
			header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php');
			die;
        }
		
		# Удаление заказа
		if (isset($_GET['delete_order']))
		{
			# Смена статуса
			$mDB->ChangeStatusById('custom', $_GET['delete_order'], $config['statuses']['deleted'][0]);

			# Запись статуса в БД
			$mDB->SaveStatus($_GET['delete_order'], date('Y-m-d H:i:s'), $config['statuses']['deleted'][0]);

			header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php');
			die;
		}
		
		# Удаление заказа
		if (isset($_GET['real_delete_order']))
		{
			$mDB->DeleteItemById('custom', $_GET['real_delete_order']);
			$mDB->DeleteItemsByParam('custom_item', 'id_custom',  $_GET['real_delete_order']);
			$mDB->DeleteItemsByParam('status', 'id_custom', $_GET['real_delete_order']);
			$mDB->DeleteItemsByParam('refund', 'id_custom', $_GET['real_delete_order']);
			
			if (is_file(dirname(__FILE__) . '/../modules/C_Track.inc.php'))
			{
				$track = $mDB->GetItemsByParam('track', 'id_custom', $_GET['real_delete_order']);
				if (isset($track[0]))
				{
					$mDB->DeleteItemsByParam('track', 'id_custom', $_GET['real_delete_order']);
					$mDB->DeleteItemsByParam('track_info', 'id_track', $track[0]['id_track']);
				}
			}
			
			header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php');
			die;
		}
    }
	
	# Теги заказов
	$tags = $mDB->GetAllItems('tag');

    # Вывод текущего ИЛИ нового заказа
	if (isset($id_order))
	{
		if ($id_order != 'new')
		{
			# Выбор заказа
			$order = $mDB->GetItemById('custom', $id_order);
			
			# Настройки для электронных товаров
			if ($order['config'] && is_file(dirname(__FILE__) . '/../config'.$order['config'].'.inc.php'))
			{
				$admin = true; # Маркер админки
				include_once dirname(__FILE__) . '/../config'.$order['config'].'.inc.php';
			}
			
			# Стоимость возврата, если есть
			$refund = $mDB->GetItemsByParam('refund', 'id_custom', $id_order);
			if ($refund)
				$order['refund_cost'] = $refund[0]['refund'];
			
			# Вывод данных по заказу
			if ($order)
			{
				# Выбор элементов заказа
				$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $id_order);
				
				# Выбор статусов
				$order_statuses = $mDB->GetItemsByParam('status', 'id_custom', $id_order);
				sort($order_statuses);

				# Отслеживание посылок
				if ($config['track']['enabled'] === true && is_file(dirname(__FILE__) . '/../modules/C_Track.inc.php'))
				{
					$track = $mDB->GetItemsByParam('track', 'id_custom', $order['id_custom']);

					if ($track && $track[0]['track'] != '')
					{
						$track_info = $mDB->GetItemsByParam('track_info', 'id_track', $track[0]['id_track']);
						
						if ($track_info)
						{
							sort($track_info);

							foreach ($track_info as $data)
								$track['status'] = $data['operation'];
								
							$track_data[$order['id_custom']] = $track_info;
						}
						else
							$track['status'] = 'Ошибка';

						$track['id'] = $order['track'] = $track[0]['track'];
						unset($track[0]);
					}
				}
				
				# Проверка дублей
				if ($config['repeats']['enabled'] === true)
				{
					$all_order = $mDB->GetAllItems('custom');

					$repeats = array();
					foreach ($all_order as $all_order)
					{
						if (!empty($all_order['ip']))
						{
							if (!empty($all_order['ip']) && $order['ip'] == $all_order['ip'] && $all_order['id_custom'] != $id_order)
								$repeats['ip'][] = $all_order;
						}
						if (!empty($all_order['phone']))
						{
							if (!empty($all_order['phone']) && !empty($order['phone']) && strpos($all_order['phone'], $order['phone']) !== false && strlen($order['phone']) > 7 && $all_order['id_custom'] != $id_order)
								$repeats['phone'][] = $all_order;
							elseif (!empty($all_order['phone']) && strpos($order['phone'], $all_order['phone']) !== false && strlen($all_order['phone']) > 7 && $all_order['id_custom'] != $id_order)
								$repeats['phone'][] = $all_order;
						}
					}
				}
			}
		}
		else
		{
			$order = array ('id_custom' => '',
							'name' => '',
							'lastname' => '',
							'fathername' => '',
							'email' => '',
							'phone' => '',
							'zip' => '',
							'country' => '',
							'region' => '',
							'city' => '',
							'address' => '',
							'comment' => '',
							'payment' => '',
							'delivery' => '',
							'delivery_cost' => '',
							'sum' => '',
							'status' => '',
							'country' => '',
							'id_partner' => '',
							'tags' => '',
							'admin_comment' => '',
							'payment_ym' => '',
							'utm' => '',
							'ip' => '',
							'source' => '',
							'config' => '',
							'payment_ym' => '',
							'id_manager' => '',
							'domain' => ''
							);
		}
		
		# Определение статуса заказа
		$statuses = $config['statuses'];
		
		# Выбираем все товары в базе
		if (is_file(dirname(__FILE__) . '/products.php') && mysql_table_seek('product', $config['database']['name']))
			$products = $mDB->GetAllItems('product');
			
		# Данные партнёра
		if (isset($order['id_partner']) && $order['id_partner'] != '0' && $order['id_partner'] != '')
			$partner = $mDB->GetItemById('partner', $order['id_partner']);

		# Список менеджеров
		if ($config['manager']['enabled'] === true && $config['manager']['type'] === 'order')
			$managers = $mDB->GetAllItems('manager');

		include_once dirname(__FILE__) . '/../design/adminOrderEdit.tpl.php';
	}
	# Вывод результатов поиска 
	elseif (isset($_SESSION['search']['res']))
	{
		$orders = $_SESSION['search']['res'];
		$search = $_SESSION['search']['form'];
		$search['payment'] = (isset($search['payment'])) ? array_flip($search['payment']) : null;
		$search['delivery'] = (isset($search['delivery'])) ? array_flip($search['delivery']) : null;
		unset($_SESSION['search']);

		# Выбор позиций товаров
		$orders_items = array();
		foreach ($orders as $order)
			$order_items[$order['id_custom']] = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
			
		# Отслеживание посылок
		if ($config['track']['enabled'] === true && is_file(dirname(__FILE__) . '/../modules/C_Track.inc.php'))
		{
			$tracks = $track = array();
			foreach ($orders as $i => $order)
			{
				$track = $mDB->GetItemsByParam('track', 'id_custom', $order['id_custom']);

				if ($track && $track[0]['track'] != '')
				{
					$track_info = $mDB->GetItemsByParam('track_info', 'id_track', $track[0]['id_track']);
					sort($track_info);

					if ($track_info)
					{
						foreach ($track_info as $data)
						{
							$tracks[$order['id_custom']]['date'] = $data['date'];
							$tracks[$order['id_custom']]['status'] = $data['operation'];
						}

						$tracks[$order['id_custom']]['track'] = $track[0]['id_track'];
						$track_data[$order['id_custom']] = $track_info;
					}
					$orders[$i]['track'] = $track[0]['track'];
				}
				else
					$orders[$i]['track'] = '';
			}
		}
		
		# Определение статуса заказов
		foreach ($orders as $i => $order)
		{
			$orders[$i]['status_id'] = $order['status'];
			$orders[$i]['status'] = $config['statuses'][$order['status']];
		}

		$current_category['code'] = '';
		$statuses = $config['statuses'];

		include_once dirname(__FILE__) . '/../design/adminOrdersList.tpl.php';
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
		if ($ordersList === 'all' && $status === 'all')
		{
			$all_orders = $orders = $mOrders->GetAllOrders($config['statuses']);
			rsort($orders);
		}
		# Выбор всех заказов определённого статуса
		elseif ($ordersList === 'all' && $status !== 'all')
		{
			$all_orders = $orders = $mDB->GetItemsByParam('custom', 'status', $status);
		}
        # Выбор всех заказов с учётом пагинации
		elseif ($ordersList !== 'all' && $status === 'all')
		{
			# Выборка данных
			$navi = $mOrders->Paginate($navi['page'], $ordersList, $config['statuses'], null, $order_tags);
			$orders = $mOrders->GetPaginatedList($navi['start'], $ordersList, $config['statuses'], null, $order_tags);
			
			# Подсчёт всех заказов
			if ($status != $config['statuses']['deleted'][0])
			{
				$params[] = 'status !=';
				$values[] = $config['statuses']['deleted'][0];
				$search_type[] = 'AND';
			}
			
			$all_orders = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);
		}
		# Или заказов определённого статуса
		else
		{
			# Выборка данных
			$navi = $mDB->PaginateWithParams('custom', $navi['page'], $ordersList, 'status', $status, 'tags', $order_tags);
			$orders = $mDB->GetPaginatedListWithParams('custom', $navi['start'], $ordersList, 'status', $status, 'tags', $order_tags);
			
			# Подсчёт всех заказов
			$params[] = 'status =';
			$values[] = $status;
			$search_type[] = 'AND';
			
			if ($status != $config['statuses']['deleted'][0])
			{
				$params[] = 'status !=';
				$values[] = $config['statuses']['deleted'][0];
				$search_type[] = 'AND';
			}
			
			$all_orders = $mDB->SearchItemsByParamArray('custom', $params, $values, $search_type);
		}
		
		# Всего заказов
		$all_orders = count($all_orders);
		
		# Выбор статусов заказов
		$order_statuses = array();
		foreach ($orders as $id => $order)
		{
			$order_statuses = $mDB->GetItemsByParam('status', 'id_custom', $order['id_custom']);
			if ($order_statuses)
			{
				sort($order_statuses);
				$key = count($order_statuses) - 1;
				$order_status[$order['id_custom']] = $order_statuses[$key];
			}

			# Подсчёт возвратов
			if ($order['status'] == $config['statuses']['refund'][0])
				$refund_count = (isset($refund_count)) ? $refund_count + 1 : 1;
				
			# Подстановка названий тегов
			$orders[$id]['tags'] = explode(',', $order['tags']);	
		}

		# Процент возвратов
		if (isset($refund_count))
			$refund_percent = number_format($refund_count / count($orders) * 100, 2, '.', '');
		else
			$refund_count = $refund_percent = 0;
		
		# Выбор позиций товаров
		$orders_items = array();
		foreach ($orders as $order)
			$order_items[$order['id_custom']] = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);

		# Отслеживание посылок
		if ($config['track']['enabled'] === true && is_file(dirname(__FILE__) . '/../modules/C_Track.inc.php'))
		{
			$tracks = $track = array();
			foreach ($orders as $i => $order)
			{
				$track = $mDB->GetItemsByParam('track', 'id_custom', $order['id_custom']);

				if ($track && $track[0]['track'] != '')
				{
					$track_info = $mDB->GetItemsByParam('track_info', 'id_track', $track[0]['id_track']);
					sort($track_info);

					if ($track_info)
					{
						foreach ($track_info as $data)
						{
							$tracks[$order['id_custom']]['date'] = $data['date'];
							$tracks[$order['id_custom']]['status'] = $data['operation'];
						}

						$tracks[$order['id_custom']]['track'] = $track[0]['id_track'];
						$track_data[$order['id_custom']] = $track_info;
					}
					$orders[$i]['track'] = $track[0]['track'];
				}
				else
					$orders[$i]['track'] = '';
			}
		}

        # Определение статуса заказов
		foreach ($orders as $i => $order)
		{
			$orders[$i]['status_id'] = $order['status'];
			$orders[$i]['status'] = $config['statuses'][$order['status']];
		}
			
		# Данные партнёра
		foreach ($orders as $i => $order)
		{
			if (!empty($order['id_partner']) && $order['id_partner'] != '0')
				$orders[$i]['partner'] = $mDB->GetItemById('partner', $order['id_partner']);
		}

        ob_start();
        include_once dirname(__FILE__) . '/../design/adminPagination.tpl.php';
        $pagination = ob_get_clean();
		
		# Статусы заказов
		$statuses = $config['statuses'];

		include_once dirname(__FILE__) . '/../design/adminOrdersList.tpl.php';
	}
}