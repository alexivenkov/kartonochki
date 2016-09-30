<?php

# jSale v1.431 - VRGuru.ru 25.08.16
# http://jsale.biz

$config = array();

////////////////////////////////////////////////////////////////////////////////
// ОБЩИЕ НАСТРОЙКИ

$config['dir']								= 'jsale/'; # Папка, содержащая скрипт
$config['secretWord']               	    = '1322662196'; # Секретное слово для генерации антиспама
$config['sitelink']							= 'http://kartonochki.dev/'; # Адрес сайта (со слэшем на конце)
$config['sites']							= array ('jsale.biz'); # Домены сайтов, где будет также работать скрипт. Закомментировать, если не нужно.
$config['sitename']							= 'Kartonochki.ru Test'; # Название магазина
$config['resultURL']						= 'done.html'; # Страница, после оформления заказа (если не нужна, оставьте пустой)
$config['successURL']						= ''; # Страница, после оплаты заказа (если не нужна, оставьте пустой)
$config['upsellURL']						= 'thanks.html'; # Страница с апселлом, после оформления заказа (если не нужна, оставьте пустой)
$config['failURL']							= ''; # Страница, при сбое оплаты заказа (если не нужна, оставьте пустой)
$config['confirmURL']						= ''; # Страница, после подтверждения заказа (если не нужна, оставьте пустой)
$config['refuseURL']						= ''; # Страница, после отказа от заказа (если не нужна, оставьте пустой)
$config['no_confirmURL']					= ''; # Страница, если подтверждение заказа прошло не удачно (если не нужна, оставьте пустой)
$config['no_refuseURL']						= ''; # Страница, если отказ от заказа прошёл не удачно (если не нужна, оставьте пустой)
$config['errors']							= true; # Выводить ошибки
$config['error_logging']					= true; # Логировать ошибки
$config['payment_logging']					= true; # Логировать уведомление об оплате
$config['currency']							= 'руб.'; # Валюта
$config['currencyCode']						= 'RUB'; # Код валюты. Нужно для оплаты онлайн
$config['encoding']							= 'utf-8'; # Кодировка
$config['min_sum']							= 0.1; # Минимальная сумма заказа

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА EMAIL

$config['email']['receiver']				= 'databot2015@yandex.ru'; # E-mail адрес, на который отправляется заказ
$config['email']['answer']					= 'databot2015@yandex.ru'; # E-mail адрес, от имени которого отправляется ответное письмо покупателю
$config['email']['from_customer']			= false; # Присылать продавцу письма от имени покупателя? true/false
$config['email']['answerName']				= 'Kartonochki Robo';	# Имя робота
$config['email']['answerMessageTop']		= '<h3>Спасибо за покупку на сайте ' . $config['sitename'] . '</h3><br><br>'; # Ответное письмо покупателю
$config['email']['answerMessageSignature']	= '<p>--------------<br> </p>'; # Подпись в письме
$config['email']['subjectOrder']			= 'Оформлен заказ №№ на сайте «' . $config['sitename'] . '»'; # Тема письма о заказе
$config['email']['subjectAdminOrder']		= 'Оформлен заказ №№ на сайте «' . $config['sitename'] . '»'; # Тема письма админу о заказе
$config['email']['subjectStatus']			= 'Изменение статуса заказа №№ на сайте «' . $config['sitename'] . '»'; # Тема письма об изменении статуса
$config['email']['subjectFeedback']			= 'Получено письмо на сайте «' . $config['sitename'] . '»'; # Тема письма обратной связи
$config['email']['subjectCall']				= 'Получен заказ звонка на сайте «' . $config['sitename'] . '»'; # Тема письма заказа звонка
$config['email']['subjectDownload']			= 'Получите ссылку для скачивания товара'; # Тема письма о ссылке на скачивание
$config['email']['subjectNoticeOrder']		= 'Напоминание. Вы оставили заказ...'; # Тема письма с уведомлением об оплате
$config['email']['subjectNoticeReview']		= 'Оставьте отзыв...'; # Тема письма с уведомлением об отзыве
$config['email']['subjectNoticePartner']	= 'Заработайте на нашей партнёрской программе...'; # Тема письма с уведомлением о партнёрке
$config['email']['subjectPartnerRegister']	= 'Вы зарегистрирован в партнёрской программе ' . $config['sitename']; # Тема письма о регистрации партнёра
$config['email']['subjectPincodeNotice']	= 'Осталось немного товаров на сайте!'; # Тема уведомления об остатках пин-кодов
$config['email']['confirm']					= false; # Подтверждение заказа по ссылке в письме
$config['email']['refuse']					= false; # Отказ от заказа по ссылке в письме
$config['email']['changePayment']			= false; # Изменение метода оплаты по ссылке из письма

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА БД
$config['database']['enabled']				= true; # Использовать БД?
$config['database']['host']					= 'localhost'; # Хост
$config['database']['user']					= 'root'; # Пользователь
$config['database']['pass']					= '66673506'; # Пароль
$config['database']['name']					= 'kartonocru_dev'; # Название базы

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА МЕТОДОВ ОПЛАТЫ

$config['rate']										= 1 / 50; # Курс доллара по отношению к рублю

# Отправка на почту
$config['payments']['email']['enabled']				= true; # Использовать отправку на почту?
$config['payments']['email']['title']				= 'Оплата при получении'; # Название оплаты курьеру в выборе формы оплаты
$config['payments']['email']['info']				= 'Оплата при получении курьеру или на почте'; # Описание
$config['payments']['email']['details']				= ''; # Детали (будут высланы на email)
$config['payments']['email']['free_delivery']		= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['email']['discount']			= 0; # Скидка для этого метода оплаты

# Отправка курьеру
$config['payments']['courier']['enabled']			= false;
$config['payments']['courier']['title']				= 'Наличные курьеру';
$config['payments']['courier']['info']				= 'Передать наличные курьеру после получения товара.';
$config['payments']['courier']['details']			= '';
$config['payments']['courier']['free_delivery']		= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['courier']['discount']			= 0; # Скидка для этого метода оплаты


