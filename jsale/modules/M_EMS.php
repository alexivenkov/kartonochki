<?php
# Модель работы с файлами

if (isset($_POST['type']))
{
	if ($_POST['type'] == 'get_locations')
	{
		include_once dirname(__FILE__) . '/../config.inc.php';
		$mEMS = M_EMS::Instance();
		
		$result = $mEMS->GetLocations();

		if ($result->rsp->stat == 'ok')
		{
			$to = iconv('utf-8', 'windows-1251', $_POST['to']);
			$to = mb_convert_case($to, MB_CASE_UPPER, 'windows-1251');
			
			foreach ($result->rsp->locations as $location)
			{
				$city = mb_strtoupper(iconv('utf-8', 'windows-1251', $location->name));

				if ($city == $to)
				{
					echo $location->value;
					break;
				}
			}
		}
		else
			echo '';
	}
	if ($_POST['type'] == 'calculate')
	{
		include_once dirname(__FILE__) . '/../config.inc.php';
		$mEMS = M_EMS::Instance();

		$result = $mEMS->Calculate($_POST['to'], $config['deliveries']['99']['from'], $config['deliveries']['99']['weigth'], $config['deliveries']['99']['type']);

		if ($result->rsp->stat == 'ok')
			echo $result->rsp->price;
	}
}

class M_EMS
{
	private static $instance; 	# ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_EMS();

		return self::$instance;
	}
	
	# Выбор локаций
	public function GetLocations($type = 'cities')
	{
		# Запрос
		$url = "http://emspost.ru/api/rest/?method=ems.get.locations&type=$type&plain=true";
		
		# Отправка запроса
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		curl_close($ch);

		# Обработка результата
		$result = json_decode($result);
	
		return $result;
	}

	# Подсчёт стоимости
	public function Calculate($to, $from, $weigth, $type)
	{
		# Запрос
		$url = "http://emspost.ru/api/rest/?method=ems.calculate&from=$from&to=$to&weight=$weigth&type=att";
		
		# Отправка запроса
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		curl_close($ch);

		# Обработка результата
		$result = json_decode($result);

		return $result;
	}
}