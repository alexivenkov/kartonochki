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

# Подключение модуля отправки почты
include_once dirname(__FILE__) . '/modules/M_Email.inc.php';
$mEmail = M_Email::Instance();

# Данные ввода
if (isset($_POST['product']))
	$product = $_POST['product'];

# Куки
if (isset($_POST['cookies']))
	$cookies_string = $_POST['cookies'];

# Идентификатор формы
if (isset($_POST['id']))
	$id_form = (int) htmlspecialchars($_POST['id']);

# Перекодировка данных в случае старой кодировки
if ($config['encoding'] == 'windows-1251')
	foreach ($product as $i => $var)
		$product[$i] = iconv('utf-8', 'windows-1251', $var);
		
# Обработка данных массива
foreach ($product as $i => $var)
	$product[$i] = htmlspecialchars($var);
		
# Подключение модуля скидок
if ($config['discounts']['enabled'] === true || $config['codes']['enabled'] === true)
{
	include_once dirname(__FILE__) . '/modules/M_Discounts.inc.php';
	$mDiscounts = M_Discounts::Instance();
}

# Вычисляем накопительную скидку, если задана
if (is_file(dirname(__FILE__) . '/modules/M_Discounts.inc.php') && $config['discounts']['enabled'] === true)
	$user = $mDiscounts->CountDiscount($product['subtotal'], $config['discounts']['table']);
else
	$user['discount'] = 0;

# Раньше доставали здесь промо-код из куки
$code = '';

# Подсчёт скидки по промо-коду
$code_discount = ($config['codes']['enabled'] === true) ? $mDiscounts->CountCodeDiscount($code, $config['codes']['table']) : 0;

# Определяем партнёра и скидку
if ($config['partner']['enabled'] === true)
{
	$partner = $mDB->GetItemByCode('partner', $code);
	if (is_array($partner) && isset($partner['id_partner']))
	{
		$id_partner = $partner['id_partner'];
		$ref_discount = ($config['partner']['discount']['enabled'] === true && $partner['discount'] > $config['partner']['discount']['percent']) ? $partner['discount'] : $config['partner']['discount']['percent'];
	}
}
$ref_discount = (isset($ref_discount)) ? $ref_discount : 0;

# Выбор максимальной скидки
$product['discount'] = $discount = max($user['discount'], $code_discount, $ref_discount, $product['discount'], 0);

session_start();
$deadline = (isset($_SESSION['jsale_deadline'])) ? $_SESSION['jsale_deadline'] : 0;
# Убираем скидку, апселл и бонус, если просрочен дедлайн
if ($config['deadlines']['enabled'] === true && isset($config['deadline'][$product['deadline']]) && $config['deadline'][$product['deadline']]['type'] == 'discount' && time() > $deadline)
{
	$product['discount'] = 0;
	$product['upsell'] = $product['bonus'] = '';
}

# Подставляем вид формы дедлайна
if ($config['deadlines']['enabled'] === true && isset($product['deadline']) && isset($config['deadline'][$product['deadline']]))
	$product['form_type'] = 'deadline';
	
# Генерация хэша
$product['hash'] = md5($config['secretWord'].'_'.$product['price'].'_'.$product['discount'].'_'.$product['title']);

# Генерация антиспама
$antispam = $mEmail->GenerateAntispam($config['secretWord']);

# Настройки для электронных товаров
if (isset($config['download']['dir']) && $config['download']['enabled'] === true && is_file(dirname(__FILE__) . '/' . $config['download']['dir'] . '/' . $product['code'] . '.' . $config['download']['type']) && is_file(dirname(__FILE__) . '/config2.inc.php'))
	include_once dirname(__FILE__) . '/config2.inc.php';

# Подключение дополнительных настроек
if (isset($product['form_config']) && !empty($product['form_config']) && is_file(dirname(__FILE__) . '/config' . $product['form_config'] . '.inc.php'))
	include_once dirname(__FILE__) . '/config' . $product['form_config'] . '.inc.php';

