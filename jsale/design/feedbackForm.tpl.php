<form action="<?= $config['sitelink'] . $config['dir'] ?>feedback/relay.php" method="post" class="jSaleForm">
	<? if (isset($message)): ?>
	<h6 class="jSaleMessage">
		<?= $message; ?>
	</h6>
	<? else: ?>
	<h6>Не уверены, <br>подойдет ли ваш смартфон?</h6>
	<p>Чтобы не получить кота в мешке, убедитесь, что ваш смартфон совместим. <br>Оставьте заявку и наш менеджер проконсультирует вас</p>
	<? endif; ?>
	<? if ($config['feedback']['name']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['feedback']['name']['label'];?><? if ($config['feedback']['name']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_name" value="<?= (isset($name)) ? $name : '';?>">
	</p>
	<? endif; ?>
	<? if ($config['feedback']['email']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['feedback']['email']['label'];?><? if ($config['feedback']['email']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_email" value="<?= (isset($email)) ? $email : '';?>">
	</p>
	<? endif; ?>
	<? if ($config['feedback']['phone']['enabled'] == true): ?>
	<p class="float">
		<label><?= $config['feedback']['phone']['label'];?><? if ($config['feedback']['phone']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<input type="text" name="order_phone" value="<?= (isset($phone)) ? $phone : '';?>">
	</p>
	<? endif; ?>
	<? if ($config['feedback']['comment']['enabled'] == true): ?>
	<p>
		<label><?= $config['feedback']['comment']['label'];?><? if ($config['feedback']['comment']['required'] == true): ?><span class="attention" title="Поле, обязательное к заполнению">*</span><? endif;?></label><br>
		<textarea name="order_comment"><?= (isset($comment)) ? $comment : '';?></textarea>
	</p>
	<? endif; ?>
	<p class="submit">
		<input type="submit" name="order_submit" value="Проверить совместимость" class="jSaleSubmit jSaleButton">

		<input type="hidden" name="order_spam" value="<?= $antispam ?>">
		<input type="hidden" name="order_nospam" value="">
		<input type="hidden" name="feedback" value="true">
		<input type="hidden" name="form_type" value="<?= $form_type ?>">
		<input type="hidden" name="template" value="<? preg_match('/feedbackForm\_(.*).tpl.php/', __FILE__, $file); if (isset($file[1])) echo $file[1]; ?>">
		<input type="hidden" name="referer" value="<?= $referer ?>">
	</p>
</form>