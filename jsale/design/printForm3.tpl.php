<?php

list($rub, $kop) = explode('.', $sum);

include_once dirname(__FILE__) . '/../config.inc.php';
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();

$sum_string = $mDB->Number2String($sum);

$customer_phone = (isset($customner_phone) && $config['print']['sms2'] === true) ? str_replace('+7', '', $customner_phone) : '';
$zip = str_replace(' ', '', $config['print']['zip']);
$code = str_replace(' ', '', $config['print']['code']);
$account = str_replace(' ', '', $config['print']['account']);
$bank = $config['print']['bank'];
$bank_account = str_replace(' ', '', $config['print']['bank_account']);
$bik = str_replace(' ', '', $config['print']['bik']);
$fio = $config['print']['fio'];
$address = $config['print']['address'];
$phone = (!empty($config['print']['phone']) && $config['print']['sms'] === true) ? str_replace('+7', '', $config['print']['phone']) : '';

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />

	<style type="text/css">
		#img{position:absolute;z-index:1;width:1045px;height:1516px}
		#linur{display:block;width:1045px;height:1516px;position:relative;z-index:10}
		#summarub{display:block;font-size:25px;height:28px;left:47px;position:absolute;text-align:center;top:299px;width:109px}
		#summakop{display:block;font-size:25px;height:28px;left:186px;position:absolute;text-align:center;top:299px;width:38px}
		#summapro{display:block;font-size:25px;height:28px;left:286px;position:absolute;text-align:center;top:275px;width:721px}
		#komu{display:block;font-size:25px;height:28px;left:101px;position:absolute;text-align:left;top:390px;width:700px}
		#kuda{display:block;font-size:25px;height:28px;left:101px;position:absolute;text-align:left;top:429px;width:700px}
		#index{display:block;font-size:25px;height:28px;left:865px;letter-spacing:.560em;position:absolute;text-align:left;top:460px;width:162px}
		#inn{display:block;font-size:25px;height:28px;left:100px;letter-spacing:.48em;position:absolute;text-align:left;top:600px;width:285px}
		#korrschet{display:block;font-size:25px;height:28px;left:532px;letter-spacing:.48em;position:absolute;text-align:left;top:600px;width:482px}
		#naimenov{display:block;font-size:25px;height:28px;left:250px;position:absolute;text-align:left;top:632px;width:760px}
		#rasschet{display:block;font-size:25px;height:28px;left:136px;letter-spacing:.48em;position:absolute;text-align:left;top:664px;width:478px}
		#bik{display:block;font-size:25px;height:28px;left:801px;letter-spacing:.48em;position:absolute;text-align:left;top:664px;width:215px}
		#otkogo{display:block;font-size:25px;height:28px;left:115px;position:absolute;text-align:center;top:697px;width:897px}
		#adres1{display:block;font-size:25px;height:28px;left:235px;position:absolute;text-align:center;top:734px;width:780px}
		#adres2{display:block;font-size:25px;height:28px;left:27px;position:absolute;text-align:center;top:772px;width:815px}
		#zip{display:block;font-size:25px;height:28px;left:865px;letter-spacing:.560em;position:absolute;text-align:left;top:765px;width:162px}
		#galka{display:block;font-size:39px;height:41px;left:39px;position:absolute;text-align:center;top:337px;width:38px}
		#phone{display:block;font-size:28px;height:41px;left:810px;letter-spacing:.24em;position:absolute;text-align:left;top:322px;width:220px}
		#customer_phone{display:block;font-size:28px;height:41px;left:810px;letter-spacing:.24em;position:absolute;text-align:left;top:355px;width:220px}
	</style>
</head>
<body>

<img id="img" src="<?= $config['sitelink'] . $config['dir'] ?>images/f112ep.jpg" />
<div id="linur">
	<div id="galka">V</div>
	<div id="summarub"><?= $rub; ?></div>
	<div id="summakop"><?= $kop; ?></div>
	<div id="summapro"><?= $sum_string; ?></div>
	<div id="komu"><?= $fio; ?></div>
	<div id="kuda"><?= $address; ?></div>
	<div id="index"><?= $zip; ?></div>
	<div id="inn"><?= $code; ?></div>
	<div id="korrschet"><?= $account; ?></div>
	<div id="naimenov"><?= $bank; ?></div>
	<div id="rasschet"><?= $bank_account; ?></div>
	<div id="bik"><?= $bik; ?></div>
	<div id="otkogo"><?= $customer_fio; ?></div>
	<div id="adres1"><?= $customer_address1; ?></div>
	<div id="adres2"><?= $customer_address2; ?></div>
	<div id="zip"><?= $customer_zip ?></div>
	<div id="phone"><? echo $phone; ?></div>
	<div id="customer_phone"><? echo $customer_phone; ?></div>
</div>

<script type="text/javascript">
	   window.onload = print;
</script>
</body>
</html>