<?php

# jSale v1.431
# http://jsale.biz

# Подключение настроек
include_once dirname(__FILE__) . '/../config.inc.php';

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

# Подключение модуля отправки почты
include_once dirname(__FILE__) . '/../modules/M_Email.inc.php';
$mEmail = M_Email::Instance();

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
    $name = (isset($_POST['order_name'])) ? $mEmail->ProcessText($_POST['order_name']) : '';
    $phone = (isset($_POST['order_phone'])) ? $mEmail->ProcessText($_POST['order_phone']) : '';
	$email = (isset($_POST['order_email'])) ? $mEmail->ProcessText($_POST['order_email']) : '';
    $comment = (isset($_POST['order_comment'])) ? $mEmail->ProcessText($_POST['order_comment']) : '';
	$topic = (isset($_POST['order_topic'])) ? $mEmail->ProcessText($_POST['order_topic']) : '';
	$referer = (isset($_POST['referer'])) ? $mEmail->ProcessText($_POST['referer']) : '';
	$form_type = (isset($_POST['form_type'])) ? $mEmail->ProcessText($_POST['form_type']) : '';
	$template = (isset($_POST['template'])) ? (int) htmlspecialchars($_POST['template']) : '';

	if (isset($_POST['action']) && $_POST['action'] == 'send')
	{
		# Валидация данных
		$validate = $mEmail->ValidateCallForm($name, $phone, $email, $comment);

		if (!$spam)
		{
			$message = $config['form']['isSpam'];
		}
		elseif ($validate)
		{
			$message = $validate;
		}
		# Обработка успешного заказа
		else
		{
			# Подготовка сообщения к отправке.
			$content = $mEmail->PrepareCall($name, $phone, $comment, $topic, $referer, $config);
			
			# Cохранение звонка в БД
			$date = date("Y-m-d H:i:s");
			$params = array ('name' => $name, 'phone' => $phone, 'email' => $email, 'comment' => $comment, 'topic' => $topic, 'link' => $referer, 'date' => $date);
			$id_call = $mDB->CreateItem('call', $params, true);
			
			# Сохранение статуса звонка
			$params = array ('id_call' => $id_call, 'date' => $date, 'status' => '0');
			$mDB->CreateItem('call_status', $params, true);
			
			# Подключение модуля отправки SMS уведомления
			if (is_file(dirname(__FILE__) . '/../modules/C_SMS.inc.php'))
			{
				if ($config['sms']['call2admin'] === true)
				{
					$sms_type = 'call2admin';
					include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
				}

				if ($config['sms']['call2customer'] === true)
				{
					$sms_type = 'call2customer';
					include dirname(__FILE__) . '/../modules/C_SMS.inc.php';
				}
			}

			# Отправка письма.
			if (!$mEmail->SendEmail($config['email']['receiver'], $config['email']['answer'], $config['email']['subjectCall'], $content, $name, $config['encoding']))
			{
				$message = $config['form']['notSent'];
			}
			# Подтверждение отправки.
			else
			{
				$sent = 1;
			}
		}
	}
}

# Если письмо отправлено, выводим сообщение об этом.
if (isset($sent))
{
	$echo = $config['form']['call_sent'];
	$echo .= '<!--order_send-->';
}
else
{
	# Генерируем антиспам.
	$antispam = $mEmail->GenerateAntispam($config['secretWord']);
	
	# Устанавливаем шаблон
	$template = (!empty($template)) ? '_' .$template : '';
	
	ob_start();
	include_once dirname(__FILE__) . "/../design/callForm$template.tpl.php";
	$echo = ob_get_clean();
}

# Вывод на экран
echo $echo;