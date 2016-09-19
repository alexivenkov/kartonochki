<?php

#$sum = '2984.00';
list($rub, $kop) = explode('.', $sum);
#$customer_fio = 'Василькин Энокентий Федерович';
#$customer_address1 = 'Челябинская область, г. Варшава';
#$customer_address2 = 'поселок Аненково, улица Московская, дом 123, кв 444';
#$customer_zip = '101231';
$customer_zip = str_split($customer_zip);

include_once dirname(__FILE__) . '/../config.inc.php';
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();

$sum_string = $mDB->Number2String($sum);

$zip = explode(' ', $config['print']['zip']);
$code = explode(' ', $config['print']['code']);
$account = explode(' ', $config['print']['account']);
$bank_account = explode(' ', $config['print']['bank_account']);
$bik = explode(' ', $config['print']['bik']);

$fio = $config['print']['fio'];
$address = $config['print']['address'];
$bank = $config['print']['bank'];


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />

	<style type="text/css">
	body {
		width: 600px;
	}
	
	code { white-space: pre; }
	.nowr { white-space: nowrap; }
	td { padding: 0; border: 0;}
	table { border: none; }
	img { border: none; }
	form { margin: 0px; padding: 0px; }
	sup { font-size: 66%; line-height: .5; }
	li { list-style: square outside; padding: 0px; margin: 0px; }
	ul { list-style: square outside; padding: 0em 0em 0em 0em; margin: 0em 0em 0em 1.5em; }
	.fakelink { cursor: pointer; }
	.centered { margin-left: auto; margin-right: auto; }
	.zerosize { font-size: 1px; }
	.underlined { text-decoration: underline; }
	.bolded { font-weight: bold; }
	.vbottom { vertical-align: bottom; }
	.vsub { vertical-align: sub; }
	.h_left { text-align: left; }
	.h_right { text-align: right; }
	.h_center { text-align: center; }
	.v_top { vertical-align: top; }
	.v_bottom { vertical-align: bottom; }
	.v_middle { vertical-align: middle; }
	.w100, .full_w, .full { width: 100%; }
	.h100, .full_h, .full { height: 100%; }
	.cramp, .cramp_w { width: 1px; }
	.cramp, .cramp_h { height: 1px; }
	.ucfirst:first-letter { text-transform: uppercase; }
	.clean { clear: both; }

	input { font-family: Arial, sans-serif; font-size: 9pt; color: black; background-color: white; border: 1px solid #333; margin: 8pt 8pt 8pt 0; }
	a { text-decoration: none; color: #555; }
	a:hover { text-decoration: underline; }
	#toolbox { font-family: Arial, sans-serif; font-size: 9pt; border-bottom: dashed 1pt black; margin-bottom: 0; padding: 2mm 0 0 0; text-align: justify; }
	p { margin: 2pt 0 2pt 0; }

	body { text-align: center; background-color: white; margin: 0; }
	.cell { font-family: Arial, sans-serif; border-left: black 1px solid; border-bottom: black 1px solid; border-top: black 1px solid; font-weight: bold; font-size: 8pt; line-height: 1.1; height:4.5mm; vertical-align: bottom; text-align: center; }
	.cells { border-right: black 1px solid; width: 100%; }
	.linebottom { border-bottom: black 1pt solid; }
	.lineleft { border-left: black 1pt solid; }
	.lineright { border-right: black 1pt solid; }
	.linetop { border-top: black 1pt solid; }
	.number { font-family: Arial, sans-serif; font-size: 8pt; font-weight: bold; line-height: 1.1; }
	.string { font-family: Arial, sans-serif; font-size: 8pt; font-weight: bold; line-height: 1.1; }
	.data, .data2 { font-family: Arial, sans-serif; line-height: 1; border-bottom: black 1pt solid; text-align: center; font-weight: bold; vertical-align: bottom; }
	.data { font-size: 9pt; }
	.data2 { font-size: 8pt; }
	#toolbox { width: 195mm; margin-left: auto; margin-right: auto; }
	.vmiddle { vertical-align: middle; }
	.t6i { font-size: 6pt; }
	.t7, .t7b, .t7bi { font-size: 7pt; }
	.t75bi { font-size: 7.5pt; }
	.t8, .t8b, .t8bi { font-size: 8pt; }
	.t9, .t9b, .t9bi { font-size: 9pt; }
	.t10, .t10b { font-size: 10pt; }
	.t11b { font-size: 11pt; }
	.t11b, .t10b, .t9b, .t9bi, .t8b, .t8bi, .t7bi, .t75bi { font-weight: bold; }
	.t11b, .t10b, .t9b, .t9bi, .t8b, .t8bi, .t7bi, .t75bi, .t6i, .t7 { font-family: "Times New Roman", Times, serif; }
	.t9bi, .t8bi, .t7bi, .t75bi, .t6i { font-style: italic; }
	.subscript, .subscript7 { font-family: "Times New Roman", Times, serif; vertical-align: top; text-align: center; white-space: nowrap; line-height: 0.9; }
	.subscript { font-size: 6pt; }
	.subscript7 { font-size: 7pt; }
	.down { position: relative; bottom: -1pt; }
	.field { font-family: "Times New Roman", Times, serif; line-height: 1; white-space: nowrap; width: 1px; vertical-align: bottom; }
	.riska { border-right: black 1pt solid; width: 0; height: 1.5mm; }
	.left2 { border-left: black 1.5pt solid; }
	.right2 { border-right: black 1.5pt solid; }
	.top2 { border-top: black 1.5pt solid; }
	.bottom2 { border-bottom: black 1.5pt solid; }
	.canvas { width: 195mm; height: 185mm; margin: 0 auto 12mm auto; }
	.topmargin { height: 12mm; }
	</style>

	<style type="text/css" media="print">
	#toolbox { display: none; }
	</style>
</head>
<body>

<table cellspacing="0" class="canvas" style="margin-bottom: 0; border-top: black 1px dashed; border-bottom: black 1px dashed;">
	<tbody><tr><td style="width: 65mm; height: 1px"><div style="width: 65mm; height: 1px; font-size: 1px; line-height: 1;"></div></td><td style="width: 3mm; height: 1px"><div style="width: 3mm; height: 1px; font-size: 1px; line-height: 1;"></div></td><td style="width: 124mm; height: 1px"><div style="width: 124mm; height: 1px; font-size: 1px; line-height: 1;"></div></td><td style="width: 3mm; height: 1px"><div style="width: 3mm; height: 1px; font-size: 1px; line-height: 1;"></div></td></tr>
	<tr>
	<td style="border-right: black 1pt dotted; text-align: right;"><span class="t8" style="position: relative; left: 5px;">
		<img src="../images/vertical01.png" width="9" height="58" alt="&nbsp;">	</span></td>
	<td class="h_right t8" style="padding: 15mm 1pt 0 0;">
		<img src="../images/vertical02.png" width="11" height="256" alt="&nbsp;">	</td><td><table class="full_w full_h" cellspacing="0">

		<tbody><tr><td style="height: 40mm; padding-top: 4mm;">
			<div class="t8b" style="float: right;">ф.&nbsp;112эф</div>
			<div class="t10b" style="width: 35mm;">
				<table cellspacing="0"><tbody><tr><td>
					<div class="t8" style="float: left;">
						<img src="../images/postlogo2.png" width="55" height="60" alt="&nbsp;"></div>
					<div class="t10b" style="float: right; line-height: 1.1; text-align: center;">
						П<br>Р<br>И<br>Е<br>М</div>
				</td></tr>
				<tr><td class="cramp_w t10b" style="letter-spacing: -1pt; padding-top: 2mm;">
					ПОЧТА&nbsp;&nbsp;РОССИИ
				</td></tr></tbody></table>
				<table cellspacing="0" style="width: 100%; margin-top: 2mm;">
					<tbody><tr><td class="field t9">№</td><td class="linebottom data">&nbsp;</td></tr>
					<tr><td></td><td class="subscript7" style="text-align: left; padding-left: 3mm;">
						(по накладной ф. 16)
					</td></tr>
					<tr><td class="field t9">№</td><td class="linebottom data">&nbsp;</td></tr>
					<tr><td></td><td class="subscript7" style="text-align: left; padding-left: 3mm;">
						(по реестру ф. 10)
					</td></tr>
				</tbody></table>
			</div>
		</td></tr>

		<tr><td style="width: 120mm; height: 115mm; border: black 2.5pt solid; padding: 0 2pt 1pt 2pt;">
		<table class="full_w full_h" cellspacing="0">

			<tbody><tr><td class="cramp_h" style="padding: 0 2pt 1pt 2pt;"><table class="full_w" cellspacing="0"><tbody><tr>
				<td class="field t11b"><span class="down">ПОЧТОВЫЙ&nbsp;ПЕРЕВОД&nbsp;на&nbsp;</span></td>
				<td class="data" style="width: 30mm;"><?= $rub ?></td>
				<td class="field t10b"><span class="down">&nbsp;руб.&nbsp;</span></td>
				<td class="data" style="width: 25mm;"><?= $kop ?></td>
				<td class="field t10b"><span class="down">&nbsp;коп.</span></td>
			</tr></tbody></table></td></tr>
			<tr><td class="data" style="line-height: 0.1; border: none;"><span style="position: relative; top: 3.5mm;"><?= $sum_string ?></span></td></tr>
			<tr><td style="padding: 0 2pt 0 2pt;"><table class="full_w" cellspacing="0">
<tbody><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr><tr><td style="height: 2pt; border-bottom: #AAA 1pt solid;"><div style="height: 1pt; overflow: hidden; font-size: 0;">&nbsp;</div></td></tr>				<tr><td class="subscript7">
					(Рубли прописью, копейки цифрами)</td></tr>
			</tbody></table></td></tr>

			<tr><td style="padding: 0 2pt 0 2pt;"><table class="full_w" cellspacing="0">
				<tbody><tr><td class="field t9bi">Кому:</td>
				<td class="data"><span class="nowr"><?= $fio ?></span></td></tr>
				<tr><td></td><td class="subscript">
					(для юридического лица – полное или краткое наименование,
					для гражданина – фамилия, имя, отчество полностью)
				</td></tr>
				<tr><td class="field t9bi">Куда:</td>
				<td class="data"><span class="nowr"><?= $address ?></span></td></tr>
				<tr><td></td><td class="subscript">
					(Адрес получателя)
				</td></tr>
				<tr><td class="data" style="border: none;" colspan="2">&nbsp;</td></tr>
			</tbody></table></td></tr>

			<tr><td><table class="full_w" cellspacing="0">

				<tbody><tr><td class="left2 top2" style="padding: 2pt 1pt 0 1pt;" rowspan="2">
					<div class="data left2 right2 top2 bottom2" style="width: 4mm; height: 4mm;">
					&nbsp;				</div></td>
				<td class="top2 right2 subscript" style="font-size: 7.5pt;">
					<b><i>Заполняется при приеме перевода в адрес юридического лица</i></b>
				</td>
				<td class="field t7 h_right bottom2 linetop" style="width: 9%; vertical-align: top;" rowspan="2">Индекс&nbsp;</td>
				<td class="bottom2 linetop" style="width: 16%; padding: 1pt 2pt 1pt 0;" rowspan="2">
					<table class="cells" cellspacing="0"><tbody><tr>
					<? foreach ($zip as $number): ?>
					<td class="cell" style="width: 16%;"><?= $number; ?></td>
					<? endforeach; ?>
					</tr></tbody></table>				</td></tr>
				<tr><td class="t8b right2" style="vertical-align: bottom;">
					<span style="line-height: 0.5; position: relative; bottom: 0.2em;">
						Выплатить наличными деньгами</span></td></tr>
				<tr><td colspan="4" class="left2 right2" style="padding: 2mm 1pt 0 1pt">
				<table class="full_w" cellspacing="0"><tbody><tr>
					<td class="field t9bi">ИНН:</td>
					<td style="width: 36%;"><table class="cells" cellspacing="0"><tbody><tr>
					<? foreach ($code as $number): ?>
					<td class="cell" style="width: 8%;"><?= $number ?></td>
					<? endforeach; ?>
					</tr></tbody></table></td>
					<td class="field t9bi">&nbsp;Кор/счет:</td>
					<td style="width: 60%"><table class="cells" cellspacing="0"><tbody><tr>
					<? foreach ($account as $number): ?>
					<td class="cell" style="width: 5%;"><?= $number ?></td>
					<? endforeach; ?>
					</tr></tbody></table></td>
				</tr></tbody></table></td></tr>
				<tr><td colspan="4" class="left2 right2" style="padding: 1mm 1pt 0 1pt;">
				<table class="full_w" cellspacing="0"><tbody><tr>
					<td class="field t9bi">Наименование&nbsp;банка:</td>
					<td class="data2"><span class="nowr"><?= $bank ?></span></td>
				</tr></tbody></table></td></tr>
				<tr><td colspan="4" class="left2 right2 bottom2" style="padding: 1pt 1pt 1pt 1pt">
				<table class="full_w" cellspacing="0"><tbody><tr>
					<td class="field t9bi">Рас/счет:&nbsp;&nbsp;</td>
					<td style="width: 58%;"><table class="cells" cellspacing="0"><tbody><tr>
					<? foreach ($bank_account as $number): ?>
					<td class="cell" style="width: 5%;"><?= $number ?></td>
					<? endforeach; ?>
					</tr></tbody></table></td>
					<td class="field t9bi" style="padding-left: 8mm;">БИК:</td>
					<td style="width: 27%"><table class="cells" cellspacing="0"><tbody><tr>
					<? foreach ($bik as $number): ?>
					<td class="cell" style="width: 11%;"><?= $number ?></td>
					<? endforeach; ?>
					</tr></tbody></table></td>
				</tr></tbody></table>
				</td></tr>

			</tbody></table></td></tr>
			
			<tr><td style="padding: 1pt 2pt 0 2pt;"><table class="full_w" cellspacing="0">
				<tbody><tr><td class="field t9bi">От&nbsp;кого:</td>
				<td class="data"><span class="nowr"><?= $customer_fio ?></span></td>
				<td class="field t6i linebottom" style="text-align: right;">
					<i>ИНН&nbsp;при&nbsp;<br>его&nbsp;наличии&nbsp;</i></td>
				<td style="width: 30%;"><table class="cells" cellspacing="0"><tbody><tr><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td><td class="cell" style="width: 8%;">&nbsp;</td></tr></tbody></table></td></tr>
				<tr><td></td><td class="subscript">
					(фамилия, имя, отчество)
				</td></tr>
				<tr><td class="data" colspan="4">&nbsp;</td></tr>
			</tbody></table></td></tr>

			<tr><td style="padding: 0 2pt 0 2pt;"><table class="full_w" cellspacing="0">
				<tbody><tr><td class="field t9bi" style="height: 5mm;">Адрес&nbsp;отправителя:</td>
				<td class="data" colspan="3"><span class="nowr"><?= $customer_address1 ?></span></td></tr>
				<tr><td></td><td class="subscript" colspan="3">
Адрес места жительства (регистрации), <strike>адрес пребывания</strike> (ненужное зачеркнуть)				</td></tr>
				<tr><td class="data" colspan="4"><span class="nowr"><?= $customer_address2 ?></span></td></tr>
			</tbody></table><table class="full_w" cellspacing="0">
				<tbody><tr><td class="data">&nbsp;</td>
				<td class="field t7 h_right linebottom" style="vertical-align: top;">Индекс&nbsp;</td>
				<td style="width: 15%; padding-top: 1pt;">
					<table class="cells" cellspacing="0"><tbody><tr>
					<? foreach ($customer_zip as $number): ?>
					<td class="cell" style="width: 16%;"><?= $number ?></td>
					<? endforeach; ?>
					</tr></tbody></table>				</td></tr>
			</tbody></table></td></tr>

			<tr><td style="padding: 1mm 2pt 0 2pt;"><table class="full_w" cellspacing="0">
				<tbody><tr><td class="field t9bi">Сообщение:&nbsp;&nbsp;</td>
				<td class="data"><table cellspacing="0" class="full_w lineleft lineright"><tbody><tr><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td></tr></tbody></table></td></tr>
				<tr><td class="subscript" colspan="2" style="text-align: left;">
					(назначение&nbsp;платежа)</td></tr>
				<tr><td style="padding-left: 7mm;" colspan="2"><table cellspacing="0" class="full_w lineleft lineright"><tbody><tr><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td><td class="v_bottom zerosize"><div class="riska"></div></td><td class="data" style="width: 2%; border: none;">&nbsp;</td></tr></tbody></table></td></tr>
			</tbody></table></td></tr>

			<tr><td style="padding: 0 2pt 0 2pt;"><table class="full_w" cellspacing="0">
			<tbody><tr><td class="lineleft linetop lineright linebottom" style="padding: 2mm 1mm 0 1mm;">
			<table class="full_w" cellspacing="0">
				<tbody><tr><td class="field t8">Предъявлен&nbsp;</td>
				<td class="data2" style="width: 30%;">
					&nbsp;</td>
				<td class="field t8">&nbsp;Серия&nbsp;</td>
				<td class="data2" style="width: 15%;">
					&nbsp;</td>
				<td class="field t8">&nbsp;№&nbsp;</td>
				<td class="data2" style="width: 20%;">
					&nbsp;</td>
				<td class="field t8">&nbsp;выдан&nbsp;</td>
				<td class="data2" style="width: 4%;">
					&nbsp;</td>
				<td class="field t8">.</td>
				<td class="data2" style="width: 4%;">
					&nbsp;</td>
				<td class="field t8">20</td>
				<td class="data2" style="width: 4%;">
					&nbsp;</td>
				<td class="field t8">г.</td></tr>
				<tr><td></td><td class="subscript">(наименование документа)</td></tr>
				<tr><td class="data2" colspan="13">
					&nbsp;				</td></tr>
				<tr><td class="subscript" colspan="13">(наименование учреждения)</td></tr>
			</tbody></table></td></tr>
			<tr><td class="lineleft lineright linebottom" style="padding: 0 1mm 1pt 1mm;">
			<div class="field t7bi"><u>Для нерезидентов России</u></div>
			<table class="full_w" cellspacing="0">
				<tbody><tr><td class="field t8">Предъявлен&nbsp;</td>
				<td class="data2" style="width: 30%;">
					&nbsp;</td>
				<td class="field t8">&nbsp;Серия&nbsp;</td>
				<td class="data2" style="width: 15%;">
					&nbsp;</td>
				<td class="field t8">&nbsp;№&nbsp;</td>
				<td class="data2" style="width: 20%;">
					&nbsp;</td>
				<td class="field t8">&nbsp;выдан&nbsp;</td>
				<td class="data2" style="width: 4%;">
					&nbsp;</td>
				<td class="field t8">.</td>
				<td class="data2" style="width: 4%;">
					&nbsp;</td>
				<td class="field t8">20</td>
				<td class="data2" style="width: 4%;">
					&nbsp;</td>
				<td class="field t8">г.</td></tr>
				<tr><td></td><td class="subscript">(наименование документа)</td></tr>
			</tbody></table><table cellspacing="0">
				<tbody><tr><td class="field t8">Дата&nbsp;срока&nbsp;пребывания&nbsp;с&nbsp;</td>
				<td class="data2" style="width: 8mm;">
					&nbsp;</td>
				<td class="field t8">.</td>
				<td class="data2" style="width: 8mm;">
					&nbsp;</td>
				<td class="field t8">20</td>
				<td class="data2" style="width: 8mm;">
					&nbsp;</td>
				<td class="field t8">г.,&nbsp;по</td>
				<td class="data2" style="width: 8mm;">
					&nbsp;</td>
				<td class="field t8">.</td>
				<td class="data2" style="width: 8mm;">
					&nbsp;</td>
				<td class="field t8">20</td>
				<td class="data2" style="width: 8mm;">
					&nbsp;</td>
				<td class="field t8">г.</td></tr>
			</tbody></table></td></tr>
			</tbody></table></td></tr>

			<tr><td style="padding: 2mm 1mm 1pt 3mm;"><table cellspacing="0"><tbody><tr>
				<td class="field t8bi"><u>Гражданство:</u>&nbsp;</td>
				<td class="data2" style="width: 40mm; text-align: left; border: none;">
					Россия				</td>
				<td class="field t8bi">&nbsp;<u>Подпись&nbsp;отправителя</u></td>
			</tr></tbody></table></td></tr>
			

		</tbody></table>
		</td></tr>

		<tr><td style="padding: 0 3mm 5mm 70mm;">
			<table class="full_w" cellspacing="0">
				<tbody><tr><td class="data" style="width: 25mm; line-height: 2;">&nbsp;</td></tr>
				<tr><td class="data" style="width: 25mm;">&nbsp;</td>
				<td class="field t9b" style="width: 3mm;">&nbsp;</td>
				<td class="data" style="width: 25mm; line-height: 2;">&nbsp;</td></tr>
				<tr><td class="subscript7">(шифр и подпись)</td>
				<td></td>
				<td class="subscript7">(подпись оператора)</td></tr>
				
			</tbody></table>
			
		</td></tr>
	</tbody></table></td>
	<td class="t8" style="padding-top: 20mm;">
		<img src="../images/vertical03.png" width="11" height="134" alt="&nbsp;">	</td>
</tr></tbody>
</table>

    <script type="text/javascript">
           window.onload = print;
    </script>
</body>
</html>