<?php

# jSale v1.431
# http://jsale.biz

# Подключение настроек
include_once dirname(__FILE__) . '/../config.inc.php';

# Подключение модуля для работы с файлами
include_once dirname(__FILE__) . '/../modules/M_Files.inc.php';
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

# Очистка старых ссылок
$mFiles->ClearLinks();

# Выбор кода ссылки
$uri = explode('/', $_SERVER['REQUEST_URI']);
$code = $uri[count($uri) - 2];
$code = htmlspecialchars($code);

# Проверка на наличие кода
if ($download = $mFiles->CheckCode($code))
{
	# Использование ссылки
	$used = $mFiles->UseCode($code, $download['uses']);

	# Полный путь
	$path = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/';

	# Скачивание файла
	$result = ($config['download']['downloaders'] === true) ? $mFiles->DownloadBigFile($download['file'], $path) : $mFiles->DownloadFile($download['file'], $path);
	
	if ($result)
		die;
	else
		$message = '<h1>Такого файла нет!</h1><p>Если это ошибка, обязательно напишите продавцу на <a href="' . $config['sitelink'] . 'contact.html">email</a>!</p>';
}
else
	$message = '<h1>Такой ссылки не существует!</h1><p>Видимо её время истекло или закончилось ваше количество закачек. Если вы покупатель товара, получите новую ссылку, пожалуста.</p>';

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="<?= $config['encoding'] ?>" />
	<title>Скачивание не удалось!</title>
	
	<link rel="stylesheet" href="<?= $config['sitelink'] ?>style.css" type="text/css" media="screen, projection" />
	<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>

<body>

<div id="wrapper">
<?= $message ?>
<p>Сейчас вы будете перенаправлены на главную страницу магазина.</p>
<meta http-equiv="refresh" content="5; url=<?= $config['sitelink'] ?>">
</div><!-- #wrapper -->

</body>
</html>