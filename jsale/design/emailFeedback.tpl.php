<? if ($fromName): ?><p><b>Имя:</b> <?= $fromName ?></p><? endif;?>
<? if ($fromEmail): ?><p><b>E-mail:</b> <?= $fromEmail ?></p><? endif;?>
<? if ($subject): ?><p><b>Телефон:</b> <?= $subject ?></p><? endif;?>
<? if ($content): ?><p><b>Комментарий:</b><br><?= $content ?></p><? endif;?>
<p><b>Данные отправки:</b><br>
<?= date("d.m.Y") ?><br>
<?= $_SERVER['REMOTE_ADDR'] ?><br>
<?= $referer ?></p>