<?php

# jSale v1.431
# http://jsale.biz

# Подключение настроек
include_once dirname(__FILE__) . '/config.inc.php';

# Кодировка
header('Content-type: text/html; charset=' . $config['encoding']);
header('Access-Control-Allow-Origin: *');

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

# Подключение модулей
include_once dirname(__FILE__) . '/modules/M_Email.inc.php';
$mEmail = M_Email::Instance();

# Подключение модуля скидок
if ($config['discounts']['enabled'] === true || $config['codes']['enabled'] === true)
{
	include_once dirname(__FILE__) . '/modules/M_Discounts.inc.php';
	$mDiscounts = M_Discounts::Instance();
}

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($sent))
{
	# Проверка на спам
	$spam = $mEmail->CheckSpam(htmlspecialchars($_POST['order_nospam']), $config['secretWord']);

	# Перекодировка данных в случае старой кодировки
	if ($config['encoding'] == 'windows-1251')
		foreach ($_POST as $i => $var)
			$_POST[$i] = iconv('utf-8', 'windows-1251', $var);
	
	# Обработка и сохранение данных в переменные
	$id_form = (int) htmlspecialchars($_POST['id_form']);

	$product['id_product'] = htmlspecialchars($_POST['product_id']);
	$product['code'] = htmlspecialchars($_POST['product_code']);
	$product['title'] = htmlspecialchars($_POST['product_title']);
	$product['price'] = htmlspecialchars($_POST['product_price']);
	$product['qty'] = htmlspecialchars($_POST['product_qty']);
	$product['qty_type'] = htmlspecialchars($_POST['product_qty_type']);
	$product['unit'] = htmlspecialchars($_POST['product_unit']);
	$product['param1'] = (isset($_POST['product_param1'])) ? htmlspecialchars($_POST['product_param1']) : '';
	$product['param2'] = (isset($_POST['product_param2'])) ? htmlspecialchars($_POST['product_param2']) : '';
	$product['param3'] = (isset($_POST['product_param3'])) ? htmlspecialchars($_POST['product_param3']) : '';
	$product['hash'] = htmlspecialchars($_POST['hash']);

	$product['upsell'] = (isset($_POST['order_upsell'])) ? $mEmail->ProcessText($_POST['order_upsell']) : '';
	$product['bonus'] = (isset($_POST['order_bonus'])) ? $mEmail->ProcessText($_POST['order_bonus']) : '';
	$product['discount'] = (isset($_POST['product_discount'])) ? htmlspecialchars($_POST['product_discount']) : 0;
	
	$product['form_config'] = (isset($_POST['form_config'])) ? htmlspecialchars((int) $_POST['form_config']) : 0;
	$product['bandle_products'] = (isset($_POST['bandle_products'])) ? $_POST['bandle_products'] : '';
	
	$template = (isset($_POST['template'])) ? (int) htmlspecialchars($_POST['template']) : '';
	$product['form_type'] = (isset($_POST['form_type'])) ? htmlspecialchars($_POST['form_type']) : $config['product']['form_type'];
	
	$cookies_string = (isset($_POST['cookies'])) ? $_POST['cookies'] : '';
	$cookies = json_decode($_POST['cookies'], true);
	
	$ref = (isset($_POST['ref'])) ? $mEmail->ProcessText($_POST['ref']) : ((isset($cookies['jsale_ref'])) ? $cookies['jsale_ref'] : '');
	$source = (isset($_POST['source'])) ? $mEmail->ProcessText($_POST['source']) : ((isset($cookies['jsale_source'])) ? $cookies['jsale_source'] : '');
	$utm = (isset($_POST['utm'])) ? $mEmail->ProcessText($_POST['utm']) : ((isset($cookies['jsale_utm'])) ? $cookies['jsale_utm'] : '');
	
	# Проверка хэша
	if (md5($config['secretWord'].'_'.$product['price'].'_'.$product['discount'].'_'.$product['title']) != $product['hash'])
		die;
		
	# Настройки для электронных товаров
	if (isset($config['download']['dir']) && $config['download']['enabled'] === true && is_file(dirname(__FILE__) . '/' . $config['download']['dir'] . '/' . $product['code'] . '.' . $config['download']['type']) && is_file(dirname(__FILE__) . '/config2.inc.php'))
		include_once dirname(__FILE__) . '/config2.inc.php';
		
	# Подключение дополнительных настроек
	if (isset($product['form_config']) && !empty($product['form_config']) && is_file(dirname(__FILE__) . '/config' . $product['form_config'] . '.inc.php'))
		include_once dirname(__FILE__) . '/config' . $product['form_config'] . '.inc.php';

	# Обработка и сохранение данных в переменные
    $email = (isset($_POST['order_email'])) ? $mEmail->ProcessText($_POST['order_email']) : '';
    $name = (isset($_POST['order_name'])) ? $mEmail->ProcessText($_POST['order_name']) : '';
    $lastname = (isset($_POST['order_lastname'])) ? $mEmail->ProcessText($_POST['order_lastname']) : '';
    $fathername = (isset($_POST['order_fathername'])) ? $mEmail->ProcessText($_POST['order_fathername']) : '';
    $phone = (isset($_POST['order_phone'])) ? $mEmail->ProcessText($_POST['order_phone']) : '';
	if (!empty($phone))
	{
		$phone = str_replace('(', '', $phone);
		$phone = str_replace(')', '', $phone);
		$phone = str_replace(' ', '', $phone);
		$phone = str_replace('-', '', $phone);
	}
	$country = (isset($_POST['order_country'])) ? $mEmail->ProcessText($_POST['order_country']) : '';
    $region = (isset($_POST['order_region'])) ? $mEmail->ProcessText($_POST['order_region']) : '';
    $zip = (isset($_POST['order_zip'])) ? $mEmail->ProcessText($_POST['order_zip']) : '';
    $city = (isset($_POST['order_city'])) ? $mEmail->ProcessText($_POST['order_city']) : '';
    $address = (isset($_POST['order_address'])) ? $mEmail->ProcessText($_POST['order_address']) : '';
    $comment = (isset($_POST['order_comment'])) ? $mEmail->ProcessText($_POST['order_comment']) : '';
    $payment = (isset($_POST['order_payment'])) ? htmlentities($_POST['order_payment'], ENT_QUOTES) : key($config['payments']);
    $delivery = (isset($_POST['order_delivery'])) ? htmlentities($_POST['order_delivery'], ENT_QUOTES) : key($config['deliveries']);
	$upsell_form = (isset($_POST['upsell_submit'])) ? true : false;

	if (isset($config['payments']['yandex_eshop']['enabled']) && $config['payments']['yandex_eshop']['enabled'] === true)
		$yandex_payment_type = (isset($_POST['yandex_payment_type'])) ? htmlentities($_POST['yandex_payment_type'], ENT_QUOTES) : key($config['payments']['yandex_eshop']['types']);
	else
		$yandex_payment_type = '';
	$order_step = (isset($_POST['order_step'])) ? (int) $_POST['order_step'] : '0';
	
	$disabled = (isset($_POST['disabled'])) ? true : false;
	$message = (isset($_POST['message'])) ? $_POST['message'] : false;

	# Обработка дополнительных полей
	if (isset($config['form']['add']) && is_array($config['form']['add']))
		foreach ($config['form']['add'] as $add_name => $add)
			if (isset($add['enabled']) && $add['enabled'] === true)
				$$add_name = $adds[$add_name] = (isset($_POST['order_' . $add_name])) ? $mEmail->ProcessText($_POST['order_' . $add_name]) : '';

	$code = (isset($_POST['order_code'])) ? htmlspecialchars($_POST['order_code']) : '';
		
	# Добавляем стоимость дополнительных полей
	$delta = 0;
	if (isset($config['form']['add']) && is_array($config['form']['add']))
		foreach ($config['form']['add'] as $add_name => $add)
			if (isset($add['enabled']) && $add['enabled'] === true && isset($$add_name))
				if (isset($config['form']['add'][$add_name]['cost'][$$add_name]))
					$delta += $config['form']['add'][$add_name]['cost'][$$add_name];
					
	# Выборка параметров из БД
	if ($config['params']['enabled'] === true && !empty($product['id_product']))
	{
		$params = $mDB->GetItemsByParamAndSort('param', 'id_product', $product['id_product']);
		$product_params = $disabled = array();
		if (is_array($params) && !empty($params))
		{
			foreach ($params as $param)
			{	
				$key = $param['id_param'];
				$product_params[$key]['title'] = $param['title'];
				$product_params[$key]['type'] = $param['type'];
				$product_params[$key]['parent'] = $param['parent'];
				$product_params[$key]['required'] = $param['required'];
				$product_params[$key]['name'] = $param_name = $param['name'];
				$product_params[$key]['items'] = $mDB->GetItemsByParam('param_item', 'id_param', $param['id_param']);
				sort($product_params[$key]['items']);
				
				# Обработка параметра
				if ($param['type'] == 'flags' && isset($_POST['order_param_'.$param_name]))
				{
					foreach ($_POST['order_param_'.$param_name] as $key2 => $param_key)
						$product['param_'.$param_name][$key2] = (isset($param_key)) ? htmlspecialchars($param_key) : '';
				}
				else
					$product['param_'.$param_name] = (isset($_POST['order_param_'.$param_name])) ? htmlspecialchars($_POST['order_param_'.$param_name]) : '';

				# Добавление наценки за параметр
				foreach ($product_params[$key]['items'] as $key2 => $item)
				{
					if ($item['id_param_item'] == $product['param_'.$param_name] || isset($product['param_'.$param_name][$key2]) && $item['id_param_item'] == $product['param_'.$param_name][$key2])
						$delta += $item['price'];
				
					if ($item['id_param_item'] == $product['param_'.$param_name] && $param['type'] != 'flags')
						$product['param_'.$param_name. '_title'] = $item['title'];
				}

				# Блокировка кнопки отправки заказа
				$disabled[] = (isset($_POST['order_param_'.$param_name]) || $param['required'] == '0') ? 0 : 1;
			}
		}
		if ($config['params']['disable_submit'] === true)
			$disabled = (in_array(1, $disabled)) ? true : false;
		else
		{
			if (in_array(1, $disabled))
				$param_validate = 'Укажите все обязательные параметры';
		}
	}

	# Подсчитываем сумму
	$product['subtotal'] = ($product['price'] + $delta) * $product['qty'];

	# Подсчёт накопительной скидки
	if ($config['discounts']['enabled'] === true)
		$user = $mDiscounts->CountDiscount($product['subtotal'], $config['discounts']['table']);
	else
		$user['discount'] = 0;

	# Подсчёт скидки по промо-коду
	$code_discount = ($config['codes']['enabled'] === true) ? $mDiscounts->CountCodeDiscount($code, $config['codes']['table']) : 0;
	
	# Определяем партнёра и скидку
	if ($config['partner']['enabled'] === true && isset($ref) && !empty($ref))
	{
		$partner = $mDB->GetItemByCode('partner', $ref);
		if (is_array($partner) && isset($partner['id_partner']))
		{
			$id_partner = $partner['id_partner'];
			$ref_discount = ($config['partner']['discount']['enabled'] === true && $partner['discount'] > $config['partner']['discount']['percent']) ? $partner['discount'] : $config['partner']['discount']['percent'];
		}
	}
	else
		$partner = '';
	$ref_discount = (isset($ref_discount)) ? $ref_discount : 0;

	# Определение названия формы оплаты
    $payment_type = $payment;
    $payment = $config['payments'][$payment_type];
    $payment['type'] = $payment_type;
	$payment_discount = (isset($payment['discount'])) ? $payment['discount'] : 0;

	# Выбор максимальной скидки
	$discount = max($user['discount'], $code_discount, $ref_discount, $product['discount'], $payment_discount, 0);

	# Определение названия способа доставки
	if ($config['payment2delivery']['enabled'] === true && isset($config['payment2delivery'][$payment_type]) && !in_array($delivery, $config['payment2delivery'][$payment_type]))
		$delivery = $config['payment2delivery'][$payment_type][0];
    $delivery_type = $delivery;
    $delivery = $config['deliveries'][$delivery_type];
    $delivery['type'] = $delivery_type;

	# Учёт скидки
	if ($config['discounts']['fixed'] === true)
		$product['subtotal'] = number_format($product['subtotal'] - $discount, 2, '.', '');
	else
		$product['subtotal'] = number_format($product['subtotal'] * (1 - $discount / 100), 2, '.', '');
	
	# Прибавление стоимости доставки
	if (isset($delivery['free']) && $delivery['free'] != '' && $product['subtotal'] > $delivery['free'] || isset($payment['free_delivery']) && $payment['free_delivery'] === true)
		$delivery['cost'] = 0;

	# Подсчитываеим стоимость доставки
	#if (isset($config['deliveries_different_costs']) && $config['deliveries_different_costs'] === true)
	#	if (!empty($product['param3']))
	#		$delivery['cost'] *= floatval(str_replace(',', '.', $product['param3']));
	
	# Вычисляем сумму заказа
	$order_sum = number_format($product['subtotal'] + $delivery['cost'], 2, '.', '');

	# Предупреждение, если остатков не достаточно
	if ($config['store']['enabled'] === true && $config['store']['notice']['enabled'] === true || $config['store']['enabled'] === true && $config['store']['decrease_order'] === true)
	{
		$db_product = $mDB->GetItemByCode('product', $product['code']);

		if ($db_product && $db_product['store'] < $product['qty'] && $config['store']['notice']['enabled'] === true)
			$message = $config['store']['notice']['text'];
	}

	if ($_POST['action'] == 'send')
	{
		# Валидация данных
		$validate = $mEmail->ValidateForm($email, $name, $lastname, $fathername, $phone, $zip, $country, $region, $city, $address, $product['qty'], $config);

		# Валидация дополнительных полей
		if ($validate === false && isset($adds))
			$validate = $mEmail->ValidateAddForm($adds, $config);
			
		# Валидация дополнительных полей товара
		if (!$validate && isset($param_validate))
			$validate = $param_validate;

		if (!$spam)
		{
			$message = $config['form']['isSpam'];
		}
		elseif ($validate)
		{
			$message = $validate;
		}
		elseif ($order_sum < $config['min_sum'])
		{
			$message = $config['form']['notMinSum'];
		}
		# Вывод апсела
		elseif ($config['upsells']['enabled'] === true && isset($config['upsell'][$product['upsell']]) && $upsell_form === false)
		{
			# Генерация антиспама
			$antispam = $mEmail->GenerateAntispam($config['secretWord']);		
		
			$upsell_product = $config['upsell'][$product['upsell']];
			ob_start();
			include_once dirname(__FILE__) . '/design/upsellForm.tpl.php';
			$echo = ob_get_clean();
			
			$upsell_form = true;
		}
		# Обработка успешного заказа
		else
		{
			# Маркер, сигнализирующий создание нового заказа
			$new_order = 1;
			
			# Подстановка скидки в массив product
			$product['discount'] = number_format($discount, 2, '.', '');
			
			#  Вставляем в комментарий промо-код
			if (isset($code) && !empty($code))
				$comment .= '<br/>Использован промо-код:' . $code . '<br/>';

			# Вставляем в комментарий дополнительные поля
			if (isset($config['form']['add']) && is_array($config['form']['add']))
				foreach ($config['form']['add'] as $add_name => $add)
					if (is_array($add) && isset($add['enabled']) && $add['enabled'] === true && isset($$add_name))
						$comment .= ($add['type'] == 'checkbox' && $$add_name == '' || $add['type'] == 'select' && $$add_name == '') ? '' : $add['label'] . ' ' . $$add_name . '<br/>';
						
			# Вставляем в комментарий дополнительные поля товара
			if (isset($product_params) && is_array($product_params) && !empty($product_params))
			{
				foreach ($product_params as $product_param)
				{
					$param_name = $product_param['name'];

					# Добавление
					foreach ($product_param['items'] as $key2 => $item)
					{
						if ($item['id_param_item'] == $product['param_'.$param_name] || isset($product['param_'.$param_name][$key2]) && $item['id_param_item'] == $product['param_'.$param_name][$key2])
							$comment .= $product_param['title'] . ': '. $item['title'] . '<br/>';
					}
				}
			}

			# Увеличение цены товара
			if (isset($delta))
				$product['price'] = $product['price'] + $delta;

			# Уменьшение остатков
			if ($config['store']['enabled'] === true && $config['store']['decrease_order'] === true)
			{
				if (isset($db_product) && isset($db_product['store']))
				{
					$params = array ('store' => $db_product['store'] - $product['qty']);
					$result = $mDB->EditItemByCode('product', $params, $product['code'], true);
				} 
			}
			
			$products = array($product);
			
			# Добавляем товары комплекта
			if ($product['bandle_products'] != '')
			{
				$bandle_products = explode('|', $product['bandle_products']);
				
				foreach ($bandle_products as $bandle_product)
				{
					$product_for_add = $mDB->GetItemByParam('product', 'code', $bandle_product);
					$product_for_add['price'] = '0.00';
					$product_for_add['discount'] = '0';
					$product_for_add['subtotal'] = '0';
					$product_for_add['form_config'] = $product['form_config'];
					array_push($products, $product_for_add);
				}
			}

			# Добавляем апсел
			if ($_POST['submit'] == 'upsell_accept' && isset($config['upsell'][$product['upsell']]))
			{
				$upsell_product = $config['upsell'][$product['upsell']];
				
				# Учёт скидки
				if ($config['discounts']['fixed'] === true)
					$upsell_product['subtotal'] = number_format($upsell_product['qty'] * $upsell_product['price'] - $upsell_product['discount'], 2, '.', '');
				else
					$upsell_product['subtotal'] = number_format( $upsell_product['qty'] * $upsell_product['price'] * (1 - $upsell_product['discount'] / 100) , 2, '.', '');
					
				# Объединяем массив
				$products = array_push($product, $upsell_product);
				# Новая сумма заказа
				$order_sum = number_format( $order_sum + $upsell_product['subtotal'] , 2, '.', '');
			}
			
			# Добавляем бонус
			if ($config['bonuses']['enabled'] === true && $product['bonus'] != '' && isset($config['bonus'][$product['bonus']]))
			{
				$bonus_product = $config['bonus'][$product['bonus']];
				
				# Объединяем массив
				array_push($products, $bonus_product);
			}


            $delivery['cost'] = $_POST['delivery_cost'];
            $delivery['pvz-address'] = $_POST['pvz-address'];

			# Подключение модуля работы с заказами (сохранение заказа в БД).
			if ($config['database']['enabled'] === true && is_file(dirname(__FILE__) . '/modules/C_Orders.inc.php'))
				include_once dirname(__FILE__) . '/modules/C_Orders.inc.php';

			# Подключение модуля партнёрской программы
			if ($config['partner']['enabled'] === true && is_file(dirname(__FILE__) . '/modules/C_Partner.inc.php') && isset($ref) && !empty($ref))
				include_once dirname(__FILE__) . '/modules/C_Partner.inc.php';

			# Генерация идентификатора заказа, если его ещё не существует.
			if (empty($id_order))
				$id_order = time();

			# Подключение модуля оплаты (генерация формы или счёта для оплаты)
			if (is_file(dirname(__FILE__) . '/modules/C_Payment.php'))
				include_once dirname(__FILE__) . '/modules/C_Payment.php';
				
			# Подключение модуля отправки SMS уведомления
			if (is_file(dirname(__FILE__) . '/modules/C_SMS.inc.php'))
			{
				if ($config['sms']['order2admin'] === true)
				{
					$sms_type = 'order2admin';
					include dirname(__FILE__) . '/modules/C_SMS.inc.php';
				}

				if ($config['sms']['order2customer'] === true)
				{
					$sms_type = 'order2customer';
					include dirname(__FILE__) . '/modules/C_SMS.inc.php';
				}
			}
			
			# Генерация хеш-строки для подтверждения заказа
			$hash = ($config['email']['confirm'] === true || $config['email']['refuse'] === true) ? $hash = $mEmail->GenerateHash($id_order, $order_sum, $config['secretWord']) : '';
			$hash2 = ($config['email']['changePayment'] === true) ? $hash2 = $mEmail->GenerateHash($id_order, 'CHANGE', $config['secretWord']) : '';

			if (!isset($_SESSION))
				session_start();
			
			# Сохраняем ID заказа в сессию
			$_SESSION['jsale_order'] = $id_order;
			
			# Подстановка номера заказа в тему письма
			$emailSubjectAdminOrder = str_replace('№№', '№' . $id_order, $config['email']['subjectAdminOrder']);
			$emailSubjectOrder = str_replace('№№', '№' . $id_order, $config['email']['subjectOrder']);
			
			# Подготовка сообщения к отправке.
			$adminContent = $mEmail->PrepareOrder($id_order, $email, $lastname, $name, $fathername, $phone, $zip, $country, $region, $city, $address, $comment, $products, $order_sum, $payment, $yandex_payment_type, $delivery, $hash, $hash2, $config, $partner, 'true', $product['form_config']);
			$customerContent = $mEmail->PrepareOrder($id_order, $email, $lastname, $name, $fathername, $phone, $zip, $country, $region, $city, $address, $comment, $products, $order_sum, $payment, $yandex_payment_type, $delivery, $hash, $hash2, $config, $partner, false, $product['form_config']);
			
			# Подстановка почтового адреса и имени
			if (!empty($email) && $config['email']['from_customer'] === true)
			{
				$email_from = $email;
				$name_from = $name;
			}
			else
			{
				$email_from = $config['email']['answer'];
				$name_from = $config['email']['answerName'];
			}

			# Отправка письма.
			if ( !$mEmail->SendEmail($config['email']['receiver'], $email_from, $emailSubjectAdminOrder, $adminContent, $name_from, $config['encoding']) )
			{
				$message = $config['form']['notSent'];
			}
			# Подтверждение отправки.
			else
			{
				if (!empty($email))
					$mEmail->SendEmail($email, $config['email']['answer'], $emailSubjectOrder, $customerContent, $config['email']['answerName'], $config['encoding']);
				$sent = 1;
			}
		}
	}
}

