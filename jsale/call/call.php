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

# Генерация антиспама
$antispam = $mEmail->GenerateAntispam($config['secretWord']);

# Страница отправки
$referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';

# Тип формы
$form_type = $product['form_type'] = (isset($_POST['form_type'])) ? $_POST['form_type'] : $config['call']['form_type'];

# Выбор шаблона
$callForm = (!empty($_POST['template'])) ? 'callForm_' . (int) $_POST['template'] : 'callForm';

# Обёртка шаблона формы
ob_start();
include_once dirname(__FILE__) . "/../design/$callForm.tpl.php";
$orderForm = ob_get_clean();

# Текст кнопки заказа
$button_text = (empty($product['button_text'])) ? $config['button']['call'] : $product['button_text'];

# Обёртка шаблона всплывающего окна
ob_start();
include_once dirname(__FILE__) . '/../design/orderButton.tpl.php';
echo ob_get_clean();