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

if ($config['author']['enabled'] !== true)
{
	echo 'Авторский кабинет отключён администратором.';
	die;
}

# Подключение модулей
include_once dirname(__FILE__) . '/../modules/M_Authors.inc.php';
$mAuthors = M_Authors::Instance();

# Кодировка
header('Content-type: text/html; charset=' . $config['encoding']);

# Формирование GET запроса (на случай PHP как CGI)
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

# Авторизация
session_start();
if (!$mAuthors->CheckLogin())
	die;

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

if ($config['database']['enabled'] === false)
	$message = 'Использование базы данных отключено в настройках. Админка в данном случае бесполезна и будет выдавать ошибки.';

# Сохранение данных
if (isset($_POST['save']))
{
	$email = (isset($_POST['email'])) ? trim($_POST['email']) : '';
	$password = (isset($_POST['password'])) ? trim($_POST['password']) : '';
	$payment = (isset($_POST['payment'])) ? trim($_POST['payment']) : '';
	
	$params = array (
		'email' => $email,
		'password' => $password,
		'payment' => $payment
	);
	
	$mDB->EditItemById('author', $params, $_POST['id_partner'], true);
	
	$message = 'Данные сохранены';
}

# Выборка данных по партнёрку из БД
$author = $mDB->GetItemByID('author', $_SESSION['id_author']);	

# Вывод дизайна
include_once dirname(__FILE__) . '/_menu.php';
include_once dirname(__FILE__) . '/../design/authorConfig.tpl.php';