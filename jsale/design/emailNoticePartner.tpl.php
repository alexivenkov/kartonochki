<p>Здравствуйте, <?= $name ?>.</p>
<p>Вы оформляли ранее заказ на сайте <a href="<?= $config['sitelink'] ?>"><?= $config['sitename'] ?></a>.</p>
<p>Надеюсь вам понравился товар и вы оставили свой отзыв. Теперь мы хотим предложить вам заработать за нашем магазине.</p>
<p>Для этого нужно зарегистрироваться в партнёрской программе и предложить вашим знакомым скидку на отличный товар!</p>

<p>Ссылка для <a href="<?= $config['sitelink'] . $config['dir'] ?>partner/new.php">регистрации</a>.</p>

<? if ($config['notice']['name']): ?><p><?= $config['notice']['name'] ?></p><? endif; ?>

<p>--------------------------------------------------------------</p>

<p><strong>Данные отправки:</strong><br>
<?= date("d.m.Y") ?></p>