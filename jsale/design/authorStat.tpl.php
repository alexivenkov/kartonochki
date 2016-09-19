<?/*Шаблон индекса админки======================*/?><!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><?# Подключение шапки админкиinclude_once dirname(__FILE__) . '/../design/adminHeader.tpl.php';?>	<br/>    <div class="container-fluid">		<? if (isset($message)): ?>		<div class="alert alert-error fade in">			<button type="button" class="close" data-dismiss="alert">&times;</button>			<?= $message ?>		</div>		<? endif ?>				<p class="text-info">Здесь находится статистика продаж ваших продуктов.</p>				<? if (count($products) > 0): ?>		<h5 class="products_list btn">Список товаров (<?= count($products) ?>)</h5>		<table id="products_list" class="table table-striped table-bordered table-hover table-condensed">		<tr>			<th>ID</th>			<th>Название</th>			<th>Стоимость</th>		</tr>		<? foreach ($products as $product): ?>		<tr>			<td><?= $product['id_product'] ?></td>			<td><?= $product['title'] ?></td>			<td><?= $product['price'] ?> <?= $config['currency'] ?></td>		</tr>		<? endforeach; ?>		</table>		<hr/>		<? endif; ?>				<? if (count($all_orders) > 0): ?>		<div class="well">		<h4>Заказы</h4>		<p>Заказов оформлено: <?= $total_count ?></p>		<p>Заказов оплачено: <?= $paid_count ?></p>		<hr/>		<p>Заработано: <?= number_format($paid_sum, 2, '.', '') ?> <?= $config['currency'] ?></p>		</div>		<h5 class="orders_list btn">Список заказов (<?= count($all_orders) ?>)</h5>		<table id="orders_list" class="table table-striped table-bordered table-hover table-condensed">		<tr>			<th>ID</th>			<th>Дата</th>			<th>Статус</th>			<th>Товары</th>			<th>Сумма</th>			<th>Комиссия</th>		</tr>		<? foreach ($all_orders as $order): ?>		<tr>			<td><?= $order['id_custom'] ?></td>			<td><?= $order['date'] ?></td>			<td <?= (in_array($order['status'], $success_statuses)) ? 'class="text-success"' : '' ?>><?= $config['statuses'][$order['status']] ?></td>			<td><? foreach ($order['items'] as $item): ?><?= $item['product'] ?> - <?= $item['quantity'] ?> <?= $item['unit'] ?> по <?= ($config['discounts']['fixed'] === true) ? number_format($item['price'] - $item['discount'], 2, '.', '') : number_format($item['price'] * (1 - $item['discount'] / 100), 2, '.', '') ?> <?= $config['currency'] ?><br><? endforeach; ?></td>			<td><?= $order['sum'] ?> <?= $config['currency'] ?></td>			<td><?= (isset($order['commission'])) ? $order['commission'] : 0 ?> <?= $config['currency'] ?></td>		</tr>		<? endforeach; ?>		</table>		<hr />		<? endif; ?>				<p>Вам выплачено: <?= $author['paid'] ?> <?= $config['currency'] ?></p>		<h4 <? if ($paid_sum - $author['paid'] != 0): ?>class="text-success"<? endif ?>>Вам осталось выплатить: <?= number_format($paid_sum - $author['paid'], 2, '.', '') ?> <?= $config['currency'] ?></h4>    </div><script type="text/javascript">	$(document).ready(function() {			$(".orders_list").click(function () {			$("#orders_list").toggle('fast');		});		$(".products_list").click(function () {			$("#products_list").toggle('fast');		});	});</script></body></html>