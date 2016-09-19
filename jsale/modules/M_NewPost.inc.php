<?php
# Модель для работы с API Новой Почты

class M_NewPost
{
	private static $instance; 	# Ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_NewPost();

		return self::$instance;
	}

	# Создание накладной 
	public function CreateDocument($data, $api_key)
	{
		$array = array(
			'apiKey'			=> $api_key,
			'modelName'			=> 'InternetDocument',
			'calledMethod'		=> 'save',
			'methodProperties'	=> array ($data)
		);
		
		$result = $this->Request($array);

		return $result;
	}
	
	# Получение данных по накладной
	public function GetTrackData($track_id, $api_key)
	{
		$array = array(
			'apiKey'			=> $api_key,
			'modelName'			=> 'InternetDocument',
			'calledMethod'		=> 'documentsTracking',
			'methodProperties'	=> array ('Documents' => array($track_id))
		);
		
		$result = $this->Request($array);

		return $result;
	}
	
	# Получение городов
	public function GetCities($api_key, $city = null)
	{
		$properties = array();
		if ($city !== null)
			$properties = array('FindByString' => $city);
	
		$array = array(
			'apiKey'			=> $api_key,
			'modelName'			=> 'Address',
			'calledMethod'		=> 'getCities',
			'methodProperties'	=> $properties
		);
		
		$result = $this->Request($array);

		return $result;
	}
	
	# Получение улиц города
	public function GetStreets($api_key, $cityRef, $street)
	{
		$properties = array('CityRef' => $cityRef);
		if ($street !== null)
			$properties[] = array('FindByString' => $street);
	
		$array = array(
			'apiKey'			=> $api_key,
			'modelName'			=> 'Address',
			'calledMethod'		=> 'getStreet',
			'methodProperties'	=> $properties
		);
		
		$result = $this->Request($array);

		return $result;
	}
	
	# Отправка запроса к API
	private function Request($data)
	{
		$data_string = json_encode($data);

		$ch = curl_init('https://api.novaposhta.ua/v2.0/json/');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',                                                                       
			'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		$result = json_decode($result);
		curl_close($ch);
		
		return (!empty($result)) ? $result : false;
	}
	
}
?>