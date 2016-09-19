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

# Подключение модуля
if (is_file(dirname(__FILE__) . '/../modules/M_Files.inc.php'))
{
	include_once dirname(__FILE__) . '/../modules/M_Files.inc.php';
	$mFiles = M_Files::Instance();
}

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

# Сохранение прошлой и текущей страницы
if ($_SERVER['REQUEST_METHOD'] != 'POST')
{
	if (!isset($_GET['download_file']) && !isset($_GET['delete_file']))
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
}
if (!isset($_SESSION['referer']) && isset($_SESSION['this_page']))
	$_SESSION['referer'] = $_SESSION['this_page'];

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	# Очистка формы
	if (isset($_POST['clean']))
	{
		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/codegen.php');
		die;
	}
	
	# Добавление параметра
	if (isset($_POST['add_new_param']) && isset($_POST['new_param_title']) && isset($_POST['new_param_type']) && isset($_POST['new_param_name']) && isset($_POST['id_product']))
	{		
		$params = array(
			'title' => htmlspecialchars(stripslashes($_POST['new_param_title'])),
			'type' => htmlspecialchars(stripslashes($_POST['new_param_type'])),
			'name' => htmlspecialchars(stripslashes($_POST['new_param_name'])),
			'parent' => htmlspecialchars(stripslashes($_POST['new_param_parent'])),
			'required' => htmlspecialchars(stripslashes($_POST['new_param_required'])),
			'id_product' => $_POST['id_product']
			);
		$mDB->CreateItem('param', $params);
		
		header('Location: ' . $_SESSION['this_page']);
		die;
	}
	
	# Добавление варианта параметра
	if (isset($_POST['add_param']) && isset($_POST['id_product']))
	{
		$params = array('id_param' => $_POST['add_param']);
		$mDB->CreateItem('param_item', $params);
	
		header('Location: ' . $_SESSION['this_page']);
		die;
	}
	
	# Редактирование варианта параметра
	if (isset($_POST['edit_param']) && isset($_POST['id_product']))
	{
		$params = array('sort' => $_POST['param_sort_'.$_POST['edit_param']], 'type' => $_POST['param_type_'.$_POST['edit_param']], 'parent' => $_POST['param_parent_'.$_POST['edit_param']], 'required' => $_POST['param_required_'.$_POST['edit_param']]);
		$mDB->EditItemById('param', $params, $_POST['edit_param']);
		
		if (isset($_POST['item_title_'.$_POST['edit_param']]) && is_array($_POST['item_title_'.$_POST['edit_param']]))
		{
			# Перебор и редактирование
			foreach ($_POST['item_title_'.$_POST['edit_param']] as $id => $item_title)
			{
				# Сохранение данных
				$params = array('title' => $_POST['item_title_'.$_POST['edit_param']][$id], 'price' => $_POST['item_price_'.$_POST['edit_param']][$id]);
			
				$mDB->EditItemById('param_item', $params, $id);

				# Загрузка файлов
				if (!empty($_FILES['item_file']['tmp_name'][$id]) && !empty($_FILES['item_file']['tmp_name'][$id]) && $config['params']['upload']['enabled'] === true)
				{
					$path = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $config['params']['upload']['dir'] . '/';
					
					# Подготовка загрузки
					$upload_file = array();
					$upload_file['name'] = $id . '.' . $config['params']['upload']['type'];
					$upload_file['tmp_name'] = $_FILES['item_file']['tmp_name'][$id];
					$upload_file['size'] = $_FILES['item_file']['size'][$id];
					$upload_file['type'] = $_FILES['item_file']['type'][$id];
					$upload_file['error'] = $_FILES['item_file']['error'][$id];

					# Загрузка нового файла
					$mFiles->UploadFile($upload_file, $path, true);
					unset($upload_file);
				}
			}
		}
	
		header('Location: ' . $_SESSION['this_page']);
		die;
	}
	
	# Удаление параметра
	if (isset($_POST['delete_param']) && isset($_POST['id_product']))
	{
		# Удаление параметра
		$mDB->DeleteItemById('param', $_POST['delete_param']);
		
		# Выбор вариантов
		$items = $mDB->GetItemsByParam('param_item', 'id_param', $_POST['delete_param']);
		
		# Удаление вариантов
		foreach ($items as $item)
		{
			$mDB->DeleteItemById('param_item', $item['id_param_item']);
		
			# Удаление файлов
			$file = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $config['params']['upload']['dir'] . '/' . $item['id_param_item'] . '.' . $config['params']['upload']['type'];

			if (is_file($file))
				unlink($file);
		}
			
		header('Location: ' . $_SESSION['this_page']);
		die;
	}

	# Сохранение
	if (isset($_POST['save']))
	{
		# Сохранение массива POST в переменные
		$id_product = (!empty($_POST['id_product'])) ? $_POST['id_product'] : '';
		$product['code'] = (!empty($_POST['code'])) ? $_POST['code'] : '';
		$product['title'] = (!empty($_POST['title'])) ? $_POST['title'] : '';
		$product['price'] = (!empty($_POST['price'])) ? $_POST['price'] : '';
		$product['discount'] = (!empty($_POST['discount'])) ? $_POST['discount'] : '';
		$product['unit'] = (!empty($_POST['unit'])) ? $_POST['unit'] : '';
		$product['qty'] = (!empty($_POST['qty'])) ? $_POST['qty'] : '';
		$product['qty_type'] = (!empty($_POST['qty_type'])) ? $_POST['qty_type'] : '';
		$product['param1'] = (!empty($_POST['param1'])) ? $_POST['param1'] : '';
		$product['param2'] = (!empty($_POST['param2'])) ? $_POST['param2'] : '';
		$product['param3'] = (!empty($_POST['param3'])) ? $_POST['param3'] : '';
		$product['store'] = (!empty($_POST['store'])) ? $_POST['store'] : '';
		$product['form_type'] = (!empty($_POST['form_type'])) ? $_POST['form_type'] : '';
		$product['button_img'] = (!empty($_POST['button_img'])) ? $_POST['button_img'] : '';
		
		if ($config['bandles']['enabled'] === true)
		{
			$product['bandle_products'] = array();
			for ($x = 0; $x < $config['bandles']['products']; $x++)
				if ((!empty($_POST['bandle_product_' . $x])))
					$product['bandle_products'][] = $_POST['bandle_product_' . $x];
			$product['bandle_products'] = (is_array($product['bandle_products'])) ? implode('|', $product['bandle_products']) : '';
		}
		else
			$product['bandle_products'] = '';
		
		# Подключение модуля
		include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
		include_once dirname(__FILE__) . '/../modules/M_Products.inc.php';
		$mDB = M_DB::Instance();
		$mProducts = M_Products::Instance();

		# Редактирование или создание нового товара
		if ($id_product != '')
			$mProducts->EditFullProduct($id_product, $product);
		else
			$id_product = $mProducts->CreateProduct($product);
		
		$tale = '';
		$tale .= (!empty($_POST['bonus'])) ? '&bonus=' . $_POST['bonus'] : '';
		$tale .= (!empty($_POST['deadline'])) ? '&deadline=' . $_POST['deadline'] : '';
		$tale .= (!empty($_POST['upsell'])) ? '&upsell=' . $_POST['upsell'] : '';
		
		# Редирект на страницу товара
		header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/codegen.php?id_product=' . $id_product . $tale);
		die;
	}
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
	header('Location: ' . $_SESSION['referer']);
	die;
}

