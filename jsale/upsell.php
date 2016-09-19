<?php

# Подключение настроек
include_once dirname(__FILE__) . '/config.inc.php';

# Кодировка
header('Content-type: text/html; charset=' . $config['encoding']);
header('Access-Control-Allow-Origin: *');

# Вывод ошибок
if ($config['errors'] === true)
{
	error_reporting(E_ALL); # Уровень вывода ошибок
	ini_set('display_errors', 'on'); # Вывод ошибок включён
	ini_set("log_errors", 'on'); # Логирование включено
	ini_set("error_log", dirname(__FILE__) . '/error_log.txt'); # Путь файла с логами
}

# Подключение модулей
include_once dirname(__FILE__) . '/modules/M_Email.inc.php';
$mEmail = M_Email::Instance();

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (!isset($_POST['id_order']) || !isset($_POST['code']) || !isset($_POST['price']) || !isset($_POST['title']))
		die;

	$id_order = htmlspecialchars($_POST['id_order']);
	
	session_start();
	if (!isset($_SESSION['jsale_order']) || isset($_SESSION['jsale_order']) && $_SESSION['jsale_order'] != $id_order)
		die;
	
	$codes = $_POST['code'];
	$prices = $_POST['price'];
	$titles = $_POST['title'];

	$order = $mDB->GetItemById('custom', $id_order);
	
	if (!$order)
		die;
	
	$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $id_order);
	
	$order_sum = $order['sum'];
	foreach ($titles as $key => $title)
	{
		$order_sum += $prices[$key];
		$params = array('product' => htmlspecialchars($title), 'id_product' => htmlspecialchars($codes[$key]), 'price' => htmlspecialchars($prices[$key]), 'id_custom' => $id_order, 'quantity' => 1);
		$mDB->CreateItem('custom_item', $params, true);
	}
	
	$params = array('sum' => $order_sum);
	$mDB->EditItemById('custom', $params, $id_order, true);
	
	header('Location: ' . $config['sitelink'] . $config['resultURL']);
}