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

# Подключение меню
include_once dirname(__FILE__) . '/_menu.php';

# Подключение модуля работы с базой данных.
include_once dirname(__FILE__) . '/../modules/M_CSV.inc.php';
$mCSV = M_CSV::Instance();

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    # Обработка экспорта
    if (isset($_POST['export']))
    {
		if (isset($_POST['export_table']))
		{
			$first_line = (isset($_POST['csv_col_output']))? true : false ;
			
			if ($_POST['export_table'] != 'custom')
				$_POST['date_from'] = $_POST['date_to'] = null;
			
			if ($mCSV->MySQL2CSV($_POST['export_table'], $first_line, $_POST['date_from'], $_POST['date_to']))
				$_SESSION['success_message'] = 'Выгрузка произведена успешно';
			else
				$_SESSION['error_message'] = 'Что-то пошло не так!';
		}
	    else
			$_SESSION['error_message'] = 'База данных не выбрана!';
    }
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	die;
}

include_once dirname(__FILE__) . '/../design/adminDatabase.tpl.php';