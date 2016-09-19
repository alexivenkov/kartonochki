<p><b>Статус заказа #<?= $id_custom ?> изменён. Новый статус: <?= $status ?></b></p>

<h3>Данные заказчика:</h3>
<? if ($lastName || $name || $fatherName): ?>
<p><strong>ФИО:</strong> <?= $lastName ?> <?= $name ?> <?= $fatherName ?></p>
<? endif; ?>
<? if ($email): ?>
<p><strong>E-mail:</strong> <?= $email ?></p>
<? endif; ?>
<? if ($phone): ?>
<p><strong>Телефон:</strong> <?= $phone ?></p>
<? endif; ?>
<? if ($zip || $region || $city || $address): ?>
<p><strong>Адрес:</strong><br>
<? if ($zip): ?><?= $zip ?><? endif; ?>
<? if ($region): ?>, <?= $region ?><? endif; ?>
<? if ($city): ?>, <?= $city ?><? endif; ?>
, <?= $address ?></p>
<? endif; ?>
<? if ($comment): ?>
<p><strong>Комментарий:</strong><br><?= $comment ?></p>
<? endif; ?>
<h3>Данные заказа:</h3>

<? foreach ($order_items as $order_item): ?>
<p>Наименование:
<? if (isset($order_item['url'])): ?>
	<a href="<?= $order_item['url'] ?>"><?= $order_item['product'] ?></a>
<? else: ?>
	<?= $order_item['product'] ?>
<? endif; ?>
<? if ($order_item['size'] || $order_item['size'] || $order_item['param']): ?> (
	<? if ($order_item['size']): ?> <?= $order_item['size'] ?> <? endif; ?>
	<? if ($order_item['color']): ?> <?= $order_item['color'] ?> <? endif; ?>
	<? if ($order_item['param']): ?> <?= $order_item['param'] ?> <? endif; ?>
)<? endif; ?>
<br>
Код товара: <?= $order_item['id_product'] ?><br>
Цена: <?= $order_item['price'] ?> <?= $config['currency'] ?><br>
Количество: <?= $order_item['quantity'] ?> <?= $order_item['unit'] ?><br>
<? if (isset($order_item['discount']) && $order_item['discount'] != 0): ?>Ваша скидка: <?= $order_item['discount'] ?> <?if ($config['discounts']['fixed'] === true):?><?= $config['currency'] ?><? else: ?>%<? endif; ?><br><? endif;?>
Всего:</strong> <?= number_format($order_item['quantity'] * $order_item['price'] * (1 - $order_item['discount'] / 100), 2, '.', '') ?> <?= $config['currency'] ?>
<? if (isset($order_item['download_link'])): ?><br>Ссылка на скачивание: <a href="<?= $order_item['download_link'] ?>">скачать</a> (Этот файл можно скачать не более <?= $config['download']['uses'] ?> раз в течении <?= $config['download']['hours'] ?> часов)<br>Получить обновление в любой момент можете <a href="<?= $config['sitelink'] . $config['dir'] ?>update/<?= $order_item['id_product'] ?>/">здесь</a><? endif; ?>
</p>
<? endforeach;?>

<p><strong>Сумма заказа:</strong> <?= number_format($order_sum - $delivery['cost'], 2, '.', '') ?> <?= $config['currency'] ?></p>
<p><strong>Форма оплаты:</strong> <?= $payment['title'] ?> – <?= $payment['info'] ?><? if ($yandex_payment_type): ?> - <?= $config['payments']['yandex_eshop']['types'][$yandex_payment_type] ?><? endif; ?></p>

<? if (isset($payment['link'])): ?>
<p><strong>Оплатить заказ:</strong> <a href="<?= $payment['link'] ?>" title="Оплатить онлайн" target="_blank">к оплате</a></p>
<? elseif (!empty($payment['details'])): ?>
<p><strong>Реквизиты для оплаты:</strong> <?= $payment['details'] ?></p>
<? endif; ?>

<? if (isset($delivery)): ?>
<p><strong>Cпособ доставки:</strong> <?= $delivery['title'] ?> – <?= $delivery['info'] ?></p>
	<? if (!empty($delivery['cost']) && $delivery['cost'] != 0): ?>
	<p><strong>Стоимость доставки:</strong> <?= $delivery['cost'] ?> <?= $config['currency'] ?></p>
	<p><strong>Итого:</strong> <?= $order_sum ?> <?= $config['currency'] ?></p>
	<? endif; ?>
<? endif; ?>

<? if ($config['admin']['upload']['enabled'] === true): ?>
	<? if (is_file(dirname(__FILE__) . '/../' .$config['download']['dir'] . '/' . $config['admin']['upload']['dir'] . '/' . $id_custom . '.' . $config['admin']['upload']['type']) && $hash): ?>
	<p><strong><?= $config['admin']['upload']['title'] ?>: <a href="<?= $config['sitelink'] . $config['dir'] ?>download.php?download_file=<?= $id_custom ?>.<?= $config['admin']['upload']['type'] ?>&hash=<?= $hash ?>">скачать</a></strong></p>
	<? endif;?>
<? endif;?>

<? if ($admin && isset($partner) && is_array($partner)): ?>
<p><strong>Продажа сделана через партнёра:</strong> <?= $partner['email'] ?><br>
<strong>Комиссия партнёра:</strong> <?= $partner['commission'] ?> <?= $config['currency'] ?><br>
<strong>Итого с учётом комиссии:</strong> <?= number_format($order_sum - $partner['commission'], 2, '.', '') ?> <?= $config['currency'] ?></p>
<? endif; ?>

<? if (!$admin): ?>
<?= $config['email']['answerMessageSignature'] ?>
<? else: ?>
<p><strong>Данные отправки:</strong><br>
<?= date("d.m.Y") ?><br>
<?= $_SERVER['REMOTE_ADDR'] ?></p>
<hr><p><a href="<?= $config['sitelink'] . $config['dir'] ?>admin/orders.php?order=<?= $id_custom ?>">Редактировать заказ</a></p>
<? endif; ?>