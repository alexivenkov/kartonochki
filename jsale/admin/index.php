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

# Подключение модуля работы с базой данных.
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();

# Подсчёт оплаченных заказов
$PaidOrders = $mDB->GetItemsByParam('custom', 'status', $config['statuses']['confirmed'][0]);
$PaidOrdersCount = count($PaidOrders);

# Подключение меню
include_once dirname(__FILE__) . '/_menu.php';

# Бейджики в меню
$NewOrdersCount .= $mDB->Plural($NewOrdersCount, ' новый заказ', ' новых заказа', ' новых заказов');
$PaidOrdersCount .= $mDB->Plural($PaidOrdersCount, ' подтверждён и ожидает обработки', ' подтверждены и ожидают обработки', ' подтверждены и ожидают обработки');
if (isset($NewCallsCount))
	$NewCallsCount .= $mDB->Plural($NewCallsCount, ' новый звонок', ' новых звонков', ' новых звонков');
	
# Подключение модуля отправки SMS уведомления
if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php') && $config['sms']['enabled'] === true && $config['sms']['provider'] == 'SMSru' && !empty($config['sms']['api_key']))
{
	require_once dirname(__FILE__) . '/../modules/M_SMSru.inc.php';
			
	if (!isset($sms))
		$sms = new smsru( $config['sms']['api_key'] );
	$sms_balance = $sms->my_balance();
}

# Вывод дизайна
include_once dirname(__FILE__) . '/../design/adminIndex.tpl.php';