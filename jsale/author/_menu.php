<?php

# Генерация меню
function GetMenuItems($page, $menu_items)
{
	global $config;
	
	$menu = '';

	foreach ($menu_items as $i => $item)
	{
		if ($page == $item[0])
			$menu .= '<li class="active"><a href="' . $config['sitelink'] . $config['dir'] . 'author/' . $item[0] . '.php" title="' . $item[2] . '">' . $item[1] . '</a></li>';
		else
			$menu .= '<li><a href="' . $config['sitelink'] . $config['dir'] . 'author/' . $item[0] . '.php" title="' . $item[2] . '">' . $item[1] . '</a></li>';
	}

	return $menu;
}

$menu_items = array (
				array ('index', 'Главная', 'Главная страница кабинета'),
				array ('stat', 'Статистика', 'Статистика продаж'),
				array ('config', 'Настройки', 'Смена пароля и реквизитов'),
			);