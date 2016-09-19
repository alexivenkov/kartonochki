<?php

foreach ($prints as $key1 => $tmp)
{
	list($rub, $kop) = explode('.', $tmp['sum']);
	$prints[$key1]['rub'] = $rub;
	$prints[$key1]['kop'] = $kop;
	
	$prints[$key1]['address'] = wordwrap($tmp['address'], 100, '</div><div class="label">&nbsp;</div><div class="underline">');
}

include_once dirname(__FILE__) . '/../config.inc.php';
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();

$fio = $config['print']['fio'];
$address = wordwrap($config['print']['address'], 50, '</div><div class="label">&nbsp;</div><div class="underline">');
$zip = str_replace(' ', '', $config['print']['zip']);
$phone = $config['print']['phone'];

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />

	<style type="text/css">
		body { font: normal 12px/16px Arial, sans-family; }
		.print { width: 480px; height: 300px; float: left; border-right: 1px dashed #000; }
		.top { padding: 10px; }
		.bottom { border-bottom: 1px dashed #000; clear: both; padding:10px; height: 120px; }
		.underline { border-bottom: 2px solid #000; text-align: center; }
		.bordered { border: 2px solid #000; padding: 3px; }
		.top .right { float: right; width: 175px; margin-top: 58px; text-align: center; }
		.top .left { width: 250px; }
		.stamp { border: 2px solid #000; width: 130px; height: 40px; margin: 10px 0 20px 20px; }
		.top .left .label { width: 50px; float: left; }
		.top .left .underline { width: 200px; float: left; }
		.bottom .right { width: 400px; float: right; }
		.bottom .right .label { width: 60px; float: left; }
		.bottom .right .underline { width: 340px; float: left; }
		.zip { text-align: right; clear: both; padding-top: 10px; }
		.clear { clear: both; }
		.phone {  }
	</style>

	<style type="text/css" media="print">
	#toolbox { display: none; }
	</style>
</head>
<body>

	<? foreach ($prints as $key => $print): ?>
	<div class="print">
	<div class="top">
		<div class="right">
			Объявленная ценность<br>
			<div class="underline"><span><?= $print['rub'] ?> руб. <?= $print['kop'] ?> коп.</span></div>
			Наложенный платеж<br>
			<div class="underline"><span><?= $print['rub'] ?> руб. <?= $print['kop'] ?> коп.</span></div>
		</div>
	
		<div class="stamp">&nbsp;</div>
		
		<div class="left">
			<div class="label">От кого</div> <div class="underline"><span><?= $fio ?></span></div>
			<div class="label">Адрес</div> <div class="underline"><span><?= $address ?></span></div>
			<div class="zip">Индекс <span class="bordered"><?= $zip ?></span></div>
			<div class="phone">Телефон <?= $phone ?></div>
		</div>
	</div>
	<div class="bottom">
		<div class="right">
			<div class="label"><strong>Кому</strong></div> <div class="underline"><?= $print['fio'] ?></div>
			<div class="label"><strong>Адрес</strong></div> <div class="underline"><?= $print['address'] ?></div>
			<div class="zip">Индекс <span class="bordered"><?= (!empty($print['zip'])) ? $print['zip'] : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ?></span></div>
			<div class="phone"><strong>Телефон</strong> <?= $print['phone']; ?></div>
			<br>
		</div>
		<div class="clear"></div>
	</div>
	</div>
	<? endforeach; ?>

    <script type="text/javascript">
        //window.onload = print;
    </script>
</body>
</html>