<?/*
Шаблон шапки админки
===============================
*/?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="<?= $config['encoding'] ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами - <?= $config['sitename'] ?></title>
    <link rel="stylesheet" href="<?= $config['sitelink'] . $config['dir'] ?>bootstrap/css/bootstrap.min.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?= $config['sitelink'] . $config['dir'] ?>bootstrap/css/bootstrap-responsive.min.css" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="<?= $config['sitelink'] . $config['dir'] ?>css/admin.css" type="text/css" media="screen, projection" />
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js" type="text/javascript"></script>
    <!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<script type="text/javascript" src="<?= $config['sitelink'] . $config['dir'] ?>bootstrap/js/bootstrap.min.js"></script>
	
	<link rel="stylesheet" href="<?= $config['sitelink'] . $config['dir'] ?>js/datepicker/datepicker.css" type="text/css" media="screen, projection" />
	<script type="text/javascript" src="<?= $config['sitelink'] . $config['dir'] ?>js/datepicker/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="<?= $config['sitelink'] . $config['dir'] ?>js/datepicker/locales/bootstrap-datepicker.ru.js"></script>
	<script type="text/javascript">
		$('.dropdown-toggle').dropdown();
	</script>
</head>
<body>

<? if (!isset($no_header)): ?>
<div class="navbar navbar-static-top navbar-inverse">
	<div class="navbar-inner">
		<div class="container-fluid">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" href="<?= $config['sitelink'] ?>"><?= $config['sitename'] ?></a>
			
			<div class="nav-collapse collapse">	
				<ul class="nav">
					<?= GetMenuItems($file, $menu_items); ?>
				</ul>
				<ul class="nav pull-right">
					<li><a href="logout.php" onclick="return confirm('Вы действительно хотите выйти? Заново войти вы сможете только после перезагрузки браузера')">Выйти</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<? endif; ?>