# Метод оплаты по умолчанию
foreach ($config['payments'] as $key => $payment)
{
	if ($payment['enabled'] == true)
	{
		$payment_type = $key;
		break;
	}
}

# Способ доставки по умолчанию
foreach ($config['deliveries'] as $key => $delivery)
{
	if ($delivery['enabled'] == true && $config['payment2delivery']['enabled'] !== true || $delivery['enabled'] == true && $config['payment2delivery']['enabled'] === true && !isset($config['payment2delivery'][$payment_type]) || $delivery['enabled'] == true && $config['payment2delivery']['enabled'] === true && isset($config['payment2delivery'][$payment_type]) && in_array($key, $config['payment2delivery'][$payment_type]))
	{
		$delivery_type = $key;
		break;
	}
}

if ($config['params']['enabled'] === true && isset($product['id_product']) && !empty($product['id_product']))
{
	# Выборка параметров из БД
	$params = $mDB->GetItemsByParamAndSort('param', 'id_product', $product['id_product']);

	$product_params = array();
	$delta = 0;
	if (is_array($params) && !empty($params))
	{
		foreach ($params as $param)
		{
			# Выбор из БД
			$key = $param['id_param'];
			$product_params[$key]['title'] = $param['title'];
			$product_params[$key]['type'] = $param['type'];
			$product_params[$key]['parent'] = $param['parent'];
			$product_params[$key]['required'] = $param['required'];
			$product_params[$key]['name'] = $param['name'];
			$product_params[$key]['items'] = $mDB->GetItemsByParam('param_item', 'id_param', $param['id_param']);
			sort($product_params[$key]['items']);
			
			# Параметр по умолчанию
			$default_param_key = key($product_params[$key]['items']);
			
			# Добавление наценки за параметр
			$delta += $product_params[$key]['items'][$default_param_key]['price'];
		}
	}

	# Блокировка кнопки отправки заказа
	if (count($params) > 1 && $config['params']['disable_submit'] === true)
		$disabled = true;
}


# Учёт скидки
if ($config['discounts']['fixed'] === true)
	$product['subtotal'] = number_format($product['subtotal'] - $discount, 2, '.', '');
else
	$product['subtotal'] = number_format($product['subtotal'] * (1 - $discount / 100), 2, '.', '');
	
# Прибавление стоимости доставки
if (isset($delivery['free']) && $delivery['free'] != '' && $product['subtotal'] > $delivery['free'])
	$delivery['cost'] = 0;

# Вычисляем сумму заказа
$order_sum = number_format($product['subtotal'] + $delivery['cost'], 2, '.', '');
	
# Текст кнопки заказа
$button_text = (empty($product['button_text'])) ? (!isset($feedback)) ? $config['button']['order'] : $config['button']['feedback'] : $product['button_text'];

# Выбор шаблона
$template = (!empty($product['template'])) ? 'orderForm_' . (int) $product['template'] : 'orderForm';

# Вывод сообщения об отсутствии товаров
if ($config['download']['enabled'] === true && $config['download']['pincode'] === true)
{
	$path = dirname(__FILE__) . '/' . $config['download']['dir'] . '/' . $product['code'] . '.' . $config['download']['type'];
	if (is_file(dirname(__FILE__) . '/' . $config['download']['dir'] . '/' . $product['code'] . '.' . $config['download']['type']))
	{
		$products = file($path);

		if (empty($products))
		{
			$message = 'К сожалению, товар временно отсутствует. Приходите позже.';
			$disabled = true;
		}
	}
}

$order_step = '1';

# Обёртка шаблона формы
$code = (isset($code)) ? $code : '';
ob_start();
include_once dirname(__FILE__) . '/design/' . $template . '.tpl.php';
$orderForm = ob_get_clean();

# Обёртка шаблона всплывающего окна
ob_start();
include_once dirname(__FILE__) . '/design/orderButton.tpl.php';
echo ob_get_clean();