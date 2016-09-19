<?php
# Контроллер доставки цифровых товаров

# Подключение модуля работы со файлами
include_once dirname(__FILE__) . '/M_Files.inc.php';
$mFiles = M_Files::Instance();

# Проверка товаров по типу
foreach ($order_items as $key => $order_item)
{
	# Есть файл существует
	if (is_file(dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $order_item['id_product'] . '.' . $config['download']['type']))
	{
		# Продажа пин-кодов
		if ($config['download']['pincode'] === true)
		{
			if (strstr($order_item['param'], 'Ваш товар:') === false)
			{
				# Читаем файл
				$path = dirname(__FILE__) . '/../' . $config['download']['dir'] . '/' . $order_item['id_product'] . '.' . $config['download']['type'];
				$products = file($path);

				if (!empty($products))
				{
					# Выбираем первый товар
					$product = $products[0];

					# Удаляем этот товар из файла товаров
					unset($products[0]);
					
					# Пишем оставшиеся товары обратно
					file_put_contents($path, $products);

					# Добавляем товар в заказ
					$param = 'Ваш товар: ' . $product;
					
					$params = array('param' => $param);
					$order_items[$key]['param'] = $param;

					$mDB->EditItemById('custom_item', $params, $order_item['id_custom_item'], true);
					
					# Если осталось менее n товаров
					if (count($products) <= $config['download']['min_qty'])
					{
						# Высылаем уведомление админу
						$content = $mEmail->PreparePincodeNotice($order_item, count($products), $config);
						$mEmail->SendEmail($config['email']['receiver'], $config['email']['answer'], $config['email']['subjectPincodeNotice'], $content, $config['email']['answerName'], $config['encoding']);
					}
				}
			}
		}
		else
		{
			# Генерация кода
			$code = $mFiles->CreateLink($order_item['id_product'], $config['download']['uses'], $config['download']['type']);
			
			$order_items[$key]['download_link'] = $config['sitelink'] . $config['dir'] . 'download/' . $code . '/';
			$order_items[$key]['update_link'] = $config['sitelink'] . $config['dir'] . 'update/' . $order_item['id_product'] . '/';
		}
	}
}