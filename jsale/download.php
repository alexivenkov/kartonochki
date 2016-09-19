<?php

# jSale v1.431
# http://jsale.biz

# Подключение настроек
include_once dirname(__FILE__) . '/config.inc.php';

include_once dirname(__FILE__) . '/modules/M_Files.inc.php';
$mFiles = M_Files::Instance();
include_once dirname(__FILE__) . '/modules/M_Email.inc.php';
$mEmail = M_Email::Instance();

if (isset($_GET['download_file']) && isset($_GET['hash']))
{
	# Обработка данных
	$download_file = htmlspecialchars($_GET['download_file']);
	$hash = htmlspecialchars($_GET['hash']);
	list ($id_order, $ext) = explode('.', $download_file);

	# Проверка хеш-строки
	if ($mEmail->CheckHash($hash, $id_order, 'DOWNLOAD', $config['secretWord']) == false)
		die;

	# Путь к файлу
	$path = dirname(__FILE__) . '/' . $config['download']['dir'] . '/' . $config['admin']['upload']['dir'] . '/';

	# Убиваем скрипт, если кто-то пытается ввести кривой путь
	if (strstr('/', $download_file) !== false)
		die;

	# Скачивание файла
	if ($mFiles->DownloadFile($download_file, $path))
		die;
}