# Если письмо отправлено, выводим сообщение об этом.
if (isset($sent))
{
	$echo = $config['form']['sent'];

	if (isset($payment['form']))
		$echo .= <<<EOF
		<div id="jsale-payment" style="text-align:center; padding-bottom: 30px;">Оплатить заказ можно прямо сейчас: {$payment['form']}
		<br/>
		Сейчас вы будете перенаправлены на платёжный шлюз. Если этого не произошло, нажмите кнопку "Оплатить".
		<script type="text/javascript">
			setTimeout(function(){
				jQuery('#jsale-payment form').attr('target', '_self');
				jQuery('#jsale-payment form').submit();
			}
			, 3000);
			
		</script>
		</div>
EOF;
	else
		$echo .= '<!--order_send-->';

	if (isset($payment['link']))
		$echo .= '<script type="text/javascript">var redirect="'.$payment['link'].'";</script>';
}
elseif ($config['upsells']['enabled'] === false || $config['upsells']['enabled'] === true && isset($upsell_form) && $upsell_form === false)
{
	# Генерируем антиспам.
	$antispam = $mEmail->GenerateAntispam($config['secretWord']);
	
	# Устанавливаем шаблон
	$template = (!empty($template)) ? '_' .$template : '';
	
	ob_start();
	include_once dirname(__FILE__) . "/design/orderForm$template.tpl.php";
	$echo = ob_get_clean();
}

# Вывод на экран
echo $echo;