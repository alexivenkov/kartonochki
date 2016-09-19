<?php
if (!isset($_SESSION))
	session_start();

if (!isset($_SESSION['city']) || isset($_SESSION['city']) && $_SESSION['city'] == '' || !isset($_SESSION['referer']))
{
	include_once dirname(__FILE__) . '/../config.inc.php';
	$geo_params = array ('charset' => $config['encoding']);

	include_once dirname(__FILE__) . '/M_IPGeoBase.inc.php';
	$mIPGeoBase = M_IPGeoBase::Instance($geo_params);
	
	$_SESSION['city'] = $mIPGeoBase->get_value('city', false);
	$_SESSION['referer'] = $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'Прямой заход';
	setcookie('jsale_source', $referer, 0 /*, '/', '.'*/);
}

if (!empty($_GET))
{
	$utm = '';
	foreach ($_GET as $type_param => $get_param)
	{
		if (strstr($type_param, 'utm_'))
			$utm .= "$type_param|$get_param    ";
	}
	if ($utm)
		setcookie('jsale_utm', $utm, 0 /*, '/', '.'*/);
}

if (isset($_GET['ref']))
{
	# Установка куки на год
	setcookie('jsale_ref', $_GET['ref'], time() + 3600 * 24 * 30 * 12 /*, '/', '.'*/);
	
	# Сохранение перехода в БД
	include_once dirname(__FILE__) . '/../config.inc.php';
	include_once dirname(__FILE__) . '/M_DB.inc.php';
	$mDB = M_DB::Instance();
	
	$partner = $mDB->GetItemByCode('partner', $_GET['ref']);
	if (is_array($partner))
	{
		$params = array( 'clicks' => $partner['clicks'] + 1 );
		$mDB->EditItemById('partner', $params, $partner['id_partner'], true);
	}
	
	# Перенаправляем на страницу без рефки
	if ($config['partner']['links']['reload'] === true)
	{
		$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$get = strstr($url, 'ref');
		$url = str_replace($get, '', $url);
		$url = substr($url, 0, strlen($url) - 1);
		header('Location: ' . $url);
	}
}
else
{
	if (isset($mDB) && isset($ref) && !empty($ref))
	{
		# Сохранение привязки заказа к партнёру в БД
		$partner = $mDB->GetItemByCode('partner', $ref);
		
		$partner['commission'] = 0;
		foreach ($products as $product)
			$partner['commission'] += $product['commission'];

		if (is_array($partner) && isset($partner['id_partner']))
		{
			$params = array ( 'id_partner' => $partner['id_partner']);
			$mDB->EditItemById('custom', $params, $id_order, true);
		}
	}
}