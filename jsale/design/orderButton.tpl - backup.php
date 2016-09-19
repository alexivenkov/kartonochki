<?= (isset($product['description'])) ? $product['description'] : '' ?>

<? if ($product['form_type'] == 'form'): ?>
	<div class="jSaleWrapper jSaleWrapper<?= (isset($id_form)) ? $id_form : '' ?>">
	<?= $orderForm; ?>
	</div>
<? elseif ($product['form_type'] == 'deadline'): ?>

	<? if ($config['deadlines']['unique_link'] === true): ?>	
		<?= (isset($_SESSION['jsale_countdown']) && isset($_SESSION['param']) && $_SESSION['param'] == $config['deadline'][$product['deadline']]['param']) ? str_replace('countdown-item', 'countdown-' . $id_form, $_SESSION['jsale_countdown']) : '' ?>

		<? if (isset($_SESSION['jsale_deadline']) && $_SESSION['param_key'] == $product['deadline'] && time() > $_SESSION['jsale_deadline'] && $config['deadline'][$_SESSION['param_key']]['type'] == 'order'): ?>
			<p>Предложение более не актуально. Приходите в следующий раз.</p>
		<? else: ?>
			<span class="jSaleOrder jSaleButton"><?= $button_text ?></span>
			<div class="jSaleWindow" style="display: none;">
				<?= $orderForm; ?>
			</div>
		<? endif; ?>
	<? else: ?>
		<?= (isset($_SESSION['jsale_countdown']) && $product['deadline'] == 'mass') ? str_replace('countdown-item', 'countdown-' . $id_form, $_SESSION['jsale_countdown']) : '' ?>

		<? if (isset($_SESSION['jsale_deadline']) && time() > $_SESSION['jsale_deadline'] && $config['deadline']['mass']['type'] == 'order'): ?>
			<p>Предложение более не актуально. Приходите в следующий раз.</p>
		<? else: ?>
			<span class="jSaleOrder jSaleButton"><?= $button_text ?></span>
			<div class="jSaleWindow" style="display: none;">
				<?= $orderForm; ?>
			</div>
		<? endif; ?>
	<? endif; ?>

<? elseif ($product['form_type'] == 'button_img'): ?>
	<img class="jSaleOrder" src="<?= $product['button_img'] ?>" title="<?= $button_text ?>">
	<div class="jSaleWindow" style="display: none;">
		<?= $orderForm; ?>
	</div>
<? elseif ($product['form_type'] == 'qty_button'): ?>
	<input type="text" class="jSaleQty2Form" value="1">
	<span class="jSaleOrder jSaleButton"><?= $button_text ?></span>
	<div class="jSaleWindow" style="display: none;">
		<?= $orderForm; ?>
	</div>
<? elseif ($product['form_type'] == 'feedback'): ?>
	<span class="jSaleOrder jSaleButton"><?= $button_text ?></span>
	<div class="jSaleWindow" style="display: none;">
		<?= $orderForm; ?>
	</div>
<? elseif ($product['form_type'] == 'call'): ?>
	<span class="jSaleOrder jSaleButton"><?= $button_text ?></span>
	<div class="jSaleWindow" style="display: none;">
		<?= $orderForm; ?>
	</div>
<? else: ?>
	<span class="jSaleOrder jSaleButton"><?= $button_text ?></span>
	<div class="jSaleWindow" style="display: none;">
		<?= $orderForm; ?>
	</div>
<? endif; ?>