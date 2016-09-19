<?php
# Модель работы с файлами

include_once dirname(__FILE__) . '/M_Admin.inc.php';
include_once dirname(__FILE__) . '/MSQL.inc.php';

class M_Files
{
	private static $instance; 	# ссылка на экземпляр класса
    private $admin; 			# признак админа
	private $msql; 				# драйвер БД

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Files();

		return self::$instance;
	}

	# Конструктор
	function __construct()
	{
        # Подключение модели администратора
        $this->admin = M_Admin::Instance();
		$this->msql = MSQL::Instance();
	}

	# Извлечение контента файла
	public function Get($file)
	{
		return file_get_contents($file);
	}

	# Сохранение контента в файл
	public function Save($file, $content)
	{
		return (file_put_contents($file, stripslashes($content)));
	}

	# Загрузка файла на сервер
	public function UploadFile($file, $path)
	{
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
			return false;

		# Проверка на существование папки загрузки
		if (!is_dir($path))
			return false;

		# Составляем полный путь до файла
		$fullpath = $path . $file['name'];

		# Загрузка файла
		if	(!@copy($file['tmp_name'], $fullpath))
		{
			return false;
		}
		else
		{
			chmod($fullpath, 0644);
			return $fullpath;
		}
	}

	# Очистка устаревших ссылок [доп. модуль Download Products]
	public function ClearLinks()
	{
		$date = date('Y-m-d H:i:s', (time() - 60 * 60 * 24));

		$where = "date < '$date' OR uses = '0'";
		$this->msql->Delete('download', $where);

		return true;
	}
	
	# Скачивание файла [доп. модуль Download Products]
	public function DownloadFile($file, $path)
	{
		# Проверка на существование папки загрузки
		if (!is_dir($path))
			return false;
		
		# Проверка на существование файла
		if (!is_file($path . $file))
			return false;

		# Составляем полный путь до файла
		$fullpath = $path . $file;
		
		# Тип файла
		$content_type = mime_content_type($fullpath);
		
		# Сброс буфера вывода PHP
		if (ob_get_level())
			ob_end_clean();

		# Скачивание файла
		header ('Content-Description: File Transfer');
		header ('Content-Transfer-Encoding: binary');
		header ('Expires: 0');
		header ('Cache-Control: must-revalidate');
		header ('Pragma: public');
		header ('Accept-Ranges: bytes');
		
		header ("Content-Type: $content_type");
		header ('Content-Length: '.filesize($path . $file)); 
		header ("Content-Disposition: attachment; filename=$file");  
		readfile($path . $file);
		exit;
		
		return true;
	}
	
	# Скачивание большого файла [доп. модуль Download Products]
	public function DownloadBigFile($file, $path, $mimetype = 'application/octet-stream')
	{
		# Проверка на существование папки загрузки и файла
		if (!is_dir($path) || !is_file($path . $file))
			return false;
				
		# Сброс буфера вывода PHP
		if (ob_get_level())
			ob_end_clean();

		# Полный путь до файла
		$filepath = $path . $file;
		
		# Размер файла
		$fsize = filesize($filepath);
		
		# Дата модификации файла 
		$ftime = date('D, d M Y H:i:s T', filemtime($filepath));
		
		# Открываем файл на чтение в бинарном режиме 
		$fd = @fopen($filepath, 'rb');
		
		# Определяем данные докачки
		if (isset($_SERVER['HTTP_RANGE'])) 
		{ 
			# Определяем, с какого байта скачивать файл 
			$range = $_SERVER['HTTP_RANGE'];
			$range = str_replace('bytes=', '', $range); 
			list($range, $end) = explode('-', $range); 
			if (!empty($range)) 
			{
				fseek($fd, $range); 
			} 
		} 
		else 
		{
			# Докачка не поддерживается 
			$range = 0; 
		} 
		if ($range)
		{ 
			# Говорим браузеру, что это часть какого-то контента 
			header($_SERVER['SERVER_PROTOCOL'].' 206 Partial Content');
		} 
		else 
		{ 
			# Стандартный ответ браузеру 
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		}
		# Прочие заголовки, необходимые для правильной работы 
		header('Content-Disposition: attachment; filename='.basename($filepath)); 
		header('Last-Modified: '.$ftime); 
		header('Accept-Ranges: bytes'); 
		header('Content-Length: '.($fsize - $range)); 
		if ($range)
		{ 
			header("Content-Range: bytes $range-".($fsize - 1).'/'.$fsize); 
		} 
		header('Content-Type: '.$mimetype);

		# Отдаем часть файла в браузер (программу докачки) 
		fpassthru($fd);
		fclose($fd); 
		exit;
	}
	
	# Проверка наличия валидного ключа. [доп. модуль Download Products]
	public function CheckCode($code)
	{
		$t = "SELECT * FROM download WHERE code = '%s' AND uses > 0";
		$query = sprintf($t, $code);
		$result = $this->msql->Select($query);
	
		return (isset($result[0])) ? $result[0] : false;
	}
	
	# Проверка оплаченного товара клиентов. [доп. модуль Download Products]
	public function CheckPaidProduct($email, $product, $config)
	{
	
		$success_statuses = array_merge($config['statuses']['success'], $config['statuses']['delivered']);

		# Выборка всех заказов.
		$t_status = '';
		foreach ($success_statuses as $key => $status)
		{
			if ($key != 0)
				$t_status .= ' OR';
			$t_status .= " status = '$status' AND email = '$email'";
		}
		$query = "SELECT id_custom FROM custom WHERE $t_status";
		$id_customs1 = $this->msql->Select($query);

		# Выборка всех заказанных товаров.
		$t = "SELECT id_custom FROM custom_item WHERE id_product = '%s'";
		$query = sprintf($t, $product);
		$id_customs2 = $this->msql->Select($query);
		
		if (empty($id_customs1) || empty($id_customs2))
			return false;
		
		# Сравниваем массивы.
		foreach ($id_customs2 as $id)
			$ids[] = $id['id_custom'];
		
		foreach ($id_customs1 as $id)
			if (in_array($id['id_custom'], $ids))
				return true;
	
		return false;
	}
	
	# Использование ключа. [доп. модуль Download Products]
	public function UseCode($code, $uses)
	{
		$obj = array();
		$obj['uses'] = $uses - 1;

		$t = "code = '%s'";
		$where = sprintf($t, mysql_real_escape_string($code));
		$this->msql->Update('download', $obj, $where);
		
		return true;
	}
	
	# Генерация ключа. [доп. модуль Download Products]
	public function CreateLink($code_product, $uses, $type)
	{
        include_once dirname(__FILE__) . '/M_DB.inc.php';
        $mDB = M_DB::Instance();

		# Имя файла
		$file = $code_product . '.' . $type;
	
		# Генерация ключа
		$code = $mDB->GenerateCode(6);
		
		# Сохранение в БД
		$obj = array();
		$obj['code'] = $code;
		$obj['file'] = $file;
		$obj['uses'] = $uses;
		$obj['date'] = date('Y-m-d H:i:s');
		
       $this->msql->Insert('download', $obj);
		
		return $code;
	}

	# Перекодирование из Windows-1251 в UTF-8
	public function Win2UTF($file)
	{
		$content = file_get_contents($file);
		$content = iconv("cp1251", "utf-8", $content);
	
		return (file_put_contents($file, stripslashes($content)));
	}

	# Загрузка данных из CSV в MySQL
	public function CSV2MySQL($file)
	{	
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
		    return false;

		# Перекодирование CSV файла в UTF-8	
		$this->Win2UTF($file);
		
		# Установка локали в UTF-8
		if (!setlocale(LC_ALL, 'ru_RU.utf8'));

        # Подключение модулей
        include_once dirname(__FILE__) . '/M_Products.inc.php';
        $mProducts = M_Products::Instance();
        include_once dirname(__FILE__) . '/M_DB.inc.php';
        $mDB = M_DB::Instance();
		
        $handle = fopen($file, "r");
        $row = 0;
        while (($string = fgetcsv($handle, 0, ';')) !== false)
        {
            if ($row > 0)
            {
                /*$code = iconv("cp1251", "utf-8", $string[0]);
                $category = iconv("cp1251", "utf-8", $string[1]);
                $title = iconv("cp1251", "utf-8", $string[2]);
                $quantity = iconv("cp1251", "utf-8", $string[3]);
                $price = iconv("cp1251", "utf-8", $string[4]);
				$description = iconv("cp1251", "utf-8", $string[5]);*/
				
				$code = $string[0];
                $category = $string[1];
                $title = $string[2];
                $quantity = $string[3];
                $price = $string[4];
				$description = $string[5];

                # Поиск в базе идентификатора
			    if ($id_product = $mProducts->GetIdByCodeAndCategory($code, $category))
                {
                    $mProducts->EditProduct($id_product, $code, $category, $title, $quantity, $price, $description);
                }
                else
                {
                    $mProducts->CreateProduct($code, $category, $title, $quantity, $price, $description);
                }
            }

            # Считаем количество обновлённых строк
            $row++;
        }
        fclose($handle);

		return $row - 1;
	}
	
	# Функция очищает заданную директорию
	public function CleanDir($folder)
	{
		$handle = opendir($folder);
		# Удаляем все файлы
		if ($handle != false)
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file != '..' && $file != '.')
				{
					unlink($folder . '/' . $file);
				}
			}

			closedir($handle);
		}
		return true;
	}
	
	# Функция очищает временную директорию
	public function CleanTemp()
	{
		$folder = dirname(__FILE__) . '/../tmp' ;
		$this->CleanDir($folder);
		return true;
	}

}