<?php

# Настройки
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

# Статусы успешного завершения заказа (по умолчанию "Оплачен" и "Доставлен")
$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);

$result = false;

session_start();

# Проверка переменной в сессии
if (isset($_SESSION['jsale_order']))
{
	include_once dirname(__FILE__) . '/M_DB.inc.php';
	$mDB = M_DB::Instance();
	
	# Выбор заказа из БД
	$order = $mDB->GetItemById('custom', $_SESSION['jsale_order']);
	
	# Если статус Оплачен
	if (in_array($order['status'], $success_statuses))
		$result = $order;
	
	# Редирект на страницу успешной оплаты
	if (!strpos($_SERVER['REQUEST_URI'], $config['successURL']))
	{
		header('Location: ' . $config['sitelink'] . $config['successURL']);
		die;
	}
	
	# Удаляем переменную из сессии
	unset($_SESSION['jsale_order']);
}
else
	die;

# Если есть оплаченный заказ
if ($result)
{
	# Данные заказа
	$order_data = $result;
	$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order_data['id_custom']);
	
	# Подключение модуля работы с файлами (создание ссылки на скачивание)
	if ($config['download']['enabled'] === true && is_file(dirname(__FILE__) . '/C_Files.inc.php') && in_array($order_data['status'], $success_statuses))
		include_once dirname(__FILE__) . '/C_Files.inc.php';
	
	include_once dirname(__FILE__) . '/../design/successPage.tpl.php';
	
}