# Выборка товара из БД
if (isset($_GET['id_product']))
{
	# Подключение модуля
	include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
	$mDB = M_DB::Instance();
	
	# Выборка товара из БД
	$product = $mDB->GetItemById('product', $_GET['id_product']);
	
	# Выборка параметров из БД
	if ($config['params']['enabled'] === true)
	{
		$params = $mDB->GetItemsByParamAndSort('param', 'id_product', $_GET['id_product']);
		#sort($params);
		
		# Выборка пунктов параметров из БД
		if (!empty($params))
		{
			$product_params = array();
			foreach ($params as $param)
			{
				$key = $param['id_param'];
				$product_params[$key]['type'] = $param['type'];
				$product_params[$key]['sort'] = $param['sort'];
				$product_params[$key]['title'] = $param['title'];
				$product_params[$key]['name'] = $param['name'];
				$product_params[$key]['required'] = $param['required'];
				$product_params[$key]['parent'] = $param['parent'];
				$product_params[$key]['items'] = $mDB->GetItemsByParam('param_item', 'id_param', $param['id_param']);
				sort($product_params[$key]['items']);
			}
		}
	}
}

# Удаление варианта параметра.
if (isset($_GET['remove_param_item']))
{
	$mDB->DeleteItemById('param_item', $_GET['remove_param_item']);
	
	# Удаление файлов
	$file = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $config['params']['upload']['dir'] . '/' . $_GET['remove_param_item'] . '.' . $config['params']['upload']['type'];

	if (is_file($file))
		unlink($file);
	
	header('Location: ' . $_SESSION['referer']);
	die;
}

# Подключение модуля работы с базой данных.
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();

# Подсчёт оплаченных заказов
$PaidOrders = $mDB->GetItemsByParam('custom', 'status', '1');
$PaidOrdersCount = count($PaidOrders);

# Вывод дизайна
include_once dirname(__FILE__) . '/_menu.php';
$NewOrdersCount .= $mDB->Plural($NewOrdersCount, ' новый заказ', ' новых заказа', ' новых заказов');
$PaidOrdersCount .= $mDB->Plural($PaidOrdersCount, ' оплачен и ожидает отправки', ' оплачены и ожидают отправки', ' оплачены и ожидают отправки');
include_once dirname(__FILE__) . '/../design/adminCodeGenerator.tpl.php';