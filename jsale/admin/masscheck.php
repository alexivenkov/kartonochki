<?php
# Массовое отслеживание посылок

# Подключение настроек
include_once dirname(__FILE__) . '/../config.inc.php';

# Простейшая авторизация
include_once dirname(__FILE__) . '/../modules/M_Admin.inc.php';
$mAdmin = M_Admin::Instance();

session_start();
if (!$mAdmin->CheckLogin())
	die;

# Кодировка
header('Content-type: text/html; charset=' . $config['encoding']);

# Отслеживание посылок с помощью прямого запроса к сервисам Почты России
if (isset($_POST['check']) && isset($_POST['tracks']))
{
	include_once dirname(__FILE__) . '/../modules/M_TrackRussianPost.inc.php';
	$api = new RussianPostAPI();

	$tracks = explode("\n", $_POST['tracks']);
	
	foreach ($tracks as $track)
	{
		try {
			$id = trim($track);

			$get_track = $api->getOperationHistory($id);

			foreach ($get_track as $key => $tr)
			{
				$date = $tr->operationDate;
				$date = substr($date, 0, 10);
				list($y, $m, $d) = explode('-', $date);
				
				$track_data[$id][$key]['date'] = $d . '.' . $m . '.' . $y;
				$track_data[$id][$key]['type'] = $tr->operationType;
				$track_data[$id][$key]['attr'] = $tr->operationAttribute;
				$track_data[$id][$key]['code'] = $tr->operationPlacePostalCode;
				$track_data[$id][$key]['place'] = $tr->operationPlaceName;
				$track_status = $tr->operationAttribute;
			}

		} catch(RussianPostException $e) {
		  $track_status = 'Ошибка';
		}
	}
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="<?= $config['encoding'] ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами - <?= $config['sitename'] ?></title>
    <link rel="stylesheet" href="<?= $config['sitelink'] ?>jsale/bootstrap/css/bootstrap.min.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?= $config['sitelink'] ?>jsale/bootstrap/css/bootstrap-responsive.min.css" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="<?= $config['sitelink'] ?>jsale/admin/style.css" type="text/css" media="screen, projection" />
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js" type="text/javascript"></script>
    <!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<script type="text/javascript" src="<?= $config['sitelink']?>jsale/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="hero-unit">
    <h1>Массовая проверка треков</h1><br />

		<p class="text-info">Формат: каждый трек с новой строки</p>
		<br />
    	<div class="control-group">
		<form action="" method="post">
		<textarea name="tracks" rows="15" class="input-xlarge"><?= (isset($_POST['tracks']) ? $_POST['tracks'] : '') ?></textarea>
		<br/>
		<input name="check" type="submit" value="Проверить" class="btn btn-primary btn-large">
		</form>	
		</div>
	</div>
	
<? if (isset($track_data)): ?>
			<table class="table table-striped">
				<thead>
				<tr>
					<th>#</th>
					<th>Дата</th>
					<th>Статус</th>
					<th>Операция</th>
					<th>Место</th>
				</tr>
				</thead>
				<tbody>
				<? foreach ($track_data as $id => $track): ?>
				<tr class="info">
					<td colspan="5"><strong><?= $id ?></strong></td>
				</tr>
					<? foreach ($track as $i => $tr): ?>
						<tr>
							<td><?= $i + 1 ?></td>
							<td><?= $tr['date'] ?></td>
							<td><?= $tr['type'] ?></td>
							<td><?= $tr['attr'] ?></td>
							<td><?= $tr['code'] ?>, <?= $tr['place'] ?></td>
						</tr>
					<? endforeach; ?>
				<? endforeach; ?>
				</tbody>
			</table>
		<? endif; ?>
</body>
</html>