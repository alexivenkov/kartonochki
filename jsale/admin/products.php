<?php

# jSale v1.431
# http://jsale.biz

# Подключение настроек
include_once dirname(__FILE__) . '/../config.inc.php';

# Вывод ошибок
if ($config['errors'] === true)
{
	error_reporting(E_ALL); # Уровень вывода ошибок
	ini_set('display_errors', 'on'); # Вывод ошибок включён
}
# Логирование ошибок
if ($config['error_logging'] === true)
{
	ini_set("log_errors", 'on'); # Логирование включено
	ini_set("error_log", dirname(__FILE__) . '/error_log.txt'); # Путь файла с логами
}

# Простейшая авторизация
include_once dirname(__FILE__) . '/../modules/M_Admin.inc.php';
$mAdmin = M_Admin::Instance();

session_start();
if (!$mAdmin->CheckLogin())
	die;

if ($_SESSION['access_type'] != 'admin' && $config['manager']['rights']['products'] !== true)
	die;

# Кодировка.
header('Content-type: text/html; charset=' . $config['encoding']);

# Формирование GET запроса (на случай PHP как CGI)
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);

# Обработка имени файла
$file = str_replace(dirname(__FILE__) . '/', '', (__FILE__));
$file = str_replace('.php', '', $file);

# Подключение меню
include_once dirname(__FILE__) . '/_menu.php';

# Подключение необходимых модулей
include_once dirname(__FILE__) . '/../modules/M_Categories.inc.php';
$mCategories = M_Categories::Instance();
include_once dirname(__FILE__) . '/../modules/M_Products.inc.php';
$mProducts = M_Products::Instance();

if (is_file(dirname(__FILE__) . '/../modules/M_Files.inc.php'))
{
	include_once dirname(__FILE__) . '/../modules/M_Files.inc.php';
	$mFiles = M_Files::Instance();
}

# Определение текущей страницы
if (isset($_GET['page']))
    $navi['page'] = $_GET['page'];
else
    $navi['page'] = 1;

# Определение текущей категории
if (!empty($_GET['category']))
	$current_category = $mDB->GetItemByCode('category', $_GET['category']);
else
	$current_category['code'] = '';

# Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	# Редактирование подкатегорий и товаров
	if (isset($_POST['products_edit']))
	{
        # Редактирование подкатегорий
		$category_id = (!empty($_POST['category_id'])) ? $_POST['category_id'] : '';
        $category_code = (!empty($_POST['category_code'])) ? $_POST['category_code'] : '';
        $category_parent = (!empty($_POST['category_parent'])) ? $_POST['category_parent'] : '';
		$category_title = (!empty($_POST['category_title'])) ? $_POST['category_title'] : '';

        if (!empty($category_id))
        {
            foreach ($category_id as $key => $category)
                $mCategories->EditCategory($category_id[$key], $category_code[$key], $category_parent[$key], $category_title[$key]);
        }

		# Добавление новых подкатегорий
		if (!empty($_POST['new_category_code']))
		{
			$new_category_code = $_POST['new_category_code'];
            $new_category_parent = $_POST['new_category_parent'];
			$new_category_title = $_POST['new_category_title'];

			foreach ($new_category_code as $key => $category)
            {
				$mCategories->CreateCategory($new_category_code[$key], $new_category_parent[$key], $new_category_title[$key]);
            }
		}

        # Редактирование товаров
		$product_id = (!empty($_POST['product_id'])) ? $_POST['product_id'] : '';
		$product_code = (!empty($_POST['product_code'])) ? $_POST['product_code'] : '';
        $product_category = (!empty($_POST['product_category'])) ? $_POST['product_category'] : '';
		$product_description = (!empty($_POST['product_description'])) ? $_POST['product_description'] : '';
		$product_store = (isset($_POST['product_store']) && !empty($_POST['product_store'])) ? $_POST['product_store'] : '';
        $product_file = (!empty($_FILES['product_file'])) ? $_FILES['product_file'] : '';
        $path = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/';
		$product_author = (isset($_POST['product_author']) && !empty($_POST['product_author'])) ? $_POST['product_author'] : '';
		$product_partner_rate = (isset($_POST['product_partner_rate']) && !empty($_POST['product_partner_rate'])) ? $_POST['product_partner_rate'] : '';
		$product_manager = (!empty($_POST['product_manager'])) ? $_POST['product_manager'] : '';
		$product_cost_price = (!empty($_POST['product_cost_price'])) ? $_POST['product_cost_price'] : '';
		$product_link2file = (!empty($_POST['product_link2file'])) ? $_POST['product_link2file'] : '';
		
		#echo var_dump($_POST);
		#die;

        if (!empty($product_id))
        {
            foreach ($product_id as $key => $product)
            {
                # Если есть загруженный файл
                if (is_array($product_file) && !empty($product_file['tmp_name'][$key]) && $config['download']['enabled'] == true)
                {
                    # Подготовка загрузки
                    $upload_file = array();
                    $upload_file['name'] = $product_code[$key] . '.' . $config['download']['type'];
                    $upload_file['tmp_name'] = $product_file['tmp_name'][$key];
                    $upload_file['size'] = $product_file['size'][$key];
                    $upload_file['type'] = $product_file['type'][$key];
                    $upload_file['error'] = $product_file['error'][$key];
					
                    # Удаление старого файла
                    if (is_file($path . $product_code[$key] . '.' . $config['download']['type']))
                        unlink($path . $product_code[$key] . '.' . $config['download']['type']);

                    # Загрузка нового файла
                    $mFiles->UploadFile($upload_file, $path);
                    unset($upload_file);
                }
				
				# Заглушка
				$product_store[$key] = (isset($product_store[$key])) ? $product_store[$key] : '';
				$product_author[$key] = (isset($product_author[$key])) ? $product_author[$key] : '';
				$product_manager[$key] = (isset($product_manager[$key])) ? $product_manager[$key] : '';
				$product_partner_rate[$key] = (isset($product_partner_rate[$key])) ? $product_partner_rate[$key] : 0;
				$product_cost_price[$key] = (isset($product_cost_price[$key])) ? $product_cost_price[$key] : 0.00;
				$product_link2file[$key] = (isset($product_link2file[$key])) ? $product_link2file[$key] : '';
				
				# Редактирование товара
                $mProducts->EditProduct($product_id[$key], $product_code[$key], $product_category[$key], stripslashes($product_description[$key]), $product_store[$key], $product_author[$key], $product_manager[$key], $product_cost_price[$key], $product_partner_rate[$key], $product_link2file[$key]);
            }
        }
    }
		
    # Редирект на список
    header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/products.php?page=' . $navi['page'] . '&category=' . $current_category['code']);
}
else
{
    # Обработка GET запроса
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        # Удаление товара
        if (isset($_GET['delete_product_id']))
        {
			$product = $mDB->GetItemById('product', $_GET['delete_product_id']);
			$mDB->DeleteItemById('product', $_GET['delete_product_id']);
			$delete_file = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $product['code'] . '.' . $config['download']['type'];
			if (is_file($delete_file))
				unlink($delete_file);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die;
        }

        # Удаление файла товара
        if (isset($_GET['delete_file']))
        {
            $delete_file = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $_GET['delete_file'];
            if (is_file($delete_file))
                unlink($delete_file);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die;
        }
		
        # Скачать файл товара
        if (isset($_GET['download_file']))
        {
			$path = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/';
			
			# Убиваем скрипт, если кто-то пытается ввести кривой путь
			if (strstr('/', $_GET['download_file']) !== false)
				return false;

			# Скачивание файла
			if ($mFiles->DownloadFile($_GET['download_file'], $path))
				die;
        }

        # Удаление категории и всех связанных элементов
        if (isset($_GET['delete_category_id']))
        {
            # Выбор удаляемой категории
            $category = $mDB->GetItemById('category', $_GET['delete_category_id']);
            # Удаление категории
            $mDB->DeleteItemById('category', $_GET['delete_category_id']);
			# Удаление файлов товаров категории
			$products = $mDB->GetItemsByParam('product', 'category', $category['code']);
			foreach ($products as $product)
			{
				$delete_file = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $product['code'] . '.' . $config['download']['type'];
				if (is_file($delete_file))
					unlink ($delete_file);
			}
            # Удаление товаров категории
            $mDB->DeleteItemsByParam('product', 'category', $category['code']);

            # Выбор подкатегорий
            $cats = $mDB->GetItemsByParam('category', 'parent', $category['code']);
            # Удаление подкатегорий
            $mDB->DeleteItemsByParam('category', 'parent', $category['code']);

            # Проход по массиву подкатегорий
            foreach ($cats as $cat)
            {
                # Удаление подкатегорий следующего уровня
                $mDB->DeleteItemsByParam('category', 'parent', $cat['code']);
				# Удаление файлов товаров
				$products = $mDB->GetItemsByParam('product', 'category', $cat['code']);
				foreach ($products as $product)
				{
					$delete_file = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $product['code'] . '.' . $config['download']['type'];
					if (is_file($delete_file))
						unlink ($delete_file);
				}
                # Удаление товаров
                $mDB->DeleteItemsByParam('product', 'category', $cat['code']);
            }
            header('Location: ' . $config['sitelink'] . $config['dir'] . 'admin/products.php?page=' . $navi['page'] . '&category=' . $current_category['code']);
            die;
        }
    }

	# Вывод всех категорий
	$all_categories = $mDB->GetAllItems('category');

	# Вывод категории или списка категорий
	if (isset($_GET['category']))
	{
		$categories = $mCategories->GetSubcategories($_GET['category']);
		$navi = $mProducts->Paginate($_GET['category'], $navi['page'], $config['admin']['productsList']);
		$products = $mProducts->GetPaginatedList($_GET['category'], $navi['start'], $config['admin']['productsList']);
	}
	else
	{
		$categories = $mCategories->GetMainCategories();
		$navi = $mProducts->Paginate('', $navi['page'], $config['admin']['productsList']);
		$products = $mProducts->GetPaginatedList('', $navi['start'], $config['admin']['productsList']);
	}
	
	# Вывод всех авторов и менеджеров
	if ($config['author']['enabled'] === true)
		$authors = $mDB->GetAllItems('author');
	if ($config['manager']['enabled'] === true)
		$managers = $mDB->GetAllItems('manager');
		
	# Вывод всех файлов
	if ($config['download']['enabled'] === true && $config['download']['link2files'] === true)
	{
		$files_path = dirname(__FILE__) . '/../' . $config['download']['dir'];
		$dir_files = scandir($files_path);
		foreach ($dir_files as $key => $dir_file)
		{
			if (!strpos($dir_file, $config['download']['type']))
				unset($dir_files[$key]);
		}
	}

    ob_start();
    include_once dirname(__FILE__) . '/../design/adminPagination.tpl.php';
    $pagination = ob_get_clean();

	include_once dirname(__FILE__) . '/../design/adminProductsList.tpl.php';
}