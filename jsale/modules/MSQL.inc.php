<?php
# Драйвер работы с БД.

# Языковая настройка.
setlocale(LC_ALL, 'ru_RU.CP1251');

if ($config['database']['enabled'] == true)
{
	$encoding = ($config['encoding'] == 'windows-1251') ? 'cp1251' : 'utf8';

	# Подключение к БД.
	@mysql_connect($config['database']['host'], $config['database']['user'], $config['database']['pass']) or die('No connect with database');
	mysql_query('SET NAMES ' . $encoding);
	mysql_select_db($config['database']['name']) or die('No database');
	
	mysql_query("ALTER TABLE `custom` ADD `domain` varchar(256) NOT NULL");
	#mysql_query("ALTER TABLE `custom` ADD `ip` varchar(255) NOT NULL");
	#mysql_query("ALTER TABLE `custom` ADD `source` varchar(255) NOT NULL");
	# Если нужно удалить существующие таблицы, раскомментируйте эти строки
	#mysql_query("DROP TABLE `custom`");
	#mysql_query("DROP TABLE `custom_item`");

	# Установка jSale
	$jsale_install = dirname(__FILE__) . '/../install.inc.php';
	if (is_file($jsale_install))
		include_once $jsale_install;

	# Удаление файла установки
	if (isset($jsale_installed))
		unlink($jsale_install);
}

class MSQL
{
	private static $instance; 	# ссылка на экземпляр класса

	# Получение единственного экземпляра (одиночка)
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new MSQL();

		return self::$instance;
	}

	# Выборка строк
	# $query    	- полный текст SQL запроса
	# результат	- массив выбранных объектов
	public function Select($query)
	{
		$result = mysql_query($query);

		if (!$result)
			die(mysql_error());

		$n = mysql_num_rows($result);
		$arr = array();

		for($i = 0; $i < $n; $i++)
		{
			$row = mysql_fetch_assoc($result);		
			$arr[] = $row;
		}

		return $arr;
	}

	# Вставка строки
	# $table 		- имя таблицы
	# $object 		- ассоциативный массив с парами вида "имя столбца - значение"
	# результат	- идентификатор новой строки
	public function Insert($table, $object)
	{			
		$columns = array();
		$values = array();

		foreach ($object as $key => $value)
		{
			$key = mysql_real_escape_string($key . '');
			$columns[] = $key;

			if ($value === null)
			{
				$values[] = 'NULL';
			}
			else
			{
				$value = mysql_real_escape_string($value . '');							
				$values[] = "'$value'";
			}
		}

		$columns_s = implode(',', $columns);
		$values_s = implode(',', $values);

		$query = "INSERT INTO `$table` ($columns_s) VALUES ($values_s)";
		$result = mysql_query($query);

		if (!$result)
			die(mysql_error());
			
		return mysql_insert_id();
	}

	# Изменение строк
	# $table 		- имя таблицы
	# $object 		- ассоциативный массив с парами вида "имя столбца - значение"
	# $where		- условие (часть SQL запроса)
	# результат	- число измененных строк
	public function Update($table, $object, $where)
	{
		$sets = array();
	
		foreach ($object as $key => $value)
		{
			$key = mysql_real_escape_string($key . '');
			
			if ($value === null)
			{
				$sets[] = "$key=NULL";			
			}
			else
			{
				$value = mysql_real_escape_string($value . '');					
				$sets[] = "$key='$value'";			
			}			
		}

		$sets_s = implode(',', $sets);			
		$query = "UPDATE `$table` SET $sets_s WHERE $where";
		$result = mysql_query($query);

		if (!$result)
			die(mysql_error());

		return mysql_affected_rows();	
	}

	# Удаление строк
	# $table 		- имя таблицы
	# $where		- условие (часть SQL запроса)	
	# результат	- число удаленных строк
	public function Delete($table, $where)
	{
		$query = "DELETE FROM `$table` WHERE $where";		
		$result = mysql_query($query);

		if (!$result)
			die(mysql_error());

		return mysql_affected_rows();	
	}
}