////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА МЕТОДОВ ДОСТАВКИ

$config['deliveries']['99']['enabled']		= false; # Использовать?
$config['deliveries']['99']['title']		= 'Доставка EMS-почтой'; # Название в выпадающем меню
$config['deliveries']['99']['info']			= 'Отправляется EMS-почтой.'; # Описание
$config['deliveries']['99']['details']		= ''; # Детали (будут высланы на email)
$config['deliveries']['99']['cost']			= '0'; # Стоимость (будет добавлено к стоимости заказа)
$config['deliveries']['99']['free']			= ''; # Сумма заказа, более которой доставка будет бесплатна. Если не нужно, оставьте поле пустым

$config['deliveries']['99']['from']			= 'city--sankt-peterburg'; # Идентификатор пункта отправления
$config['deliveries']['99']['weigth']		= '1'; # Вес отправления
$config['deliveries']['99']['type']			= 'att'; # Тип международного отправления (doc или att)

$config['deliveries']['0']['enabled']		= true;
$config['deliveries']['0']['title']			= 'Курьер СДЭК';
$config['deliveries']['0']['info']			= '400 р., 2-4 дня. Подробности у оператора.';
$config['deliveries']['0']['details']		= '';
$config['deliveries']['0']['cost']			= '400';
$config['deliveries']['0']['free']			= '2800';

$config['deliveries']['1']['enabled']		= true; # Использовать?
$config['deliveries']['1']['title']			= 'Почта России'; # Название в выпадающем меню
$config['deliveries']['1']['info']			= '310 р., 5-7 дней. Подробности у оператора.'; # Описание
$config['deliveries']['1']['details']		= ''; # Детали (будут высланы на email)
$config['deliveries']['1']['cost']			= '310'; # Стоимость (будет добавлено к стоимости заказа)
$config['deliveries']['1']['free']			= '2800'; # Сумма заказа, более которой доставка будет бесплатна. Если не нужна, оставьте поле пустым

$config['deliveries']['2']['enabled']		= false;
$config['deliveries']['2']['title']			= 'ПВЗ';
$config['deliveries']['2']['info']			= '200 р., 2-4 дня. Подробности у оператора';
$config['deliveries']['2']['details']		= '';
$config['deliveries']['2']['cost']			= '200';
$config['deliveries']['2']['free']			= '2800';

$config['deliveries_view']					= false; # Если хотите, чтобы селект выводился в любом случае, поставьте true
#$config['deliveries_different_costs']		= true; # Использовать разную стоимость доставки для товаров?

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА СВЯЗКИ МЕТОДОВ ОПЛАТЫ И ДОСТАВКИ

$config['payment2delivery']['enabled']		= false; # Использовать связку?
$config['payment2delivery']['email'] 		= array('1', '99'); # Пропишите идентификаторы методов доставки в массиве
$config['payment2delivery']['courier'] 		= array('2'); # Пропишите идентификаторы методов доставки в массиве

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА СКИДОК

$config['discounts']['enabled']				= false; # Использовать накопительные скидки?
$config['discounts']['table']				= array ( 
												0 => 20,
												1000 => 0
											); # Массив значений "сумма: скидка"

$config['codes']['enabled']					= true; # Использовать купонные скидки?
$config['codes']['table']					= array (
												'promo' => 20,
												'222' => 5,
												'333' => 10
											); # Массив значений "купон: скидка"

$config['discounts']['fixed']				= false; # Использовать не процентные скидки, а фиксированные?
# Выставляйте эту настройку до начала активной работы, т.к. её изменение приведёт к пересчёту сумм по старым заказам при их редактировании.

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ПОЛЕЙ ЗАКАЗА

$config['form']['email']['label']			= 'E-mail'; # Название поля
$config['form']['email']['enabled']			= true; # Используется
$config['form']['email']['required']		= false; # Обязательное поле
$config['form']['email']['example']			= 'petrov@mail.ru'; # Пример
$config['form']['email']['empty']			= 'Введите e-mail'; # Уведомление об ошибке

$config['form']['name']['label']			= 'ФИО';
$config['form']['name']['enabled']			= true;
$config['form']['name']['required']			= true;
$config['form']['name']['example']			= 'Фамилия Имя Отчество';
$config['form']['name']['empty']			= 'Введите имя.';

$config['form']['lastname']['label']		= 'Фамилия:';
$config['form']['lastname']['enabled']		= false;
$config['form']['lastname']['required']		= false;
$config['form']['lastname']['example']		= 'Петров';
$config['form']['lastname']['empty']		= 'Введите фамилию';

$config['form']['fathername']['label']		= 'Отчество:';
$config['form']['fathername']['enabled']	= false;
$config['form']['fathername']['required']	= false;
$config['form']['fathername']['example']	= 'Петрович';
$config['form']['fathername']['empty']		= 'Введите отчество';

$config['form']['phone']['label']			= 'Телефон';
$config['form']['phone']['enabled']			= true;
$config['form']['phone']['required']		= true;
$config['form']['phone']['example']			= '89171234576';
$config['form']['phone']['empty']			= 'Введите телефон';

$config['form']['phone']['masked']			= false; # Маска включена?
$config['form']['phone']['mask']			= '+7 (999) 999 99 99'; # Маска телефона

$config['form']['zip']['label']				= 'Индекс:';
$config['form']['zip']['enabled']			= false;
$config['form']['zip']['required']			= false;
$config['form']['zip']['example']			= '141701';
$config['form']['zip']['empty']				= 'Введите индекс';

$config['form']['country']['label']			= 'Страна';
$config['form']['country']['enabled']		= false;
$config['form']['country']['required']		= false;
$config['form']['country']['example']		= 'Россия';
$config['form']['country']['empty']			= 'Введите страну';
# Выбор страны в выпадающем списке (закоментируйте, если не нужно)
$config['form']['country']['select']		= array (
											'RU' => array('Российская Федерация', '+7 (999) 999 99 99', '+7 (945) 488 33 54'),
											'UA' => array('Украина', '+38 (099) 999 99 99', '+38 (093) 346 23 53'),
											'BY' => array('Белоруссия', '+375 (99) 999 99 99', '+375 (25) 124 45 12')
											); # Ключ: код страны. Данные: название, маска ввода, пример (placeholder)

