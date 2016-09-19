<h1>Спасибо за оплату заказа №<?= $order['id_custom'] ?>!</h1>

<p>Ваши товары:</p>

<ul>
<? foreach ($order_items as $order_item): ?>
<li><?= $order_item['product'] ?> - <a href="<?= $order_item['download_link'] ?>">Скачать<a/></li>
<? endforeach; ?>
</ul>