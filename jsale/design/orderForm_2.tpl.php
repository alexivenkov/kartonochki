<form action="<?= $config['sitelink'] . $config['dir'] ?>relay.php" method="post" class="jSaleForm"
	  id="jsale_form_<?= $id_form ?>"
	  xmlns="http://www.w3.org/1999/html">
	<? if (isset($message) && is_string($message)): ?>
	<h6 class="jSaleMessage">
		<?= $message; ?>
	</h6>
	<? else: ?>
	<h6>
		Заполните эту фомру,<br/>
		<span style="color:#f60">чтобы получить Google Cardboard 2.0+<br/>
		и 2 подарка с общей выгодой 730р.</span>
	</h6>
	<? endif; ?>
	
	<!-- Дополнительные поля товара -->
	<? if (isset($product_params) && is_array($product_params) && !empty($product_params)): ?>
		<? $a = 0; ?>
		<? foreach ($product_params as $product_param): ?>
			<? $a++; ?>
			
			<? if ($a <= $order_step && $product_param['parent'] == '0' || $product_param['parent'] != '0' && isset($product['param_'.$product_params[$product_param['parent']]['name']]) && !empty($product['param_'.$product_params[$product_param['parent']]['name']]) || $config['params']['steps'] === false): ?>

				<div id="param_<?= $a ?>" class="add_product_param">
				<h4 style="float: left; width: 100px;"><?= $product_param['title'] ?>: <? if ($product_param['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></h4>
				<? foreach ($product_param['items'] as $key2 => $item): ?>
					<p class="float-left <? if (isset($product['param_'.$product_param['name']]) && $product['param_'.$product_param['name']] == $item['id_param_item'] || $product_param['type'] == 'flags' && isset($product['param_'.$product_param['name']][$key2]) && $product['param_'.$product_param['name']][$key2] == $item['id_param_item']): ?>hover<? endif; ?>">
					<? if (is_file(dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $config['params']['upload']['dir'] . '/' . $item['id_param_item'] . '.' . $config['params']['upload']['type'])): ?>
						<label for="order_param_<?= $id_form ?>[<?= $item['id_param_item'] ?>]">
							<img src="<?= $config['sitelink'] . $config['dir'] . $config['download']['dir'] .'/'. $config['params']['upload']['dir'] .'/'. $item['id_param_item'] . '.' . $config['params']['upload']['type'] ?>" style="width:100px;">
						</label><br>
					<? endif; ?>
					<? if ($product_param['type'] == 'flags'): ?>
						<input type="checkbox" name="order_param_<?= $product_param['name'] ?>[<?= $key2 ?>]" id="order_param_<?= $id_form ?>[<?= $item['id_param_item'] ?>]" value="<?= $item['id_param_item'] ?>"<? if (isset($product['param_'.$product_param['name']][$key2]) && $product['param_'.$product_param['name']][$key2] == $item['id_param_item']): ?> checked="checked"<? endif; ?> onchange="change_adds_<?= $id_form ?>(<?= $a ?>)">
					<? elseif ($product_param['type'] == 'checkbox'): ?>
						<input type="checkbox" name="order_param_<?= $product_param['name'] ?>" id="order_param_<?= $id_form ?>[<?= $item['id_param_item'] ?>]" value="<?= $item['id_param_item'] ?>"<? if (isset($product['param_'.$product_param['name']]) && $product['param_'.$product_param['name']] == $item['id_param_item']): ?> checked="checked"<? endif; ?> onchange="change_adds_<?= $id_form ?>(<?= $a ?>)">
					<? elseif ($product_param['type'] == 'radio'): ?>
						<input type="radio" name="order_param_<?= $product_param['name'] ?>" id="order_param_<?= $id_form ?>[<?= $item['id_param_item'] ?>]" value="<?= $item['id_param_item'] ?>"<? if (isset($product['param_'.$product_param['name']]) && $product['param_'.$product_param['name']] == $item['id_param_item']): ?> checked="checked"<? endif; ?> onchange="change_adds_<?= $id_form ?>(<?= $a ?>)">
					<? endif; ?>
						<label for="order_param_<?= $id_form ?>[<?= $item['id_param_item'] ?>]"><?= $item['title'] ?></label>
					</p>
				<? endforeach; ?>
				<div class="clear"></div>
				</div>
			<? endif; ?>
		<? endforeach; ?>
	<? endif; ?>

	<? if (isset($disabled) && $disabled === true): ?>
	<? else: ?>
	<? if ($config['form']['lastname']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['form']['lastname']['label'];?><? if ($config['form']['lastname']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_lastname" value="<?= (isset($lastname)) ? $lastname : '';?>" placeholder="<?= $config['form']['lastname']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('lastname', $message)): ?>
		<span class="warning"><?= $config['form']['lastname']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['name']['enabled'] == true): ?>
	<p class="float form-center">
		<label><?= $config['form']['name']['label'];?><? if ($config['form']['name']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_name" value="<?= (isset($name)) ? $name : '';?>" placeholder="<?= $config['form']['name']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('name', $message)): ?>
			<span class="warning"><?= $config['form']['name']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['fathername']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['form']['fathername']['label'];?><? if ($config['form']['fathername']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_fathername" value="<?= (isset($fathername)) ? $fathername : '';?>" placeholder="<?= $config['form']['fathername']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('fathername', $message)): ?>
		<span class="warning"><?= $config['form']['fathername']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['email']['enabled'] == true): ?>
	<p class="float form-center">
		<label><?= $config['form']['email']['label'];?><? if ($config['form']['email']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_email" value="<?= (isset($email)) ? $email : '';?>" placeholder="<?= $config['form']['email']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('email', $message)): ?>
		<span class="warning"><?= $config['form']['email']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['phone']['enabled'] == true): ?>
	<p class="float form-center">
		<label><?= $config['form']['phone']['label'];?><? if ($config['form']['phone']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_phone" value="<?= (isset($phone)) ? $phone : '';?>" class="jSalePhone" placeholder="<?= (isset($country) && isset($config['form']['country']['select'][$country])) ? $config['form']['country']['select'][$country][2] : $config['form']['phone']['example']; ?>">
		<? if (isset($message) && is_array($message) && in_array('phone', $message)): ?>
		<span class="warning"><?= $config['form']['phone']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['country']['enabled'] == true): ?>
	<div class="clear"></div>
	<p class="float">
		<label><?= $config['form']['country']['label'];?><? if ($config['form']['country']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		
		<? if (isset($config['form']['country']['select'])): ?>
		<select name="order_country" onchange="change_country_<?= $id_form ?>(this)">
			<? foreach ($config['form']['country']['select'] as $country_code => $country_data): ?>
			<option value="<?= $country_code ?>" <?= (isset($country) && $country == $country_code) ? 'selected="selected"' : ''?>><?= $country_data[0] ?></option>
			<? endforeach; ?>
		</select>
		<? else: ?>
		<input type="text" name="order_country" value="<?= (isset($country)) ? $country : '';?>" placeholder="<?= $config['form']['country']['example'] ?>">
		<? endif; ?>
		<? if (isset($message) && is_array($message) && in_array('country', $message)): ?>
		<span class="warning"><?= $config['form']['country']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['zip']['enabled'] == true): ?>
	<div class="clear"></div>
	<p class="float">
		<label><?= $config['form']['zip']['label'];?><? if ($config['form']['zip']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_zip" value="<?= (isset($zip)) ? $zip : '';?>" placeholder="<?= $config['form']['zip']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('zip', $message)): ?>
		<span class="warning"><?= $config['form']['zip']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['region']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['form']['region']['label'];?><? if ($config['form']['region']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_region" value="<?= (isset($region)) ? $region : '';?>" placeholder="<?= $config['form']['region']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('region', $message)): ?>
		<span class="warning"><?= $config['form']['region']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? endif; ?>

	<p class="submit">
		<input type="submit" name="order_submit" value="Получить Google Cardboard 2.0" class="jSaleSubmit jSaleButton jSaleLarge" <?= (isset($disabled) && $disabled === true) ? 'disabled="disabled"' : '' ?>>

		<input type="hidden" name="sum" value="<?= 730 ?>">
		<input type="hidden" name="order_spam" value="<?= $antispam ?>">
		<input type="hidden" name="order_nospam" value="">
		<input type="hidden" name="hash" value="<?= $product['hash']; ?>">
		<input type="hidden" name="utm" value="<?= (isset($product['utm'])) ? $product['utm'] : '' ?>">
		<input type="hidden" name="source" value="<?= (isset($product['source'])) ? $product['source'] : '' ?>">
		<input type="hidden" name="cookies" value=''>
		<input type="hidden" name="product_qty" value="1">
		<input type="hidden" name="city" value="1">

		<input type="hidden" name="order_payment" value="email" />
		<input type="hidden" name="product_id" value="<?= (isset($product['id_product'])) ? $product['id_product'] : '' ?>">
		<input type="hidden" name="product_code" value="<?= $product['code']; ?>">
		<input type="hidden" name="product_title" value="<?= $product['title']; ?>">
		<input type="hidden" name="product_price" value="<?= $product['price']; ?>">
		<input type="hidden" name="product_discount" value="<?= $product['discount']; ?>">
		<input type="hidden" name="product_unit" value="<?= $product['unit']; ?>">
		<input type="hidden" name="product_param1" value="<?= (isset($product['param1'])) ? $product['param1'] : ''; ?>">
		<input type="hidden" name="product_param2" value="<?= (isset($product['param2'])) ? $product['param2'] : ''; ?>">
		<input type="hidden" name="product_param3" value="<?= (isset($product['param3'])) ? $product['param3'] : ''; ?>">
		<input type="hidden" name="template" value="<? preg_match('/orderForm\_(.*).tpl.php/', __FILE__, $file); if (isset($file[1])) echo $file[1]; ?>">
		<input type="hidden" name="form_type" value="<?= $product['form_type'] ?>">
		<input type="hidden" name="form_config" value="<?= $product['form_config'] ?>">
		<input type="hidden" name="order_bonus" value="<?= $product['bonus'] ?>">
		<input type="hidden" name="order_upsell" value="<?= $product['upsell'] ?>">
		<input type="hidden" name="bandle_products" value="<?= $product['bandle_products'] ?>">
		
		<input type="hidden" name="order_step" value="<?= $order_step ?>">
		
		<input type="hidden" name="delivery_cost" value="<?= (isset($delivery_type) && $config['deliveries'][$delivery_type]['cost'] != '') ? $config['deliveries'][$delivery_type]['cost'] : 0 ?>">
		<input type="hidden" name="free_delivery" value="<?= (isset($config['payments'][$payment_type]['free_delivery']) && $config['payments'][$payment_type]['free_delivery'] === true) ? 'true' : 'false' ?>">
		<input type="hidden" name="id_form" value="<?= $id_form ?>">
		<? if (isset($_COOKIE['jsale_ref'])): ?><input type="hidden" name="ref" value="<?= $_COOKIE['jsale_ref'] ?>"><? endif; ?>
		<? if (isset($disabled) && $disabled === true): ?><input type="hidden" name="disabled" value="disabled"><? endif; ?>
		<? if (isset($message) && $message !== false && !is_array($message)): ?><input type="hidden" name="message" value="<?= $message ?>"><? endif; ?>
		<input type="hidden" name="product_qty_type" value="<?= $product['qty_type']; ?>">
	</p>
