<?php
# Модель работы с CSV

include_once dirname(__FILE__) . '/M_Admin.inc.php';
include_once dirname(__FILE__) . '/MSQL.inc.php';

class M_CSV
{
	private static $instance; 	# ссылка на экземпляр класса
	private $admin;				# признак админа
	private $msql; 				# драйвер БД

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_CSV();

		return self::$instance;
	}

	# Конструктор
	function __construct()
	{
		# Подключение модели администратора
		$this->admin = M_Admin::Instance();
		$this->msql = MSQL::Instance();
	}

	# Returns true if $string is valid UTF-8 and false otherwise.
	public function is_utf8($string) {
		# From http://w3.org/International/questions/qa-forms-utf-8.html
		return preg_match('%^(?:
           [\x09\x0A\x0D\x20-\x7E]            # ASCII
         | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
         |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
         | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
         |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
         |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
         | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
         |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
     )*$%xs', $string);

	} # function is_utf8
	
	# Выгрузка таблици в файл CSV
	public function MySQL2CSV($table, $first_line, $date_from = null, $date_to = null)
	{
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
		    return false;
			
		# Подключение модулей
		include_once dirname(__FILE__) . '/M_DB.inc.php';
		$mDB = M_DB::Instance();

		if ($date_from != null || $date_to != null)
		{
			$date_from = date("Y-m-d", strtotime($date_from));
			$date_to = date("Y-m-d", strtotime($date_to) + 24 * 3600);
			
			# Выбор заказов по дате
			$params = array ('date >=' , 'date <=');
			$values = array ("'$date_from'" , "'$date_to'");
			$search_type = array('AND', 'AND');
			
			$exp = $mDB->SearchItemsByParamArray($table, $params, $values, $search_type);
		}
		else
			$exp = $mDB->GetAllItems($table);

		if (!empty($exp))
		{
			$row = $first_row = $check =  '';
			$f = 1;
			foreach($exp as $i=>$val)
			{
				$c = 1;
				foreach ($val as $j=>$field)
				{
					if ($c<count($val))
						$sep1 = ';';
					elseif ($c == count($val))
						$sep1 = "\n";
					if ($field != '')
						$sep2 = '"';
					else 
						$sep2 = '';
					$row .= $sep2 . $field . $sep2 . $sep1;
					if($first_line && $f == 1)
					{
						$first_row .= '"' . $j . '"' . $sep1;
					}
					if($f == 2)
						$check .= $field;
					$c++;
				}
				$f++;
			}
			$row = $first_row . $row;

			if($this->is_utf8($check))
				$row = iconv("utf-8", "cp1251", $row);

			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Length: " . strlen($row));
			# Output to browser with appropriate mime type, you choose
			//header('Content-Type: text/xml, charset=UTF-8; encoding=UTF-8');
			//header('Content-Type: text/csv, charset=UTF-8');
			//header("Content-type: text/x-csv charset=cp1251");
			header("Content-type: text/csv charset=cp1251");
			//header("Content-type: application/csv charset=cp1251");
			$filename = $table."_".date("Y-m-d_H-i",time());
			//header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header( "Content-disposition: filename=$filename.csv");
			//header("Content-Disposition: attachment; filename=$filename");
			echo $row;
			exit;
		}
		else
		return false;
	}
}