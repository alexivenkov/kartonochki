<?php
# Модуль работы с заказами
include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Calls
{
	private static $instance; 	# ссылка на экземпляр класса
	private $msql; 				# драйвер БД
	private $admin; 			# признак админа

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Calls();

		return self::$instance;
	}

	# Конструктор
	public function __construct()
	{
		# Подключение драйвера работы с БД и модели администратора
		$this->msql = MSQL::Instance();
		$this->admin = M_Admin::Instance();
	}
	
	public function GetAllCalls($statuses = null, $fail = null)
	{
		$where = '';
		if ($statuses != null)
		{
			$where = 'WHERE ';
			if ($fail != null)
				foreach ($statuses['fail'] as $key => $status)
					$where .= "`status` != $status AND ";
			
			$where .= '`status` != ' . $statuses['deleted'][0];
		}
	
		$query = "SELECT * FROM `call` $where";

		return $this->msql->Select($query);
	}
	
	public function Paginate($page, $num, $statuses = null, $fail = null)
	{
		$where = '';
		if ($statuses != null)
		{
			$where = 'WHERE ';
			if ($fail != null)
				foreach ($statuses['fail'] as $key => $status)
					$where .= "`status` != `$status` AND ";
			
			$where .= '`status` != ' . $statuses['deleted'][0];
		}
	
		# Находим общее количество заказов
		$query = "SELECT COUNT(*) as count FROM `call` $where";
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
	
	public function GetPaginatedList($start, $num, $statuses = null, $fail = null)
	{
		$where = '';
		if ($statuses != null)
		{
			$where = 'WHERE ';
			if ($fail != null)
				foreach ($statuses['fail'] as $key => $status)
					$where .= "`status` != $status AND ";
			
			$where .= '`status` != ' . $statuses['deleted'][0];
		}
	
		$t = "SELECT * FROM `call` $where ORDER BY `id_call` DESC LIMIT %d, %d";
		$query = sprintf($t, $start, $num);

		return $this->msql->Select($query);
	}

	# Создание заказа
	public function CreateCall($lastName, $name, $fatherName, $phone, $email, $comment, $date, $status, $topic, $link, $operator)
	{
		# Проверка данных
		if (!isset($name) || !isset($phone) || !isset($date))
			return false;

		# Запрос
		$obj = array();
		$obj['name'] = $name;
		$obj['lastname'] = $lastName;
		$obj['fathername'] = $fatherName;
		$obj['phone'] = $phone;
		$obj['email'] = $email;
		$obj['comment'] = $comment;
		$obj['date'] = $date;
		$obj['status'] = $status;
        $obj['operator'] = $operator;
		$obj['topic'] = $topic;
		$obj['link'] = $link;

		return $this->msql->Insert('call', $obj);
	}

	# Редактирование заказа
	public function EditCall($id_call, $lastName = null, $name, $fatherName = null, $phone, $email = null, $comment = null, $date = null, $status, $topic, $link, $operator)
	{
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
		    return false;

		# Проверка данных
		if ($id_call == '' || $name == '' || $phone == '' || $date == '' || $status == '')
			return false;

		# Запрос
		$obj = array();
		$obj['name'] = $name;
		$obj['lastname'] = $lastName;
		$obj['fathername'] = $fatherName;
		$obj['phone'] = $phone;
		$obj['email'] = $email;
		$obj['comment'] = $comment;
		$obj['date'] = $date;
		$obj['status'] = $status;
        $obj['operator'] = $operator;
		$obj['topic'] = $topic;
		$obj['link'] = $link;

		$t = "id_call = '%d'";
		$where = sprintf($t, $id_call);
		$this->msql->Update('call', $obj, $where);
		return true;
	}

	# Сохранение статуса
	public function SaveStatus($id_call, $date, $status, $admin = null)
	{
		# Проверка наличия прав
		if ($admin == null && !$this->admin->CheckLogin())
			return false;
		
		# Проверка данных.
		if (!$id_call || !$date || !isset($status))
			return false;

		$params['id_call'] = $id_call;
		$params['date'] = $date;
		$params['status'] = $status;

		return $this->msql->Insert('call_status', $params);
	}
}