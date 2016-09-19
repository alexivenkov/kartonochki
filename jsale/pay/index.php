<?php

# jSale v1.431
# http://jsale.biz

# Модуль для редиректа на оплату

# Работает только при наличии переменных id_order и hash
if (!isset($_GET['id_order']) || !isset($_GET['hash']))
	die;

$id_order = htmlspecialchars($_GET['id_order']);
$hash = htmlspecialchars($_GET['hash']);

include_once dirname(__FILE__) . '/../config.inc.php';
include_once dirname(__FILE__) . '/../modules/M_Email.inc.php';
$mEmail = M_Email::Instance();

# Выбор заказа и его элементов
$order = $mDB->GetItemById('custom', $id_order);
$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $id_order);
$order_sum = $order['sum'];

# Проверка статуса заказа
$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);
if (in_array($order['status'], $success_statuses))
{
	header('Location: ' . $config['sitelink'] . $config['successURL']);
	die;
}

# Проверка хеш-строки
if ($mEmail->CheckHash($hash, $id_order, $order['sum'], $config['secretWord']) == false)
	die;

# Маркер, сигнализирующий создание нового заказа
$new_order = true;

# Маркер, сигнализирующий о переходе по ссылке
$payment_link = true;

# Важные переменные
$email = $order['email'];
$phone = $order['phone'];

# Определение данных формы оплаты
$payment = $config['payments'][$order['payment']];
$payment['type'] = $order['payment'];

include_once dirname(__FILE__) . '/../modules/C_Payment.php';

if (isset($payment['form']))
{
	echo <<<EOF
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="utf-8" />
		<title>Оплата заказа</title>
	</head>
	<body>
		{$payment['form']}
	<noscript>Видимо у вас отключён JavaScript. Просто нажмите на кнопку.</noscript>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		$('form').submit();
	</script>
	</body>
	</html>
EOF;
}
elseif (isset($payment['link']))
	header('Locatin: ' . $payment['link']);
