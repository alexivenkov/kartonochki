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
include_once dirname(__FILE__) . '/../modules/M_Calls.inc.php';
$mCalls = M_Calls::Instance();

# Определение текущей страницы
if (isset($_GET['page']))
    $navi['page'] = $_GET['page'];
else
    $navi['page'] = 1;

# Определение текущего звонка
if (isset($_GET['call']))
    $id_call = $_GET['call'];

# Определение статуса звонков для фильтра
$status = (isset($_GET['status']) && $_GET['status'] != 'all') ? intval($_GET['status']) : 'all';

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
	# Множественная смена статусов заказов
	if (isset($_POST['change_status']))
	{
		if (isset($_POST['id']))
		{
			foreach ($_POST['id'] as $id_call)
			{
				$mDB->ChangeStatusById('call', $id_call, $_POST['status']);
				$mCalls->SaveStatus($id_call, date('Y-m-d H:i:s'), $_POST['status']);
			}
		}
		
		header('Location: ' . $_SESSION['this_page']);
		die;
	}
	# Количество заказов в списке
	elseif (isset($_POST['callsList']))
	{
		$_SESSION['callsList'] = $_POST['callsList'];
		
		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/calls.php');
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
						$tmp_params = $mDB->SearchItemsByParamArray('call_item', $prod_params, $prod_values, $prod_search_type);

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
							$params[] = $pre . '`id_call` =';
							$values[] = '\'' . $val['id_call'] . '\'' . $app;

							$i++;
						}

						if (count($tmp_params) == 0)
						{
							$params[] = '`id_call` =';
							$values[] = '\'false_product_dummy_string_for_search_result_prevention\'';
							$search_type[] = 'AND';
						}
					}
					else
					{
						unset($pre_search['product']);
					}
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
				elseif ($field != 'phone' && $field != 'zip' && $field != 'id_call')
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
			$calls = $mDB->SearchItemsByParamArray('call', $params, $values, $search_type);
			if(isset($calls) && count($calls) > 0)
				$_SESSION['success_message'] = 'Найдено результатов: ' . count($calls);
			elseif (!isset($calls) || count($calls) == 0)
				$_SESSION['search_massage'] = 'Ничего не найдено';
		}
		else
			$_SESSION['search_massage'] = 'Введите данные для поиска';

		$_SESSION['search']['res'] = (isset($calls))? $calls : null;
		$_SESSION['search']['form'] = $pre_search;

		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/calls.php');
		die;
	}
	# Удаление звонка
	elseif (isset($_POST['call_delete']))
	{
		$mDB->ChangeStatusById('call', $_POST['call_id'], $config['call_statuses']['deleted'][0]);
		$mCalls->SaveStatus($_POST['call_id'], date('Y-m-d H:i:s'), $config['call_statuses']['deleted'][0]);
		header('Location: ' . $_SESSION['referer']);
		die;
	}
	# Полное удаление звонка
	elseif (isset($_POST['real_call_delete']))
	{
		$mDB->DeleteItemById('call', $_POST['call_id']);
		$mDB->DeleteItemsByParam('call_status', 'id_call', $_POST['call_id']);
		header('Location: ' . $_SESSION['referer']);
		die;
	}
	# Отмена редактирования заказа
	elseif (isset($_POST['call_cancel']))
	{
		header('Location: ' . $_SESSION['referer']);
		die;
	}
	# Создание заказа
	elseif (isset($_POST['call_create_order']))
	{
		# Красивая дата
		list($date, $time) = explode(' ', $_POST['call_date']);
		list($day, $month, $year) = explode('.', $date);
		list($hour, $minute, $second) = explode(':', $time);
		$_POST['call_date'] = "$year-$month-$day $hour:$minute:$second";
		unset($day, $month, $year, $date, $time, $hour, $minute, $second);
	
		# Создание нового заказа в БД
		$params = array ('name' => $_POST['call_name'], 'lastname' => $_POST['call_lastname'], 'fathername' => $_POST['call_fathername'], 'phone' => $_POST['call_phone'], 'email' => $_POST['call_email'], 'admin_comment' => stripslashes($_POST['call_comment']), 'date' => $_POST['call_date'], 'status' => $config['statuses']['confirmed'][0]);
		if (!$id_order = $mDB->CreateItem('custom', $params, true))
			die('<p>Данные не были сохранены. Проверьте корректность заполнения полей.</p>');

		# Запись статуса в БД
		$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['confirmed'][0]);
		
		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/orders.php?order=' . $id_order);
        die;
	}
	# Редактирование звонка
	elseif (isset($_POST['call_edit']) || isset($_POST['call_save']))
    {
		# Красивая дата
		list($date, $time) = explode(' ', $_POST['call_date']);
		list($day, $month, $year) = explode('.', $date);
		list($hour, $minute, $second) = explode(':', $time);
		$_POST['call_date'] = "$year-$month-$day $hour:$minute:$second";
		unset($day, $month, $year, $date, $time, $hour, $minute, $second);

		# Создание нового звонка в БД
		if ($_POST['call_id'] == '')
		{
			if (!$id_call = $mCalls->CreateCall($_POST['call_lastname'], $_POST['call_name'], $_POST['call_fathername'], $_POST['call_phone'], $_POST['call_email'], stripslashes($_POST['call_comment']), $_POST['call_date'], $_POST['call_status'], $_POST['call_topic'], $_POST['call_link'], $_POST['call_operator']))
				die('<p>Данные не были сохранены. Проверьте корректность заполнения полей.</p>');
		}

		# Идентификатор звонка в БД
		$id_call = isset($id_call) ? $id_call : $_POST['call_id'];

		# Запись статуса в БД
		if ($_POST['call_status'] != $_POST['call_last_status'] || $_POST['call_last_status'] == '')
			$mCalls->SaveStatus($id_call, date('Y-m-d H:i:s'), $_POST['call_status']);
		
        # Редактирование данных звонка
		if (!$mCalls->EditCall($id_call, $_POST['call_lastname'], $_POST['call_name'], $_POST['call_fathername'], $_POST['call_phone'], $_POST['call_email'], stripslashes($_POST['call_comment']), $_POST['call_date'], $_POST['call_status'], $_POST['call_topic'],$_POST['call_link'], $_POST['call_operator']))
			die('<p>Данные не были сохранены. Проверьте корректность заполнения полей.</p>');

		if (isset($_POST['last_call_status']) && $_POST['last_call_status'] != '' || isset($_POST['call_save']))
		{
			header('Location: ' . $_SESSION['this_page']);
			$_SESSION['is_save'] = true;
		}
		else
			header('Location: ' . $_SESSION['referer']);
        die;
    }
}
else
{
    # Обработка GET запроса
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {	
		# Удаление заказа
		if (isset($_GET['delete_call']))
		{
			# Смена статуса
			$mDB->ChangeStatusById('call', $_GET['delete_call'], $config['call_statuses']['deleted'][0]);

			# Запись статуса в БД
			$mCalls->SaveStatus($_GET['delete_call'], date('Y-m-d H:i:s'), $config['call_statuses']['deleted'][0]);

			header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/calls.php');
			die;
		}
		
		# Удаление заказа
		if (isset($_GET['real_delete_call']))
		{
			$mDB->DeleteItemById('call', $_GET['real_delete_call']);
			$mDB->DeleteItemsByParam('call_status', 'id_call', $_GET['real_delete_call']);
			
			header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/calls.php');
			die;
		}
    }

    # Вывод текущего ИЛИ нового заказа
	if (isset($id_call))
	{
		if ($id_call != 'new')
		{
			# Выбор заказа
			$call = $mDB->GetItemById('call', $id_call);
			
			# Вывод данных по заказу
			if ($call)
			{	
				# Выбор статусов
				$call_statuses = $mDB->GetItemsByParam('call_status', 'id_call', $id_call);
				sort($call_statuses);
			}
		}
		else
		{
			$call = array ('id_call' => '',
							'name' => '',
							'lastname' => '',
							'fathername' => '',
							'phone' => '',
							'email' => '',
							'comment' => '',
							'status' => '',
							'operator' => '',
							'link' => '',
							'topic' => ''
							);
		}
		
		# Список операторов
		if ($config['call']['managers'] === true)
			$operators = $mDB->GetAllItems('manager');
		else
			$operators = $config['call']['operators'];
		
		# Определение статуса заказа
		$statuses = $config['call_statuses'];

		include_once dirname(__FILE__) . '/../design/adminCallEdit.tpl.php';
	}
	# Вывод результатов поиска 
	elseif (isset($_SESSION['search']['res']))
	{
		$calls = $_SESSION['search']['res'];
		$search = $_SESSION['search']['form'];
		unset($_SESSION['search']);
		
		# Определение статуса заказов
		foreach ($calls as $i => $call)
			$calls[$i]['status'] = $config['statuses'][$call['status']];

		$statuses = $config['call_statuses'];

		include_once dirname(__FILE__) . '/../design/adminCallsList.tpl.php';
	}
    # Вывод списка заказов
	else
	{
		# Количество заказов в списке
		if (isset($_SESSION['callsList']))
			$callsList = $_SESSION['callsList'];
		else
			$callsList = $config['admin']['callsList'];
	
		# Выбор всех заказов
		if ($callsList === 'all' && $status === 'all')
		{
			$all_calls = $calls = $mCalls->GetAllCalls($config['call_statuses']);
			rsort($calls);
		}
		# Выбор всех заказов определённого статуса
		elseif ($callsList === 'all' && $status !== 'all')
		{
			$all_calls = $calls = $mDB->GetItemsByParam('call', 'status', $status);
		}
        # Выбор всех заказов с учётом пагинации
		elseif ($callsList !== 'all' && $status === 'all')
		{
			# Выборка данных
			$navi = $mCalls->Paginate($navi['page'], $callsList, $config['call_statuses']);
			$calls = $mCalls->GetPaginatedList($navi['start'], $callsList, $config['call_statuses']);
			
			# Подсчёт всех заказов
			if ($status != $config['statuses']['deleted'][0])
			{
				$params[] = 'status !=';
				$values[] = $config['statuses']['deleted'][0];
				$search_type[] = 'AND';
			}
			
			$all_calls = $mDB->SearchItemsByParamArray('call', $params, $values, $search_type);
		}
		# Или заказов определённого статуса
		else
		{
			# Выборка данных
			$navi = $mDB->PaginateWithParam('call', $navi['page'], $callsList, 'status', $status);
			$calls = $mDB->GetPaginatedListWithParam('call', $navi['start'], $callsList, 'status', $status);
			
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
			
			$all_calls = $mDB->SearchItemsByParamArray('call', $params, $values, $search_type);
		}
		
		# Всего заказов
		$all_calls = count($all_calls);
		
		# Выбор статусов заказов
		$call_statuses = array();
		foreach ($calls as $call)
		{
			$call_statuses = $mDB->GetItemsByParam('call_status', 'id_call', $call['id_call']);
			if ($call_statuses)
			{
				sort($call_statuses);
				$key = count($call_statuses) - 1;
				$call_status[$call['id_call']] = $call_statuses[$key];
			}
		}

        # Определение статуса заказов
		foreach ($calls as $i => $call)
			$calls[$i]['status'] = $config['call_statuses'][$call['status']];

        ob_start();
        include_once dirname(__FILE__) . '/../design/adminPagination.tpl.php';
        $pagination = ob_get_clean();
		
		# Статусы заказов
		$statuses = $config['call_statuses'];

		include_once dirname(__FILE__) . '/../design/adminCallsList.tpl.php';
	}
}