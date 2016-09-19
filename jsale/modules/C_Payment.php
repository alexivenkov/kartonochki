<?php
# Контроллер обработки оплаты заказа

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

# Подключение настроек
include_once dirname(__FILE__) . '/../config.inc.php';

# Настройки для электронных товаров
if (is_file(dirname(__FILE__) . '/../config2.inc.php'))
	include_once dirname(__FILE__) . '/../config2.inc.php';

# Подключение модулей оплаты
if (is_file(dirname(__FILE__) . '/M_Robokassa.inc.php'))
{
	include_once dirname(__FILE__) . '/M_Robokassa.inc.php';
	$mRobokassa = M_Robokassa::Instance();
}
if (is_file(dirname(__FILE__) . '/M_Interkassa.inc.php'))
{
	include_once dirname(__FILE__) . '/M_Interkassa.inc.php';
	$mInterkassa = M_Interkassa::Instance();
}
if (is_file(dirname(__FILE__) . '/M_A1Pay.inc.php'))
{
	include_once dirname(__FILE__) . '/M_A1Pay.inc.php';
	$mA1Pay = M_A1Pay::Instance();
}
if (is_file(dirname(__FILE__) . '/M_Liqpay.inc.php'))
{
	include_once dirname(__FILE__) . '/M_Liqpay.inc.php';
	$mLiqpay = M_Liqpay::Instance();
}
if (is_file(dirname(__FILE__) . '/M_RBKmoney.inc.php'))
{
	include_once dirname(__FILE__) . '/M_RBKmoney.inc.php';
	$mRBKmoney = M_RBKmoney::Instance();
}
if (is_file(dirname(__FILE__) . '/M_QIWI.inc.php'))
{
	include_once dirname(__FILE__) . '/M_QIWI.inc.php';
	$mQIWI = M_QIWI::Instance();
}
if (is_file(dirname(__FILE__) . '/M_Webmoney.inc.php'))
{
	include_once dirname(__FILE__) . '/M_Webmoney.inc.php';
	$mWebmoney = M_Webmoney::Instance();
}
if (is_file(dirname(__FILE__) . '/M_YandexMoney.inc.php'))
{
	include_once dirname(__FILE__) . '/M_YandexMoney.inc.php';
	$mYandexMoney = M_YandexMoney::Instance();
}
if (is_file(dirname(__FILE__) . '/M_PayPal.inc.php'))
{
	include_once dirname(__FILE__) . '/M_PayPal.inc.php';
	$mPayPal = M_PayPal::Instance();
}
if (is_file(dirname(__FILE__) . '/M_W1.inc.php'))
{
	include_once dirname(__FILE__) . '/M_W1.inc.php';
	$mW1 = M_W1::Instance();
}
if (is_file(dirname(__FILE__) . '/M_Privat24.inc.php'))
{
	include_once dirname(__FILE__) . '/M_Privat24.inc.php';
	$mPrivat24 = M_Privat24::Instance();
}
if (is_file(dirname(__FILE__) . '/M_Paysera.inc.php'))
{
	include_once dirname(__FILE__) . '/M_Paysera.inc.php';
	$mPaysera = M_Paysera::Instance();
}
if (is_file(dirname(__FILE__) . '/M_Paybox.inc.php'))
{
	include_once dirname(__FILE__) . '/M_Paybox.inc.php';
	$mPaybox = M_Paybox::Instance();
}
if (is_file(dirname(__FILE__) . '/M_SpryPay.inc.php'))
{
	include_once dirname(__FILE__) . '/M_SpryPay.inc.php';
	$mSpryPay = M_SpryPay::Instance();
}

# Проверка статуса заказа
if (isset($_GET['check_payment']))
{
	$check_payment = true;
	$order_for_check = htmlspecialchars($_GET['order']);
}

