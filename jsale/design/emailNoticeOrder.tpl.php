<p>Здравствуйте, <?= $name ?>.</p>
<p>Вы оформили заказ на сайте <a href="<?= $config['sitelink'] ?>"><?= $config['sitename'] ?></a>, но по какой-то причине не оплатили его.</p>
<p>Возможно у вас возникли трудности с оплатой? Могу я вам чем-то помочь?</p>
<p>На всякий случай дублирую данные заказа.</p>
<? if ($config['notice']['name']): ?><p><?= $config['notice']['name'] ?></p><? endif; ?>

<p>--------------------------------------------------------------</p>

<h3>Заказ №<?= $id_custom ?>. Cтатус заказа: <?= $status ?></h3>

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
<? endforeach;?>

<p><strong>Сумма заказа:</strong> <?= number_format($order_sum - $delivery['cost'], 2, '.', '') ?> <?= $config['currency'] ?></p>
<p><strong>Форма оплаты:</strong> <?= $payment['title'] ?> – <?= $payment['info'] ?></p>

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

<? if ($config['email']['confirm'] == true && isset($hash)): ?>
<p style="background: #ff0; padding: 10px 20px; font-size: 110%;">Подтвердите, пожалуйста, ваш заказ по этой <a href="<?= $config['sitelink'] . $config['dir'] ?>modules/C_Confirm.php?id_order=<?=$id_custom?>&order_sum=<?=$order_sum?>&hash=<?=$hash?>&confirm">ссылке</a>.</p>
<? endif; ?>
<? if ($config['email']['refuse'] == true && isset($hash)): ?>
<p style="font-size: 80%;color: #666;">Если хотите отказаться от заказа, перейдите, пожалуйста, по этой <a href="<?= $config['sitelink'] . $config['dir'] ?>modules/C_Confirm.php?id_order=<?=$id_custom?>&order_sum=<?=$order_sum?>&hash=<?=$hash?>&refuse">ссылке</a>.</p>
<? endif; ?>

<p><strong>Данные отправки:</strong><br>
<?= date("d.m.Y") ?></p>