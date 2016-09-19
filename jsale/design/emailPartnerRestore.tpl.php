<p>Здравствуйте!</p>
<p>Вы заказали восстановление данных по регистрации в партнёрской программе на сайте <a href="<?= $config['sitelink'] ?>"><?= $config['sitename'] ?></a>.</p>

<p>-----</p>

<p><strong>Ваш доступ</strong></p>
<p>Ссылка для входа: <a href="<?= $config['sitelink'] . $config['dir'] ?>partner/"><?= $config['sitelink'] . $config['dir'] ?>partner/</a></p>
<p>Логин: <?= $email ?></p>
<p>Пароль: <?= $password ?></p>

<p>-----</p>

<p>Ваша ссылка для продвижения: <a href="<?= $config['sitelink'] ?>?ref=<?= $code ?>"><?= $config['sitelink'] ?>?ref=<?= $code ?></a></p>

<p>Спасибо за участие.</p>

<p>-----</p>

<p><strong>Данные отправки:</strong><br>
<?= date("d.m.Y") ?></p>