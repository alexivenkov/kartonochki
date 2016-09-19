<?php

# Генерация меню
function GetMenuItems($page, $menu_items)
{
	global $config;
	
	$menu = '';

	foreach ($menu_items as $i => $item)
	{
		if ($page == $item[0])
			$menu .= '<li class="active"><a href="' . $config['sitelink'] . $config['dir'] . 'partner/' . $item[0] . '.php" title="' . $item[2] . '">' . $item[1] . '</a></li>';
		else
			$menu .= '<li><a href="' . $config['sitelink'] . $config['dir'] . 'partner/' . $item[0] . '.php" title="' . $item[2] . '">' . $item[1] . '</a></li>';
	}

	return $menu;
}

$menu_items = array (
				array ('index', 'Главная', 'Главная страница админки'),
				array ('instruments', 'Инструменты', 'Реф.ссылки, промо-коды и т.д.'),
				array ('stat', 'Статистика', 'Статистика и конверсии'),
				array ('config', 'Настройки', 'Смена пароля и реквизитов'),
			);