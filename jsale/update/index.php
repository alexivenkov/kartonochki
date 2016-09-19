<?php

# jSale v1.431
# http://jsale.biz

# Модуль обновления (скачивания) товара

# Настройки.
include_once dirname(__FILE__) . '/../config.inc.php';

# Подключение модулей.
include_once dirname(__FILE__) . '/../modules/M_Email.inc.php';
include_once dirname(__FILE__) . '/../modules/M_Files.inc.php';
$mEmail = M_Email::Instance();
$mFiles = M_Files::Instance();

# Кодировка
header('Content-type: text/html; charset=' . $config['encoding']);

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

# Формирование GET запроса (на случай PHP как CGI)
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);

# Выбор кода ссылки.
$uri = explode('/', $_SERVER['REQUEST_URI']);
$update_product = $uri[count($uri) - 2];

# Выбор товара.
$product = $mDB->GetItemByCode('product', $update_product);

# Вывод ошибки
if (!$product)
{
	$form['message'] = 'Такого товара нет. Видимо вы ошиблись адресом.';
    include_once dirname(__FILE__) . '/../design/updateForm.tpl.php';
	die;
}

# Обработка отправки формы.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($form['sent']))
{
	# Проверка на спам.
    $spam = $mEmail->CheckSpam(htmlspecialchars($_POST['update_nospam']), $config['secretWord']);

    # Обработка и сохранение данных в переменные.
    $update_email = htmlspecialchars(trim($_POST['update_email']));

	# Валидация email.
	$validate = $mEmail->ValidateEmail($update_email);
	
	if ($spam && !$validate)
	{
		# Выбор клиента.
		$orders = $mFiles->CheckPaidProduct($update_email, $product['code'], $config);
		
		if (!$orders)
		{
			$form['message'] = $config['form']['noUpdate'];
			
			# Генерируем антиспам.
			$antispam = $mEmail->GenerateAntispam($config['secretWord']);

			# Шаблон вывода формы.
            include_once dirname(__FILE__) . '/../design/updateForm.tpl.php';
			die;
		}
		
		# Создание ссылки для скачивания.
		$code = $mFiles->CreateLink($product['code'], $config['download']['uses'], $config['download']['type']);
		$link = $config['sitelink'] . $config['dir'] . 'download/' . $code . '/';

		# Подготовка данных.
		$content = $mEmail->PrepareDownloadLink($update_email, $product, $link, $config['download']['uses'], $config['download']['hours']);
	}
	
	# Отправка письма.
    if (!$spam)
    {
        $form['message'] = $config['form']['isSpam'];
    }
    elseif ($validate)
    {
        $form['message'] = $validate;
    }
    elseif (!$mEmail->SendEmail($update_email, $config['email']['answer'], $config['email']['subjectDownload'], $content, $config['email']['answerName']))
    {
        $form['message'] = $config['form']['notSent'];
    }
    # Подтверждение отправки.
    else
    {
        $form = array();
        $form['sent'] = 1;
    }
	
}

# Если письмо отправлено, выводим сообщение об этом.
if (isset($form['sent']))
{
    echo <<<EOF
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="{$config['encoding']}" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Отправка ссылки</title>
		<link rel="stylesheet" href="{$config['sitelink']}jsale/bootstrap/css/bootstrap.min.css" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="{$config['sitelink']}jsale/bootstrap/css/bootstrap-responsive.min.css" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="{$config['sitelink']}jsale/admin/style.css" type="text/css" media="screen, projection" />
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js" type="text/javascript"></script>
		<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
		<script type="text/javascript" src="{$config['sitelink']}jsale/bootstrap/js/bootstrap.min.js"></script>
		
		<script type="text/javascript">
		$('.alert').alert();
		</script>
	</head>
	
	<body>
	<div class="hero-unit">
    <h1>Отправка ссылки</h1><br />
	<div class="alert alert-success">
	<a class="close" data-dismiss="alert" href="#">&times;</a>
	{$config['form']['downloadSent']}
	</div>
	<p>Сейчас вы будете перенаправлены на главную страницу магазина.</p>
	</div>
	<meta http-equiv="refresh" content="3; url='{$config['sitelink']}'">
	</body>
	</html>
EOF;
}
# Иначе выводим форму.
else
{
	if (isset($_GET['email']))
		$update_email = $_GET['email'];

    # Генерируем антиспам.
    $antispam = $mEmail->GenerateAntispam($config['secretWord']);

    # Шаблон вывода формы.
	include_once dirname(__FILE__) . '/../design/updateForm.tpl.php';
}