</form>
<script type="text/javascript">
function show_payment_info_<?= $id_form ?>(select)
{
	var payments = eval(<?= json_encode($config['payments']) ?>);
	var orderPayment = (select == '<?= $payment_type;?>') ? '<?= $payment_type;?>' : select.value;
	
	if (orderPayment == select.value)
		jQuery('#jsale_form_<?= $id_form ?>').find('.jSaleQty').trigger('keyup');
	
	var free_delivery = payments[orderPayment]['free_delivery'];
	var is_free_delivery = jQuery('#jsale_form_<?= $id_form ?>').find('[name="free_delivery"]').val();
	
	if (free_delivery === true && is_free_delivery == 'false' || free_delivery === false && is_free_delivery == 'true')
		jQuery('#jsale_form_<?= $id_form ?>').find('.jSaleQty').trigger('keyup');
	
	for (var key in payments)
	{
		jQuery('#jsale_form_<?= $id_form ?>').find('#payment_info .' + key).css('display', 'none');

		if (key === orderPayment)
			jQuery('#jsale_form_<?= $id_form ?>').find('#payment_info .' + key).css('display', 'block');
	}
}
function show_delivery_info_<?= $id_form ?>(select)
{
	var deliveries = eval(<?= json_encode($config['deliveries']) ?>);
	var orderDelivery = (select == '<?= $delivery_type;?>') ? '<?= $delivery_type;?>' : select.value;

	var cost = parseFloat(deliveries[orderDelivery]['cost']);
	var prev_cost = jQuery('#jsale_form_<?= $id_form ?>').find('[name="delivery_cost"]').val();
	var free_delivery = jQuery('#jsale_form_<?= $id_form ?>').find('[name="free_delivery"]').val();

	if (cost != prev_cost && free_delivery == 'false')
		jQuery('#jsale_form_<?= $id_form ?>').find('.jSaleQty').trigger('keyup');

	for (var key in deliveries)
	{
		jQuery('#jsale_form_<?= $id_form ?>').find('#delivery_info .' + key).css('display', 'none');

		if (key === orderDelivery)
			jQuery('#jsale_form_<?= $id_form ?>').find('#delivery_info .' + key).css('display', 'block');
	}
}
function change_adds_<?= $id_form ?>(step)
{
	if (typeof step !== 'undefined')
	{
		var order_step = jQuery('#jsale_form_<?= $id_form ?>').find('[name="order_step"]');
		var new_step = parseInt(order_step.val());
		if (step >= new_step)
			order_step.val(new_step + 1);
	}
		
	jQuery('#jsale_form_<?= $id_form ?>').find('.jSaleQty').trigger('keyup');
}
function change_country_<?= $id_form ?>(select)
{
	<? if ($config['form']['phone']['masked'] === true && isset($config['form']['country']['select']) && !empty($config['form']['country']['select'])): ?>
	var country = select.value;
	var countries = eval(<?= json_encode($config['form']['country']['select']) ?>);
	
	for (var key in countries)
	{
		if (key === country)
		{
			jQuery('#jsale_form_<?= $id_form ?>').find('.jSalePhone').mask(countries[country][1]);
			jQuery('#jsale_form_<?= $id_form ?>').find('.jSalePhone').attr('placeholder', countries[country][2]);
		}
	}
	<? endif; ?>
}
show_payment_info_<?= $id_form ?>('<?= $payment_type;?>');
show_delivery_info_<?= $id_form ?>('<?= $delivery_type;?>');
<? if ($config['form']['phone']['masked'] === true): ?>
jQuery('.jSalePhone').mask('<?= (isset($country) && isset($config['form']['country']['select'][$country])) ? $config['form']['country']['select'][$country][1] : $config['form']['phone']['mask'] ?>');
<? endif; ?>
<? if (isset($message) && is_array($message)): ?>
	jQuery('#jsale_form_<?= $id_form ?>').find('[name="order_<?= $message[0] ?>"]').focus();
	<? foreach ($message as $input): ?>
		jQuery('#jsale_form_<?= $id_form ?>').find('[name="order_<?= $input ?>"]').addClass('error');
	<? endforeach; ?>
<? endif; ?>
</script>