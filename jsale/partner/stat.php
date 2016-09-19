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

if ($config['database']['enabled'] === false)
	$message = 'Использование базы данных отключено в настройках. Админка в данном случае бесполезна и будет выдавать ошибки.';

# Выборка данных по партнёрку из БД
$partner = $mDB->GetItemByID('partner', $_SESSION['id_partner']);

# Подсчёт статистики
$orders = $mDB->GetItemsByParam('custom', 'id_partner', $_SESSION['id_partner']);
$total_count = $paid_count = $paid_sum = $commission = 0;
$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);
if (is_array($orders))
{
	foreach ($orders as $i => $order)
	{
		$total_count++;
		$orders[$i]['items'] = $order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $order['id_custom']);
		
		if (in_array($order['status'], $success_statuses))
		{
			$paid_count++;
			
			$order_item_sum = 0;
			$order_commission = 0;
			foreach ($order_items as $order_item)
			{
				if ($config['discounts']['fixed'] === true)
					$paid_sum += $order_item_sum = $order_item['quantity'] * $order_item['price'] - $order_item['discount'];
				else
					$paid_sum += $order_item_sum = $order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100);
				
				$order_commission += $order_item_sum * $order_item['partner_rate'] / 100;
			}
			$commission += $order_commission;
			$orders[$i]['commission'] = $order_commission;
		}
	}
}
if ($config['partner']['rate_product'] === true)
	$paid_sum = $commission;
else
	$paid_sum = $paid_sum * $config['partner']['percent']['level_1'] / 100;

$referers = $mDB->GetItemsByParam('partner', 'referer', $_SESSION['id_partner']);
$referers_sum = $referers_count = 0;
if (is_array($referers))
{
	foreach ($referers as $referer)
	{
		$referers_count++;
		$referers_sum += $referer['paid'];
	}
}
$referers_profit = $referers_sum * $config['partner']['percent']['level_2'] / 100;
	
# Вывод дизайна
include_once dirname(__FILE__) . '/_menu.php';
include_once dirname(__FILE__) . '/../design/partnerStat.tpl.php';