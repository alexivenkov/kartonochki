<?php
# Модуль основных запросов к БД

include_once dirname(__FILE__) . '/MSQL.inc.php';
include_once dirname(__FILE__) . '/M_Admin.inc.php';

class M_DB
{
	private static $instance; 	# Ссылка на экземпляр класса
	private $msql; 				# Драйвер БД
	private $admin; 			# Признак админа

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_DB();

		return self::$instance;
	}

	# Конструктор
	public function __construct()
	{
		# Подключение драйвера работы с БД и модели Администратора
		$this->msql = MSQL::Instance();
		$this->admin = M_Admin::Instance();
	}

	# Чтение всех элементов из базы
	public function GetAllItems($table)
	{
		$query = "SELECT * FROM `$table`";

		return $this->msql->Select($query);
	}

	# Генерация пагинации
	public function Paginate($table, $page, $num)
	{
		# Находим общее количество заказов
		$query = "SELECT COUNT(*) as count FROM `$table`";
		$result = $this->msql->Select($query);
		$items = $result[0]['count'];

		# Если записей нет, то отдаём false
		if (empty($items))
			return false;

		# Находим общее количество страниц
		$total = (($items - 1) / $num) + 1;
		$navi['total'] =  intval($total);

		# Находим начальную статью
		# Если значение текущей страницы больше максимального или меньше нуля, то отдаём ошибку 404
		$page = intval($page);
		if (empty($page) or $page < 0)
			return false;
		if ($page > $total)
			return false;
		$navi['start'] = $page * $num - $num;

		# Сохраняем также в массив текущую страницу
		$navi['page'] = $page;
		
		return $navi;
	}

	# Генерация пагинации с параметром
	public function PaginateWithParam($table, $page, $num, $param, $value)
	{
		# Находим общее количество заказов
		$t = "SELECT COUNT(*) as count FROM `$table` WHERE `$param` = '%s'";
        $query = sprintf($t, mysql_real_escape_string($value));

		$result = $this->msql->Select($query);
		$items = $result[0]['count'];

		# Если записей нет, то отдаём false
		if (empty($items))
			return false;

		# Находим общее количество страниц
		$total = (($items - 1) / $num) + 1;
		$navi['total'] =  intval($total);

		# Находим начальную статью
		# Если значение текущей страницы больше максимального или меньше нуля, то отдаём ошибку 404
		$page = intval($page);
		if (empty($page) or $page < 0)
			return false;
		if ($page > $total)
			return false;
		$navi['start'] = $page * $num - $num;

		# Сохраняем также в массив текущую страницу
		$navi['page'] = $page;

		return $navi;
	}
	
	# Генерация пагинации с параметром
	public function PaginateWithParams($table, $page, $num, $param1, $value1, $param2, $value2)
	{
		# Находим общее количество
		if ($value2[0] == 'all')
		{
			$t = "SELECT COUNT(*) as count FROM `$table` WHERE `$param1` = '%s'";
			$query = sprintf($t, mysql_real_escape_string($value1));
		}
		else
		{
			$and = '';
			foreach ($value2 as $key => $value)
			{
				$and .= "`$param2` LIKE '%[$value]%' ";
				
				if (isset($value2[$key + 1]))
					$and .= "OR `$param1` = '$value1' AND";
			}
		
			$query = "SELECT COUNT(*) as count FROM `$table` WHERE `$param1` = '$value1' AND $and";
		}

		$result = $this->msql->Select($query);
		$items = $result[0]['count'];

		# Если записей нет, то отдаём false
		if (empty($items))
			return false;

		# Находим общее количество страниц
		$total = (($items - 1) / $num) + 1;
		$navi['total'] =  intval($total);

		# Находим начальную статью
		# Если значение текущей страницы больше максимального или меньше нуля, то отдаём ошибку 404
		$page = intval($page);
		if (empty($page) or $page < 0)
			return false;
		if ($page > $total)
			return false;
		$navi['start'] = $page * $num - $num;

		# Сохраняем также в массив текущую страницу
		$navi['page'] = $page;

		return $navi;
	}
	
	# Генерация пагинации с параметром
	public function PaginateWithArrayParam($table, $page, $num, $param, $values)
	{
		# Находим общее количество заказов
		$query = "SELECT COUNT(*) as count FROM `$table` WHERE ";
		foreach ($values as $key => $value)
		{
			$query .= "`$param` = '$value'";
			if (isset($values[$key + 1]))
				$query .= " OR ";
		}

		$result = $this->msql->Select($query);
		$items = $result[0]['count'];

		# Если записей нет, то отдаём false
		if (empty($items))
			return false;

		# Находим общее количество страниц
		$total = (($items - 1) / $num) + 1;
		$navi['total'] =  intval($total);

		# Находим начальную статью
		# Если значение текущей страницы больше максимального или меньше нуля, то отдаём ошибку 404
		$page = intval($page);
		if (empty($page) or $page < 0)
			return false;
		if ($page > $total)
			return false;
		$navi['start'] = $page * $num - $num;

		# Сохраняем также в массив текущую страницу
		$navi['page'] = $page;

		return $navi;
	}
	

	# Выбор списка с пагинацией
	public function GetPaginatedList($table, $start, $num)
	{
		$t = "SELECT * FROM `$table` ORDER BY `id_$table` DESC LIMIT %d, %d";
		$query = sprintf($t, $start, $num);

		return $this->msql->Select($query);
	}

	# Выбор списка с пагинацией. С параметром
	public function GetPaginatedListWithParam($table, $start, $num, $param, $value)
	{
		$t = "SELECT * FROM `$table` WHERE `$param` = '%s' ORDER BY `id_$table` DESC LIMIT %d, %d";
		$query = sprintf($t, mysql_real_escape_string($value), $start, $num);

		return $this->msql->Select($query);
	}
	
	# Выбор списка с пагинацией. С параметром
	public function GetPaginatedListWithParams($table, $start, $num, $param1, $value1, $param2, $value2)
	{
		if ($value2[0] == 'all')
		{
			$t = "SELECT * FROM `$table` WHERE `$param1` = '%s' ORDER BY `id_$table` DESC LIMIT %d, %d";
			$query = sprintf($t, mysql_real_escape_string($value1), $start, $num);
		}
		else
		{	
			$and = '';
			foreach ($value2 as $key => $val)
			{
				$and .= "`$param2` LIKE '%[$val]%' ";
				
				if (isset($value2[$key + 1]))
					$and .= "OR `$param1` = '$value1' AND";
			}

			$start = ($start == null) ? 0 : $start;
			$query = "SELECT * FROM `$table` WHERE `$param1` = '$value1' AND $and ORDER BY `id_$table` DESC LIMIT $start, $num";
		}

		return $this->msql->Select($query);
	}

	# Выбор списка с пагинацией. С параметром в виде массива
	public function GetPaginatedListWithArrayParam($table, $start, $num, $param, $values)
	{
		$t = "SELECT * FROM $table WHERE ";
		foreach ($values as $key => $value)
		{
			$t .= "$param = '$value'";
			if (isset($values[$key + 1]))
				$t .= " OR ";
		}
		$t .= " ORDER BY id_$table DESC LIMIT %d, %d";
		$query = sprintf($t, $start, $num);

		return $this->msql->Select($query);
	}


	# Выбор элемента по идентификатору
	public function GetItemById($table, $id_item)
	{
		$t = "SELECT * FROM `$table` WHERE `id_$table` = '%d'";
		$query = sprintf($t, $id_item);
		$result = $this->msql->Select($query);
		return (isset($result[0])) ? $result[0] : false;
	}

	# Выбор элементов по параметру
	public function GetItemsByParam($table, $param, $value)
	{
		$t = "SELECT * FROM `$table` WHERE `$param` = '%s' ORDER BY `id_$table` DESC";
		$query = sprintf($t, mysql_real_escape_string($value));
		$result = $this->msql->Select($query);
		return $result;
	}
	
	# Выбор элемента по параметру
	public function GetItemByParam($table, $param, $value)
	{
		$t = "SELECT * FROM `$table` WHERE `$param` = '%s' ORDER BY `id_$table` DESC";
		$query = sprintf($t, mysql_real_escape_string($value));
		$result = $this->msql->Select($query);
		return (isset($result[0])) ? $result[0] : false;
	}
	
	# Выбор элементов по двум параметрам
	public function GetItemsByParams($table, $param1, $value1, $param2, $value2)
	{
		$t = "SELECT * FROM `$table` WHERE `$param1` = '%s' AND `$param2` = '%s' ORDER BY `id_$table` DESC";
		$query = sprintf($t, mysql_real_escape_string($value1), mysql_real_escape_string($value2));
		$result = $this->msql->Select($query);
		return $result;
	}
	
	# Выбор элементов по параметру с сортировкой
	public function GetItemsByParamAndSort($table, $param, $value)
	{
		$t = "SELECT * FROM `$table` WHERE `$param` = '%s' ORDER BY `sort` ASC";
		$query = sprintf($t, mysql_real_escape_string($value));
		$result = $this->msql->Select($query);
		return $result;
	}
	
	# Выбор элементов по параметру
	public function GetItemsByParamLike($table, $param, $value)
	{
		$t = "SELECT * FROM `$table` WHERE `$param` LIKE '%%%s%%' ORDER BY `id_$table` DESC";
		$query = sprintf($t, mysql_real_escape_string($value));
		$result = $this->msql->Select($query);
		return $result;
	}

	# Выбор элемента по имени
	public function GetItemByCode($table, $code_item)
	{
		$t = "SELECT * FROM `$table` WHERE `code` = '%s'";
		$query = sprintf($t, mysql_real_escape_string($code_item));
		$result = $this->msql->Select($query);

		return (isset($result[0])) ? $result[0] : false;
	}

	# Добавление элемента
	public function CreateItem($table, $params, $admin = false)
	{
		# Проверка наличия прав
		if ($admin == false)
			if (!$this->admin->CheckLogin())
				return false;

		# Проверка данных.
		if (!$table || !$params)
			return false;

		return $this->msql->Insert($table, $params);
	}

	# Редактирование элемента по идентификатору
	public function EditItemById($table, $params, $id_item, $admin = false)
	{
		# Проверка наличия прав
		if ($admin == false)
			if (!$this->admin->CheckLogin())
				return false;

		# Проверка данных.
		if (!$table || !$params || !$id_item)
			return false;

		$t = "id_$table = '%d'";
		$where = sprintf($t, $id_item);
		$this->msql->Update($table, $params, $where);
	}
	
	# Редактирование элемента по коду
	public function EditItemByCode($table, $params, $code, $admin = false)
	{
		# Проверка наличия прав
		if ($admin == false)
			if (!$this->admin->CheckLogin())
				return false;

		# Проверка данных.
		if (!$table || !$params || !$code)
			return false;

		$t = "code = '%s'";
		$where = sprintf($t, mysql_real_escape_string($code));
		$this->msql->Update($table, $params, $where);
	}
	
	# Редактирование элементов по параметру
	public function EditItemsByParam($table, $params, $param_name, $param_value, $admin = false)
	{
		# Проверка наличия прав
		if ($admin == false)
			if (!$this->admin->CheckLogin())
				return false;

		# Проверка данных.
		if (!$table || !$params || !$param_name || !$param_value)
			return false;

		$t = "$param_name = '%d'";
		$where = sprintf($t, $param_value);
		$this->msql->Update($table, $params, $where);
	}

	# Удаление элемента по идентификатору
	public function DeleteItemById($table, $id_item)
	{
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
		    return false;

		$t = "id_$table = '%s'";
		$where = sprintf($t, mysql_real_escape_string($id_item));
		$this->msql->Delete($table, $where);
		return true;
	}

	# Удаление элементов по параметру
	public function DeleteItemsByParam($table, $param, $value)
	{
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
		    return false;

		$t = "$param = '%s'";
		$where = sprintf($t, mysql_real_escape_string($value));
		
		$this->msql->Delete($table, $where);
		return true;
	}

    # Изменение статуса элемента по идентификатору
    public function ChangeStatusById($table, $id_item, $status)
	{
		# Проверка данных.
		if ($table == '' || $id_item == '' || $status == '')
			return false;

		# Запрос.
		$obj = array();
		$obj['status'] = $status;

		$t = "id_$table = '%d'";
		$where = sprintf($t, $id_item);
		$this->msql->Update($table, $obj, $where);
		return true;
	}

    # Проверка наличия категории с указанным кодом (если указан идентификатор записи в БД, то она не учитывается).
	public function IssetItem($table, $code, $id_item = null)
    {
        if ($id_item == null)
        {
            $t = "SELECT COUNT(*) as count FROM `$table` WHERE `code` = '%s'";
            $query = sprintf($t, mysql_real_escape_string($code));
        }
        else
        {
		    $t = "SELECT COUNT(*) as count FROM `$table` WHERE `code` = '%s' AND `id_$table` != '%d'";
            $query = sprintf($t, mysql_real_escape_string($code), $id_item);
        }

		$result = $this->msql->Select($query);

		return ($result[0]['count'] > 0);
    }
	
    # Проверка наличия категории с указанным кодом.
	public function IssetItemByParam($table, $param, $value)
    {
		$t = "SELECT COUNT(*) as count FROM $table WHERE $param = '%s'";
		$query = sprintf($t, $value);

		$result = $this->msql->Select($query);

		return ($result[0]['count'] > 0);
    }
	
	# Генерация случайной строки
	public function GenerateCode($length = 10, $all_chars = true)
	{
		if ($all_chars == true)
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		else
			$chars = "ABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		$code = '';
		$clen = strlen($chars) - 1;

		while (strlen($code) < $length)
			$code .= $chars[mt_rand(0, $clen)];

		return $code;
	}
	
	# Выбор содержимого дива из текста
	public function GetDivFromContent($text, $div)
	{
		preg_match('|<div class="' . $div . '">(.*)</div>|Uisu', $text, $matches);

		return (isset($matches[1])) ? $matches[1] : '';
	}

	# Выбор содержимого тега из текста
	public function GetTagFromContent($text, $tag)
	{
		preg_match('|<' . $tag . '">(.*)</' . $tag . '>|Uisu', $text, $matches);

		return $matches[1];
	}

	# Выбор краткого описания статьи
	public function GetPreviewFromText($separator, $text)
	{
		$text_after_more = strstr($text, $separator); # находим текст после тега more
		$preview = str_replace($text_after_more, '', $text); # удаляем его

		return $preview;
	}

	# Выбор полного текста статьи
	public function GetContentFromText($separator, $text)
	{
		$content = strstr($text, $separator); # находим текст после тега more
		$content = str_replace($separator, '', $content);

		return $content;
	}
	
	# Получение суммы прописью
	public function Number2String($num) {
		$nul = 'ноль';
		$ten = array(
			array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
			array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
		);
		$a20 = array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
		$tens = array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
		$hundred = array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
		$unit = array( // Units
			array('копейка' ,'копейки' ,'копеек',	 1),
			array('рубль'   ,'рубля'   ,'рублей'    ,0),
			array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
			array('миллион' ,'миллиона','миллионов' ,0),
			array('миллиард','милиарда','миллиардов',0),
		);
		
		list($rub,$kop) = explode(',', sprintf("%015.2f", floatval($num)));
		$out = array();
		if (intval($rub)>0) {
			foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
				if (!intval($v)) continue;
				$uk = sizeof($unit)-$uk-1; // unit key
				$gender = $unit[$uk][3];
				list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
				// mega-logic
				$out[] = $hundred[$i1]; # 1xx-9xx
				if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
				else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
				// units without rub & kop
				if ($uk>1) $out[]= $this->Plural($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
			} //foreach
		}
		else $out[] = $nul;
		$out[] = $this->Plural(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
		$out[] = $kop.' '.$this->Plural($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
		return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
	}

	# Склонение существительных с числительными (http://mcaizer.habrahabr.ru/blog/11555/)
	public function Plural($n, $form1, $form2, $form5)
	{
		$n = abs($n) % 100;
		$n1 = $n % 10;
		if ($n > 10 && $n < 20) return $form5;
		else if ($n1 > 1 && $n1 < 5) return $form2;
		else if ($n1 == 1) return $form1;
	
		return $form5;
	} # echo $n." ".plural($n, "письмо", "письма", "писем")." у Вас в ящике";	
	
	# Поиск по параметрам
	public function SearchItemsByParamArray($table, $params, $values, $search_type)
	{
		$t = "SELECT * FROM `$table` WHERE ";
		foreach ($values as $key => $value)
		{
			$t .= "$params[$key] $value";
			if (isset($values[$key + 1]))
				$t .= " $search_type[$key] ";
		}
		$t .= " ORDER BY `id_$table` DESC";
		$query = sprintf($t);

		return $this->msql->Select($query);
	}
	
	# Сохранение статуса
	public function SaveStatus($id_custom, $date, $status, $admin = null)
	{
		# Проверка наличия прав
		if ($admin == null && !$this->admin->CheckLogin())
			return false;
		
		# Проверка данных.
		if (!$id_custom || !$date || !isset($status))
			return false;

		$params['id_custom'] = $id_custom;
		$params['date'] = $date;
		$params['status'] = $status;

		return $this->msql->Insert('status', $params);
	}

    public function autocompleteCity($value) {
        $query = "SELECT `id`, `city_name` FROM `geo_data` WHERE `city_name` LIKE '" . mysql_real_escape_string($value) . "%'";
        $result = $this->msql->Select($query);

        return $result;
    }

    public function checkCity($value) {
        $query = "SELECT `id` FROM `geo_data` WHERE `city_name` = '" . mysql_real_escape_string($value) . "'";
        $result = $this->msql->Select($query);

        return $result;
    }

	# Вырезание GET параметров из URL
	public function RemoveGET($url)
	{
		return preg_replace('/^([^?]+)(\?.*?)?(#.*)?$/', '$1$3', $url);
	}
}
