<script type="text/javascript" src="js/order-form.js" xmlns="http://www.w3.org/1999/html"></script>
<form action="<?= $config['sitelink'] . $config['dir'] ?>relay.php" method="post" class="jSaleForm" id="jsale_form_<?= $id_form ?>">
	<? if (isset($message) && is_string($message)): ?>
	<h6 class="jSaleMessage">
		<?= $message; ?>
	</h6>
	<? else: ?>
	<? endif; ?>
	
	<!-- Скрипт подмены текста в зависимости от геолокации -->
	<div class="free-delivery-wrapper">
		<div class="free-delivery-text">&gt;&gt;&gt; При заказе от 2800 р. доставка бесплатно! &lt;&lt;&lt;</div>
	</div>

<div id="u44" class="text" style="visibility: visible;">
          <p><span style="color: #ff9000;">1. Выберите количество</span></p>
        </div>

	<div class="topimage">
		<img class="imgorder" src="http://test.kartonochki.ru/images/orderpage_img.jpg">
	</div>
	<div class="toptext">
		<span><?= $product['title']; ?></span><br>
		<span><?= $product['param3']; ?><span>

	<? if ($product['qty_type'] == 'text'): ?>
		<div style="float:right; margin-top: -15px; padding-right: 70px;">
		<?= ($config['product']['qty_buttons'] === true) ? '<button class="jSaleQtyBtn jSaleQtyMinus">-</button>' : ''?>
		<input type="text" name="product_qty" value="<?= $product['qty']; ?>" class="jSaleQty">
		<?= ($config['product']['qty_buttons'] === true) ? '<button class="jSaleQtyBtn jSaleQtyPlus">+</button>' : ''?> &nbsp;&nbsp;
		<b style="font-size: 18px;"><span id="price"><?= $product['subtotal']; ?></span>&nbsp;р.</b>
		</div>
	<? else: ?>
		<input type="hidden" name="product_qty" value="<?= $product['qty']; ?>" class="jSaleQty">
	<? endif; ?>
	</div>
	<br>
	
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
						<label for="order_param_<?= $id_form ?>[<?= $item['id_param_item'] ?>]"><?= $item['title'] ?>11</label>
					</p>
				<? endforeach; ?>
				<div class="clear"></div>
				</div>
			<? endif; ?>
		<? endforeach; ?>
	<? endif; ?>
	

	<? if (isset($disabled) && $disabled === true): ?>
	<? else: ?>

	<div class="clear"></div>
	
