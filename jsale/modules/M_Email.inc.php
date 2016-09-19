<?php
# Модуль обработки заказа и отправки заказа на почту

include_once dirname(__FILE__) . '/M_DB.inc.php';
$mDB = M_DB::Instance();

class M_Email
{
	private static $instance; 	# Ссылка на экземпляр класса

	# Получение единственного экземпляра класса
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_Email();

		return self::$instance;
	}

	# Генерация антиспама
	public function GenerateAntispam($secret)
	{
		return md5('s' . date("Y-m-d") . 'p' . date("d-m-Y") . 'a' . $secret . 'm');
	}

    # Проверка антиспама
    public function CheckSpam($antispam, $secret)
    {
        return (md5('s' . date("Y-m-d") . 'p' . date("d-m-Y") . 'a' . $secret . 'm') == $antispam);
    }
	
	# Генерация хеш-строки
	public function GenerateHash($id_order, $sum, $secret)
	{
		return md5('s' . $id_order . 'p' . $sum . 'a' . $secret . 'm');
	}
	
    # Проверка хеш-строки
    public function CheckHash($hash, $id_order, $sum, $secret)
    {
        return (md5('s' . $id_order . 'p' . $sum . 'a' . $secret . 'm') == $hash);
    }

    # Валидация формы (обязательные поля)
    public function ValidateForm($email, $name, $lastName, $fatherName, $phone, $zip, $country, $region, $city, $address, $qty, $config)
    {
		$return = array();
        $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $lastName = filter_var($lastName, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $fatherName = filter_var($fatherName, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $phone = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        $zip = filter_var($zip, FILTER_SANITIZE_NUMBER_INT);
		$country = filter_var($country, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
		$region = filter_var($region, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $city = filter_var($city, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $address = filter_var($address, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
			
		if (empty($name) && $config['form']['name']['required'] === true)
			$return[] = 'name';
		if (empty($lastName) && $config['form']['lastname']['required'] === true)
			$return[] = 'lastname';
		if (empty($fatherName) && $config['form']['fathername']['required'] === true)
			$return[] = 'fathername';
		if (empty($phone) && $config['form']['phone']['required'] === true)
			$return[] = 'phone';
		if ($this->CheckEmail($email) === false && $config['form']['email']['required'] === true)
			$return[] = 'email';
		if (empty($zip) && $config['form']['zip']['required'] === true)
			$return[] = 'zip';
		if (empty($country) && $config['form']['country']['required'] === true)
			$return[] = 'country';
		if (empty($region) && $config['form']['region']['required'] === true)
			$return[] = 'region';
		if (empty($city) && $config['form']['city']['required'] === true)
			$return[] = 'city';
		if (empty($address) && $config['form']['address']['required'] === true)
			$return[] = 'address';
		if ($qty == 0)
			$return[] = 'qty';
			
		return (!empty($return)) ? $return : false;
    }
	
    # Валидация формы (дополнительные поля)
    public function ValidateAddForm($adds, $config)
    {
		$return = array();
		foreach ($adds as $add_name => $add)
		{
			$add = filter_var($add, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
			if ($config['form']['add'][$add_name]['required'] === true && empty ($add))
				$return[] = $add_name;
		}
		
		return $return;
	}
	
    # Валидация email адреса
    public function ValidateEmail($email)
	{
		global $config;
	
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
            return $config['form']['emptyEmail'];
		else
			return false;
	}
	
	# Валидация email адреса (new)
	public function CheckEmail($email)
	{
		if (preg_match("/[а-яА-Я]+(.*)@(.*)/iu", $email) == 1 || filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			return false;
		else
			return true;
	}

    # Валидация формы обратной связи (обязательные поля)
    public function ValidateFeedbackForm($email, $name, $phone, $message)
    {
        global $config;

        $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $phone = filter_var($phone, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $message = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

        if (empty($name) && $config['feedback']['name']['required'])
            return $config['form']['emptyName'];
        elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false && $config['feedback']['email']['required'] === true)
            return $config['form']['emptyEmail'];
        elseif (empty($phone) && $config['feedback']['phone']['required'] === true)
            return $config['form']['emptyPhone'];
        elseif (empty($message) && $config['feedback']['comment']['required'] === true)
            return $config['form']['emptyMessage'];
        else
            return false;
    }
	
    # Валидация формы заказа звонка (обязательные поля)
    public function ValidateCallForm($name, $phone, $email, $message)
    {
        global $config;

        $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $phone = filter_var($phone, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
        $message = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

        if (empty($name) && $config['call']['name']['required'])
            return $config['form']['emptyName'];
        elseif (empty($phone) && $config['call']['phone']['required'] === true)
            return $config['form']['emptyPhone'];
		elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false && $config['call']['email']['required'] === true)
            return $config['form']['emptyEmail'];
        elseif (empty($message) && $config['call']['comment']['required'] === true)
            return $config['form']['emptyMessage'];
        else
            return false;
    }

    # Подготовка письма о составлении заказа
    public function PrepareOrder($id_order, $email, $lastName, $name, $fatherName, $phone = null, $zip, $country, $region, $city, $address, $comment, $order_items, $order_sum, $payment, $yandex_payment_type, $delivery, $hash = null, $hash2 = null, $config = null, $partner = null, $admin = null, $form_config = null)
    {
		ob_start();
		include dirname(__FILE__) . '/../design/emailOrder.tpl.php';
		return ob_get_clean();
    }

    # Подготовка письма обратной связи
    public function PrepareFeedback($fromEmail, $fromName, $subject, $content, $referer, $config)
    {
		ob_start();
		include_once dirname(__FILE__) . '/../design/emailFeedback.tpl.php';
		return ob_get_clean();
    }
	
    # Подготовка письма заказа звонка
    public function PrepareCall($fromName, $subject, $content, $referer, $config)
    {
		ob_start();
		include_once dirname(__FILE__) . '/../design/emailCall.tpl.php';
		return ob_get_clean();
    }

    # Подготовка письма об изменении статуса
    public function PrepareChangeStatus($id_custom, $email, $lastName, $name, $fatherName, $phone = null, $zip = null, $country = null, $region = null, $city = null, $address = null, $comment = null, $order_items, $order_sum, $payment, $yandex_payment_type, $delivery, $date, $config, $status, $partner = null, $admin = null, $form_config = null, $hash = null)
    {
		ob_start();
		include dirname(__FILE__) . '/../design/emailChangeStatus.tpl.php';
		return ob_get_clean();
	}
	
	# Подготовка письма со ссылкой на скачивание
	public function PrepareDownloadLink($email, $product, $link, $uses, $hours)
	{
		ob_start();
		include_once dirname(__FILE__) . '/../design/emailDownloadLink.tpl.php';
		return ob_get_clean();
	}
	
    # Подготовка уведомления о неоплаченном заказе
    public function PrepareNoticeOrder($id_custom, $email, $lastName, $name, $fatherName, $phone = null, $zip = null, $region = null, $city = null, $address = null, $comment = null, $order_items, $order_sum, $payment, $delivery, $date, $status, $hash, $config)
    {
		ob_start();
		include dirname(__FILE__) . '/../design/emailNoticeOrder.tpl.php';
		return ob_get_clean();
    }

    # Подготовка уведомления об отзыве
    public function PrepareNoticeReview($name, $config)
    {
		ob_start();
		include dirname(__FILE__) . '/../design/emailNoticeReview.tpl.php';
		return ob_get_clean();
    }
	
    # Подготовка уведомления о партнёрской программе
    public function PrepareNoticePartner($name, $config)
    {
		ob_start();
		include dirname(__FILE__) . '/../design/emailNoticePartner.tpl.php';
		return ob_get_clean();
    }
	
	# Подготовка письма о регистрации партнёра
	public function PreparePartnerRegister($email, $password, $code, $config)
	{
		ob_start();
		include_once dirname(__FILE__) . '/../design/emailPartnerRegister.tpl.php';
		return ob_get_clean();
	}
	
	# Подготовка письма о восстановлении доступа партнёра
	public function PreparePartnerRestore($email, $password, $code, $config)
	{
		ob_start();
		include_once dirname(__FILE__) . '/../design/emailPartnerRestore.tpl.php';
		return ob_get_clean();
	}

	# Подготовка письма с уведомлением о малом количестве пин-кодов
	public function PreparePincodeNotice($product, $qty, $config)
	{
		ob_start();
		include_once dirname(__FILE__) . '/../design/emailPincodeNotice.tpl.php';
		return ob_get_clean();
	}
	
	# Отправка письма
	public function SendEmail($toEmail, $fromEmail, $subject, $content, $from = null, $encoding = 'utf-8')
	{
		include dirname(__FILE__) . '/../config.inc.php';
	
		if ($config['email']['smtp']['enabled'] === true)
			return $this->SendEmailBySMTP($toEmail, $fromEmail, $subject, $content, $from, $encoding);
		else
			return $this->SendEmailBySendmail($toEmail, $fromEmail, $subject, $content, $from, $encoding);
	}

    # Отправка письма с помощью Sendmail
    public function SendEmailBySendmail($toEmail, $fromEmail, $subject, $content, $from = null, $encoding = 'utf-8')
    {
		# Если от кого не указано, подставляем робота :)
        if ($from == null)
            $from = 'Robo';

        # Обработка темы.
        $subject = "=?$encoding?b?" . base64_encode($subject) . "?=";
        # Формирование заголовков.
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=$encoding\r\n";
        $headers .= "From: =?$encoding?b?" . base64_encode($from) . "?= <" . $fromEmail . ">";

        return (mail($toEmail, $subject, $content, $headers));
    }
	
	# Отправка письма с помощью SMTP
	public function SendEmailBySMTP($toEmail, $fromEmail, $subject, $content, $fromName = null, $encoding = 'utf-8', $attach = false)
	{
		include dirname(__FILE__) . '/../config.inc.php';
		$__smtp = $config['email']['smtp'];
		include_once dirname(__FILE__) . '/smtp/class.phpmailer.php';
		$mail = new PHPMailer(true);

		$mail->IsSMTP();

		try
		{
			$mail->Host       = $__smtp['host']; 
			$mail->SMTPDebug  = $__smtp['debug']; 
			$mail->SMTPAuth   = $__smtp['auth'];
			$mail->SMTPSecure = 'tls';
			$mail->Host       = $__smtp['host'];
			$mail->Port       = $__smtp['port']; 
			$mail->Username   = $__smtp['username'];
			$mail->Password   = $__smtp['password'];
			$mail->AddAddress($toEmail);
			$mail->SetFrom($fromEmail, $fromName);
			$mail->AddReplyTo($fromEmail, $fromName);
			$mail->Subject = htmlspecialchars($subject);
			$mail->MsgHTML($content);
			if ($attach)
				$mail->AddAttachment($attach);
			$mail->Send();

			return true;
		}
		catch (phpmailerException $e)
		{
			echo $e->errorMessage(); 
		}
		catch (Exception $e)
		{
			echo $e->getMessage(); 
		}
	}

    # Отправка письма
    /*public function SendEmail($toEmail, $fromEmail, $subject, $content, $fromName = null, $invoices = null, $encoding = 'utf-8')
    {
        if ($fromName == null)
            $fromName = 'Robo';

        # Обработка темы
        $subject = "=?$encoding?b?" . base64_encode($subject) . "?=";
        # Генерируем разделитель
        $boundary = "--" . md5(uniqid(time()));
        # Формирование заголовков
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From: =?$encoding?b?" . base64_encode($fromName) . "?= <" . $fromEmail . ">\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";

        # Открываем тело письма. Вначале текстовая часть
        $multipart = "--" . $boundary . "\r\n";
        $multipart .= "Content-Type: text/html; charset=$encoding\r\n";
        $multipart .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        # Обрабатываем текст письма
        $multipart .= $this->QuotedPrintableEncode($content) . "\r\n\r\n";

        # Обработка вложений
        $file = '';
		$count = (is_array($invoices)) ? count($invoices) : '';

        if ($count > 0)
        {
            for ($i = 0; $i < $count; $i++)
            {
                $attach = file_get_contents($invoices[$i]['filepath']);
                $file .= "--" . $boundary . "\r\n";
                $file .= "Content-Type: text/html; charset=$encoding\r\n";
                $file .= "Content-Transfer-Encoding: quoted-printable\r\n";
                $file .= "Content-Disposition: attachment; filename=\"" . $invoices[$i]['filename'] . "\"\r\n\r\n";
                $file .= $this->QuotedPrintableEncode($attach) . "\r\n";
            }
        }
        $multipart .= $file . "--" . $boundary . "--\r\n";

        return (mail($toEmail, $subject, $multipart, $headers));
    }*/

    # Кодирование строки методом printable_encode
    public function QuotedPrintableEncode($string)
    {
        # rule #2, #3 (leaves space and tab characters in tact)
        $string = preg_replace_callback (
        '/[^\x21-\x3C\x3E-\x7E\x09\x20]/',
        @array($this, QuotedPrintableEncodeCharacter),
        $string
        );
        $newline = "=\r\n"; # '=' + CRLF (rule #4)
        # make sure the splitting of lines does not interfere with escaped characters
        # (chunk_split fails here)
        $string = preg_replace ( '/(.{73}[^=]{0,3})/', '$1' . $newline, $string);
        return $string;
    }

    # Вспомогательная функция для кодирования
    public function QuotedPrintableEncodeCharacter($matches)
    {
        $character = $matches[0];
        return sprintf ('=%02x', ord($character));
    }

	# Обработка текста для сохранения в базу данных или отправки по почте.
	public function ProcessText($text)
	{
		$text = trim($text); # Удаляем пробелы по бокам.
		$text = stripslashes($text); # Удаляем слэши, лишние пробелы и переводим html символы.
		$text = htmlspecialchars($text); # Переводим HTML в текст.
		$text = preg_replace("/ +/"," ", $text); # Множественные пробелы заменяем на одинарные.
		$text = preg_replace("/(\r\n){3,}/","\r\n\r\n", $text); # Убираем лишние пробелы (больше 1 строки).
		$text = str_replace("\r\n","<br>", $text); # Заменяем переводы строк на тег.

		return $text; # Возвращаем переменную.
	}
}