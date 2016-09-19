<?php
# Модуль для работы с API SypexGeo
include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_SypexGeo
{
	private static $instance; 	# ссылка на экземпляр класса

	# Получение единственного экземпляра класса.
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_SypexGeo();

		return self::$instance;
	}
	
	# Возвращает данные по IP
	public function GetDataByIP()
	{
		# Опеределение IP
		$ip = $this->GetIP();
	
		# Путь запроса
		$request = 'http://api.sypexgeo.net/json/'.$ip;

		# Отправка запроса
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $request);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		# Получение ответа
		$result = curl_exec($curl);
		curl_close($curl);

		$result = strstr($result, '{');

		$decoded_result = json_decode($result, true);

		return $decoded_result;
	}
	
	# Определение IP адреса по глобальному массиву $_SERVER
	private function GetIP()
	{
		$ip = false;
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipa[] = trim(strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ','));
		
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipa[] = $_SERVER['HTTP_CLIENT_IP'];       
		
		if (isset($_SERVER['REMOTE_ADDR']))
			$ipa[] = $_SERVER['REMOTE_ADDR'];
		
		if (isset($_SERVER['HTTP_X_REAL_IP']))
			$ipa[] = $_SERVER['HTTP_X_REAL_IP'];
		
		# Проверяем IP-адреса на валидность начиная с приоритетного.
		foreach($ipa as $ips)
		{
			# Если IP валидный обрываем цикл, назначаем IP адрес и возвращаем его
			if ($this->IsValidIP($ips))
			{                    
				$ip = $ips;
				break;
			}
		}
		return $ip;
	}
	
	# Проверка валидности IP адреса
	private function IsValidIP($ip = null)
	{
		if (preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $ip))
			return true; // если IP-адрес попадает под регулярное выражение, возвращаем true
		
		return false; // иначе возвращаем false
	}
	
	# Проверка на наличие в СНГ
	private function IsCIS($iso = null)
	{
		$cis = array('RU', 'UA', 'KZ', 'BY', 'AZ', 'AM', 'KG', 'MD', 'TJ', 'TM', 'UZ');
	
		if (in_array($iso, $cis))
			return true; // если IP-адрес попадает под регулярное выражение, возвращаем true
		
		return false; // иначе возвращаем false
	}
	
	# Проверка на наличие в Ближнем зарубежье
	private function IsNearAbroad($iso = null)
	{
		$abroad = array('RU', 'UA', 'KZ', 'BY', 'AZ', 'AM', 'KG', 'MD', 'TJ', 'TM', 'UZ', 'LT', 'LV', 'GE', 'EE');
	
		if (in_array($iso, $abroad))
			return true; // если IP-адрес попадает под регулярное выражение, возвращаем true
		
		return false; // иначе возвращаем false
	}
}