$config['form']['region']['label']			= 'Регион:';
$config['form']['region']['enabled']		= false;
$config['form']['region']['required']		= false;
$config['form']['region']['example']		= 'Московская обл.';
$config['form']['region']['empty']			= 'Введите область';

$config['form']['city']['label']			= 'Город доставки:';
$config['form']['city']['enabled']			= true;
$config['form']['city']['required']			= true;
$config['form']['city']['example']			= 'Ваш город или населенный пункт';
$config['form']['city']['empty']			= 'Введите населённый пункт';

$config['form']['address']['label']			= 'Адрес доставки';
$config['form']['address']['enabled']		= true;
$config['form']['address']['required']		= false;
$config['form']['address']['example']		= 'ул. Пушкина, д.7, кв.5';
$config['form']['address']['empty']			= 'Введите адрес';

$config['form']['comment']['label']			= 'Комментарий:';
$config['form']['comment']['enabled']		= true;
$config['form']['comment']['required']		= false;
$config['form']['comment']['example']		= 'Например, доставка в рабочее время до 18.00';
$config['form']['comment']['empty']			= 'Введите комментарий';

/// ДОПОЛНИТЕЛЬНЫЕ ПОЛЯ

$config['form']['add']['color']['label']	= 'Добавить к каждому шлему <br> крепление для головы за 150 р.?';
$config['form']['add']['color']['type']		= 'select';
$config['form']['add']['color']['select']	= array ('Нет' => 'Нет, буду держать конструкцию руками', 'Да' => 'Да, хочу, чтобы было как в Oculus!');
$config['form']['add']['color']['cost']		= array ('Нет' => 0, 'Да' => 150);
$config['form']['add']['color']['enabled']	= false;
$config['form']['add']['color']['required']	= false;
$config['form']['add']['color']['empty']	= 'Введите, пожалуйста, опцию';

$config['form']['add']['opt']['label']		= 'Добавить hands-free за 150 р.?';
$config['form']['add']['opt']['type']		= 'checkbox';
$config['form']['add']['opt']['checkbox']	= 'Включена';
$config['form']['add']['opt']['cost']		= array ('Включена' => 150);
$config['form']['add']['opt']['enabled']	= false;
$config['form']['add']['opt']['required']	= false;
$config['form']['add']['opt']['empty']		= 'Введите, пожалуйста, опцию';

$config['form']['add']['prm']['label']		= 'Ещё что-то:';
$config['form']['add']['prm']['type']		= 'input';
$config['form']['add']['prm']['cost']		= array('123' => -20);
$config['form']['add']['prm']['enabled']	= false;
$config['form']['add']['prm']['required']	= false;
$config['form']['add']['prm']['example']	= 'Что-то';
$config['form']['add']['prm']['empty']		= 'Введите, пожалуйста, ещё что-то';

$config['form']['add']['empty']				= 'Заполните все обязательные поля, пожалуйста';

////////////////////////////////////////////////////////////////////////////////
/// ДОПОЛНИТЕЛЬНЫЕ ПОЛЯ ТОВАРОВ

$config['params']['enabled']				= false; # Включить работу с уникальными параметрами товара?
$config['params']['steps']					= false; # Включить пошаговое оформление заказа?
$config['params']['disable_submit']			= false; # Включить блокировку формы при пошаговом оформлении заказа?
$config['params']['upload']['enabled']		= false; # Включить загрузку изображений для параметров?
$config['params']['upload']['dir']			= 'params'; # Папка с файлами параметров в папке files/
$config['params']['upload']['type']			= 'jpg'; # Расширение файлов изображений
$config['params']['upload']['mime']			= 'image/jpeg, image/pjpeg'; # MIME тип файла. Для удобства выбора при загрузке :)

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ПОЛЕЙ ФОРМЫ СООБЩЕНИЯ

$config['feedback']['form_type']			= 'feedback'; # Тип формы
$config['feedback']['resultURL']			= ''; # Редирект

$config['feedback']['email']['label']		= 'Email:'; # Название поля
$config['feedback']['email']['enabled']		= true; # Используется
$config['feedback']['email']['required']	= true; # Обязательное поле

$config['feedback']['name']['label']		= 'Имя:';
$config['feedback']['name']['enabled']		= true;
$config['feedback']['name']['required']		= true;

$config['feedback']['phone']['label']		= 'Телефон:';
$config['feedback']['phone']['enabled']		= true;
$config['feedback']['phone']['required']	= true;

$config['feedback']['comment']['label']		= 'Комментарий:';
$config['feedback']['comment']['enabled']	= true;
$config['feedback']['comment']['required']	= false;

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ПОЛЕЙ ФОРМЫ ЗАКАЗА ЗВОНКА

$config['call']['form_type']				= 'call'; # Тип формы
$config['call']['resultURL']				= ''; # Редирект

$config['call']['name']['label']			= 'Имя:';
$config['call']['name']['enabled']			= true;
$config['call']['name']['required']			= false;

$config['call']['phone']['label']			= 'Телефон:';
$config['call']['phone']['enabled']			= true;
$config['call']['phone']['required']		= true;

$config['call']['email']['label']			= 'Email:';
$config['call']['email']['enabled']			= false;
$config['call']['email']['required']		= false;

$config['call']['comment']['label']			= 'Комментарий:';
$config['call']['comment']['enabled']		= false;
$config['call']['comment']['required']		= false;

$config['call']['topic']['label']			= 'Тема звонка:';
$config['call']['topic']['enabled']			= false;
$config['call']['topic']['required']		= true;
$config['call']['topics']					= array ('Сделать заказ', 'Узнать наличие', 'Тех. поддержка');

$config['call']['managers']					= true; 
$config['call']['operators']				= array (
												array('name' => 'Алексей Опанасенко'),
												array('name' => 'Сергей Василец'),
												array('name' => 'Антон Остапенко')
											);

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА УВЕДОМЛЕНИЙ

