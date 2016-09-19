<?php
# Модель работы с категориямм

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Categories
{
	private static $instance; 	# ссылка на экземпляр класса
	private $msql; 				# драйвер БД
	private $admin; 			# признак админа
    private $db; 			    # основная модель работы с БД

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Categories();

		return self::$instance;
	}

	# Конструктор
	public function __construct()
	{
		# Подключение драйвера работы с БД и модели Администратора
		$this->msql = MSQL::Instance();
		$this->admin = M_Admin::Instance();
        $this->db = M_DB::Instance();
	}

	# Выбор подкатегорий
	public function GetSubcategories($category)
	{
		$t = "SELECT * FROM category WHERE parent = '%s' ORDER BY id_category";
		$query = sprintf($t, mysql_real_escape_string($category));
		$result = $this->msql->Select($query);

		return $result;
	}

	# Выбор подкатегорий
	public function GetMainCategories()
	{
		$query = "SELECT * FROM category WHERE parent = '' ORDER BY id_category";
		$result = $this->msql->Select($query);

		return $result;
	}

	# Создание категории
	public function CreateCategory($code, $parent, $title)
	{
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
		    return false;

		# Проверка данных.
		if ($code == '')
			return false;

		# Проверка на уникальность идентификатора. Если такой уже есть в базе, то генерируется новый
		$id = $code;
        $i = 1;
		while ($this->db->IssetItem('category', $code))
		{
			$code = $id . '-' . $i;
            $i++;
		}

		# Запрос.
		$obj = array();
        $obj['code'] = $code;
        $obj['parent'] = $parent;
		$obj['title'] = $title;

		return $this->msql->Insert('category', $obj);
	}

	# Редактирование категории
	public function EditCategory($id_category, $code, $parent, $title)
	{
		# Проверка наличия прв
	    if (!$this->admin->CheckLogin())
		    return false;

		# Проверка данных.
		if ($id_category == '' || $code == '')
			return false;

		# Проверка на уникальность идентификатора. Если такой уже есть в базе, то генерируется новый.
		$id = $code;
        $i = 1;
		while ($this->db->IssetItem('category', $code, $id_category))
		{
			$code = $id . '-' . $i;
            $i++;
		}

		# Запрос.
		$obj = array();
		$obj['code'] = $code;
        $obj['parent'] = $parent;
		$obj['title'] = $title;

		$t = "id_category = '%d'";
		$where = sprintf($t, $id_category);
		$this->msql->Update('category', $obj, $where);
		return true;
	}
}