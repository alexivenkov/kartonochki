<? if (!$admin): ?>
<?= $config['email']['answerMessageTop'] ?>
<? endif; ?>
<p><strong>Заказ №:</strong> <?= $id_order ?></p>

<h3>Данные покупателя:</h3>
<? if ($lastName || $name || $fatherName): ?><p><strong>ФИО:</strong> <?= $lastName ?> <?= $name ?> <?= $fatherName ?></p>
<? endif; ?>
<? if ($email): ?><p><strong>E-mail:</strong> <?= $email ?></p>
<? endif; ?>
<? if ($phone): ?>
<p><strong>Телефон:</strong> <?= $phone ?></p>
<? endif; ?>
<? if ($zip || $region || $city || $address): ?>
<p><strong>Адрес:</strong><br>
<? if ($zip): ?><?= $zip ?><? endif; ?>
<? if ($region): ?> <?= $region ?><? endif; ?>
<? if ($city): ?> <?= $city ?><? endif; ?>
<? if ($address): ?> <?= $address ?><? endif; ?></p>
<? endif; ?>
<? if ($comment): ?>
<p><strong>Комментарий:</strong><br><?= $comment ?></p>
<? endif; ?>
<h3>Данные заказа:</h3>

<? foreach ($order_items as $order_item): ?>
<p>Наименование: <?= $order_item['title'] ?>
<? if ($order_item['param1'] || $order_item['param2'] || $order_item['param3']): ?> (
	<? if ($order_item['param1']): ?> <?= $order_item['param1'] ?> <? endif; ?>
	<? if ($order_item['param2']): ?> <?= $order_item['param2'] ?> <? endif; ?>
	<? if ($order_item['param3']): ?> <?= $order_item['param3'] ?><? endif; ?>
)<? endif; ?>
<br>
Код товара: <?= $order_item['code'] ?><br>
Цена: <? if ($order_item['price'] != 0): ?><?= number_format($order_item['price'], 2, '.', '') ?> <?= $config['currency'] ?><? else: ?>Бонус!<? endif; ?><br>
Количество: <?= $order_item['qty'] ?> <?= $order_item['unit'] ?><br>
<? if (isset($order_item['discount']) && $order_item['discount'] != 0): ?>Ваша скидка: <?= number_format($order_item['discount'], 2, '.', '') ?> <? if ($config['discounts']['fixed'] === true): ?><?= $config['currency'] ?><? else: ?>%<? endif; ?><br><? endif;?>
<? if ($order_item['price'] != 0): ?>Всего:</strong> <?= $order_item['subtotal'] ?> <?= $config['currency'] ?><? endif; ?></p>
<? endforeach;?>

<p><strong>Сумма заказа:</strong> <?= number_format($order_sum - $delivery['cost'], 2, '.', '') ?> <?= $config['currency'] ?></p>
<p><strong>Форма оплаты:</strong> <?= $payment['title'] ?> – <?= $payment['info'] ?><? if ($yandex_payment_type): ?> - <?= $config['payments']['yandex_eshop']['types'][$yandex_payment_type] ?><? endif; ?></p>
<? if (isset($payment['link'])): ?>
<p><strong>Оплатить заказ:</strong> <a href="<?= $payment['link'] ?>" title="Оплатить онлайн" target="_blank">к оплате</a></p>
<? endif; ?>
<? if (!empty($payment['details'])): ?>
<p><strong>Реквизиты для оплаты:</strong> <?= $payment['details'] ?></p>
<? endif; ?>
<? if ($config['email']['changePayment'] === true && isset($hash2)): ?><p>Изменить метод оплаты вы можете по <a href="<?= $config['sitelink'].$config['dir'] ?>pay/change.php?id_order=<?=$id_order?>&hash=<?=$hash2?>" target="_blank">этой ссылке</a>.</p><? endif; ?>
<? if (isset($delivery)): ?>
<p><strong>Cпособ доставки:</strong> <?= $delivery['title'] ?> – <?= $delivery['info'] ?></p>
	<? if (!empty($delivery['cost']) && $delivery['cost'] != 0): ?>
	<p><strong>Стоимость доставки:</strong> <?= $delivery['cost'] ?> <?= $config['currency'] ?></p>
	<h3><strong>Итого к оплате: <?= $order_sum ?> <?= $config['currency'] ?></strong></h3>
	<? endif; ?>
<? endif; ?>

<? if ($admin && isset($partner) && is_array($partner)): ?>
<p><strong>Продажа сделана через партнёра:</strong> <?= $partner['email'] ?><br>
<strong>Комиссия партнёра:</strong> <?= $partner['commission'] ?> <?= $config['currency'] ?><br>
<strong>Итого с учётом комиссии:</strong> <?= number_format($order_sum - $partner['commission'], 2, '.', '') ?> <?= $config['currency'] ?></p>
<? endif; ?>

<? if ($config['email']['confirm'] == true && isset($hash)): ?>
<p style="background: #ff0; padding: 10px 20px; font-size: 110%;">Подтвердите, пожалуйста, ваш заказ по этой <a href="<?= $config['sitelink'] . $config['dir'] ?>modules/C_Confirm.php?id_order=<?=$id_order?>&order_sum=<?=$order_sum?>&hash=<?=$hash?>&confirm">ссылке</a>.</p>
<? endif; ?>
<? if ($config['email']['refuse'] == true && isset($hash)): ?>
<p style="font-size: 80%;color: #666;">Если хотите отказаться от заказа, перейдите, пожалуйста, по этой <a href="<?= $config['sitelink'] . $config['dir'] ?>modules/C_Confirm.php?id_order=<?=$id_order?>&order_sum=<?=$order_sum?>&hash=<?=$hash?>&refuse">ссылке</a>.</p>
<? endif; ?>

<? if (!$admin): ?>
<?= $config['email']['answerMessageSignature'] ?>
<? else: ?>
<p><strong>Данные отправки:</strong><br>
Дата: <?= date("d.m.Y") ?><br>
<? if (isset($_SESSION['referer'])): ?>Источник перехода: <?= $_SESSION['referer'] ?><br><? endif; ?>
<? if (isset($_SESSION['city'])): ?>Город: <?= ($_SESSION['city'] != '') ? $_SESSION['city'] : 'Не определён' ?><br><? endif; ?>
IP: <?= $_SERVER['REMOTE_ADDR'] ?></p>
<hr><p><a href="<?= $config['sitelink'] . $config['dir'] ?>admin/orders.php?order=<?= $id_order ?>">Редактировать заказ</a></p>
<? endif; ?>