$config['form']['sent']						= '<div class="success"><h3>Секундочку...</h3></div>'; # Успешная отправка заказа
$config['form']['feedback_sent']			= '<div class="success"><h2>Спасибо. Ваше сообщение отправлено</h2> <h3>Сейчас вы будете перенаправлены...</h3></div>'; # Успешная отправка фидбека
$config['form']['call_sent']				= '<div class="success"><h3>Спасибо, заказ звонка принят!</h3> <h4>Менеджер позвонит вам в течение 5-15 минут</h4></div>'; # Успешная отправка заказа звонка
$config['form']['notSent']					= 'Извините, письмо не было отправлено. Пожалуйста, повторите отправку.'; # Неудачная отправка
$config['form']['isSpam']					= 'Не спамер ли вы часом?!'; # СПАМ!
$config['form']['notMinSum']				= 'Пожалуйста, укажите любое количество, но не менее чем на ' . $config['min_sum'] . ' ' . $config['currency']; # Больше минимальной суммы!
$config['form']['emptyQty']					= 'Извините, количество нужно обязательно ввести.'; # Нет мыла!
$config['form']['emptyEmail']				= 'Извините, e-mail не введён либо его формат неверен.'; # Нет мыла!
$config['form']['emptyName']				= 'Извините, имя не введено либо его формат неверен.'; # Нет имени!
$config['form']['emptyPhone']				= 'Извините, телефон не введён либо его формат неверен.'; # Нет телефона!
$config['form']['emptyAddress']				= 'Извините, адрес не введён либо его формат неверен.'; # Нет адреса!
$config['form']['downloadSent']				= '<strong>Просто проверьте почту!</strong><br/>Письмо со ссылкой должно прийти в течении нескольких минут!'; # Ссылка отправлена
$config['form']['noUpdate']					= 'Видимо вы ещё не оплатили ваш заказ. Либо не заказывали данный товар вообще.<br> Обратитесь к администратору!'; # Обновление не удалось

////////////////////////////////////////////////////////////////////////////////
// СТАТУСЫ ЗАКАЗОВ И ЗВОНКОВ

$config['statuses']							= array (0 => 'Новый', 1 => 'Обработан', 2 => 'Отправлен', 3 => 'Доставлен', 4 => 'Оплачен', 5 => 'Возврат', 6 => 'Отменён', 7 => 'Удалён', 8 => 'Архив'); # Массив всех статусов заказа
$config['statuses']['confirmed']			= array(1); # Подтверждение заказа
$config['statuses']['sent']					= array(2); # Заказ отправлен
$config['statuses']['delivered']			= array(3); # Успешная доставка
$config['statuses']['success']				= array(4); # Успешная покупка
$config['statuses']['refund']				= array(5); # Возврат
$config['statuses']['fail']					= array(6); # Неуспешная покупка
$config['statuses']['deleted']				= array(7); # Удалён (Не будет показан в общем списке)
$config['statuses']['archive']				= array(8); # Архив (Не будет показан в общем списке)

$config['call_statuses']					= array (0 => 'Новый', 1 => 'Обработан', 2 => 'Недозвон', 3 => 'Удалён'); # Массив всех статусов звонка
$config['call_statuses']['deleted']			= array(3); # Удалён

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА АДМИНКИ

$config['admin']['login']					= 'demo'; # Логин админки
$config['admin']['password']				= '21232f297a57a5a743894a0e4a801fc3'; # Пароль админки. Хранится в зашифрованом виде. Получить шифр пароля можно здесь: http://pr-cy.ru/md5
$config['admin']['ordersList']				= '20'; # Количество заказов в списке админки
$config['admin']['productsList']			= '10'; # Количество товаров в списке админки
$config['admin']['report1List']				= '20'; # Количество строк в первом отчёте
$config['admin']['report2List']				= '14'; # Количество строк во втором отчёте
$config['admin']['partnersList']			= '20'; # Количество партнёров в списке админка
$config['admin']['callsList']				= '20'; # Количество звонков в списке админки
$config['admin']['authorsList']				= '20'; # Количество авторов в списке админки
$config['admin']['managersList']			= '20'; # Количество менеджеров в списке админки
$config['admin']['ordersListColors']		= array (0 => '#D1E8FF', 1 => '#FFFFD8', 2 => '#DDFFDD', 3 => '#B2F3C8', 4 => '#FFE1C5', 5 => '#F0DEF7', 6 => '#FFD5D5', 7 => '#FFAC97', 8 => '#FFAC97', 9 => '#C7C6C6', 10 => '#EAEAEA'); # Цвета заказов в списке

$config['admin']['orders']['name']			= true; # Выводить имя в списке заказов?
$config['admin']['orders']['email']			= false; # Выводить email в списке заказов?
$config['admin']['orders']['phone']			= true; # Выводить телефон в списке заказов?
$config['admin']['orders']['address']		= true; # Выводить адрес в списке заказов?
$config['admin']['orders']['comment']		= false; # Выводить комментарий в списке заказов?
$config['admin']['orders']['qty']			= true; # Выводить количество в списке заказов?
$config['admin']['orders']['product']		= true; # Выводить товары в списке заказов?
$config['admin']['orders']['partner']		= false; # Выводить партнёра в списке заказов?
$config['admin']['orders']['admin_comment']	= true; # Выводить комментарий администратора в списке заказов?
$config['admin']['orders']['track']			= true; # Выводить трек посылки в списке заказов?
$config['admin']['orders']['payment']		= false; # Выводить метод оплаты в списке заказов?
$config['admin']['orders']['delivery']		= true; # Выводить метод доставки в списке заказов?
$config['admin']['orders']['domain']		= false; # Выводить домен в списке заказов?

$config['admin']['productsCatMenu']			= true; # Показывать выпадающее меню с разделами товаров в меню?
$config['calls']['enabled']					= true; # Выводить звонки в админке?
$config['tags']['enabled']					= true; # Выводить теги в админке?
$config['repeats']['enabled']				= true; # Выводить дубли заказа?

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ РАБОТЫ С МЕНЕДЖЕРАМИ