<div id="u44" class="text u441" style="visibility: visible;">
          <p><span style="color: #ff9000;">2. Укажите данные доставки</span></p>
        </div>

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
		<input type="text" name="order_zip" value="<?= (isset($zip)) ? $zip : '';?>" placeholder="<?= $config['form']['zip']['example'] ?>" <? if (isset($config['deliveries']['99']['enabled']) && $config['deliveries']['99']['enabled'] === true): ?>onchange="get_index_<?= $id_form ?>()"<?endif;?>>
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
	<? if ($config['form']['city']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['form']['city']['label'];?><? if ($config['form']['city']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
		<span id="city-container">
			<span id="city-spinner" style="display: none">
				<img src="/images/spin.gif" />
			</span>
			<span id="city-text"></span>
			<span id="city-change">Другой город?</span>

			<input id="city-input" type="text" value="<?= (isset($city)) ? $city : '';?>" style="display: none">
		</span>
		<input id="city-value" type="hidden" name="order_city" value="">
		<? if (isset($message) && is_array($message) && in_array('city', $message)): ?>
		<span class="warning"><?= $config['form']['city']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if (count($config['payments']) > 1 || $config['deliveries_view'] == true): ?>
		<p>
		<label>Выбор метода оплаты:</label>
		<select name="order_payment" onchange="show_payment_info_<?= $id_form ?>(this);">
			<? foreach ($config['payments'] as $type => $payment): ?>
			<? if ($payment['enabled'] == true): ?>
			<option value="<?= $type ?>"<? if (isset($payment_type) && $type == $payment_type): ?> selected="selected"<? endif; ?>><?= $payment['title'] ?></option>
			<? endif; ?>
			<? endforeach; ?>
		</select>
		</p>

		<div id="payment_info">
		<? foreach ($config['payments'] as $type => $payment): ?>
			<? if ($payment['enabled'] == true): ?>
			<p class="<?= $type ?>"><?= $payment['info'] ?></p>
			<? endif; ?>
		<? endforeach; ?>
		</div>
		
		<? if ($payment_type == 'yandex_eshop'): ?>
		<p>
			<label>Выбор метода оплаты:</label>
			<select name="yandex_payment_type">
				<? foreach ($config['payments']['yandex_eshop']['types'] as $type => $payment_title): ?>
				<option value="<?= $type ?>" <?= (isset($yandex_payment_type) && $yandex_payment_type == $type) ? 'selected="selected"' : '' ?>><?= $payment_title ?></option>
				<? endforeach; ?>
			</select>
		</p>
		<? endif; ?>
	<? else: ?>
		<input type="hidden" name="order_payment" value="<? reset($config['payments']); echo key($config['payments']);?>">
		<? if (key($config['payments']) == 'yandex_eshop'): ?>
		<p>
			<label>Выбор метода оплаты:</label>
			<select name="yandex_payment_type">
				<? foreach ($config['payments']['yandex_eshop']['types'] as $type => $payment_title): ?>
				<option value="<?= $type ?>" <?= (isset($yandex_payment_type) && $yandex_payment_type == $type) ? 'selected="selected"' : '' ?>><?= $payment_title ?></option>
				<? endforeach; ?>
			</select>
		</p>
		<? endif; ?>
	<? endif; ?>
	<? if (count($config['deliveries']) > 1 || $config['deliveries_view'] == true): ?>
		<div>
			<label style="vertical-align: top;padding-top: 15px;">Способ доставки:</label>
			<div style="display: inline-block">
				<div class="deliv_type_info">
					<input type="radio" name="order_delivery" id="option-russianmail" checked value="1" />
					<label>Почта России</label>
					<div id="russianmail-info">
						<p>Посылка 1 класса до ближайшего вам почтового отделения</p>
						<div class="delivery-info">
						</div>
					</div>
				</div>
				<div class="deliv_type_info">
					<input type="radio" name="order_delivery" id="option-courier" value="0" />
					<label>Курьерская доставка</label>
					<div id="courier-info" style="display: none;">
						<p>экспресс доставка домой или в офис</p>
						<div class="delivery-info">
						</div>
					</div>
				</div>
				<div class="deliv_type_info">
					<input type="radio" name="order_delivery" id="option-pvz" value="2" />
					<label>Пункты выдачи заказов</label>
					<div id="map" class="pvz-map" style="display: none"></div>
					<div id="pvz-info">
						<div class="delivery-info">
						</div>
					</div>
				</div>
			</div>

		<!--<div style="display:inline-block" class="deliv_type_info">
			<select name="order_delivery" onchange="show_delivery_info_<?/*= $id_form */?>(this);" class="deliv_type">
				<?/* foreach ($config['deliveries'] as $type => $delivery): */?>
				<?/* if ($delivery['enabled'] == true && isset($payment_type)): */?>
				<?/* if ($config['payment2delivery']['enabled'] !== true || $config['payment2delivery']['enabled'] === true && !isset($config['payment2delivery'][$payment_type]) || $config['payment2delivery']['enabled'] === true && isset($config['payment2delivery'][$payment_type]) && in_array($type, $config['payment2delivery'][$payment_type])): */?>
				<option value="<?/*= $type */?>"<?/* if (isset($delivery_type) && $type == $delivery_type): */?> selected="selected"<?/* endif; */?>><?/*= $delivery['title'] */?></option>
				<?/* endif; */?>
				<?/* endif; */?>
				<?/* endforeach; */?>
			</select>
			<div id="delivery_info">
			<?/* foreach ($config['deliveries'] as $type => $delivery): */?>
				<?/* if ($delivery['enabled'] == true): */?>
				<p class="<?/*= $type */?>"><?/*= $delivery['info'] */?></p>
				<?/* endif; */?>
			<?/* endforeach; */?>
			</div>
		</div>-->
		</div>
	<? else: ?>
	<input type="hidden" name="order_delivery" value="<? reset($config['deliveries']); echo key($config['deliveries']);?>">
	<? endif; ?>
	<p id="address" class="float">
		<label><?= $config['form']['address']['label'];?><? if ($config['form']['address']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?>:</label>
		<input type="text" name="order_address" value="<?= (isset($address)) ? $address : '';?>" placeholder="<?= $config['form']['address']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('address', $message)): ?>
			<span class="warning"><?= $config['form']['address']['empty'] ?></span>

		<? endif; ?>
	</p>
<div id="u44" class="text u441" style="visibility: visible; margin-top:30px;">
          <p><span style="color: #ff9000;">
	3. Укажите контактную информацию</span></p>
        </div>
	
		<? if ($config['form']['lastname']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['form']['lastname']['label'];?><? if ($config['form']['lastname']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
		<input type="text" name="order_lastname" value="<?= (isset($lastname)) ? $lastname : '';?>" placeholder="<?= $config['form']['lastname']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('lastname', $message)): ?>
		<span class="warning"><?= $config['form']['lastname']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['name']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['form']['name']['label'];?><? if ($config['form']['name']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
		<input type="text" name="order_name" value="<?= (isset($name)) ? $name : '';?>" placeholder="<?= $config['form']['name']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('name', $message)): ?>
		<span class="warning"><?= $config['form']['name']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	<? if ($config['form']['fathername']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['form']['fathername']['label'];?><? if ($config['form']['fathername']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
		<input type="text" name="order_fathername" value="<?= (isset($fathername)) ? $fathername : '';?>" placeholder="<?= $config['form']['fathername']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('fathername', $message)): ?>
		<span class="warning"><?= $config['form']['fathername']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	
		<? if ($config['form']['phone']['enabled'] == true): ?>
	
	
		<p class="float">
		<label><?= $config['form']['phone']['label'];?><? if ($config['form']['phone']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
		<input type="text" name="order_phone" value="<?= (isset($phone)) ? $phone : '';?>" class="jSalePhone" placeholder="<?= (isset($country) && isset($config['form']['country']['select'][$country])) ? $config['form']['country']['select'][$country][2] : $config['form']['phone']['example']; ?>">
		<? if (isset($message) && is_array($message) && in_array('phone', $message)): ?>
		<span class="warning"><?= $config['form']['phone']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	
	<? if ($config['form']['email']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['form']['email']['label'];?><? if ($config['form']['email']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
		<input type="text" name="order_email" value="<?= (isset($email)) ? $email : '';?>" placeholder="<?= $config['form']['email']['example'] ?>">
		<? if (isset($message) && is_array($message) && in_array('email', $message)): ?>
		<span class="warning"><?= $config['form']['email']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
	


	
		<? if ($config['form']['comment']['enabled'] == true): ?>
	<p>
		<label style="vertical-align: top; padding-top: 15px;"><?= $config['form']['comment']['label'];?><? if ($config['form']['comment']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
		<textarea name="order_comment" placeholder="<?= $config['form']['comment']['example'] ?>" class="t_comm"><?= (isset($comment)) ? $comment : '';?></textarea>
		<? if (isset($message) && is_array($message) && in_array('comment', $message)): ?>
		<span class="warning"><?= $config['form']['comment']['empty'] ?></span>
		<? endif; ?>
	</p>
	<? endif; ?>
		
	<p>
			
	<!-- Дополнительные поля -->
	<? if (isset($config['form']['add']) && is_array($config['form']['add'])): ?>
	<? foreach ($config['form']['add'] as $add_name => $add): ?>
		<? if (isset($add['enabled']) && $add['enabled'] === true): ?>
		<p id="order_add_<?= $add_name ?>">
			<? if ($add['type'] == 'select'): ?>
			<label><?= $add['label'];?><? if ($add['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
			<select class="deliv_type" name="order_<?= $add_name ?>" <?= (isset($add['cost'])) ? 'onchange="change_adds_' . $id_form . '()"' : '' ?>>
				<? foreach ($add['select'] as $key => $value): ?>
				<option value="<?= $key ?>"<? if (isset($$add_name) && $key == $$add_name): ?> selected="selected"<? endif; ?>><?= $value ?></option>
				<? endforeach; ?>
			</select>
			<? elseif ($add['type'] == 'checkbox'): ?>
			<label><?= $add['label'];?><? if ($add['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label>
			<input type="checkbox" name="order_<?= $add_name ?>" value="<?= $add['checkbox'] ?>" <?= (isset($$add_name) && $add['checkbox'] == $$add_name) ? 'checked="checked"' : '' ?> <?= (isset($add['cost'])) ? 'onchange="change_adds_' . $id_form. '()"' : '' ?> />
			<? else: ?>
			<label><?= $add['label'];?><? if ($add['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
			<input type="text" name="order_<?= $add_name ?>" value="<?= (isset($$add_name)) ? $$add_name : '';?>" <?= (isset($add['cost'])) ? 'onkeyup="change_adds_' . $id_form. '()"' : '' ?> placeholder="<?= $config['form']['add'][$add_name]['example'] ?>" />
			<? endif; ?>

			<? if (isset($message) && is_array($message) && in_array($add_name, $message)): ?>
			<span class="warning"><?= (isset($config['form']['add'][$add_name]['empty'])) ? $config['form']['add'][$add_name]['empty'] : $config['form']['add']['empty'] ?></span>
			<? endif; ?>
		</p>
		<? endif; ?>
	<? endforeach; ?>
	<? endif; ?>
	<? endif; ?>
	
	<? if ($config['codes']['enabled'] === true): ?>
		<label>Промо-код:</label>
		<input type="text" name="order_code" value="<?= ($config['partner']['codes']['auto'] === true && isset($_COOKIE['jsale_ref']) && empty($code)) ? $_COOKIE['jsale_ref'] : $code ?>" class="jSaleCode">
	<? else: ?>
		<input type="hidden" name="order_code" value="<?= ($config['partner']['codes']['auto'] === true && isset($_COOKIE['jsale_ref']) && empty($code)) ? $_COOKIE['jsale_ref'] : $code ?>" class="jSaleCode">
	<? endif; ?>
	
	<? if (isset($discount) && $discount != 0): ?>
		<span class="attention ">
			Ура, получилось! Ваша скидка: <?= $discount;?> <?if ($config['discounts']['fixed'] === true):?><?= $config['currency'] ?><? else: ?>%<? endif; ?> <!--Ваша цена: <span id="subtotal"><?= (isset($config['payments'][$payment_type]['rate'])) ? number_format($config['payments'][$payment_type]['rate'], 2, '.', '') * $order_sum : number_format($order_sum, 2, '.', '');?></span> <?= (isset($config['payments'][$payment_type]['currency'])) ? $config['payments'][$payment_type]['currency'] : $config['currency'] ?>-->
			
		</span>
		<span>
		<hr>
		<span class="itog-light">
		<? if (isset($config['deliveries']['99']['enabled']) && $config['deliveries']['99']['enabled'] === true): ?><span id="delivery_cost"></span><br><?endif;?>
		Итого, с учетом доставки вам нужно будет оплатить <span class="itog-price" id="subtotal"><?= (isset($config['payments'][$payment_type]['rate'])) ? number_format($config['payments'][$payment_type]['rate'] * $order_sum, 2, '.', '') : $order_sum  ?></span> <?= (isset($config['payments'][$payment_type]['currency'])) ? $config['payments'][$payment_type]['currency'] : $config['currency'] ?>
		</span> 
		<span class="itog-light">Оплата при получении</span>
		</span>
	<? else: ?>
		<hr>
		<span class="itog-light">
		<? if (isset($config['deliveries']['99']['enabled']) && $config['deliveries']['99']['enabled'] === true): ?><span id="delivery_cost"></span><br><?endif;?>
		Итого, с учетом доставки вам нужно будет оплатить <span class="itog-price" id="subtotal"><?= (isset($config['payments'][$payment_type]['rate'])) ? number_format($config['payments'][$payment_type]['rate'] * $order_sum, 2, '.', '') : $order_sum  ?></span> <?= (isset($config['payments'][$payment_type]['currency'])) ? $config['payments'][$payment_type]['currency'] : $config['currency'] ?>
		</span> 
		<span class="itog-light">Оплата при получении</span>

	<? endif; ?>

	<div id="default-delivery-cost" data-defaultDeliveryCost = "<?= $config['deliveries']['1']['cost'] ?>"></div>
	<p class="submit">
		<input type="submit" name="order_submit" value="Готово, оформляем!" class="jSaleSubmit jSaleButton jSaleLarge" <?= (isset($disabled) && $disabled === true) ? 'disabled="disabled"' : '' ?>>

		<input type="hidden" name="order_spam" value="<?= $antispam ?>">
		<input type="hidden" name="order_nospam" value="">
		<input type="hidden" name="hash" value="<?= $product['hash']; ?>">

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
		<input type="hidden" name="subtotal" value="<?= $product['subtotal'] ?>">
		
		<input type="hidden" name="order_step" value="<?= $order_step ?>">
		
		<input type="hidden" name="delivery_cost" value="<?= (isset($delivery_type) && $config['deliveries'][$delivery_type]['cost'] != '') ? $config['deliveries'][$delivery_type]['cost'] : 0 ?>">
		<input type="hidden" name="free_delivery" value="<?= (isset($config['payments'][$payment_type]['free_delivery']) && $config['payments'][$payment_type]['free_delivery'] === true) ? 'true' : 'false' ?>">
		<input type="hidden" name="id_form" value="<?= $id_form ?>">
		
		<? if (isset($_COOKIE['jsale_ref']) || isset($cookies['jsale_ref'])): ?><input type="hidden" name="ref" value="<?= (isset($_COOKIE['jsale_ref'])) ? $_COOKIE['jsale_ref']: $cookies['jsale_ref']; ?>"><? endif; ?>
		
		<? if (isset($_COOKIE['jsale_utm']) || isset($cookies['jsale_utm'])): ?><input type="hidden" name="utm" value="<?= (isset($_COOKIE['jsale_utm'])) ? $_COOKIE['jsale_utm'] : $cookies['jsale_utm']; ?>"><? endif; ?>
		
		<? if (isset($_COOKIE['jsale_source']) || isset($cookies['jsale_source'])): ?><input type="hidden" name="source" value="<?= (isset($_COOKIE['jsale_source'])) ? $_COOKIE['jsale_source'] : $cookies['jsale_source']; ?>"><? endif; ?>
		
		<? if (isset($cookies_string)): ?><input type="hidden" name="cookies" value='<?= $cookies_string ?>'><? endif; ?>
		
		<? if (isset($disabled) && $disabled === true): ?><input type="hidden" name="disabled" value="disabled"><? endif; ?>
		<? if (isset($message) && $message !== false && !is_array($message)): ?><input type="hidden" name="message" value="<?= $message ?>"><? endif; ?>
		<input type="hidden" name="product_qty_type" value="<?= $product['qty_type']; ?>">
		<input type="hidden" name="pvz-address" value="" />
	</p>
	
	<center>Или просто позвоните по бесплатному телефону <span class="bold-phone">8 800 200 1599</span></center>
	
	<p style="margin-top:40px;" align="center">
	<a> Оформляя заказ, вы соглашаетесь с правилами <a href="privacy.html" target="_blank">обработки персональных данных</a>. 
	</br>Мы не передаем ваши данные третьим лицам и не шлем спам
	</p>
	
</form>
<script type="text/javascript">
function show_payment_info_<?= $id_form ?>(select)
{
	var payments = eval(<?= json_encode($config['payments']) ?>);
	var orderPayment = (select == '<?= $payment_type;?>') ? '<?= $payment_type;?>' : select.value;
	
	if (orderPayment == select.value)
		jQuery('#jsale_form_<?= $id_form ?>').find('.jSaleCode').trigger('keyup');
	
	var free_delivery = payments[orderPayment]['free_delivery'];
	var is_free_delivery = jQuery('#jsale_form_<?= $id_form ?>').find('[name="free_delivery"]').val();
	
	if (free_delivery === true && is_free_delivery == 'false' || free_delivery === false && is_free_delivery == 'true')
		jQuery('#jsale_form_<?= $id_form ?>').find('.jSaleCode').trigger('keyup');
	
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
		jQuery('#jsale_form_<?= $id_form ?>').find('.jSaleCode').trigger('keyup');

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
		
	jQuery('#jsale_form_<?= $id_form ?>').find('.jSaleCode').trigger('keyup');
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
<? if (isset($config['deliveries']['99']['enabled']) && $config['deliveries']['99']['enabled'] === true): ?>
function get_index_<?= $id_form ?>()
{
	var zip = jQuery('#jsale_form_<?= $id_form ?>').find('[name="order_zip"]').val();
	var address = jQuery('#jsale_form_<?= $id_form ?>').find('[name="order_address"]');
	var delivery_cost = jQuery('#jsale_form_<?= $id_form ?> #delivery_cost');
	var delivery = jQuery('#jsale_form_<?= $id_form ?> [name="order_delivery"]').val();
	var delivery_cost_hidden = jQuery('#jsale_form_<?= $id_form ?>').find('[name="delivery_cost"]');
	var subtotal_cost = jQuery('#jsale_form_<?= $id_form ?>').find('[name="subtotal"]');
	var subtotal = jQuery('#jsale_form_<?= $id_form ?> #subtotal');
	
	if (delivery == '99')
	{
		if (parseInt(zip) == zip && zip.length == 6)
		{
			jQuery.getJSON("http://api.print-post.com/api/index/v2/?callback=?",
				{ 'index': zip },
				function(data) {
					if (data.error==undefined)
					{
						if (address.text() == '')
							address.text(data.string);
						arr = data.string.split(',');
						get_delivery_cost_<?= $id_form ?>(arr[0]);
					}
					else
						delivery_cost.html(data.error);
				});
		} else
		{
			delivery_cost.html('Введите корректный индекс для расчёта стоимости доставки');
			delivery_cost_hidden.val(0);
			subtotal.html(subtotal_cost.val());
		}
	};
}
function get_delivery_cost_<?= $id_form ?>(to)
{
	var delivery_cost = jQuery('#jsale_form_<?= $id_form ?> #delivery_cost');
	var subtotal_cost = jQuery('#jsale_form_<?= $id_form ?>').find('[name="subtotal"]');
	var subtotal = jQuery('#jsale_form_<?= $id_form ?> #subtotal');
	var delivery_cost_hidden = jQuery('#jsale_form_<?= $id_form ?>').find('[name="delivery_cost"]');

	jQuery.post('<?= $config['sitelink'] . $config['dir'] ?>modules/M_EMS.php',
		{ 'type' : 'get_locations', 'to' : to },
		function(data) {
				if (data != null && data != '')
				{
					jQuery.post('<?= $config['sitelink'] . $config['dir'] ?>modules/M_EMS.php',
						{ 'type' : 'calculate', 'to' : data },
						function(data) {
								console.log(parseFloat(subtotal_cost.val()));
								console.log(parseFloat(data));
								var real_delivery_cost = (parseFloat(subtotal_cost.val()) + parseFloat(data)) * 0.01 + parseFloat(data);
								console.log(real_delivery_cost);
								delivery_cost.html('Стоимость доставки: ' + real_delivery_cost.toFixed(2) + ' <?= $config['currency'] ?>');
								delivery_cost_hidden.val(real_delivery_cost);
								var cost = parseFloat(subtotal_cost.val()) + parseFloat(real_delivery_cost);
								subtotal.html(cost.toFixed(2));
							});
				}
				else
				{
					delivery_cost.html('Доставка EMS в выбранный регион не работает');
					delivery_cost_hidden.val(0);
					subtotal.html(subtotal_cost.val());
				}
			});
}
get_index_<?= $id_form ?>();
<? endif; ?>
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