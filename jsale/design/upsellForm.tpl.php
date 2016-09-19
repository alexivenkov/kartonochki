<form action="<?= $config['sitelink'] . $config['dir'] ?>relay.php" method="post" class="jSaleForm">
	<h6 class="jSaleMessage"><?= $upsell_product['notice_title'] ?></h6>
	<p><?= $upsell_product['notice_text'] ?></p>

	<p class="submit">
		<input type="hidden" name="order_spam" value="<?= $antispam ?>">
		<input type="hidden" name="order_nospam" value="">
		<input type="hidden" name="hash" value="<?= $product['hash']; ?>">
		<input type="hidden" name="template" value="<? preg_match('/orderForm\_(.*).tpl.php/', __FILE__, $file); if (isset($file[1])) echo $file[1]; ?>">
		<input type="hidden" name="form_type" value="<?= $product['form_type'] ?>">
		<input type="hidden" name="id_form" value="<?= $id_form ?>">
		
		<input type="hidden" name="order_name" value="<?= $name; ?>">
		<input type="hidden" name="order_lastname" value="<?= $lastname; ?>">
		<input type="hidden" name="order_fathername" value="<?= $fathername; ?>">
		<input type="hidden" name="order_email" value="<?= $email; ?>">
		<input type="hidden" name="order_phone" value="<?= $phone; ?>">
		<input type="hidden" name="order_region" value="<?= $region; ?>">
		<input type="hidden" name="order_zip" value="<?= $zip; ?>">
		<input type="hidden" name="order_city" value="<?= $city; ?>">
		<input type="hidden" name="order_address" value="<?= $address; ?>">
		<input type="hidden" name="order_comment" value="<?= $comment; ?>">
		<input type="hidden" name="order_payment" value="<?= $payment_type; ?>">
		<input type="hidden" name="order_delivery" value="<?= $delivery_type; ?>">
		
		<input type="hidden" name="order_upsell" value="<?= $product['upsell']; ?>">
		<input type="hidden" name="order_bonus" value="<?= $product['bonus']; ?>">
		
		<input type="hidden" name="product_id" value="<?= (isset($product['id'])) ? $product['id'] : '' ?>">
		<input type="hidden" name="product_code" value="<?= $product['code']; ?>">
		<input type="hidden" name="product_qty" value="<?= $product['qty']; ?>">
		<input type="hidden" name="product_title" value="<?= $product['title']; ?>">
		<input type="hidden" name="product_price" value="<?= $product['price']; ?>">
		<input type="hidden" name="product_discount" value="<?= $product['discount']; ?>">
		<input type="hidden" name="product_unit" value="<?= $product['unit']; ?>">
		<input type="hidden" name="product_param1" value="<?= (isset($product['param1'])) ? $product['param1'] : ''; ?>">
		<input type="hidden" name="product_param2" value="<?= (isset($product['param2'])) ? $product['param2'] : ''; ?>">
		<input type="hidden" name="product_param3" value="<?= (isset($product['param3'])) ? $product['param3'] : ''; ?>">
		<input type="hidden" name="product_qty_type" value="no">
	
		<input type="submit" name="upsell_decline" value="Отказаться" class="jSaleSubmit">
		<input type="submit" name="upsell_accept" value="Добавить в заказ" class="jSaleSubmit jSaleButton jSaleLarge">
		<input type="hidden" name="upsell_submit" value="<?= $product['upsell'] ?>">
	</p>
</form>