$config['manager']['enabled']				= true; # Работать с менеджерами?
$config['manager']['percent']				= 5; # Процент отчислений менеджеру
$config['manager']['type']					= 'order'; # Тип выплаты. Варианты: product - все заказы товара, order - все обработанные заказы

$config['manager']['info']					= '<p>Выплаты комиссионных проходят ежемесячно в первых числах месяца.</p><p>Минимум для выплат: 10$.</p><p>Ваша комиссия с каждой продажи: ' . $config['manager']['percent'] . '%</p>'; # Информация для менеджера. Выводится на главной странице кабинета
$config['manager']['rights']['calls']		= true; # Доступ к разделу заказа звонков
$config['manager']['rights']['products']	= false; # Доступ к разделу товаров
$config['manager']['orders_link']			= true; # Доступ к списку своих заказов
$config['manager']['fix_bonus']             = 100;

# Доступ менеджера, если он один
$config['manager']['login']					= 'manager'; # Логин менеджера
$config['manager']['password']				= '1d0258c2440a8d19e716292b231e3190'; # Пароль менеджера. Хранится в зашифрованом виде. Получить шифр пароля можно здесь: http://pr-cy.ru/md5

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ПОЛЕЙ ТОВАРА

$config['product']['code']					= 'gc2.0+';
$config['product']['title']					= 'Google Cardboard 2.0+';
$config['product']['price']					= '980';
$config['product']['discount']				= '';
$config['product']['qty']					= '1';
$config['product']['unit']					= 'шт.';
$config['product']['qty_type']				= 'text';
$config['product']['qty_buttons']			= true;
$config['product']['param1']				= '';
$config['product']['param2']				= '';
$config['product']['param3']				= '';
$config['product']['description']			= '';
$config['product']['form_type']				= 'form'; # 2 варианта вывода формы: form или button

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА КОМПЛЕКТОВ ТОВАРОВ

$config['bandles']['enabled']				= false; # Использовать комплекты товаров?
$config['bandles']['products']				= 3; # Количество товаров в комплекте

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ВАРИАНТОВ ВЫВОДА ФОРМ

$config['form_types'] = array('button' => 'кнопка заказа', 'button_img' => 'кнопка заказа картинкой', 'qty_button' => 'количество + кнопка', 'form' => 'форма');

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ЦИФРОВЫХ ТОВАРОВ

$config['download']['enabled']				= false; # Загрузка файлов включена?
$config['download']['downloaders']			= false; # Докачка файлов включена?
$config['download']['link2files']			= false; # Файл прикреплён к продукту? Имеет смысл, если файлы для товаров не уникальны. Загружайте в данном случае файлы по FTP, а в админке просто выбирайте из списка. Теперь вы можете использовать один и тот же файл для нескольких товаров.
$config['download']['pincode']				= false; # Продажа пин-кодов включена? При false работает продажа обычных файлов
$config['download']['uses']					= 3; # Количество скачиваний по ссылке
$config['download']['hours']				= 24; # Количество часов действия ссылки
$config['download']['dir']					= 'files'; # Папка с файлами товаров
$config['download']['type']					= 'txt'; # Расширение файлов. При продаже пин-кодов нужно выставить текстовый файл (txt или csv)
$config['download']['mime']					= 'text/plain'; # MIME тип файла. Для удобства выбора при загрузке :)
$config['download']['min_qty']				= 5; # Количество пин-кодов, при котором высылается уведомление администратору

$config['admin']['upload']['enabled']		= false; # Загрузка файла товара из админки
$config['admin']['upload']['title']			= 'Эскиз'; # Что за файл?
$config['admin']['upload']['type']			= 'zip'; # Расширение файла
$config['admin']['upload']['mime']			= 'application/zip'; # MIME тип файла. Для удобства выбора при загрузке :)
$config['admin']['upload']['dir']			= 'admin'; # Папка внутри $config['download']['dir']

////////////////////////////////////////////////////////////////////////////////
// ДОПОЛНИТЕЛЬНЫЕ НАСТРОЙКИ ТОВАРОВ

$config['products']['base2pro']				= false; # Подхватывать код по id и выводить данные из БД
$config['button']['order']					= 'Заказать'; # Надпись кнопки заказа
$config['button']['feedback']				= 'Сообщение'; # Надпись кнопки обратной связи
$config['button']['call']					= 'Заказ звонка'; # Надпись кнопки заказа звонка

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ ОТСЛЕЖИВАНИЯ ПОСЫЛОК

$config['track']['enabled']					= true; # Включить отслеживание посылок?
$config['track']['provider']				= 'RussianPost'; # Варианты: RussianPost (Почта России) и NewPost (Новая Почта)
$config['track']['login']					= 'XTstnIBOINVDna'; # Логин для подключения для Новой почты (Почта России)
$config['track']['password']				= 'waAq8cqAYKYk'; # Пароль API (Почта России)
$config['track']['api_key']					= ''; # Ключ API (Новая почта)

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ТАРГЕТИНГА ТРАФИКА

$config['targeting']['enabled']				= false; # Гео-таргетинг	включён?
$config['targeting']['good_sources']		= array('RU', 'UA'); # Подходящие источники трафика
$config['targeting']['back']				= false; # Трафик-бэк включён?
$config['targeting']['back_url']			= 'http://neverlex.com'; # Трафик-бэк линк

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ EMAIL УВЕДОМЛЕНИЙ

$config['notice_order']['enabled']			= false; # Включить email уведомления?
$config['notice_order']['period']			= array(1); # Периоды уведомлений в днях

$config['notice_review']['enabled']			= false; # Включить email уведомления?
$config['notice_review']['period']			= array(3); # Периоды уведомлений в днях

$config['notice_partner']['enabled']		= false; # Включить email уведомления?
$config['notice_partner']['period']			= array(7); # Периоды уведомлений в днях

$config['notice']['name']					= 'Алексей Опанасенко'; # Имя отправителя
# В CRON нужно забросить такой адрес http://site.ru/jsale/modules/C_Notices.php?secret=$config['secretWord'] с периодичностью в 1 сутки

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ SMS УВЕДОМЛЕНИЙ

