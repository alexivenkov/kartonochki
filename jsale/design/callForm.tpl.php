<form action="<?= $config['sitelink'] . $config['dir'] ?>call/relay.php" method="post" class="jSaleForm jSaleCall">
	<? if (isset($message)): ?>
	<h6 class="jSaleMessage">
		<?= $message; ?>
	</h6>
	<? else: ?>
	<h5>
		Заказ звонка
	</h5>
	<? endif; ?>
	<? if ($config['call']['name']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['call']['name']['label'];?><? if ($config['call']['name']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_name" value="<?= (isset($name)) ? $name : '';?>">
	</p>
	<? endif; ?>
	<? if ($config['call']['phone']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['call']['phone']['label'];?><? if ($config['call']['phone']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_phone" value="<?= (isset($phone)) ? $phone : '';?>">
	</p>
	<? endif; ?>
	<? if ($config['call']['email']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['call']['email']['label'];?><? if ($config['call']['email']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_email" value="<?= (isset($email)) ? $email : '';?>">
	</p>
	<? endif; ?>
	<? if ($config['call']['comment']['enabled'] == true): ?>
	<p>
		<label><?= $config['call']['comment']['label'];?><? if ($config['call']['comment']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<textarea name="order_comment"><?= (isset($comment)) ? $comment : '';?></textarea>
	</p>
	<? endif; ?>
	<? if ($config['call']['topic']['enabled'] == true): ?>
	<p>
		<label><?= $config['call']['topic']['label'];?><? if ($config['call']['topic']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<select name="order_topic">
			<? foreach ($config['call']['topics'] as $call_topic): ?>
			<option value="<?= $call_topic ?>" <?= (isset($topic) && $topic == $call_topic) ? 'selected="selected"' : '' ?>><?= $call_topic ?></option>
			<? endforeach; ?>
		</select>
	</p>
	<? endif; ?>
	<p class="submit">
		<input type="submit" name="order_submit" value="Перезвоните мне" class="jSaleSubmit jSaleButton">

		<input type="hidden" name="order_spam" value="<?= $antispam ?>">
		<input type="hidden" name="order_nospam" value="">
		<input type="hidden" name="call" value="true">
		<input type="hidden" name="form_type" value="<?= $form_type ?>">
		<input type="hidden" name="referer" value="<?= $referer ?>">
	</p>
</form>