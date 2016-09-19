<?php

# jSale v1.431
# http://jsale.biz

# Подключение настроек
include_once dirname(__FILE__) . '/config.inc.php';

# Проверка дополнительных доменов
$wrong_domain = true;
if (isset($config['sites']) && is_array($config['sites']))
{
	foreach ($config['sites'] as $site)
	{
		if (strpos($_SERVER['HTTP_REFERER'], $site) !== false)
			$wrong_domain = false;
	}
}

# Проверка основного домена
if (strpos($_SERVER['HTTP_REFERER'], $config['sitelink']) === false && $wrong_domain === true)
	die;

# Заголовок javascript
header('Content-Type: text/javascript; charset=' . $config['encoding']);
header('Access-Control-Allow-Origin: *');

# Запрет кэширования
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');

# Проверка подключения jQuery
echo <<<EOF
if (window.jQuery == undefined) { 
document.write(unescape("%3Cscript src='http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js' type='text/javascript'%3E%3C/script%3E")); 
}

EOF;

# Путь до папки со скриптом
$jsale_dir = $config['sitelink'] . $config['dir'];

# Проверка подключения JSale
echo <<<EOF

if (window.jSale == undefined) {
	document.write(unescape("%3Cscript src='{$jsale_dir}js/jquery.simplemodal.1.4.5.min.js' type='text/javascript'%3E%3C/script%3E"));
	document.write(unescape("%3Cscript src='{$jsale_dir}js/jquery.countdown.js' type='text/javascript'%3E%3C/script%3E"));
	document.write(unescape("%3Cscript src='{$jsale_dir}js/jquery.maskedinput.min.js' type='text/javascript'%3E%3C/script%3E"));
	document.write(unescape("%3Cscript src='{$jsale_dir}js/jsale.js' type='text/javascript'%3E%3C/script%3E"));
	document.write(unescape("%3Cscript src='{$jsale_dir}js/custom.js' type='text/javascript'%3E%3C/script%3E"));
	document.write(unescape("%3Clink href='{$jsale_dir}css/jsale.css' media='screen, projection' rel='stylesheet' type='text/css' \%3E"));
	var jSale = 1;
}
else {
	jSale++;
}

EOF;

if (isset($_GET['css']))
{
	$css = (int) htmlspecialchars($_GET['css']);
	echo <<<EOF
	document.write(unescape("%3Clink href='{$jsale_dir}css/jsale{$css}.css' media='screen, projection' rel='stylesheet' type='text/css' \%3E"));
EOF;
}

$product = array();

# Подхват данных товара
if (isset($_GET['product']))
{
	$id_product = htmlspecialchars($_GET['product']);

	# Подключение модуля
	include_once dirname(__FILE__) . '/modules/M_DB.inc.php';
	$mDB = M_DB::Instance();

	# Выборка товара
	$product = $mDB->GetItemByCode('product', $id_product);
	$feedback = $call = 'false';
}
elseif ($config['products']['base2pro'] === true && isset($_GET['id']) || $config['products']['base2pro'] === true && isset($_GET['code']))
{
	$product['code'] = (isset($_GET['id'])) ? htmlspecialchars($_GET['id']) : htmlspecialchars($_GET['code']);

	# Подключение модуля
	include_once dirname(__FILE__) . '/modules/M_DB.inc.php';
	$mDB = M_DB::Instance();

	# Выборка товара
	$product = $mDB->GetItemByCode('product', $product['code']);
	$feedback = $call = 'false';
}

# Обработки кук
$cookies = array();
foreach ($_COOKIE as $key => $cookie)
	if (strstr($key, 'jsale_'))
		$cookies[$key] = $cookie;

$cookies = json_encode($cookies);

# Если данные не заданы, берём их из GET запроса
if (empty($product))
{
	if (isset($_GET['code']))
		$product['code'] = htmlspecialchars($_GET['code']);
	elseif (isset($_GET['id']))
		$product['code'] = htmlspecialchars($_GET['id']);
	else
		$product['code'] = $config['product']['code'];

	$product['title'] = (isset($_GET['title'])) ? htmlspecialchars($_GET['title']) : $config['product']['title'];
	$product['price'] = (isset($_GET['price'])) ? htmlspecialchars($_GET['price']) : $config['product']['price'];
	$product['discount'] = (isset($_GET['discount'])) ? htmlspecialchars($_GET['discount']) : $config['product']['discount'];
	$product['qty'] = (isset($_GET['qty'])) ? htmlspecialchars($_GET['qty']) : $config['product']['qty'];
	$product['qty_type'] = (isset($_GET['qty_type'])) ? htmlspecialchars($_GET['qty_type']) : $config['product']['qty_type'];
	$product['unit'] = (isset($_GET['unit'])) ? htmlspecialchars($_GET['unit']) : $config['product']['unit'];
	$product['param1'] = (isset($_GET['param1'])) ? htmlspecialchars($_GET['param1']) : $config['product']['param1'];
	$product['param2'] = (isset($_GET['param2'])) ? htmlspecialchars($_GET['param2']) : $config['product']['param2'];
	$product['param3'] = (isset($_GET['param3'])) ? htmlspecialchars($_GET['param3']) : $config['product']['param3'];
	$product['description'] = (isset($_GET['description'])) ? htmlspecialchars($_GET['description']) : $config['product']['description'];
	$product['id_product'] = $product['bandle_products'] = '';
}