$config['sms']['enabled']					= true; # Включить SMS уведомления?
$config['sms']['order2admin']				= false; # Включить SMS уведомления о заказе продавцу?
$config['sms']['order2customer']			= true; # Включить SMS уведомления о заказе покупателю?
$config['sms']['paid2admin']				= false; # Включить SMS уведомления об оплате заказа продавцу?
$config['sms']['paid2customer']				= false; # Включить SMS уведомления об оплате заказа покупателю?
$config['sms']['status2customer']			= true; # Включить SMS уведомления для изменения статуса покупателю?
$config['sms']['status2customer_default']	= false; # Чекбокс SMS уведомления включён по умолчанию?
$config['sms']['call2admin']				= false; # Включить SMS уведомления о заказе звонка продавцу?
$config['sms']['call2customer']				= false; # Включить SMS уведомления о заказе звонка покупателю?
$config['sms']['trackSent2customer']		= true; # Включить SMS уведомления покупателю при отправке посылки?
$config['sms']['trackDelivered2customer']	= true; # Включить SMS уведомления покупателю при доставке посылки?

$config['sms']['provider']					= 'SMSru'; # Оператор. Варианты: AlphaSMS (укр.), SMSru (рус.), GoodSMS (рус.)
$config['sms']['api_key']					= '7955021a-9bcf-afe4-f9de-a0c2a82f0728'; # Ключ API (нужно взять в интерфейсе оператора)
$config['sms']['api_uid']					= ''; # ID-клиента (номер договора) от чьего имени осуществляется действие в API [GoodSMS]
$config['sms']['api_pid']					= ''; # ID смс-сервиса (значение доступно в профиле) [GoodSMS]
$config['sms']['phone']						= '+79174450155'; # Номер для отправки SMS в международном формате (пример: +380671234567)
$config['sms']['name']						= 'KARTONOCHKI'; # Имя отправителя (пример: SITE)
$config['sms']['translit']					= false; # Траслитерация кириллицы

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ ОТЧЁТОВ

$config['reports']['enabled']				= true; # Использовать отчёты?
$config['reports']['cost_prices']			= true; # Использовать себестоимость товаров в отчётах?

# Статистика Яндекс.Директа
$config['yandex']['enabled']				= false; # Включить учёт статистики Яндекс.Директа?
$config['yandex']['login']					= ''; # Логин, под которым создана рекламная кампания
$config['yandex']['campaign_id']			= array(''); # Массив идентификаторов рекламных кампаний
$config['yandex']['application_id']			= ''; # Идентификатор приложения
$config['yandex']['token']					= ''; # Токен
$config['yandex']['currency_rate']			= 30; # Курс доллара к валюте магазина по версии Яндекса
$config['yandex']['status_change']			= true; # Автоматическая смена статуса заказа (например, при обновлённом статусе трека Возврат, статус заказа автоматически сменится на Возврат)   

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ ПЕЧАТИ БЛАНКОВ

$config['print']['enabled']					= true; # Использовать печать?

$config['print']['form1']					= false; # Печатать форму 112эф?
$config['print']['form2']					= true; # Печатать адресный ярлык?
$config['print']['form3']					= true; # Печатать форму 112эп?
$config['print']['form4']					= false; # Печатать форму 116?
$config['print']['form5']					= false; # Печатать накладную?
$config['print']['form6']					= false; # Печатать форму 7-а
$config['print']['form7']					= true; # Печатать форму 7-п

$config['print']['fio']						= 'ИП Подковко Юлия Владимировна'; # ФИО 
$config['print']['address']					= 'г. Уфа, ул. Жукова, д. 2/8 кв. 20'; # Адрес
$config['print']['zip']						= '450073'; # Индекс
$config['print']['code']					= ''; # ИНН
$config['print']['account']					= ''; # Кор/счёт
$config['print']['bank']					= ''; # Наименование банка
$config['print']['bank_account']			= ''; # Рас/счёт банка
$config['print']['bik']						= ''; # БИК
$config['print']['phone']					= '+79174450155'; # Номер телефона
$config['print']['sms']						=  true; # SMS уведомление продавцу
$config['print']['sms2']					=  true; # SMS уведомление клиенту

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ ПАРТНЁРСКОЙ ПРОГРАММЫ

$config['partner']['enabled']				= true; # Использовать партнёрку?
$config['partner']['levels']				= 2; # Количество уровней партнёров. Варианты: 1 или 2.
$config['partner']['rate_product']			= false; # Комиссия начисляется отдельно по каждому товару?

$config['partner']['percent']['level_1']	= 10; # Процент отчислений на уровне 1
$config['partner']['percent']['level_2']	= 5; # Процент отчислений на уровне 2

$config['partner']['links']['enabled']		= true; # Использовать реф.ссылки?
$config['partner']['links']['reload']		= true; # Перенаправлять клиента на страницу без рефки?
$config['partner']['codes']['enabled']		= true; # Использовать промо-коды?
$config['partner']['codes']['auto']			= false; # Подставлять промо-код партнёра после перехода по реф.ссылке?
$config['partner']['forms']['enabled']		= false; # Использовать форму? !!! ФУНКЦИЯ ПОКА НЕ РАБОТАЕТ !!!

$config['partner']['discount']['enabled']	= false; # Разрешить изменять скидку? !!! ФУНКЦИЯ ПОКА НЕ РАБОТАЕТ !!!
$config['partner']['discount']['percent']	= 0; # Скидка привлечённым клиентам. Чтобы убрать скидку, просто поставьте 0

$config['partner']['invites']				= false; # Регистрация в партнёрке только по инвайтам?
$config['partner']['admin_invite']			= 'FFF'; # Админский инвайт

$config['partner']['info']					= '<p>Выплаты комиссионных проходят ежемесячно в первых числах месяца.</p><p>Минимум для выплат: 10$.</p><p>Ваша комиссия с каждой продажи: ' . $config['partner']['percent']['level_1'] . '%</p>'; # Информация для партнёра. Выводится на главной странице партнёрского кабинета
if ($config['partner']['levels'] == 2)
	$config['partner']['info'] .= '<p>Также вы получите ' . $config['partner']['percent']['level_2'] . '% со всех продаж партнёров, которых привлечёте.</p>'; # В случае 2-уровневой партнёрки
	
