<?php
# Модуль работы с заказами
include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Orders
{
	private static $instance; 	# ссылка на экземпляр класса
	private $msql; 				# драйвер БД
	private $admin; 			# признак админа
    private $config;

	# Получение единственного экземпляра класса
	public static function Instance($config = null)
	{
		if (self::$instance == null)
			self::$instance = new M_Orders($config);

		return self::$instance;
	}

	# Конструктор
	public function __construct($config = null)
	{
		# Подключение драйвера работы с БД и модели администратора
		$this->msql = MSQL::Instance();
		$this->admin = M_Admin::Instance();
        $this->config = $config;
	}
	
	public function GetAllOrders($statuses = null, $fail = null)
	{
		$where = '';
		if ($statuses != null)
		{
			$where = 'WHERE ';
			if ($fail != null)
				foreach ($statuses['fail'] as $key => $status)
					$where .= "status != $status AND ";
			
			$where .= 'status != ' . $statuses['deleted'][0];
		}
	
		$query = "SELECT * FROM custom $where";

		return $this->msql->Select($query);
	}
	
	public function Paginate($page, $num, $statuses = null, $fail = null, $tags = null)
	{
		$where = $and = '';
		if ($statuses != null)
		{
			$where = ' ';
			if ($fail != null)
				foreach ($statuses['fail'] as $key => $status)
					$where .= "status != $status AND ";

			$where .= 'status != ' . $statuses['deleted'][0];
				
			if ($tags != null && is_array($tags) && !empty($tags) && $tags[0] != 'all')
			{
				$and = 'AND ';
				foreach ($tags as $key => $value)
				{
					$and .= "`tags` LIKE '%[$value]%' ";
					
					if (isset($tags[$key + 1]))
						$and .= "OR $where AND ";
				}
			}
		}
	
		# Находим общее количество заказов
		$query = "SELECT COUNT(*) as count FROM custom WHERE $where $and";
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
	
	public function GetPaginatedList($start, $num, $statuses = null, $fail = null, $tags = null)
	{
		$where = $and = '';
		if ($statuses != null)
		{
			$where = ' ';
			if ($fail != null)
				foreach ($statuses['fail'] as $key => $status)
					$where .= "status != $status AND ";
			
			$where .= 'status != ' . $statuses['deleted'][0] . ' AND status != ' . $statuses['archive'][0];
			
			if ($tags != null && is_array($tags) && !empty($tags) && $tags[0] != 'all')
			{
				$and = 'AND ';
				foreach ($tags as $key => $value)
				{
					$and .= "`tags` LIKE '%%[$value]%%' ";
					
					if (isset($tags[$key + 1]))
						$and .= "OR $where AND ";
				}
			}
		}
	
		$t = "SELECT * FROM custom WHERE $where $and ORDER BY id_custom DESC LIMIT %d, %d";
		$query = sprintf($t, $start, $num);

		return $this->msql->Select($query);
	}

	# Создание заказа
	public function CreateOrder($lastName, $name, $fatherName, $email, $phone, $zip, $country, $region, $city, $address, $comment, $payment, $juridical = null, $delivery = null, $delivery_cost = 0, $date, $order_sum, $status, $id_user = 0,  $utm = '', $source = '', $conf = 0, $ip = '', $yandex_payment_type = '', $domain = '', $pvz_address = '')
	{
		# Проверка данных
		if (!isset($name) || !isset($email) || !isset($phone) || !isset($address) || !isset($payment) || !isset($date) || !isset($order_sum) || !isset($pvz_address))
			return false;
			
        if (isset($juridical) && is_array($juridical))
            $juridical = implode('|', $juridical);

		# Запрос
		$obj = array();
 		$obj['name'] = $name;
 		$obj['lastname'] = $lastName;
 		$obj['fathername'] = $fatherName;
 		$obj['email'] = $email;
 		$obj['phone'] = $phone;
 		$obj['zip'] = $zip;
 		$obj['country'] = $country;
 		$obj['region'] = $region;
 		$obj['city'] = $city;
 		$obj['address'] = $address;
 		$obj['comment'] = $comment;
 		$obj['payment'] = $payment;
         $obj['juridical'] = $juridical;
         $obj['delivery'] = $delivery;
 		$obj['delivery_cost'] = $delivery_cost;
 		$obj['date'] = $date;
 		$obj['sum'] = $order_sum;
 		$obj['status'] = $status;
 		$obj['admin_comment'] = $admin_comment;
 		$obj['payment_ym'] = $payment_ym;
 		$obj['id_manager'] = $id_manager;
         $obj['manager_bonus'] = $manager_bonus;
+        $obj['pvz_address'] = $pvz_address;

		return $this->msql->Insert('custom', $obj);
	}

	# Создание элементов заказа
	public function CreateOrderItem($id_custom, $id_product, $product, $quantity, $price, $discount, $unit, $size = null, $color = null, $param = null, $partner_rate = null)
	{
		# Проверка данных
		if ($id_custom == '' || $id_product == '' || $product == '' || $quantity == '' || $price == '')
			return false;

		# Запрос
		$obj = array();
		$obj['id_custom'] = $id_custom;
		$obj['id_product'] = $id_product;
		$obj['product'] = $product;
		$obj['quantity'] = $quantity;
		$obj['price'] = $price;
		$obj['discount'] = $discount;
		$obj['unit'] = $unit;
        $obj['size'] = $size;
        $obj['color'] = $color;
        $obj['param'] = $param;
		$obj['partner_rate'] = $partner_rate;

		return $this->msql->Insert('custom_item', $obj);
	}

	# Редактирование элементов заказа
	public function EditOrderItem($id_custom_item, $id_custom, $id_product, $product, $quantity, $price, $discount, $unit, $size = null, $color = null, $param = null)
	{
		# Проверка данных
		if ($id_custom == '' || $id_product == '' || $product == '' || $quantity == '' || $price == '')
			return false;

		# Запрос
		$obj = array();
		$obj['id_custom'] = $id_custom;
		$obj['id_product'] = $id_product;
		$obj['product'] = $product;
		$obj['quantity'] = $quantity;
		$obj['price'] = $price;
		$obj['discount'] = $discount;
		$obj['unit'] = $unit;
        $obj['size'] = $size;
        $obj['color'] = $color;
        $obj['param'] = $param;

		$t = "id_custom_item = '%d'";
		$where = sprintf($t, $id_custom_item);
		$this->msql->Update('custom_item', $obj, $where);
        return true;
	}

	# Редактирование заказа
	public function EditOrder($id_custom, $lastName = null, $name, $fatherName = null, $email, $phone = null, $zip = null, $country = null, $region = null, $city = null, $address = null, $comment = null, $payment, $juridical = null, $delivery = null, $delivery_cost = null, $date = null, $order_sum, $status, $admin_comment = null, $payment_ym = null, $id_manager = null, $manager_bonus = null, $pvz_address = '')
	{
		# Проверка наличия прав
	    if (!$this->admin->CheckLogin())
		    return false;

		# Проверка данных
		if ($id_custom == '' || $name == '' || $payment == '' || $date == '' || $order_sum == '' || $status == '')
			return false;

        if ($juridical)
            $juridical = implode('|', $juridical);

		# Запрос
		$obj = array();
		$obj['name'] = $name;
		$obj['lastname'] = $lastName;
		$obj['fathername'] = $fatherName;
		$obj['email'] = $email;
		$obj['phone'] = $phone;
		$obj['zip'] = $zip;
		$obj['country'] = $country;
		$obj['region'] = $region;
		$obj['city'] = $city;
		$obj['address'] = $address;
		$obj['comment'] = $comment;
		$obj['payment'] = $payment;
        $obj['juridical'] = $juridical;
        $obj['delivery'] = $delivery;
		$obj['delivery_cost'] = $delivery_cost;
		$obj['date'] = $date;
		$obj['sum'] = $order_sum;
		$obj['status'] = $status;
		$obj['admin_comment'] = $admin_comment;
		$obj['payment_ym'] = $payment_ym;
		$obj['id_manager'] = $id_manager;
        $obj['manager_bonus'] = $manager_bonus;
        $obj['pvz_address'] = $pvz_address;

		$t = "id_custom = '%d'";
		$where = sprintf($t, $id_custom);
		$this->msql->Update('custom', $obj, $where);
		return true;
	}

	# Выбор заказов за посление N дней
	public function GetLastOrders($days)
	{
		$date = date("Y-m-d H:i:s", mktime() - $days * 24 * 60 * 60);

		$t = "SELECT * FROM custom WHERE date > '%s'";
		$query = sprintf($t, $date);
		$result = $this->msql->Select($query);

		return $result;
	}
}