<?php

# Подключение конфига
include_once dirname(__FILE__) . '/../config.inc.php';

if ($config['deadlines']['enabled'] === true)
{
	# Подключение библиотеки
	include_once dirname(__FILE__) . '/M_DB.inc.php';
	$mDB = M_DB::Instance();

	# Дедлайны для уникальных ссылок
	if ($config['deadlines']['unique_link'] === true)
	{
		# Проверка существования GET параметра
		foreach ($_GET as $param => $get)
		{
			foreach ($config['deadline'] as $key => $deadline)
			{
				if ($deadline['param'] == $param)
					$param_key = $key;
			}
		}

		if (isset($param_key) && !empty($param_key))
		{
			$deadline = $mDB->GetItemsByParams('deadline', 'email', $_GET[$param], 'param', $param);
			
			if (empty($deadline))
			{
				# Сохранение конечного времени дедлайна
				if ($config['deadline'][$param_key]['interval'])
					$time = time() + $config['deadline'][$param_key]['interval'];
				elseif ($config['deadline'][$param_key]['time'])
					$time = strtotime($config['deadline'][$param_key]['time']);
			
				$params = array (
					'email' => $_GET[$param],
					'param' => $param,
					'time' => $time
				);
				$mDB->CreateItem('deadline', $params, true);
				
				# Сохранение в сессию
				$_SESSION['jsale_deadline'] = $time;
			}
			else
			{
				$_SESSION['jsale_deadline'] = $deadline[0]['time'];
			}

			$_SESSION[$param] = $_GET[$param];
			$_SESSION['param'] = $param;
			$_SESSION['param_key'] = $param_key;
			
			# Редирект на страницу без параметра
			$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			$get = strstr($url, $param);
			$url = str_replace($get, '', $url);
			$url = substr($url, 0, strlen($url) - 1);

			$url = $mDB->RemoveGET('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			header('location: ' . $url);
			die;
		}
		
		# Проверка существования SESSION параметра
		if (isset($_SESSION['param']) && isset ($_SESSION[$_SESSION['param']]))
		{
			$time = $_SESSION['jsale_deadline'];

			if (time() < $time)
			{
				$date = date('Y', $time) .','. (date('m', $time) - 1) .','.  date('d', $time) .','. date('H', $time) .','. date('i', $time) .','. date('s', $time);

				$_SESSION['jsale_countdown'] = '
				<script type="text/javascript">
					$(function() {
						$("#countdown-item").countdown(new Date('.$date.'), {prefix:\'До конца предложения: \', finish: \'Предложение закончено\',reload: true});
					});
				</script>
				<style type="text/css">
					#countdown-item { '.$config['deadline'][$_SESSION['param_key']]['css'].' }
				</style>
				<div id="countdown-item"></div>';		
			}
		}
	}
	# Массовые дедлайны
	else
	{
		# Проверка существования SESSION параметра
		if (!isset($_SESSION['jsale_deadline']))
		{
			# Сохранение конечного времени дедлайна
			if ($config['deadline']['mass']['interval'])
				$time = time() + $config['deadline']['mass']['interval'];
			elseif ($config['deadline']['mass']['time'])
				$time = strtotime($config['deadline']['mass']['time']);
			
			$_SESSION['jsale_deadline'] = $time;
		}
		
		$time = $_SESSION['jsale_deadline'];
		if (time() < $time)
		{
			$date = date('Y', $time) .','. (date('m', $time) - 1) .','.  date('d', $time) .','. date('H', $time) .','. date('i', $time) .','. date('s', $time);

			$_SESSION['jsale_countdown'] = '
			<script type="text/javascript">
				$(function() {
					$("#countdown-item").countdown(new Date('.$date.'), {prefix:\'До конца предложения: \', finish: \'Предложение закончено\',reload: true});
				});
			</script>
			<style type="text/css">
				#countdown-item { '.$config['deadline']['mass']['css'].' }
			</style>
			<div id="countdown-item"></div>';		
		}
	}
}