////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ РАБОТЫ С АВТОРАМИ

$config['author']['enabled']				= false; # Работать с авторами?
$config['author']['percent']				= 40; # Процент авторских отчислений
$config['author']['info']					= '<p>Выплаты комиссионных проходят ежемесячно в первых числах месяца.</p><p>Минимум для выплат: 10$.</p><p>Ваша комиссия с каждой продажи: ' . $config['author']['percent'] . '%</p>'; # Информация для партнёра. Выводится на главной странице партнёрского кабинета
	
////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА РАБОТЫ С ОСТАТКАМИ

$config['store']['enabled']					= true; # Работать с остатками?
$config['store']['decrease_order']			= false; # Изменять количество при заказе?
$config['store']['decrease_paid']			= true; # Изменять количество при оплате заказа?
$config['store']['notice']['enabled']		= false; # Выводить предупреждение, если количество товара не достаточно?
$config['store']['notice']['text']			= 'Количество товара на складе недостаточно для вашего заказа. Вы можете отправить предварительный заказ.'; # Текст предупреждения

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА РАБОТЫ С РАССЫЛКАМИ SMARTRESPONDER

$config['smart']['enabled'] 				= false; # Работать со SmartResponder?
$config['smart']['api_id']					= ''; # ID API ("Настройки" -> "настройки вашего аккаунта" -> "API")
$config['smart']['api_key']					= ''; # Ключ API ("Настройки" -> "настройки вашего аккаунта" -> "API")
$config['smart']['list_id']					= 1; # ID рассылки
$config['smart']['group_id']				= 1; # ID группы клиентов
$config['smart']['track_id']				= 1; # ID канала подписчиков

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА АПСЕЛОВ

$config['upsells']['enabled']				= true; # Использовать апсел?
$config['upsells']['page']					= true; # Апселы на отдельной странице?

# Описание каждого апсела
$config['upsell']['1']['notice_title']		= 'Уникальное предложение!'; # Заголовок предложения
$config['upsell']['1']['notice_text']		= 'Только сегодня! Только сейчас!<br>Купите ещё одну классную штуку! По скидке конечно!<br>Ваш дисконт - 50%!'; # Текст предложения
$config['upsell']['1']['code']				= 'upsell';
$config['upsell']['1']['title']				= 'Супер товар!';
$config['upsell']['1']['price']				= '300';
$config['upsell']['1']['discount']			= '50';
$config['upsell']['1']['qty']				= '1';
$config['upsell']['1']['unit']				= 'шт.';
$config['upsell']['1']['qty_type']			= 'text';
$config['upsell']['1']['param1']			= '';
$config['upsell']['1']['param2']			= '';
$config['upsell']['1']['param3']			= '';
$config['upsell']['1']['description']		= '';

$config['upsell']['2']['notice_title']		= 'Супер предложение!'; # Заголовок предложения
$config['upsell']['2']['notice_text']		= 'Только сегодня! Только сейчас!<br>Купите ещё одну классную штуку! По скидке конечно!<br>Ваш дисконт - 80%!'; # Текст предложения
$config['upsell']['2']['code']				= 'upsell2';
$config['upsell']['2']['title']				= 'Супер товар 2!';
$config['upsell']['2']['price']				= '300';
$config['upsell']['2']['discount']			= '80';
$config['upsell']['2']['qty']				= '1';
$config['upsell']['2']['unit']				= 'шт.';
$config['upsell']['2']['qty_type']			= 'text';
$config['upsell']['2']['param1']			= '';
$config['upsell']['2']['param2']			= '';
$config['upsell']['2']['param3']			= '';
$config['upsell']['2']['description']		= '';

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА БОНУСОВ

$config['bonuses']['enabled']				= true; # Использовать бонусы?

# Описание каждого бонуса
$config['bonus']['1']['code']				= 'bonus';
$config['bonus']['1']['title']				= 'Отличная книга!';
$config['bonus']['1']['price']				= '0';
$config['bonus']['1']['discount']			= '0';
$config['bonus']['1']['qty']				= '1';
$config['bonus']['1']['unit']				= 'шт.';
$config['bonus']['1']['param1']				= '';
$config['bonus']['1']['param2']				= '';
$config['bonus']['1']['param3']				= '';

$config['bonus']['2']['code']				= 'bonus2';
$config['bonus']['2']['title']				= 'Отличная книга 2!';
$config['bonus']['2']['price']				= '0';
$config['bonus']['2']['discount']			= '0';
$config['bonus']['2']['qty']				= '1';
$config['bonus']['2']['unit']				= 'кг.';
$config['bonus']['2']['param1']				= '';
$config['bonus']['2']['param2']				= '';
$config['bonus']['2']['param3']				= '';

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ДЕДЛАЙНОВ

$config['deadlines']['enabled']				= false; # Использовать дедлайны?
$config['deadlines']['unique_link']			= false; # Использовать дедлайны для уникальных ссылок?

# Описание каждого дедлайна
$config['deadline']['1']['param']			= 'aaa'; # Параметр для уникализации клиента.
$config['deadline']['1']['type']			= 'discount'; # Тип дедлайна. Варианты: discount (дедлайн скидки) и order (дедлайн заказа)
$config['deadline']['1']['interval']		= 3600 * 12; # Количество секунд до конца отсчёта (более приоритетно)
$config['deadline']['1']['time']			= '2013-12-31 23:00:00'; # Конечное время (учитывается только при нулевом интервале)
$config['deadline']['1']['css']				= 'border: 1px solid #f00; color: #f00; padding: 20px; text-align: center; font-weight: bold; background: #ff0;'; # Оформление счётчика
$config['deadline']['1']['title']			= 'Дедлайн 1'; # Описание в админке

