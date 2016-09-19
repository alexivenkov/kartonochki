<div class="jSaleWrapper">
<form action="" method="post" class="jSaleForm">
<h2>Выберите метод оплаты</h2>
	<? foreach ($config['payments'] as $type => $payment): ?>
	<? if ($payment['enabled'] == true): ?>
	<input type="radio" name="order_payment" value="<?= $type ?>" id="payment_<?=$type?>" <?= ($type == $order_payment) ? 'checked="checked"' : ''; ?> onchange="show_payment_info(this);" /> <label for="payment_<?=$type?>"><?= $payment['title'] ?></label><br/>
	<? endif; ?>
	<? endforeach; ?>
	
	<? if ($order_payment == 'yandex_eshop'): ?>
	<h4>Выбор метода оплаты:</h4>
	<? foreach ($config['payments']['yandex_eshop']['types'] as $type => $payment_title): ?>
	<input type="radio" name="order_payment_ym" value="<?= $type ?>" id="payment_ym_<?=$type?>" <?= (isset($yandex_payment_type) && $yandex_payment_type == $type) ? 'checked="checked"' : '' ?> /> <label for="payment_ym_<?=$type?>"><?= $payment_title ?></label><br/>
	<? endforeach; ?>
	<? endif; ?>
	
	<br/><h3>Описание</h3>
	<div id="payment_info" style="margin: 0;">
	<? foreach ($config['payments'] as $type => $payment): ?>
	<? if ($payment['enabled'] == true): ?>
	<div class="<?= $type ?>" style="padding: 10px;"><?= $payment['info'] ?></div>
	<? endif; ?>
	<? endforeach; ?>
	</div>

<h2>Выберите метод доставки</h2>
	<? foreach ($config['deliveries'] as $type => $delivery): ?>
	<? if ($delivery['enabled'] == true): ?>
	<? if ($config['payment2delivery']['enabled'] !== true || $config['payment2delivery']['enabled'] === true && !isset($config['payment2delivery'][$order_payment]) || $config['payment2delivery']['enabled'] === true && isset($config['payment2delivery'][$order_payment]) && in_array($type, $config['payment2delivery'][$order_payment])): ?>
	<input type="radio" name="order_delivery" value="<?= $type ?>" id="delivery_<?=$type?>" <?= ($type == $order_delivery) ? 'checked="checked"' : ''; ?> onchange="show_delivery_info(this);" /> <label for="delivery_<?=$type?>"><?= $delivery['title'] ?></label><br/>
	<? endif; ?>
	<? endif; ?>
	<? endforeach; ?>
	
	<br/><h3>Описание</h3>
	<div id="delivery_info" style="margin: 0;">
	<? foreach ($config['deliveries'] as $type => $delivery): ?>
	<? if ($delivery['enabled'] == true): ?>
	<div class="<?= $type ?>" style="padding: 10px;"><?= $delivery['info'] ?></div>
	<? endif; ?>
	<? endforeach; ?>
	</div>
<br/><h3>Сумма заказа</h3>
<?= $order_sum ?> <?= $config['currency'] ?>
<p class="submit">
	<input type="hidden" name="delivery_cost" value="<?= (isset($order_delivery) && $config['deliveries'][$order_delivery]['cost'] != '') ? $config['deliveries'][$order_delivery]['cost'] : 0 ?>">
	<input type="hidden" name="free_delivery" value="<?= (isset($config['payments'][$order_payment]['free_delivery']) && $config['payments'][$order_payment]['free_delivery'] === true) ? 'true' : 'false' ?>">
	<input type="hidden" name="id_order" value="<?= $id_order ?>">
	<input type="submit" name="save" class="jSaleButton" value="Изменить">
</p>
</form>
</div>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="<?= $config['sitelink'] . $config['dir'] ?>css/jsale.css"></link>
<script type="text/javascript">
function show_payment_info(select)
{
	var payments = eval(<?=json_encode($config['payments'])?>);
	var orderPayment = (select == '<?=$order_payment?>') ? '<?=$order_payment?>' : select.value;
	
	if (orderPayment == select.value)
		$('form').submit();

	var free_delivery = payments[orderPayment]['free_delivery'];
	var is_free_delivery = $('[name="free_delivery"]').val();

	if (free_delivery === true && is_free_delivery == 'false' || free_delivery === false && is_free_delivery == 'true')
		$('form').submit();
	
	for (var key in payments)
	{
		$('#payment_info .' + key).css('display', 'none');

		if (key === orderPayment)
			$('#payment_info .' + key).css('display', 'block');
	}
}
function show_delivery_info(select)
{
	var deliveries = eval(<?=json_encode($config['deliveries'])?>);
	var orderDelivery = (select == '<?=$order_delivery?>') ? '<?=$order_delivery?>' : select.value;

	var cost = parseFloat(deliveries[orderDelivery]['cost']);
	var prev_cost = $('[name="delivery_cost"]').val();
	var free_delivery = $('[name="free_delivery"]').val();
	
	if (cost != prev_cost && free_delivery == 'false')
		$('form').submit();
	
	for (var key in deliveries)
	{
		$('#delivery_info .' + key).css('display', 'none');

		if (key === orderDelivery)
			jQuery('#delivery_info .' + key).css('display', 'block');
	}
}
show_payment_info('<?=$order_payment?>');
show_delivery_info('<?=$order_delivery?>');
</script>