# Если заказ отправлен, то составление счёта на оплату
if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($payment_link) || isset($check_payment))
{
	# Обработка массива
	$post = array();
	foreach ($_POST as $key => $value)
		if (is_string($value))
			$post[$key] = htmlspecialchars($value);
		elseif (is_array($value))
			foreach ($value as $key2 => $value2)
				$post[$key][$key2] = htmlspecialchars($value2);

	if (isset($new_order))
	{
		$successURL = $config['sitelink'] . $config['successURL'];
		$failURL = $config['sitelink'] . $config['failURL'];
		$checkURL = $config['sitelink'] . $config['dir'] . 'modules/C_Payment.php';
		$currencyCode = (isset($config['payments'][$payment['type']]['currencyCode'])) ? $config['payments'][$payment['type']]['currencyCode'] : $config['currencyCode'];
		$orderSum = (isset($config['payments'][$payment['type']]['rate'])) ? number_format($order_sum * $config['payments'][$payment['type']]['rate'], 2, '.', '') : $order_sum;
	
		if ($payment['type'] == 'robokassa' && $config['payments']['robokassa']['enabled'] === true)
		{
			$payment['link'] = $mRobokassa->InitiatePayment($config['payments']['robokassa']['login'], $config['payments']['robokassa']['pass1'], $id_order, $config['payments']['robokassa']['description'], $orderSum, $config['payments']['robokassa']['test']);
		}
		elseif ($payment['type'] == 'interkassa' && $config['payments']['interkassa']['enabled'] === true)
		{
			$payment['form'] = $mInterkassa->InitiatePayment($config['payments']['interkassa']['shop_id'], $orderSum, $id_order, $config['payments']['interkassa']['description'], $config['currencyCode']);
		}
		elseif ($payment['type'] == 'a1pay' && $config['payments']['a1pay']['enabled'] === true)
		{
			$payment['form'] = $mA1Pay->InitiatePayment($config['payments']['a1pay']['secret_key'], $orderSum, $config['payments']['a1pay']['description'], $config['email']['receiver'], $id_order);
		}
		elseif ($payment['type'] == 'rbkmoney' && $config['payments']['rbkmoney']['enabled'] === true)
		{
			$payment['form'] = $mRBKmoney->InitiatePayment($config['payments']['rbkmoney']['shop_id'], $orderSum, $currencyCode, $id_order, $email, $config['payments']['rbkmoney']['description'], $config['sitelink'] . $config['successURL'], $config['sitelink'] . $config['failURL'], $config['payments']['rbkmoney']['secret_key']);
		}
        elseif ($payment['type'] == 'liqpay' && $config['payments']['liqpay']['enabled'] === true)
        {
            $payment['form'] = $mLiqpay->InitiatePayment($successURL, $checkURL, $config['payments']['liqpay']['shop_id'], $id_order, $orderSum, $currencyCode, $config['payments']['liqpay']['description'], $config['payments']['liqpay']['secret_key'], $config['payments']['liqpay']['test']);
        }
        elseif ($payment['type'] == 'webmoney' && $config['payments']['webmoney']['enabled'] === true)
        {
			$successURL = $config['sitelink'] . $config['successURL'];
            $payment['form'] = $mWebmoney->InitiatePayment($config['payments']['webmoney']['purse'], $orderSum, $id_order, $config['payments']['webmoney']['description'], $checkURL, $successURL);
        }
        elseif ($payment['type'] == 'webmoney2' && $config['payments']['webmoney2']['enabled'] === true)
        {
            $payment['form'] = $mWebmoney->InitiatePayment($config['payments']['webmoney2']['purse'], $orderSum, $id_order, $config['payments']['webmoney2']['description'], $checkURL, $successURL);
        }
        elseif ($payment['type'] == 'yandex' && $config['payments']['yandex']['enabled'] === true)
        {
			$product_title = (isset($products[0]['title'])) ? $products[0]['title'] : $order_items[0]['product'];
			$order_desc = $config['payments']['yandex']['description'] . ' ' . $product_title;
		
            $payment['form'] = $mYandexMoney->InitiatePayment($config['payments']['yandex']['purse'], $orderSum, $id_order, $order_desc, $config['sitename'], $successURL);
        }
        elseif ($payment['type'] == 'yandex2' && $config['payments']['yandex2']['enabled'] === true)
        {	
			$product_title = (isset($products[0]['title'])) ? $products[0]['title'] : $order_items[0]['product'];
			$order_desc = $config['payments']['yandex2']['description'] . ' ' . $product_title;
		
            $payment['form'] = $mYandexMoney->InitiatePaymentCards($config['payments']['yandex2']['purse'], $orderSum, $id_order, $order_desc, $config['sitename'], $successURL);
        }
		elseif ($payment['type'] == 'yandex_eshop' && $config['payments']['yandex_eshop']['enabled'] === true)
        {
			if (isset($order['payment_ym']))
				$payment_type = $order['payment_ym'];
			elseif (isset($yandex_payment_type))
				$payment_type = $yandex_payment_type;
			else
				$payment_type = key($config['payments']['yandex_eshop']['types']);
		
            $payment['form'] = $mYandexMoney->InitiatePaymentESHop($config['payments']['yandex_eshop']['shop_id'], $config['payments']['yandex_eshop']['scid'], $phone, $orderSum, $id_order, $payment_type, $successURL, $failURL, $config['payments']['yandex_eshop']['description'], $email, $name, $address, $config['payments']['yandex_eshop']['test']);
        }
        elseif ($payment['type'] == 'qiwi' && $config['payments']['qiwi']['enabled'] === true)
        {
			$payment['link'] = $mQIWI->InitiatePayment($config['payments']['qiwi']['shop_id'], $config['payments']['qiwi']['rest_id'], $config['payments']['qiwi']['rest_password'], $phone, $orderSum, $currencyCode, $config['payments']['qiwi']['description'], $id_order, $checkURL.'?check_payment=1&order='.$id_order, $failURL);
        }
        elseif ($payment['type'] == 'paypal' && $config['payments']['paypal']['enabled'] === true)
        {
			$customer_info = array();
			if (isset($name) || isset($order['name']))
				$customer_info['first_name'] = (isset($name)) ? $name : $order['name'];
			if (isset($lastname) || isset($order['lastname']))
				$customer_info['last_name'] = (isset($lastname)) ? $lastname : $order['lastname'];
			if (isset($address) || isset($order['address']))
				$customer_info['address1'] = (isset($address)) ? $address : $order['address'];
			if (isset($city) || isset($order['city']))
				$customer_info['city'] = (isset($city)) ? $city : $order['city'];
			if (isset($zip) || isset($order['zip']))
				$customer_info['zip'] = (isset($zip)) ? $zip : $order['zip'];
			if (isset($email) || isset($order['email']))
				$customer_info['email'] = (isset($email)) ? $email : $order['email'];
			if (isset($country) || isset($order['country']))
				$customer_info['country'] = (isset($country)) ? $country : $order['country'];
				
			$product_title = (isset($products[0]['title'])) ? $products[0]['title'] : $order_items[0]['product'];
			$product_id  = (isset($products[0]['code'])) ? $products[0]['code'] : $order_items[0]['id_product'];
			
			$payment['form'] = $mPayPal->InitiatePayment($checkURL, $successURL, $failURL, $config['payments']['paypal']['receiver_email'], $product_title, $product_id, $orderSum, null, $currencyCode, $config['payments']['paypal']['test'], $id_order, $customer_info);
        }
		elseif ($payment['type'] == 'paypal2' && $config['payments']['paypal2']['enabled'] === true)
        {
			$customer_info = array();
			if (isset($name) || isset($order['name']))
				$customer_info['first_name'] = (isset($name)) ? $name : $order['name'];
			if (isset($lastname) || isset($order['lastname']))
				$customer_info['last_name'] = (isset($lastname)) ? $lastname : $order['lastname'];
			if (isset($address) || isset($order['address']))
				$customer_info['address1'] = (isset($address)) ? $address : $order['address'];
			if (isset($city) || isset($order['city']))
				$customer_info['city'] = (isset($city)) ? $city : $order['city'];
			if (isset($zip) || isset($order['zip']))
				$customer_info['zip'] = (isset($zip)) ? $zip : $order['zip'];
			if (isset($email) || isset($order['email']))
				$customer_info['email'] = (isset($email)) ? $email : $order['email'];
			if (isset($country) || isset($order['country']))
				$customer_info['country'] = (isset($country)) ? $country : $order['country'];
			
			$product_title = (isset($products[0]['title'])) ? $products[0]['title'] : $order_items[0]['product'];
			$product_id  = (isset($products[0]['code'])) ? $products[0]['code'] : $order_items[0]['id_product'];
			
			$payment['form'] = $mPayPal->InitiatePayment($checkURL, $successURL, $failURL, $config['payments']['paypal2']['receiver_email'], $product_title, $product_id, $orderSum, $order_sum, $currencyCode, $config['payments']['paypal2']['test'], $id_order, $customer_info);
        }
		elseif ($payment['type'] == 'w1' && $config['payments']['w1']['enabled'] === true)
		{
			$payment['form'] = $mW1->InitiatePayment($config['payments']['w1']['shop_id'], $orderSum, $currencyCode, $id_order, $email, $config['payments']['w1']['description'], $config['sitelink'] . $successURL, $failURL, $config['payments']['w1']['secret_key'], $config['encoding']);
		}
		elseif ($payment['type'] == 'privat24' && $config['payments']['privat24']['enabled'] === true)
        {
		    $payment['form'] = $mPrivat24->InitiatePayment($id_order, $config['payments']['privat24']['id_merchant'], $orderSum, $currencyCode, $config['payments']['privat24']['description'], $checkURL);
        }
		elseif ($payment['type'] == 'paysera' && $config['payments']['paysera']['enabled'] === true)
        {
			$countryCode = (isset($country)) ? $country : $order['country'];
			$orderSum = number_format($order_sum * 100, 0, '', '');
		
			$payment['link'] = $mPaysera->InitiatePayment($config['payments']['paysera']['project_id'], $config['payments']['paysera']['password'], $id_order, $orderSum, $currencyCode, $countryCode, $successURL, $failURL, $checkURL, $config['payments']['paysera']['test']);
        }
		elseif ($payment['type'] == 'paybox' && $config['payments']['paybox']['enabled'] === true)
        {
		    $payment['form'] = $mPaybox->InitiatePayment($id_order, $config['payments']['paybox']['merchant_id'], $config['payments']['paybox']['secret_key'], $orderSum, $currencyCode, $config['payments']['paybox']['description'], $email, $phone, $successURL, $failURL, $checkURL, $config['payments']['paybox']['test']);
        }
		elseif ($payment['type'] == 'sprypay' && $config['payments']['sprypay']['enabled'] === true)
        {
		    $payment['form'] = $mSpryPay->InitiatePayment($config['payments']['sprypay']['shop_id'], $id_order, $orderSum, $currencyCode, $config['payments']['sprypay']['description'], $email, $checkURL);
        }
		
		if (isset($payment['form']))
		{
			$hash = $mEmail->GenerateHash($id_order, $order_sum, $config['secretWord']);
			$payment['link'] = $config['sitelink'] . 'jsale/pay/index.php?id_order=' . $id_order . '&hash=' . $hash;
		}
	}
	else
	{
		# Логирование ошибок
		if ($config['payment_logging'] === true)
		{
			$pathfile = dirname(__FILE__) . '/../payment.txt';
			$content = @file_get_contents($pathfile);
			foreach ($_POST as $var => $val)
				$content .= "$var => $val\n";
			$content .= "\n";
			file_put_contents($pathfile, $content);
		}
	
        if (isset($post['SignatureValue']))
        {
            if ($mRobokassa->CheckResult($config['payments']['robokassa']['pass2'], $post) == true)
            {
                $paid_order_id = $post['InvId'];
                $paid_sum = $post['OutSum'];
                $payment_type = 'robokassa';
            }
        }
        elseif (isset($post['ik_sign']))
        {
            if ($mInterkassa->CheckResult($config['payments']['interkassa']['shop_id'], $config['payments']['interkassa']['secret_key'], $post, $config['payments']['interkassa']['test_key']) == true)
            {
                $paid_order_id = $post['ik_pm_no'];
                $paid_sum = $post['ik_am'];
                $payment_type = 'interkassa';
            }
        }
        elseif (isset($post['check']))
        {
            if ($mA1Pay->CheckResult($post, $config['payments']['a1pay']['secret_word']) === true)
            {
                $paid_order_id = $post['order_id'];
                $paid_sum = $post['system_income'];
                $payment_type = 'a1pay';
            }
        }
		elseif (isset($post['rupay_payment_sum']))
		{
			if ($mRBKmoney->CheckResult($post, $config['payments']['rbkmoney']['shop_id'], $config['payments']['rbkmoney']['secret_key']) === true)
			{
                $paid_order_id = $post['orderId'];
                $paid_sum = $post['rupay_payment_sum'];
                $payment_type = 'rbkmoney';
			}
		}		
        elseif (isset($post['signature']))
        {
            if ($mLiqpay->CheckResult($config['payments']['liqpay']['secret_key']))
            {
                $paid_order_id = $post['order_id'];
                $paid_sum = $post['amount'];
                $payment_type = 'liqpay';
            }
			elseif ($payment_data = $mPrivat24->CheckResult($post, $config['payments']['privat24']['secret_key']))
            {
				preg_match('|order=(.*)&|Uisu', $payment_data, $matches);
				$paid_order_id = $matches[1];
				
				preg_match('|amt=(.*)&|Uisu', $payment_data, $matches);
				$paid_sum = $matches[1];
				
                $payment_type = 'privat24';
	        }
			
        }
		elseif (isset($post['rupay_payment_sum']))
		{
			if ($mRBKmoney->CheckResult($post, $config['payments']['rbkmoney']['shop_id'], $config['payments']['rbkmoney']['secret_key']) === true)
			{
                $paid_order_id = $post['orderId'];
                $paid_sum = $post['rupay_payment_sum'];
                $payment_type = 'rbkmoney';
			}
		}
		elseif (isset($post['LMI_PAYEE_PURSE']))
		{
			if ($mWebmoney->CheckResult($config['payments']['webmoney']['purse'], $config['payments']['webmoney']['secret'], $post))
			{
				$paid_order_id = $post['LMI_PAYMENT_NO'];
			
				if (isset($config['payments']['webmoney']['rate']))
					$paid_sum = $post['LMI_PAYMENT_AMOUNT'] / $config['payments']['webmoney']['rate'];
				else
					$paid_sum = $post['LMI_PAYMENT_AMOUNT'];

				$paid_sum = number_format($paid_sum, 2, ',', '');
				$paid_sum = str_replace(',', '.', $paid_sum);
					
				$payment_type = 'webmoney';
			}
			elseif ($mWebmoney->CheckResult($config['payments']['webmoney2']['purse'], $config['payments']['webmoney2']['secret'], $post))
			{
				$paid_order_id = $post['LMI_PAYMENT_NO'];

				if (isset($config['payments']['webmoney2']['rate']))
					$paid_sum = $post['LMI_PAYMENT_AMOUNT'] / $config['payments']['webmoney2']['rate'];
				else
					$paid_sum = $post['LMI_PAYMENT_AMOUNT'];

				$paid_sum = number_format($paid_sum, 2, ',', '');
				$paid_sum = str_replace(',', '.', $paid_sum);

				$payment_type = 'webmoney2';
			}
		}
		elseif (isset($post['prv_name']))
		{
			$headers = $mQIWI->ApacheRequestHeaders();
		
			if ($mQIWI->CheckResult($config['payments']['qiwi']['notice_password'], $post, $headers))
			{
				$paid_order_id = $post['bill_id'];
				$paid_sum = $post['amount'];
				$payment_type = 'qiwi';
			}
		}
		elseif (isset($post['codepro']))
		{
			if ($id_order = $mYandexMoney->CheckResult($config['payments']['yandex']['shop_id'], $config['payments']['yandex']['secret'], $config['payments']['yandex']['token'], $config['payments']['yandex']['description'], $post))
			{
				if (isset($config['payments']['yandex']['rate']))
					$paid_sum = $post['withdraw_amount'] / $config['payments']['yandex']['rate'];
				else
					$paid_sum = $post['withdraw_amount'];
					
				$paid_order_id = $id_order;
				$payment_type = 'yandex';
			}
			elseif ($id_order = $mYandexMoney->CheckResult($config['payments']['yandex2']['shop_id'], $config['payments']['yandex2']['secret'], $config['payments']['yandex2']['token'], $config['payments']['yandex2']['description'], $post))
			{
				if (isset($config['payments']['yandex2']['rate']))
					$paid_sum = $post['withdraw_amount'] / $config['payments']['yandex2']['rate'];
				else
					$paid_sum = $post['withdraw_amount'];
					
				$paid_order_id = $id_order;
				$payment_type = 'yandex2';
			}
		}
		elseif (isset($post['paymentPayerCode']))
		{
			if ($post['action'] == 'checkOrder')
			{
				if ($response = $mYandexMoney->CheckOrderEShop($post, $config['payments']['yandex_eshop']['shop_id'], $config['payments']['yandex_eshop']['scid'], $config['payments']['yandex_eshop']['secret']))
				{
					header('HTTP/1.1 200 OK');
					header('Content-Type: text/xml');
					echo $response;
					die;
				}
				else
				{
					header('HTTP/1.1 200 OK');
					header('Content-Type: text/xml');
					echo '<?xml version="1.0" encoding="UTF-8"?><checkOrderResponse performedDatetime="'.date('c').'" code="200" invoiceId="'.$post['invoiceId'].'" shopId="'.$post['shopId'].'" message="Error" techMessage="Error"/>';
					die;
				}
			}
			elseif ($post['action'] == 'paymentAviso')
			{
				if ($response = $mYandexMoney->CheckResultEShop($post, $config['payments']['yandex_eshop']['shop_id'], $config['payments']['yandex_eshop']['scid'], $config['payments']['yandex_eshop']['secret']))
				{
					$paid_order_id = $post['orderNumber'];
					$paid_sum = $post['orderSumAmount'];
					$payment_type = 'yandex_eshop';
				}
				else
				{
					header('HTTP/1.1 200 OK');
					header('Content-Type: text/xml');
					echo '<?xml version="1.0" encoding="UTF-8"?><checkOrderResponse performedDatetime="'.date('c').'" code="200" invoiceId="'.$post['invoiceId'].'" shopId="'.$post['shopId'].'" message="Error" techMessage="Error"/>';
					die;
				}
			}
		}
        elseif (isset($post['txn_id']))
		{
            if ($payment_data = $mPayPal->CheckResult($config['payments']['paypal']['test']))
            {
				$paid_order_id = $post['invoice'];
				if ($post['mc_currency'] == 'RUB')
				{
					$paid_sum = $post['custom'];
                	$payment_type = 'paypal2';	
				}
				else
				{
					$paid_sum = $post['mc_gross'];
                	$payment_type = 'paypal';	
				}
	        }
		}
		elseif (isset($post['WMI_ORDER_STATE']))
		{
			if ($mW1->CheckResult($post, $config['payments']['w1']['shop_id'], $config['payments']['w1']['secret_key'], $config['encoding']) === true)
			{
                $paid_order_id = $post['WMI_PAYMENT_NO'];
                $paid_sum = $post['WMI_PAYMENT_AMOUNT'];
                $payment_type = 'w1';
			}
		}
		elseif (isset($post['data']) && isset($post['ss1']))
        {
			if ($response = $mPaysera->CheckResult($config['payments']['paysera']['project_id'], $config['payments']['paysera']['password'], $config['payments']['paysera']['test']))
			{
				$paid_order_id = $response['orderid'];
				$paid_sum = number_format($response['amount'] / 100, 2, '.', '');
				$payment_type = 'paysera';
			}
        }
		elseif (isset($post['pg_sig']))
		{
			if ($mPaybox->CheckResult($post, $config['payments']['paybox']['secret_key']))
			{
				$paid_order_id = $post['pg_order_id'];
				$paid_sum = $post['pg_amount'];
				$payment_type = 'paybox';
			}
		}
		elseif (isset($post['spPaymentId']))
		{
			if ($mSpryPay->CheckResult($post, $config['payments']['sprypay']['secret_key']))
			{
				$paid_order_id = $post['spShopPaymentId'];
				$paid_sum = $post['spAmount'];
				$payment_type = 'sprypay';
			}
		}
		elseif (isset($check_payment) && isset($order_for_check))
		{
			if ($result = $mQIWI->CheckPayment($config['payments']['qiwi']['shop_id'], $config['payments']['qiwi']['rest_id'], $config['payments']['qiwi']['rest_password'], $order_for_check))
			{
				$paid_order_id = $result->bill_id;
				$paid_sum = $result->amount;
				$payment_type = 'qiwi';
			}
			else
				echo 'Sorry, invoice is not paid yet.';
		}
        else
            die;

		# Если оплата прошла, изменение статуса заказа и отправка уведомления на почту
		if (isset($payment_type) && isset($paid_sum) && isset($paid_order_id))
        {
			# Статусы успешного завершения заказа (по умолчанию "Оплачен" и "Доставлен")
			$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);
		
			if ($config['database']['enabled'] == true)
			{
				# Выбор заказа
				$order = $mDB->GetItemById('custom', $paid_order_id);
				$order_items = $mDB->GetItemsByParam('custom_item', 'id_custom', $paid_order_id);

				# Проверка суммы
				if ($paid_sum != $order['sum'])
					die;

				# Проверка статуса
				if (in_array($order['status'], $success_statuses))
				{
					# Вывод результата на экран
					if ($payment_type == 'qiwi')
					{
						header('HTTP/1.1 200 OK');
						header('Content-Type: text/xml');
						echo '<?xml version="1.0"?><result><result_code>0</result_code></result>';
						die;
					}
					elseif ($payment_type == 'paybox')
					{
						header('HTTP/1.1 200 OK');
						header('Content-Type: text/xml');
						echo $response = $mPaybox->SuccessResponse($config['payments']['paybox']['secret_key']);
						die;
					}
				}

				# Изменение статуса заказа
				$mDB->ChangeStatusById('custom', $paid_order_id, $config['statuses']['success'][0]);
				
				# Запись статуса в БД
				$mDB->SaveStatus($paid_order_id, date('Y-m-d H:i:s'), $config['statuses']['success'][0], true);
			}

            # Подключение модуля работы с формами
			include_once dirname(__FILE__) . '/M_Email.inc.php';
			$mEmail = M_Email::Instance();

            # Подстановка значений при работе без БД
            if (empty($order))
                $order = array ('id_custom' => $paid_order_id, 'payment' => $payment_type, 'delivery' => 1);

            # Определение данных формы оплаты
            $payment['title'] = $config['payments'][$order['payment']]['title'];
            $payment['info'] = $config['payments'][$order['payment']]['info'];

			# Определение данных способа доставки
			$delivery['title'] = $config['deliveries'][$order['delivery']]['title'];
			$delivery['info'] = $config['deliveries'][$order['delivery']]['info'];
			$delivery['cost'] = (isset($order['delivery_cost'])) ? $order['delivery_cost'] : $config['deliveries'][$order['delivery']]['cost'];

            # Определение статуса заказа
            $status = $config['statuses'][$config['statuses']['success'][0]];
			$order['status'] = $config['statuses']['success'][0];
			
			# Подключение модуля работы с файлами (создание ссылки на скачивание)
			if ($config['download']['enabled'] === true && is_file(dirname(__FILE__) . '/C_Files.inc.php') && in_array($order['status'], $success_statuses))
                include_once dirname(__FILE__) . '/C_Files.inc.php';
			
			# Содержание письма
            $content = $mEmail->PrepareChangeStatus($order['id_custom'], $order['email'], $order['lastname'], $order['name'], $order['fathername'], $order['phone'], $order['zip'], $order['country'], $order['region'], $order['city'], $order['address'], $order['comment'], $order_items, $order['sum'], $payment, $order['payment_ym'], $delivery, $order['date'], $config, $status, false);
			
			$emailSubjectStatus = str_replace('№№', '№' . $paid_order_id, $config['email']['subjectStatus']);

            # Отправка письма владельцу и покупателю
            $mEmail->SendEmail($config['email']['receiver'], $config['email']['answer'], $emailSubjectStatus, $content, $config['email']['answerName'], $config['encoding']);
            $mEmail->SendEmail($order['email'], $config['email']['answer'], $emailSubjectStatus, $content, $config['email']['answerName'], $config['encoding']);
			
			# Добавление с список рассылки SmartResponder
			if ($config['smart']['enabled'] === true && is_file(dirname(__FILE__) . '/C_SmartResponder.inc.php'))
				include_once dirname(__FILE__) . '/C_SmartResponder.inc.php';
			
			# Подключение модуля отправки SMS уведомления
			if (is_file(dirname(__FILE__) . '/C_SMS.inc.php'))
			{
				if ($config['sms']['paid2admin'] === true)
				{
					$sms_type = 'paid2admin';
					$id_order = $paid_order_id;
					include_once dirname(__FILE__) . '/C_SMS.inc.php';
				}
				if ($config['sms']['paid2customer'] === true)
				{
					$sms_type = 'paid2customer';
					$id_order = $paid_order_id;
					include_once dirname(__FILE__) . '/C_SMS.inc.php';
				}
			}
			
			# Уменьшение остатков
			if ($config['store']['decrease_order'] === true)
			{
				foreach ($order_items as $order_item)
				{
					$db_product = $mDB->GetItemByCode('product', $order_item['id_product']);
					if (isset($db_product) && isset($db_product['store']))
					{
						$params = array ('store' => $db_product['store'] - $order_item['quantity']);
						$result = $mDB->EditItemByCode('product', $params, $order_item['id_product'], true);
					} 
				}
			}

			if ($payment_type == 'qiwi' && !isset($check_payment))
			{
				# Вывод результата на экран для QIWI
				header('HTTP/1.1 200 OK');
				header('Content-Type: text/xml');
				echo '<?xml version="1.0"?><result><result_code>0</result_code></result>';
				die;
			}
			elseif ($payment_type == 'w1')
			{
				$mW1->PrintAnswer('Ok', 'Заказ #' . $post['WMI_PAYMENT_NO'] . ' оплачен!');
			}
			elseif ($payment_type == 'paysera')
			{
				echo 'Ok';
				die;
			}
			elseif ($payment_type == 'yandex_eshop')
			{
				header('HTTP/1.1 200 OK');
				header('Content-Type: text/xml');
				echo $response;
				die;
			}
			elseif ($payment_type == 'paybox')
			{
				header('HTTP/1.1 200 OK');
				header('Content-Type: text/xml');
				echo $response = $mPaybox->SuccessResponse($config['payments']['paybox']['secret_key']);
				die;
			}
			else
			{
				# Перенаправление на страницу успешной покупки
				header('Location: ' . $config['sitelink'] . $config['successURL']);
				die;
			}
        }
        else
            die;
	}
}