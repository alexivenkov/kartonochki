<?php
# Модель партнёрской программы

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Authors
{	
	private static $instance;	# экземпляр класса
	private $mDB; 				# библиотека

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Authors();
			
		return self::$instance;
	}
	
	# Конструктор
	public function __construct()
	{
		# Подключение библиотеки
		$this->mDB = M_DB::Instance();
	}

	/**
	* Функция авторизации
	*
	* @return boolean
	*/
	public function CheckLogin()
	{
        global $config;
		$login_successful = false;
		
		$logout = (isset($_SESSION['logout'])) ? $_SESSION['logout'] : false;

		# Проверка логина и пароля
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
		{
			$params = array ('email =', 'password =');
			$values = array ('\'' . mysql_real_escape_string($_SERVER['PHP_AUTH_USER']) . '\'', '\'' . mysql_real_escape_string($_SERVER['PHP_AUTH_PW']) . '\'');
			$search_type = array ('AND');
			
			$result = $this->mDB->SearchItemsByParamArray('author', $params, $values, $search_type);
		
			if ($result == true && $logout != true)
			{
				$_SESSION['id_author'] = $result[0]['id_author'];
				$login_successful = true;
			}
		}

		# Если юзер не залогинен
		if (!$login_successful)
		{
			# Форма авторизации
			header('WWW-Authenticate: Basic realm="Enter login and password"');
			header('HTTP/1.0 401 Unauthorized');
		}
		else
		{
			return true;
		}
	}
}