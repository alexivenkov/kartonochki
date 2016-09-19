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

if ($config['partner']['enabled'] !== true)
{
	echo 'Партнёрская программа отключена администратором.';
	die;
}

# Подключение модулей
include_once dirname(__FILE__) . '/../modules/M_Partner.inc.php';
$mPartner = M_Partner::Instance();

# Кодировка
header('Content-type: text/html; charset=' . $config['encoding']);

# Формирование GET запроса (на случай PHP как CGI)
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

# Авторизация
session_start();
if (!$mPartner->CheckLogin())
{
	$no_header = true;
	$content = '<p>Можете зарегистрироваться в партнёрской программе.</p><br />
	<p><a href="new.php" class="btn btn-primary btn-large">Перейти к регистрации</a></p>';
	include_once dirname(__FILE__) . '/../design/partnerCreate.tpl.php';
	die;
}

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

if ($config['database']['enabled'] == false)
	$message = 'Использование базы данных отключено в настройках. Админка в данном случае бесполезна и будет выдавать ошибки.';

# Сохранение данных
if (isset($_POST['save']))
{
	$email = (isset($_POST['email'])) ? trim($_POST['email']) : '';
	$password = (isset($_POST['password'])) ? trim($_POST['password']) : '';
	$payment = (isset($_POST['payment'])) ? trim($_POST['payment']) : '';
	$discount = (isset($_POST['discount'])) ? trim($_POST['discount']) : '';

	if ($discount >= $config['partner']['percent']['level_1'] + $config['partner']['discount']['percent'] || $discount <= $config['partner']['discount']['percent'])
		$discount = 0;
	
	$params = array (
		'email' => $email,
		'password' => $password,
		'payment' => $payment,
		'discount' => $discount
	);
	
	$mDB->EditItemById('partner', $params, $_POST['id_partner'], true);
	
	$message = 'Данные сохранены';
}

# Выборка данных по партнёрку из БД
$partner = $mDB->GetItemByID('partner', $_SESSION['id_partner']);	

# Вывод дизайна
include_once dirname(__FILE__) . '/_menu.php';
include_once dirname(__FILE__) . '/../design/partnerConfig.tpl.php';