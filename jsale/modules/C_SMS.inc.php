<?php

# Отправка SMS уведомлений 

if (isset($config['sms']['enabled']) && $config['sms']['enabled'] === true && isset($sms_type))
{
	if ($sms_type == 'order2admin' || $sms_type == 'paid2admin' || $sms_type == 'call2admin')
		$sms_reseiver = $config['sms']['phone'];
	elseif ($sms_type == 'order2customer' && isset($phone) || $sms_type == 'paid2customer' && isset($phone) || $sms_type == 'status2customer' && isset($phone) || $sms_type == 'call2customer' && isset($phone) || $sms_type == 'trackSent2customer' && isset($phone) || $sms_type == 'trackDelivered2customer' && isset($phone))
		$sms_reseiver = $phone;
	else
		$sms_reseiver = false;
		
	$sum = (isset($paid_sum)) ? $paid_sum : $order_sum;
	
	# Выборка трека
	if (!isset($track_id))
		$track_id = $order['track'];
	
	# Выборка первого товара
	$order_item = $mDB->GetItemByParam('custom_item', 'id_custom', $id_order);
	
	if (!isset($order))
		$order = $mDB->GetItemById('custom', $id_order);

	# Шаблон SMS
	ob_start();
	include dirname(__FILE__) . '/../design/smsTemplates.tpl.php';
	$sms_text = ob_get_clean();

	/*if ($sms_type == 'order2admin')
		$sms_text = 'Новый заказ №' . $id_order . ' на сумму ' . $sum . ' ' . $config['currency']. ' с ' . $config['sitelink'];
	elseif ($sms_type == 'paid2admin')
		$sms_text = 'Заказ на сумму ' . $sum . ' ' . $config['currency']. ' оплачен';
	elseif ($sms_type == 'order2customer')
		$sms_text = 'Ваш заказ №' . $id_order . ' на сумму ' . $sum . ' ' . $config['currency']. ' принят! ';
	elseif ($sms_type == 'paid2customer')
		$sms_text = 'Ваш заказ №' . $id_order . ' на сумму ' . $sum . ' ' . $config['currency']. ' оплачен';
	elseif ($sms_type == 'status2customer' && isset($order['status']))
		$sms_text = 'Ваш заказ №' . $id_order . ' на сумму ' . $sum . ' ' . $config['currency']. ' переведён в статус ' . $config['statuses'][$order['status']];
	elseif ($sms_type == 'call2admin')
		$sms_text = 'Заказ звонка добавлен на сайт ' . $config['sitelink'];
	elseif ($sms_type == 'call2customer')
		$sms_text = 'Заказ звонка добавлен на сайт ' . $config['sitelink'];
	else
		$sms_text = false;*/
		
	# Замена переменных
	$sitelink = preg_replace('!^https?://!i', '', $config['sitelink']);
	$sitelink = rtrim($sitelink, '/');
	
	$sms_text = str_replace('{{id_order}}', $id_order, $sms_text);
	$sms_text = str_replace('{{email}}', $order['email'], $sms_text);
	$sms_text = str_replace('{{phone}}', $order['phone'], $sms_text);
	$sms_text = str_replace('{{sum}}', $order['sum'], $sms_text);
	$sms_text = str_replace('{{currency}}', $config['currency'], $sms_text);
	$sms_text = str_replace('{{sitelink}}', $sitelink, $sms_text);
	$sms_text = str_replace('{{product}}', $order_item['product'], $sms_text);
	$sms_text = str_replace('{{price}}', $order_item['price'], $sms_text);
	$sms_text = str_replace('{{qty}}', $order_item['quantity'], $sms_text);
	$sms_text = str_replace('{{name}}', $order['name'], $sms_text);
	$sms_text = str_replace('{{lastname}}', $order['lastname'], $sms_text);
	$sms_text = str_replace('{{status}}', $config['statuses'][$order['status']], $sms_text);
	$sms_text = str_replace('{{datetime}}', date('d.m.Y H:i:s', strtotime($order['date'])), $sms_text);
	$sms_text = str_replace('{{date}}', date('d.m.Y', strtotime($order['date'])), $sms_text);
	$sms_text = str_replace('{{time}}', date('H:i:s', strtotime($order['date'])), $sms_text);
	$sms_text = str_replace('{{track_id}}', $track_id, $sms_text);

	if ($sms_reseiver != false && $sms_text != false)
	{
		# AlphaSMS.com.ua
		if ($config['sms']['provider'] == 'AlphaSMS')
		{
			if (isset($config['sms']['provider']) && $config['sms']['provider'] == 'AlphaSMS' && isset($config['sms']['api_key']) && isset($sms_reseiver))
			{
				$url = 'http://alphasms.com.ua/api/xml.php';

				$xml = '<?xml version="1.0" encoding="utf-8" ?>
				<package key="' . $config['sms']['api_key'] . '">
				<message>
				<msg recipient= "' . $sms_reseiver . '" sender= "Shop SMS" type="0">' . $sms_text . '</msg>
				</message>
				</package>';

				$curl = curl_init();
				$data = array('Content-Type: text/xml; charset=utf-8');

				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

				curl_exec($curl);

				curl_close($curl);
			}
		}
		# SMS.ru
		elseif ($config['sms']['provider'] == 'SMSru')
		{
			require_once dirname(__FILE__) . '/M_SMSru.inc.php';
			
			if (!isset($sms))
				$sms = new smsru( $config['sms']['api_key'] );
			$result = $sms->sms_send( $sms_reseiver, $sms_text, $config['sms']['name'], time(), $config['sms']['translit'], false, '17181');
		}
		# GoodSMS.ru
		elseif ($config['sms']['provider'] == 'GoodSMS')
		{
			require_once dirname(__FILE__) . '/M_GoodSMS.inc.php';
			
			# настройки API
			$Api = new SmsServiceApi($config['sms']['api_uid'], $config['sms']['api_key']);
			
			$trans = array("+" => "");
			strtr($sms_reseiver, $trans);

			# параметры
			$api_params = array(
				'pid' => $config['sms']['api_pid'],
				'sender' => $config['sms']['name'],
				'to' => $sms_reseiver,
				'text' => $sms_text
			);

			# отправка
			$sms_res = $Api->send('delivery.sendSms', $api_params);
		}
	}
}