<?php
include_once dirname(__FILE__) . '/../config.inc.php';
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />

	<style type="text/css">
		body { font: normal 12px/16px Arial, sans-family; }
		table { width: 500px; border-collapse: collapse; border-spacing: 0; } 
		table td { border: 1px solid #000; padding: 2px; }
	</style>
</head>
<body>
<h3><u><?= $config['sitename'] ?></u></h3>

<h1>ЗАКАЗ № <?= $order['id_custom'] ?> от <?= date('d.m.Y', strtotime($order['date'])) ?> г.</h1>

<p>
	<b>Способ оплаты:</b> Наличными курьеру<br>
	<b>Комментарий:</b> Предварительно позвонить<br>
	<b>Покупатель:</b> <?= $customer_fio ?><br>
	<b>Телефон:</b> <?= $order['phone'] ?><br>
	<b>Адрес:</b> <?= $order['address'] ?><br>
	<b>Email:</b> <?= $order['email'] ?><br>
	<b>Примечание:</b> <?= $order['comment'] ?>
</p>
<table>
	<tr>
		<td>№</td>
		<td>Наименование товара</td>
		<td>Единица измерения</td>
		<td>Количество</td>
		<td>Цена, <?= $config['currency'] ?></td>
		<td>Сумма, <?= $config['currency'] ?></td>
	</tr>
	<? foreach ($order_items as $key => $item): ?>
	<? $products_num = 0; ?>
	<tr>
		<td><?= $key + 1?></td>
		<td><?= $item['product'] ?></td>
		<td align="right"><?= $item['unit'] ?></td>
		<td align="right"><?= $item['quantity'] ?></td>
		<td align="right"><?= $item['price'] ?></td>
		<td align="right"><?= number_format($item['price'] * $item['quantity'], 2, '.', '') ?></td>
	</tr>
	<? $products_num += $item['quantity']; ?>
	<? endforeach; ?>
	<? if ($order['delivery_cost'] != 0): ?>
	<tr>
		<td><?= $key + 2 ?></td>
		<td colspan="4" align="right">Стоимость доставки</td>
		<td align="right"><?= number_format($order['delivery_cost'], 2, '.', '') ?></td>
	</tr>
	<? endif; ?>
	<tr>
		<td></td>
		<td colspan="4" align="right"><b>Итого к оплате:</b></td>
		<td align="right"><?= $sum ?></td>
	</tr>

</table>

<p>Всего наименований <?= $products_num ?>, на сумму <?= $sum ?> <?= $config['currency'] ?></p>

<p>Руководитель предприятия _____________________ ( ________________ )</p>

    <script type="text/javascript">
           window.onload = print;
    </script>
</body>
</html>