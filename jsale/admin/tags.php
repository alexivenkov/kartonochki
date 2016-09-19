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
	# Редактирование
	if (isset($_POST['tag_create']))
	{
		$params = array ('title' => $_POST['new_tag']);
		$mDB->CreateItem('tag', $params);
		header('Location: ' . $_SESSION['this_page']);
		die;
	}
}
else
{
	# Удаление метки
	if (isset($_GET['delete_tag']))
	{
		$mDB->DeleteItemById('tag', $_GET['delete_tag']);
		
		# Удаление связей с заказами
		############################
		
		header('Location: ' . $_SESSION['referer']);
		die;
	}
	else
	{
		# Выборка данных по партнёрам из БД
		$tags = $mDB->GetAllItems('tag');
		
		ob_start();
		include_once dirname(__FILE__) . '/../design/adminPagination.tpl.php';
		$pagination = ob_get_clean();
		
		# Вывод дизайна
		include_once dirname(__FILE__) . '/_menu.php';
		include_once dirname(__FILE__) . '/../design/adminTagsList.tpl.php';
	}
}