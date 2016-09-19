<?php

list($rub, $kop) = explode('.', $sum);

include_once dirname(__FILE__) . '/../config.inc.php';
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();

#$sum = 110.23;

#$rub = 110;
#$kop = 23;
$sum_string = $mDB->Number2String($sum);
#$customer_fio = 'Иванов Иван Иванович';
#$customer_address1 = 'Москва';
#$customer_address2 = 'ул. Ленина, 123';
#$customer_zip = '123145';

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
		#kvitprint{position:absolute;display:block;z-index:20;background:url(/images/M_images/printButton.png);width:16px;height:16px}
		#img{position:absolute;z-index:1;width:1045px;height:1516px}
		#linur{display:block;width:1045px;height:1516px;position:relative;z-index:10}
		
		#summarub{display:block;font-size:25px;height:28px;left:155px;position:absolute;text-align:center;top:1210px;width:280px}
		#summarub2{display:block;font-size:25px;height:28px;left:630px;position:absolute;text-align:center;top:1210px;width:260px}
		
		#summapro{display:block;font-size:25px;height:28px;left:60px;position:absolute;text-align:center;top:345px;width:650px}
		#summapro2{display:block;font-size:25px;height:28px;left:60px;position:absolute;text-align:center;top:405px;width:650px}
		
		#komu{display:block;font-size:25px;height:28px;left:125px;position:absolute;text-align:left;top:456px;width:580px;}
		#kuda{display:block;font-size:25px;height:28px;left:125px;position:absolute;text-align:left;top:496px;width:580px}
		#index{display:block;font-size:25px;height:28px;left:495px;letter-spacing:.9em;position:absolute;text-align:left;top:580px;width:162px}
		
		#komu2{display:block;font-size:25px;height:28px;left:125px;position:absolute;text-align:left;top:1267px;width:580px;}
		#kuda2{display:block;font-size:25px;height:28px;left:125px;position:absolute;text-align:left;top:1325px;width:580px}
		#index2{display:block;font-size:25px;height:28px;left:750px;letter-spacing:.9em;position:absolute;text-align:left;top:1375px;width:162px}
		
		#inn{display:block;font-size:25px;height:28px;left:100px;letter-spacing:.48em;position:absolute;text-align:left;top:600px;width:285px}
		#korrschet{display:block;font-size:25px;height:28px;left:532px;letter-spacing:.48em;position:absolute;text-align:left;top:600px;width:482px}
		#naimenov{display:block;font-size:25px;height:28px;left:250px;position:absolute;text-align:left;top:632px;width:760px}
		#rasschet{display:block;font-size:25px;height:28px;left:136px;letter-spacing:.48em;position:absolute;text-align:left;top:664px;width:478px}
		#bik{display:block;font-size:25px;height:28px;left:801px;letter-spacing:.48em;position:absolute;text-align:left;top:664px;width:215px}
		
		#otkogo{display:block;font-size:25px;height:28px;left:150px;position:absolute;text-align:left;top:640px;width:800px}
		#adres1{display:block;font-size:25px;height:28px;left:150px;position:absolute;text-align:left;top:690px;width:780px}
		#adres2{display:block;font-size:25px;height:28px;left:80px;position:absolute;text-align:left;top:735px;width:650px}
		#zip{display:block;font-size:25px;height:28px;left:755px;letter-spacing:.9em;position:absolute;text-align:left;top:725px;width:162px}
		
		#galka{display:block;font-size:39px;height:41px;left:39px;position:absolute;text-align:center;top:337px;width:38px}
		#phone{display:block;font-size:28px;height:41px;left:810px;letter-spacing:.24em;position:absolute;text-align:left;top:322px;width:220px}
		#customer_phone{display:block;font-size:28px;height:41px;left:810px;letter-spacing:.24em;position:absolute;text-align:left;top:355px;width:220px}
	</style>
</head>
<body>

<img id="img" src="<?= $config['sitelink'] . $config['dir'] ?>images/116_1.png" />
<div id="linur">
	<!--<div id="galka">V</div>-->
	<div id="summarub"><?= $rub; ?>.<?= $kop; ?></div>
	<div id="summarub2"><?= $rub; ?>.<?= $kop; ?></div>
	<div id="summapro"><?= $sum_string; ?></div>
	<div id="summapro2"><?= $sum_string; ?></div>
	<div id="komu"><?= $fio; ?></div>
	<div id="kuda"><?= $address; ?></div>
	<div id="index"><?= $zip; ?></div>
	<div id="otkogo"><?= $customer_fio; ?></div>
	<div id="adres1"><?= $customer_address1; ?></div>
	<div id="adres2"><?= $customer_address2; ?></div>
	<div id="zip"><?= $customer_zip ?></div>
	<div id="komu2"><?= $fio; ?></div>
	<div id="kuda2"><?= $address; ?></div>
	<div id="index2"><?= $zip; ?></div>
	<!--
	<div id="inn"><?= $code; ?></div>
	<div id="korrschet"><?= $account; ?></div>
	<div id="naimenov"><?= $bank; ?></div>
	<div id="rasschet"><?= $bank_account; ?></div>
	<div id="bik"><?= $bik; ?></div>

	<div id="phone"><? echo $phone; ?></div>
	<div id="customer_phone"><? echo $customer_phone; ?></div>-->
</div>

<script type="text/javascript">
	window.onload = print;
</script>
</body>
</html>