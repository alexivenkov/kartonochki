<?php

# Подключение конфигов
include_once dirname(__FILE__) . '/../config.inc.php';

# Определение трафика включена
if ($config['targeting']['enabled'] === true)
{
	# Подключение модуля
	include_once dirname(__FILE__) . '/../modules/M_SypexGeo.inc.php';
	$mSypexGeo = M_SypexGeo::Instance();

	# Получение данных
	$data = $mSypexGeo->GetDataByIP();
	
	# Сохранение страны в сессию
	if (!isset($_SESSION))
		session_start();
	$_SESSION['source_country'] = $data['country']['iso'];
	
	# Фильтрация трафика
	if ($config['targeting']['back'] === true)
	{
		# Проверка трафика
		if (in_array($data['country']['iso'], $config['targeting']['good_sources']))
			$targeting = true;
		else
			$targeting = false;

		# Редирект в случае лишнего трафика
		if ($targeting === false)
		{
			header('Location:' . $config['targeting']['back_url']);
			die;
		}
	}
}