<?php
# Модуль для работы с API Яндекса

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Yandex
{
	private static $instance; 	# ссылка на экземпляр класса

	# Получение единственного экземпляра класса.
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Yandex();

		return self::$instance;
	}
	
	# Возвращает статистику указанной кампании за период не более семи дней
	public function GetStat($login, $token, $application_id, $campaign_id, $start_date, $end_date, $sandbox = false)
	{
		# Путь запроса
		$request = ($sandbox === true) ? 'https://api-sandbox.direct.yandex.ru/json-api/v4/' : 'https://api.direct.yandex.ru/live/v4/json/';
		# https://api.direct.yandex.ru/json-api/v4/

		# Инициализация параметров для авторизации
		$data = array(
			'token' => $token, 
			'application_id' => $application_id, 
			'login' => $login
		);

		# Параметры для запроса
		$data['method'] = "GetBannersStat"; #GetBannersStat GetSummaryStat
		$data['param'] = array(
			'CampaignID' => $campaign_id, # 'CampaignIDS' => array($campaign_id),
			'StartDate' => $start_date,
			'EndDate' => $end_date
		);

		# Подготовка запроса
		$json_data = json_encode($data);

		# Отправка запроса
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $request);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		# Получение ответа
		$result = curl_exec($curl);
		curl_close($curl);

		$result = strstr($result, '{');

		$decoded_result = json_decode($result, true);

		return $decoded_result;
	}
}