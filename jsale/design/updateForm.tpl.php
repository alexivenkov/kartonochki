<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="<?= $config['encoding'] ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обновление товара</title>
    <link rel="stylesheet" href="<?= $config['sitelink'] ?>jsale/bootstrap/css/bootstrap.min.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?= $config['sitelink'] ?>jsale/bootstrap/css/bootstrap-responsive.min.css" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="<?= $config['sitelink'] ?>jsale/admin/style.css" type="text/css" media="screen, projection" />
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js" type="text/javascript"></script>
    <!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<script type="text/javascript" src="<?= $config['sitelink']?>jsale/bootstrap/js/bootstrap.min.js"></script>
	
    <script type="text/javascript">
    function form_validator(form)
    {
        if (form.update_email.value=="") { alert("Пожалуйста, укажите Ваш email адрес."); form.update_email.focus(); return false; }
        if (confirm("Всё верно?")) { form.update_nospam.value="<?= $antispam ?>"; } else { return false; }

        return true;
    }
	$('.alert').alert();
    </script>
</head>

<body>

<div class="hero-unit">

    <h1>Обновление товара</h1>

	<br/>
    <p>
        Здесь вы можете в любой момент скачать последнюю версию приобретённого вами товара. Введите адрес, на который была оформлена покупка, и в течении нескольких минут на него будет отправлено письмо со ссылкой для скачивания.
    </p>

    <form action="" method="post" onsubmit="return form_validator(this);	" class="form-inline">
        <? if (isset($form['message'])): ?>
        <div class="alert alert-error">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<?= $form['message'] ?>
		</div>
        <? endif ?>
        <p>
            <label>Введите ваш email:</label>
            <input type="text" name="update_email" value="<?= (isset($update_email)) ? $update_email : '' ?>">
            <input type="hidden" name="update_product" value="<?= (isset($update_product)) ? $update_product : '' ?>">
            <input type="hidden" name="update_nospam" value="">
            <button name="update_submit" class="btn btn-primary">Получить обновление</button>
        </p>
    </form>

    <p>
        <a href="<?= $config['sitelink'] ?>" title="Перейти на главную">Вернуться на главную страницу магазина</a>
    </p>

</div><!-- #wrapper -->

</body>
</html>