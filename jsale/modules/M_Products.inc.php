<?php
# Модель работы с товарами

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Products
{
	private static $instance; 	# ссылка на экземпляр класса
	private $msql; 				# драйвер БД
	private $admin; 			# признак админа
    private $db; 			    # основная модель работы с БД

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Products();

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

	# Создание товара
	public function CreateProduct($product)
	{
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
		    return false;

		# Проверка данных.
		if ($product['code'] == '')
			return false;

		# Проверка на уникальность идентификатора. Если такой уже есть в базе, то генерируется новый
		$id = $code = $product['code'];
        $i = 1;
		while ($this->db->IssetItem('product', $code))
		{
			$code = $id . '-' . $i;
            $i++;
		}

		# Запрос.
		$obj = array();
        $obj['code'] = $code;
		$obj['title'] = $product['title'];
		$obj['price'] = $product['price'];
        $obj['discount'] = $product['discount'];
		$obj['unit'] = $product['unit'];
		$obj['qty'] = $product['qty'];
		$obj['qty_type'] = $product['qty_type'];
		$obj['param1'] = $product['param1'];
		$obj['param2'] = $product['param2'];
		$obj['param3'] = $product['param3'];
		$obj['store'] = $product['store'];
		$obj['form_type'] = $product['form_type'];
		$obj['button_img'] = $product['button_img'];
		$obj['bandle_products'] = $product['bandle_products'];

		return $this->msql->Insert('product', $obj);
	}

	# Редактирование товара
	public function EditProduct($id_product, $code, $category, $description, $store, $author, $manager, $cost_price, $partner_rate, $link2file)
	{
		# Проверка наличия прв
	    if (!$this->admin->CheckLogin())
		    return false;

		# Проверка данных.
		if ($id_product == '')
			return false;

		# Проверка на уникальность идентификатора. Если такой уже есть в базе, то генерируется новый
		$id = $code;
        $i = 1;
		while ($this->db->IssetItem('product', $code, $id_product))
		{
			$code = $id . '-' . $i;
            $i++;
		}

		# Запрос.
		$obj = array();
        $obj['category'] = $category;
		$obj['description'] = $description;
		$obj['store'] = $store;
		$obj['author'] = $author;
		$obj['manager'] = $manager;
		$obj['cost_price'] = $cost_price;
		$obj['partner_rate'] = $partner_rate;
		$obj['link2file'] = $link2file;

		$t = "id_product = '%d'";
		$where = sprintf($t, $id_product);
		$this->msql->Update('product', $obj, $where);
		return true;
	}
	
	# Редактирование товара
	public function EditFullProduct($id_product, $product)
	{
		# Проверка наличия прв
	    if (!$this->admin->CheckLogin())
		    return false;

		# Проверка данных.
		if ($id_product == '' || $product['code'] == '')
			return false;

		# Проверка на уникальность идентификатора. Если такой уже есть в базе, то генерируется новый
		$id = $code = $product['code'];
        $i = 1;
		while ($this->db->IssetItem('product', $code, $id_product))
		{
			$code = $id . '-' . $i;
            $i++;
		}

		# Запрос.
		$obj = array();
        $obj['code'] = $code;
		$obj['title'] = $product['title'];
		$obj['price'] = $product['price'];
		$obj['discount'] = $product['discount'];
		$obj['unit'] = $product['unit'];
		$obj['qty'] = $product['qty'];
		$obj['qty_type'] = $product['qty_type'];
		$obj['param1'] = $product['param1'];
		$obj['param2'] = $product['param2'];
		$obj['param3'] = $product['param3'];
		$obj['store'] = $product['store'];
		$obj['form_type'] = $product['form_type'];
		$obj['button_img'] = $product['button_img'];
		$obj['bandle_products'] = $product['bandle_products'];

		$t = "id_product = '%d'";
		$where = sprintf($t, $id_product);
		$this->msql->Update('product', $obj, $where);
		return true;
	}

	# Генерация пагинации при работе с категориями.
	public function Paginate($category, $page, $num)
	{
		# Находим общее количество заказов
		$t = "SELECT COUNT(*) as count FROM product WHERE category = '%s'";
        $query = sprintf($t, mysql_real_escape_string($category));
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

	# Выбор списка с пагинацией при работе с категориями.
	public function GetPaginatedList($category, $start, $num)
	{
		$t = "SELECT * FROM product WHERE category = '%s' ORDER BY id_product DESC LIMIT %d, %d";
		$query = sprintf($t, mysql_real_escape_string($category), $start, $num);

		return $this->msql->Select($query);
	}
	
	# Выбор идентификатора товара по коду и категории
	public function GetIdByCodeAndCategory($code, $category)
	{
		# Проверка данных.
		if ($code == '' || $category == '')
			return false;
			
        # Выбор товара.
		$t = "SELECT * FROM product WHERE code = '%s' AND category='%s'";
		$query = sprintf($t, mysql_real_escape_string($code), mysql_real_escape_string($category));
		$result = $this->msql->Select($query);
		
		if (!empty($result[0]['id_product']))
			return $result[0]['id_product'];
		else
			return false;
	}
}