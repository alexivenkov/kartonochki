<?php

# Подключение настроек
include_once dirname(__FILE__) . '/../config.inc.php';

# Подключение модуля отправки почты
include_once dirname(__FILE__) . '/M_Email.inc.php';
$mEmail = M_Email::Instance();

if (isset($_GET['id_order']))
	$id_order = $_GET['id_order'];

if (isset($_GET['order_sum']))
	$order_sum = $_GET['order_sum'];
	
if (isset($_GET['hash']))
	$hash = $_GET['hash'];

# Проверка хэш-строки
if (isset($hash) && isset($id_order) && isset($order_sum) && $mEmail->CheckHash($hash, $id_order, $order_sum, $config['secretWord']) === true)
{
	# Подтверждение заказа
	if (isset($_GET['confirm']))
	{
		# Получение заказа из базы
		$order = $mDB->GetItemById('custom', $id_order);
	
		# Если заказ новый
		if ($order['status'] == 0)
		{
			# Изменение статуса заказа
			$mDB->ChangeStatusById('custom', $id_order, $config['statuses']['confirmed'][0]);
			
			# Сохранение нового статуса в БД
			$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['confirmed'][0], true);

			# Перенаправление пользователя
			header('Location:' . $config['sitelink'] . $config['confirmURL']);
		}
		else
			# Перенаправление пользователя
			header('Location:' . $config['sitelink'] . $config['no_confirmURL']);
		
	}
	# Отмена заказа
	elseif (isset($_GET['refuse']))
	{
		# Получение заказа из базы
		$order = $mDB->GetItemById('custom', $id_order);

		# Если заказ новый
		if ($order['status'] == 0)
		{
			# Изменение статуса заказа
			$mDB->ChangeStatusById('custom', $id_order, $config['statuses']['fail'][0]);
			
			# Сохранение нового статуса в БД
			$mDB->SaveStatus($id_order, date('Y-m-d H:i:s'), $config['statuses']['fail'][0], true);

			# Перенаправление пользователя
			header('Location:' . $config['sitelink'] . $config['refuseURL']);
		}
		else
			# Перенаправление пользователя
			header('Location:' . $config['sitelink'] . $config['no_refuseURL']);

	}
	else
		die;
}
else
	die;