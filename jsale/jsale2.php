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
if (isset($_POST['category']))
	$category = htmlspecialchars($_POST['category']);
	
if (isset($_POST['data']))
	$data = $_POST['data'];

# Выборка данных из базы
if ($category != null)
	$products = $mDB->GetItemsByParam('product', 'category', $category);
else
	$products = $mDB->GetAllItems('product');

$orderButtons = array();
foreach ($products as $i => $product)
{
	# Вычисляем накопительную скидку, если задана
	if (is_file(dirname(__FILE__) . '/modules/M_Discounts.inc.php') && $config['discounts']['enabled'] === true)
	{
		include_once dirname(__FILE__) . '/modules/M_Discounts.inc.php';
		$mDiscounts = M_Discounts::Instance();

		$user = $mDiscounts->CountDiscount($product['subtotal'], $config['discounts']['table']);
	}
	else
		$user['discount'] = 0;

	# Вычисляем максимальную скидку
	$product['discount'] = $discount = max($user['discount'], $product['discount'], 0);

	# Генерация хэша
	$product['hash'] = md5($config['secretWord'].'_'.$product['price'].'_'.$product['discount'].'_'.$product['title']);

	# Вычисляем сумму заказа
	$order_sum = $product['subtotal'] = number_format($product['subtotal'] * (1 - $product['discount'] / 100), 2, '.', '');

	# Генерация антиспама
	$antispam = $mEmail->GenerateAntispam($config['secretWord']);

	# Настройки для электронных товаров
	if (isset($config['download']['dir']) && is_file(dirname(__FILE__) . '/' . $config['download']['dir'] . '/' . $product['code'] . '.' . $config['download']['type']))
		include_once dirname(__FILE__) . '/config2.inc.php';

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
		if ($delivery['enabled'] == true)
		{
			$delivery_type = $key;
			break;
		}
	}

	# Текст кнопки заказа
	$button_text = (empty($data['button_text'])) ? $config['button']['order'] : htmlspecialchars($data['button_text']);
	
	# Тип формы заказа
	$product['form_type'] = (empty($data['form_type'])) ? 'button' : htmlspecialchars($data['form_type']);

	# Выбор шаблона
	$orderForm = (!empty($data['template'])) ? 'orderForm_' . (int) htmlspecialchars($data['template']) : 'orderForm';
	
	# Идентификатор формы
	$id_form = $i;

	# Обёртка шаблона формы
	$code = (isset($code)) ? $code : '';
	ob_start();
	include dirname(__FILE__) . '/design/' . $orderForm . '.tpl.php';
	$orderForm = ob_get_clean();

	# Обёртка шаблона всплывающего окна
	ob_start();
	include dirname(__FILE__) . '/design/orderButton.tpl.php';
	$orderButtons[$i] = ob_get_clean();
}

# Обёртка шаблона списка
ob_start();
include_once dirname(__FILE__) . '/design/productsView.tpl.php';
echo ob_get_clean();