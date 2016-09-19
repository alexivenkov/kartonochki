<?php

# Генерация меню
function GetMenuItems($page, $menu_items)
{
	global $config;
	
	$menu = '';

	foreach ($menu_items as $i => $item)
	{
		if (isset($item[3]) && is_array(($item[3])) && !empty($item[3]))
		{
			$selected = ($page == $item[0]) ? 'active' : '';
			$menu .= '<li class="dropdown ' . $selected . '"><a href="' . $config['sitelink'] . $config['dir'] . 'admin/' . $item[0] . '.php" title="' . $item[2] . '" class="dropdown-toggle" data-toggle="dropdown" >' . $item[1] . ' <b class="caret"></b> </a>
				<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
					<li><a href="' . $config['sitelink'] . $config['dir'] . 'admin/products.php">Все разделы</a></li>
					<li class="divider"></li>';
				
				foreach ($item[3] as $sub_item)
					$menu .= '<li><a href="' . $config['sitelink'] . $config['dir'] . 'admin/products.php?category=' . $sub_item['code'] . '">' . $sub_item['title'] . '</a></li>';
				
				$menu .= '
				</ul>
			</li>';
		}
		else
		{
			$selected = ($page == $item[0]) ? 'class="active"' : '';
			$menu .= '<li '. $selected .'><a href="' . $config['sitelink'] . $config['dir'] . 'admin/' . $item[0] . '.php" title="' . $item[2] . '">' . $item[1] . '</a></li>';
		}
	}

	return $menu;
}

# Подключение модуля работы с базой данных.
include_once dirname(__FILE__) . '/../modules/M_DB.inc.php';
$mDB = M_DB::Instance();

$NewOrdersCount = $mDB->GetItemsByParam('custom', 'status', '0');
$NewOrdersCount = count($NewOrdersCount);
if ($config['calls']['enabled'] === true && mysql_table_seek('call', $config['database']['name']))
{
	$NewCallsCount = $mDB->GetItemsByParam('call', 'status', '0');
	$NewCallsCount = count($NewCallsCount);
}

$MainCategories = ($config['admin']['productsCatMenu'] === true) ? $mDB->GetItemsByParam('category', 'parent', '') : '';

$menu_items = array (
				array ('index', 'Главная', 'Главная страница админки'),
				array ('orders', 'Заказы <span class="badge badge-success">' . $NewOrdersCount . ' </span>', 'Управление заказами'),
				array ('codegen', 'Генератор кнопки', 'Генератор кнопки заказа'),
			);
			
if (is_file(dirname(__FILE__) . '/calls.php') && $config['calls']['enabled'] === true && ($_SESSION['access_type'] == 'admin' || $_SESSION['access_type'] == 'manager' && $config['manager']['rights']['calls'] === true))
	array_push ($menu_items,
		array ('calls', 'Звонки <span class="badge badge-success">' . $NewCallsCount . ' </span>', 'Управление звонками')
	);

if (is_file(dirname(__FILE__) . '/products.php') && ($_SESSION['access_type'] == 'admin' || $_SESSION['access_type'] == 'manager' && $config['manager']['rights']['products'] === true))
	array_push ($menu_items,
		array ('products', 'Товары', 'Управление товарами', $MainCategories)
	);
		
if ($_SESSION['access_type'] == 'admin')
{
	if (is_file(dirname(__FILE__) . '/database.php'))
		array_push ($menu_items,
			array ('database', 'База данных', 'Экспорт базы в CSV')
		);
		
	if (is_file(dirname(__FILE__) . '/report_1.php') && $config['reports']['enabled'] === true)
		array_push ($menu_items,
			array ('report_1', 'Подробный отчёт', 'Отчёт с учётом статистики Яндекс.Директ')
		);
		
	if (is_file(dirname(__FILE__) . '/report_2.php') && $config['reports']['enabled'] === true)
		array_push ($menu_items,
			array ('report_2', 'Отчёт по дням', 'Отчёт с учётом статистики Яндекс.Директ')
		);

	if (is_file(dirname(__FILE__) . '/partners.php') && $config['partner']['enabled'] === true)
		array_push ($menu_items,
			array ('partners', 'Партнёры', 'Партнёрская программа')
		);
		
	if (is_file(dirname(__FILE__) . '/authors.php') && $config['author']['enabled'] === true)
		array_push ($menu_items,
			array ('authors', 'Авторы', 'Авторы продуктов')
		);

	if (is_file(dirname(__FILE__) . '/managers.php') && $config['manager']['enabled'] === true)
		array_push ($menu_items,
			array ('managers', 'Менеджеры', 'Менеджеры')
		);
		
	if (is_file(dirname(__FILE__) . '/tags.php') && $config['tags']['enabled'] === true)
		array_push ($menu_items,
			array ('tags', 'Метки', 'Метки продуктов')
		);
		
	/* if (is_file(dirname(__FILE__) . '/masscheck.php'))
		array_push ($menu_items,
			array ('masscheck', 'Массовая проверка', 'Масовая проверка треков Почты России')
		); */
}

if ($_SESSION['access_type'] == 'manager' && isset($_SESSION['id_manager']))
{
	if ($config['manager']['orders_link'] === true)
	{
		array_push ($menu_items,
			array ('manager', 'Мои заказы', 'Мои заказы')
		);
	}
}

function mysql_table_seek($tablename, $dbname)
{
    $rslt = mysql_query("SHOW TABLES FROM `{$dbname}` LIKE '" . mysql_real_escape_string(addCslashes($tablename, "\\%_")) . "';");

    return mysql_num_rows($rslt) > 0;
}