$open_modal = (isset($_GET['open_modal'])) ? 'true' : 'false';

$button_img = (isset($_GET['button_img'])) ? htmlspecialchars($_GET['button_img']) : ((!empty($product['button_img'])) ? $product['button_img'] : '');
$form_config = (isset($_GET['config'])) ? htmlspecialchars($_GET['config']) : ((!empty($product['config'])) ? $product['config'] : '');

$template = (isset($_GET['template'])) ? htmlspecialchars($_GET['template']) : '';
$deadline = (isset($_GET['deadline'])) ? htmlspecialchars($_GET['deadline']) : '';
$upsell = (isset($_GET['upsell'])) ? htmlspecialchars($_GET['upsell']) : '';
$bonus = (isset($_GET['bonus'])) ? htmlspecialchars($_GET['bonus']) : '';
$feedback = (isset($_GET['feedback'])) ? 'true' : 'false';
$call = (isset($_GET['call'])) ? 'true' : 'false';

if ($call == 'true')
	$form_type = (isset($_GET['form_type'])) ? htmlspecialchars($_GET['form_type']) : $config['call']['form_type'];
elseif ($feedback == 'true')
	$form_type = (isset($_GET['form_type'])) ? htmlspecialchars($_GET['form_type']) : $config['feedback']['form_type'];
else
	$form_type = (isset($_GET['form_type'])) ? htmlspecialchars($_GET['form_type']) : ((!empty($product['form_type'])) ? $product['form_type'] : $config['product']['form_type']);

# Подсчёт стоимости
$product['subtotal'] = $product['price'] * $product['qty'];

# Надпись на кнопке заказа
$product['button_text'] = (isset($_GET['button_text'])) ? htmlspecialchars($_GET['button_text']) : '';

echo <<<EOF

var product = {};
product['id_product'] = '{$product['id_product']}';
product['code'] = '{$product['code']}';
product['title'] = '{$product['title']}';
product['price'] = '{$product['price']}';
product['discount'] = '{$product['discount']}';
product['qty'] = '{$product['qty']}';
product['qty_type'] = '{$product['qty_type']}';
product['unit'] = '{$product['unit']}';
product['param1'] = '{$product['param1']}';
product['param2'] = '{$product['param2']}';
product['param3'] = '{$product['param3']}';
product['subtotal'] = '{$product['subtotal']}';
product['description'] = '{$product['description']}';
product['button_text'] = '{$product['button_text']}';
var template = product['template'] = '{$template}';
var form_type = product['form_type'] = '{$form_type}';
product['deadline'] = '{$deadline}';
product['upsell'] = '{$upsell}';
product['bonus'] = '{$bonus}';
product['button_img'] = '{$button_img}';
product['bandle_products'] = '{$product['bandle_products']}';
product['form_config'] = '{$form_config}';
var feedback = {$feedback};
var call = {$call};
var category, data = false;
product['open_modal'] = {$open_modal};
var cookies = '{$cookies}';

if (call) {
	var call_form_type = '{$form_type}';
	var call_template = '{$template}';
}
else if (feedback) {
	var feedback_form_type = '{$form_type}';
	var feedback_template = '{$template}';
}

if (window.products == undefined) {
	var products = [];
}

if (!feedback && !call)
	products[jSale] = product;
else
	jSale--;

if (!sitelink) {
	var sitelink = '{$config['sitelink']}';
}

if (!dir) {
	var dir = '{$config['dir']}';
}

EOF;

# Редирект после отправки заказа
if ($feedback == 'true' && isset($config['feedback']['resultURL']) && $config['feedback']['resultURL'] != '')
{
	echo <<<EOF

	if (!feedback_redirect) {
		var feedback_redirect = '{$config['sitelink']}{$config['feedback']['resultURL']}';
	}
EOF;
}
elseif ($call == 'true' && isset($config['call']['resultURL']) && $config['call']['resultURL'] != '')
{
	echo <<<EOF

	if (!call_redirect) {
		var call_redirect = '{$config['sitelink']}{$config['call']['resultURL']}';
	}
EOF;
}
elseif (isset($_GET['upsell']) && $_GET['upsell'] == 'page' && $config['upsells']['page'] === true && isset($config['upsellURL']) && $config['upsellURL'] != '')
{
	echo <<<EOF

	if (!redirect) {
		var redirect = '{$config['sitelink']}{$config['upsellURL']}';
	}
EOF;
}
elseif ($call != 'true' && $feedback != 'true' && isset($config['resultURL']) && $config['resultURL'] != '')
{
	echo <<<EOF

	if (!redirect) {
		var redirect = '{$config['sitelink']}{$config['resultURL']}';
	}
EOF;
}

# Вставка формы. Инициализацию выводим только 1 раз
echo <<<EOF

if (jSale <= 1) {
	document.write(unescape("%3Cscript src='{$jsale_dir}js/jsale_init.js' type='text/javascript'%3E%3C/script%3E"));
}
if (feedback) {
	document.write('<div class="jSaleFeedback"></div>');
} else if (call) {
	document.write('<div class="jSaleCall"></div>');
} else {
	document.write('<div class="jSale"></div>');
}

EOF;
