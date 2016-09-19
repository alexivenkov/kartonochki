<?php

# jSale v1.431
# http://jsale.biz

# Заголовок javascript
header('Content-Type: text/javascript; charset=' . $config['encoding']);
header('Access-Control-Allow-Origin: *');

# Запрет кэширования
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').'GMT');

include_once dirname(__FILE__) . '/modules/C_Partner.inc.php';

if (!isset($_COOKIE['jsale_cookies_set']))
{
	echo <<<EOF
	
	function getcookie(a) {var b = new RegExp(a+'=([^;]){1,}');var c = b.exec(document.cookie);if(c) c = c[0].split('=');else return false;return c[1] ? c[1] : false;}
	
	var getcookie = getcookie('jsale_cookies_set');
	
	var timer = setTimeout(function() {
		window.location.reload();
	}, 1);

	if (getcookie !== false)
		clearTimeout(timer);
		
	document.cookie = 'jsale_cookies_set=1';
EOF;
}
?>