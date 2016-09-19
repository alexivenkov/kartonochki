<?php

# jSale v1.431
# http://jsale.biz

# Подключение настроек
include_once dirname(__FILE__) . '/config.inc.php';

# Проверка домена
if (strpos($_SERVER['HTTP_REFERER'], $config['sitelink']) === false)
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
	document.write(unescape("%3Cscript src='{$jsale_dir}js/jsale.js' type='text/javascript'%3E%3C/script%3E"));
	document.write(unescape("%3Cscript src='{$jsale_dir}js/custom.js' type='text/javascript'%3E%3C/script%3E"));
	document.write(unescape("%3Clink href='{$jsale_dir}css/jsale.css' media='screen, projection' rel='stylesheet' type='text/css' \%3E"));
	var jSale = 1;
}
else {
	jSale++;
}

EOF;

# Установка категории
if (empty($products))
{
	$category = htmlspecialchars($_GET['category']);
	echo <<<EOF
	var category = '{$category}';
EOF;
}

echo <<<EOF

if (!sitelink) {
	var sitelink = '{$config['sitelink']}';
}

if (!dir) {
	var dir = '{$config['dir']}';
}

EOF;

$button_text = (isset($_GET['button_text'])) ? htmlspecialchars($_GET['button_text']) : $config['button']['order'];
$form_type = (isset($_GET['form_type'])) ? htmlspecialchars($_GET['form_type']) : $config['product']['form_type'];
$template = (isset($_GET['template'])) ? htmlspecialchars($_GET['template']) : '';

# Редирект после отправки заказа
if (isset($config['resultURL']) && $config['resultURL'] != '')
{
	echo <<<EOF

		var redirect = '{$config['sitelink']}{$config['resultURL']}';

		var data = {};
		data['form_type'] = '{$form_type}';
		data['template'] = '{$template}';
		data['button_text'] = '{$button_text}';
EOF;
}

# Вывод списка
echo <<<EOF

if (jSale <= 1) {
	document.write(unescape("%3Cscript src='{$jsale_dir}js/jsale_init.js' type='text/javascript'%3E%3C/script%3E"));
}

document.write('<div class="jSaleProducts"></div>');

EOF;