$config['deadline']['2']['param']			= 'bbb'; # Параметр для уникализации клиента.
$config['deadline']['2']['type']			= 'discount'; # Тип дедлайна. Варианты: discount (дедлайн скидки) и order (дедлайн заказа)
$config['deadline']['2']['interval']		= 3600 * 48; # Количество секунд до конца отсчёта (более приоритетно)
$config['deadline']['2']['time']			= '2013-12-31 23:00:00'; # Конечное время (учитывается только при нулевом интервале)
$config['deadline']['2']['css']				= 'border: 1px solid #f00; color: #f00; padding: 20px; text-align: center; font-weight: bold; background: #ff0;'; # Оформление счётчика
$config['deadline']['2']['title']			= 'Дедлайн 2'; # Описание в админке

# Настройки массовых дедлайнов
$config['deadline']['mass']['param']		= 'mass'; #
$config['deadline']['mass']['type']			= 'discount'; # Тип дедлайна. Варианты: discount (дедлайн скидки) и order (дедлайн заказа)
$config['deadline']['mass']['interval']		= 3600 * 1; # Количество секунд до конца отсчёта (более приоритетно)
$config['deadline']['mass']['time']			= '2013-12-31 23:00:00'; # Конечное время (учитывается только при нулевом интервале)
$config['deadline']['mass']['css']			= 'border: 1px solid #f00; color: #f00; padding: 20px; text-align: center; font-weight: bold; background: #ff0;'; # Оформление счётчика

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ВРЕМЕННОЙ ЗОНЫ СЕРВЕРА

# Все зоны: http://www.php.net/manual/ru/timezones.php
date_default_timezone_set('Asia/Yekaterinburg');

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ОТПРАВКИ EMAIL С ПОМОЩЬЮ SMTP

$config['email']['smtp']['enabled']			= false; # Включить отправку с помощью SMTP
$config['email']['smtp']['host']			= 'smtp.gmail.com'; # Хост
$config['email']['smtp']['debug']			= 1;
$config['email']['smtp']['auth']			= true;
$config['email']['smtp']['port']			= 587; # Порт. Для gmail подойдёт 587 порт
$config['email']['smtp']['encoding']		= 'utf-8'; # Кодировка
$config['email']['smtp']['username']		= ''; # Имя пользователя (адрес почты). Очень желательно, чтобы $config['email']['answer'] был идентичным
$config['email']['smtp']['password']		= ''; # Пароль

////////////////////////////////////////////////////////////////////////////////
// ПОШАГОВЫЙ ЗАКАЗ
#$config['steps']['enabled']					= true; # Пошаговый режим включён
#$config['step2']['page']					= 'http://jsale.biz/jsale/step.php'; # Ссылка на прокладку

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКИ API
$config['api']['enabled']					= false; # Включить работу с API?
$config['api']['key']						= 'FVDSJKDF'; # Ключ API. Нужно указать в запросе

////////////////////////////////////////////////////////////////////////////////
// СЛУЖЕБНЫЕ ФУНКЦИИ. ТРОГАТЬ НЕ НУЖНО.

# Отключение функций при отсутствии соответствующих модулей
if (!is_file(dirname(__FILE__) . '/modules/C_Payment1.php'))
	$config['payments']['robokassa']['enabled'] = $config['payments']['interkassa']['enabled'] = $config['payments']['liqpay']['enabled'] = $config['payments']['qiwi']['enabled'] = $config['payments']['rbkmoney']['enabled'] = $config['payments']['webmoney']['enabled'] = $config['payments']['webmoney2']['enabled'] = $config['payments']['yandex']['enabled'] = $config['payments']['yandex2']['enabled'] = $config['payments']['yandex_eshop']['enabled'] = $config['payments']['paypal']['enabled'] = $config['payments']['paypal2']['enabled'] = $config['payments']['w1']['enabled'] = $config['payments']['privat24']['enabled'] = $config['payments']['paysera']['enabled'] = $config['payments']['paybox']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/modules/M_Discounts.inc.php'))
	$config['discounts']['enabled'] = $config['codes']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/modules/M_Partner.inc.php'))
	$config['partner']['enabled'] = false;

if (!is_file(dirname(__FILE__) . '/modules/M_Products.inc.php'))	
	$config['admin']['productsCatMenu'] = $config['store']['enabled'] = $config['params']['enabled'] = $config['bandles']['enabled'] = $config['products']['base2pro'] = false;

if (!is_file(dirname(__FILE__) . '/modules/M_Authors.inc.php'))
	$config['author']['enabled'] = false;

if (!is_file(dirname(__FILE__) . '/modules/C_SMS.inc.php'))
	$config['sms']['enabled'] = false;

if (!is_file(dirname(__FILE__) . '/admin/managers.php'))
	$config['manager']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/admin/report_1.php') || !is_file(dirname(__FILE__) . '/admin/report_2.php'))
	$config['reports']['enabled'] = false;

if (!is_file(dirname(__FILE__) . '/modules/C_Track.inc.php'))
	$config['track']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/modules/M_Files.inc.php'))
	$config['download']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/modules/C_Deadline.inc.php'))
	$config['deadlines']['enabled'] = $config['bonuses']['enabled'] = $config['upsells']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/modules/C_Targeting.inc.php'))
	$config['targeting']['enabled'] = false;

if (!is_file(dirname(__FILE__) . '/print/print.php'))
	$config['print']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/modules/C_SmartResponder.inc.php'))
	$config['smart']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/modules/C_API.php'))
	$config['api']['enabled'] = false;
	
if (!is_file(dirname(__FILE__) . '/modules/C_Notices.php'))
	$config['notice_order']['enabled'] = $config['notice_review']['enabled'] = $config['notice_partner']['enabled'] = false;

# Удаление отключённых методов оплаты и доставки
$config['disabled_payments'] = $config['disabled_deliveries'] = array();
foreach ($config['payments'] as $key => $type)
	if ($type['enabled'] == false)
	{
		$config['disabled_payments'][$key] = $type;
		unset($config['payments'][$key]);
	}

foreach ($config['deliveries'] as $key => $type)
	if ($type['enabled'] == false)
	{
		$config['disabled_deliveries'][$key] = $type;
		unset($config['deliveries'][$key]);
	}