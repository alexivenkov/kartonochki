<?php
# Работа со статистикой Яндекс.Директ

# Вход:
# $date_stat - дата отслеживания статистики

# Выход:
# $clicks - количество кликов
# $rate - затраты

# Подключение необходимых модулей
include_once dirname(__FILE__) . '/../modules/M_Yandex.inc.php';
$mYandex = M_Yandex::Instance();

$clicks = $rate = 0;
# Если запись в БД существует, то выводим данные из БД
if ($mDB->IssetItemByParam('direct', 'date', $date_stat))
{
	$stats = $mDB->GetItemsByParam('direct', 'date', $date_stat);
	$clicks = $stats[0]['clicks'];
	$rate = $stats[0]['rate'];
}
# Иначе отправляем запрос к API и пишем статистику в БД
else
{
	foreach ($config['yandex']['campaign_id'] as $campaign)
	{
		$stats = $mYandex->GetStat($config['yandex']['login'], $config['yandex']['token'], $config['yandex']['application_id'], $campaign, $date_stat, $date_stat);
		
		if (isset($stats['error_code']))
		{
			$error_alert = '<strong>Внимание!</strong> Данные от Яндекс. Директ были получены не полностью. Возможна ошибка в статистике.
			Причина: ' . $stats['error_str'];
			$clicks = $rate = $break = $stats = 0;
		}
		else
		{
			if (isset($stats['data']['Stat']))
				$stats = $stats['data']['Stat'];
		
			if (is_array($stats))
			{
				# Подсчёт статистики по всем баннерам
				foreach ($stats as $stat)
				{
					$clicks += $stat['Clicks'];
					$rate += $stat['Sum'];
				}
			}
			else
			{
				$break = true;
				$clicks = $rate = 0;
				$params = array();
				$error_alert = '<strong>Внимание!</strong> Данные от Яндекс.Директ были получены не полностью. Возможна ошибка в статистике.';
				break;
			}
		}
	}
	
	$rate = str_replace(',', '.', $rate);
	
	# Подготовка параметров для запроса
	$params = array (
		'clicks' => $clicks,
		'rate' => $rate,
		'date' => $date_stat
		);
	
	# Добавление новой записи в статистику, если её дата ранее сегодняшнего дня
	if ($date_stat != date('Y-m-d') && strtotime($date_stat) < mktime() && !empty($params) && !isset($break))
		$mDB->CreateItem('direct', $params);
}