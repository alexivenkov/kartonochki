<?/* Шаблон вывода товара. */?>
<div class="products clearfix">

	<? foreach ($products as $key => $product): ?>
	<div class="jSale">
		<?= $product['title']; ?>
		<?= $orderButtons[$key] ?>
	</div>
	<? endforeach; ?>
</div>