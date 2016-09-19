<?php

# jSale v1.431
# http://jsale.biz

$admin = (isset($admin) && $admin === true) ? true : false;

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА ПОЛЕЙ ЗАКАЗА

$config['form']['email']['label']			= 'E-mail'; # Название поля
$config['form']['email']['enabled']			= true; # Используется
$config['form']['email']['required']		= true; # Обязательное поле
$config['form']['email']['example']			= 'petrov@mail.ru'; # Пример
$config['form']['email']['empty']			= 'Введите e-mail'; # Уведомление об ошибке

$config['form']['name']['label']			= 'Имя';
$config['form']['name']['enabled']			= true;
$config['form']['name']['required']			= true;
$config['form']['name']['example']			= 'Петр';
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
$config['form']['phone']['example']			= '+7 (945) 488 33 54';
$config['form']['phone']['empty']			= 'Введите телефон';

$config['form']['phone']['masked']			= true; # Маска включена?
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
$config['form']['city']['enabled']			= false;
$config['form']['city']['required']			= false;
$config['form']['city']['example']			= 'г. Долгопрудный';
$config['form']['city']['empty']			= 'Введите населённый пункт';

$config['form']['address']['label']			= 'Адрес доставки';
$config['form']['address']['enabled']		= true;
$config['form']['address']['required']		= false;
$config['form']['address']['example']		= 'ул. Пушкина, д.7, кв.5';
$config['form']['address']['empty']			= 'Введите адрес';

$config['form']['comment']['label']			= 'Комментарий:';
$config['form']['comment']['enabled']		= false;
$config['form']['comment']['required']		= false;
$config['form']['comment']['example']		= 'Текст';
$config['form']['comment']['empty']			= 'Введите комментарий';

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА МЕТОДОВ ОПЛАТЫ

$config['rate']										= 1 / 50; # Курс доллара по отношению к рублю

# Отправка на почту
$config['payments']['email']['enabled']				= ($admin === true) ? true : false; # Использовать отправку на почту?
$config['payments']['email']['title']				= 'Наложенный платеж'; # Название оплаты курьеру в выборе формы оплаты
$config['payments']['email']['info']				= 'Наложенный платеж - оплата при получении.'; # Описание
$config['payments']['email']['details']				= ''; # Детали (будут высланы на email)
$config['payments']['email']['free_delivery']		= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['email']['discount']			= 0; # Скидка для этого метода оплаты

# Отправка курьеру
$config['payments']['courier']['enabled']			= true;
$config['payments']['courier']['title']				= 'Наличные курьеру';
$config['payments']['courier']['info']				= 'Передать наличные курьеру после получения товара.';
$config['payments']['courier']['details']			= '';
$config['payments']['courier']['free_delivery']		= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['courier']['discount']			= 0; # Скидка для этого метода оплаты

# Настройки RoboKassa
$config['payments']['robokassa']['enabled']			= ($admin === true) ? true : false;    # Использовать RoboKassa?
$config['payments']['robokassa']['title']			= 'Оплата на сайте с помощью RoboKassa';    # Название онлайн платежей в выборе формы оплаты
$config['payments']['robokassa']['info']			= 'Электронные и мобильные платежи, карты, интернет-банкинг, терминалы...'; # Описание
$config['payments']['robokassa']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['robokassa']['free_delivery']	= true; # Бесплатная доставка для этого метода оплаты?
$config['payments']['robokassa']['discount']		= 0; # Скидка для этого метода оплаты

$config['payments']['robokassa']['login']			= '';   # Логин в Робокассе
$config['payments']['robokassa']['pass1']			= '';   # Пароль 1 в Робокассе
$config['payments']['robokassa']['pass2']			= '';   # Пароль 2 в Робокассе
$config['payments']['robokassa']['description']		= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты
$config['payments']['robokassa']['test']			= true; # Тестовый режим?

# Настройка Yandex.Money для юр.лиц
$config['payments']['yandex_eshop']['enabled']		= ($admin === true) ? true : false; # Использовать Yandex.Money для приёма оплаты банковской картой
$config['payments']['yandex_eshop']['title']		= 'Оплата онлайн'; # Название в выборе формы оплаты
$config['payments']['yandex_eshop']['info']			= 'Оплата одним из множества методов'; # Описание
$config['payments']['yandex_eshop']['details']		= ''; # Детали (будут высланы на email)
$config['payments']['yandex_eshop']['free_delivery']= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['yandex_eshop']['discount']		= 0; # Скидка для этого метода оплаты

$config['payments']['yandex_eshop']['shop_id']		= ''; # Идентификатор магазина
$config['payments']['yandex_eshop']['scid']			= ''; # Идентификатор товара
$config['payments']['yandex_eshop']['types']		= array(
														'PC' => 'Оплата с кошелька в Яндекс.Деньгах',
														'AC' => 'Оплата с произвольной банковской карты',
														'MC' => 'Платеж со счета мобильного телефона',
														'GP' => 'Оплата наличными через кассы и терминалы',
														'WM' => 'Оплата из кошелька в системе WebMoney',
														'SB' => 'Оплата через Сбербанк Онлайн',
														'MP' => 'Оплата через мобильный терминал (mPOS)',
														'AB' => 'Оплата через Альфа-Клик',
														'SB' => 'Сбербанк: оплата по SMS или Сбербанк Онлайн'
													); # Виды оплаты

$config['payments']['yandex_eshop']['secret']		= ''; # Секретный ключ
$config['payments']['yandex_eshop']['description']	= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты

# Настройка LiqPay
$config['payments']['liqpay']['enabled']			= ($admin === true) ? true : false;    # Использовать платежи по безналу?
$config['payments']['liqpay']['title']				= 'Оплата картой с помощью LiqPay';  # Название платежей банковской картой в выборе формы оплаты
$config['payments']['liqpay']['info']				= 'Моментальные платежи банковскими картами'; # Описание
$config['payments']['liqpay']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['liqpay']['free_delivery']		= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['liqpay']['discount']			= 0; # Скидка для этого метода оплаты

$config['payments']['liqpay']['description']		= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты
$config['payments']['liqpay']['id']					= '';    # ID мерчанта в LiqPay
$config['payments']['liqpay']['sign']				= '';   # Подпись

# Настройки InterKassa
$config['payments']['interkassa']['enabled']		= ($admin === true) ? true : false;    # Использовать InterKassa?
$config['payments']['interkassa']['title']			= 'Оплата на сайте с помощью InterKassa';    # Название онлайн платежей в выборе формы оплаты
$config['payments']['interkassa']['info']			= 'Электронные деньги, банковские карты, переводы, терминалы...'; # Описание
$config['payments']['interkassa']['details']		= ''; # Детали (будут высланы на email)
$config['payments']['interkassa']['free_delivery']	= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['interkassa']['discount']		= 0; # Скидка для этого метода оплаты

$config['payments']['interkassa']['shop_id']        = '';   # Идентификатор магазина
$config['payments']['interkassa']['secret_key']     = '';   # Cекретный ключ
$config['payments']['interkassa']['description']    = 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты
$config['payments']['interkassa']['test_key']		= '';   # Тестовый ключ

# Настройка RBKmoney
$config['payments']['rbkmoney']['enabled']			= ($admin === true) ? true : false; # Использовать RBKmoney
$config['payments']['rbkmoney']['title']			= 'Оплата на сайте с помощью RBKmoney'; # Название в выборе формы оплаты
$config['payments']['rbkmoney']['info']				= 'Карты Visa / MasterCard, переводы СONTACT, Почта России, интернет-банкинг, банковские платежи по квитанции, платежные терминалы...'; # Описание
$config['payments']['rbkmoney']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['rbkmoney']['free_delivery']	= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['rbkmoney']['discount']			= 0; # Скидка для этого метода оплаты

$config['payments']['rbkmoney']['shop_id']			= ''; # Идентификатор магазина в RBK
$config['payments']['rbkmoney']['secret_key']		= ''; # Cекретный ключ
$config['payments']['rbkmoney']['description']		= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты

# Настройка QIWI
$config['payments']['qiwi']['enabled']				= ($admin === true) ? true : false; # Использовать QIWI
$config['payments']['qiwi']['title']				= 'Оплата на сайте с помощью QIWI'; # Название в выборе формы оплаты
$config['payments']['qiwi']['info']					= 'Оплата с помощью QIWI кошелька'; # Описание
$config['payments']['qiwi']['details']				= ''; # Детали (будут высланы на email)
$config['payments']['qiwi']['free_delivery']		= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['qiwi']['discount']				= 0; # Скидка для этого метода оплаты

$config['payments']['qiwi']['shop_id']				= ''; # Логин магазина в QIWI
$config['payments']['qiwi']['rest_id']				= ''; # ID REST
$config['payments']['qiwi']['rest_password']		= ''; # Пароль REST
$config['payments']['qiwi']['notice_password']		= ''; # Пароль для оповещения
$config['payments']['qiwi']['description']			= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты

# Настройка Webmoney
$config['payments']['webmoney']['enabled']			= ($admin === true) ? true : false; # Использовать Webmoney
$config['payments']['webmoney']['title']			= 'Оплата на сайте с помощью Webmoney (WMR)'; # Название в выборе формы оплаты
$config['payments']['webmoney']['info']				= 'Оплата с помощью Webmoney в рублях'; # Описание
$config['payments']['webmoney']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['webmoney']['free_delivery']	= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['webmoney']['discount']			= 0; # Скидка для этого метода оплаты

$config['payments']['webmoney']['purse']			= ''; # Кошелёк WM (в валюте магазина)
$config['payments']['webmoney']['secret']			= ''; # Секретный ключ
$config['payments']['webmoney']['description']		= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты

# Настройка Webmoney
$config['payments']['webmoney2']['enabled']			= ($admin === true) ? true : false; # Использовать Webmoney
$config['payments']['webmoney2']['title']			= 'Оплата на сайте с помощью Webmoney (WMZ)'; # Название в выборе формы оплаты
$config['payments']['webmoney2']['info']			= 'Оплата с помощью Webmoney в долларах'; # Описание
$config['payments']['webmoney2']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['webmoney2']['free_delivery']	= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['webmoney2']['discount']		= 0; # Скидка для этого метода оплаты

$config['payments']['webmoney2']['purse']			= ''; # Кошелёк WM (в валюте магазина)
$config['payments']['webmoney2']['secret']			= ''; # Секретный ключ
$config['payments']['webmoney2']['description']		= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты
$config['payments']['webmoney2']['rate']			= $config['rate']; # Курс по отношению к валюте магазина
$config['payments']['webmoney2']['currency']		= '$'; # Валюта

# Настройка Yandex.Money
$config['payments']['yandex']['enabled']			= ($admin === true) ? true : false; # Использовать Yandex.Money
$config['payments']['yandex']['title']				= 'Оплата на сайте с помощью Яндекс.Денег'; # Название в выборе формы оплаты
$config['payments']['yandex']['info']				= 'Оплата с помощью Яндекс.Денег'; # Описание
$config['payments']['yandex']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['yandex']['free_delivery']		= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['yandex']['discount']			= 0; # Скидка для этого метода оплаты

$config['payments']['yandex']['purse']				= ''; # Кошелёк Яндекс.Денег
$config['payments']['yandex']['shop_id']			= ''; # Идентификатор приложения. Создать здесь: https://sp-money.yandex.ru/myservices/new.xml Просмотреть существующие здесь: https://sp-money.yandex.ru/tunes.xml
$config['payments']['yandex']['token']				= ''; # Токен

$config['payments']['yandex']['secret']				= ''; # Секретный ключ. Брать здесь https://sp-money.yandex.ru/myservices/online.xml
$config['payments']['yandex']['description']		= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты
$config['payments']['yandex']['currency']			= 'руб.'; # Валюта

# Настройка Yandex.Money для приёма карт
$config['payments']['yandex2']['enabled']			= ($admin === true) ? true : false; # Использовать Yandex.Money для приёма карт
$config['payments']['yandex2']['title']				= 'Оплата на сайте с помощью карты'; # Название в выборе формы оплаты
$config['payments']['yandex2']['info']				= 'Оплата с помощью сервиса Яндекс.Деньги'; # Описание
$config['payments']['yandex2']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['yandex2']['free_delivery']		= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['yandex2']['discount']			= 0; # Скидка для этого метода оплаты

$config['payments']['yandex2']['purse']				= ''; # Кошелёк Яндекс.Денег
$config['payments']['yandex2']['shop_id']			= ''; # Идентификатор приложения. Создать здесь: https://sp-money.yandex.ru/myservices/new.xml Просмотреть существующие здесь: https://sp-money.yandex.ru/tunes.xml
$config['payments']['yandex2']['token']				= ''; # Токен
#$config['payments']['yandex2']['rate']				= $config['rate']; # Курс по отношению к валюте магазина

$config['payments']['yandex2']['secret']			= ''; # Секретный ключ. Брать здесь https://sp-money.yandex.ru/myservices/online.xml
$config['payments']['yandex2']['description']		= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты

# Настройки PayPal (руб.)
$config['payments']['paypal2']['enabled']			= ($admin === true) ? true : false; # Использовать PayPal?
$config['payments']['paypal2']['title']				= 'Оплата с помощью PayPal (руб.)'; # Название онлайн платежей в выборе формы оплаты
$config['payments']['paypal2']['info']				= 'Оплата картой.'; # Описание
$config['payments']['paypal2']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['paypal2']['free_delivery']		= true; # Бесплатная доставка для этого метода оплаты?
$config['payments']['paypal2']['discount']			= 0; # Скидка для этого метода оплаты

$config['payments']['paypal2']['receiver_email']	= '';   # Email аккаунта PayPal
$config['payments']['paypal2']['description']		= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты
$config['payments']['paypal2']['test']				= false; # Тестовый режим?

$config['payments']['paypal2']['rate']				= 1; # Курс по отношению к валюте магазина
$config['payments']['paypal2']['currency']			= 'руб.'; # Валюта
$config['payments']['paypal2']['currencyCode']		= 'RUB'; # Валюта

# Настройки PayPal (дол.)
$config['payments']['paypal']['enabled']			= ($admin === true) ? true : false; # Использовать PayPal?
$config['payments']['paypal']['title']				= 'Оплата с помощью PayPal (дол.)'; # Название онлайн платежей в выборе формы оплаты
$config['payments']['paypal']['info']				= 'Оплата картой.'; # Описание
$config['payments']['paypal']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['paypal']['free_delivery']		= true; # Бесплатная доставка для этого метода оплаты?
$config['payments']['paypal']['discount']			= 0; # Скидка для этого метода оплаты

$config['payments']['paypal']['receiver_email']		= '';   # Email аккаунта PayPal
$config['payments']['paypal']['description']		= '';   # Описание оплаты
$config['payments']['paypal']['test']				= true; # Тестовый режим?

$config['payments']['paypal']['rate']				= $config['rate']; # Курс по отношению к валюте магазина
$config['payments']['paypal']['currency']			= '$'; # Валюта
$config['payments']['paypal']['currencyCode']		= 'USD'; # Валюта

# Настройка W1
$config['payments']['w1']['enabled']				= ($admin === true) ? true : false; # Использовать W1
$config['payments']['w1']['title']					= 'Оплата на сайте с помощью Единого Кошелька'; # Название в выборе формы оплаты
$config['payments']['w1']['info']					= 'Единый кошелёк, карты Visa/MasterCard, Почта России, интернет-банкинг, банковские платежи, платежные терминалы...'; # Описание
$config['payments']['w1']['details']				= ''; # Детали (будут высланы на email)
$config['payments']['w1']['free_delivery']			= false; # Бесплатная доставка для этого метода оплаты?
$config['payments']['w1']['discount']				= 0; # Скидка для этого метода оплаты

$config['payments']['w1']['shop_id']				= ''; # Идентификатор магазина в W1
$config['payments']['w1']['secret_key']				= ''; # Cекретный ключ
$config['payments']['w1']['description']			= 'Оплата покупки в магазине «' . $config['sitename'] . '»';   # Описание оплаты 

# Настройки Приват24
$config['payments']['privat24']['enabled']			= ($admin === true) ? true : false; # Использовать Приват24?
$config['payments']['privat24']['title']			= 'Оплата с помощью Приват24'; # Название онлайн платежей в выборе формы оплаты
$config['payments']['privat24']['info']				= 'Оплата с помощью системы платежей Приват24.'; # Описание
$config['payments']['privat24']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['privat24']['free_delivery']	= true; # Бесплатная доставка для этого метода оплаты?
$config['payments']['privat24']['discount']			= 0; # Скидка для этого метода оплаты

$config['payments']['privat24']['id_merchant']		= '';   # ID мерчанта
$config['payments']['privat24']['secret_key']		= '';   # Пароль мерчанта
$config['payments']['privat24']['description']		= 'Оплата покупки в магазине';   # Описание оплаты

$config['payments']['privat24']['rate']				= 1 / 3; # Курс по отношению к валюте магазина
$config['payments']['privat24']['currency']			= 'грн.'; # Валюта
$config['payments']['privat24']['currencyCode']		= 'UAH'; # Валюта

# Настройки Paysera
$config['payments']['paysera']['enabled']			= ($admin === true) ? true : false; # Использовать Paysera
$config['payments']['paysera']['title']				= 'Оплата с помощью Paysera'; # Название онлайн платежей в выборе формы оплаты
$config['payments']['paysera']['info']				= 'Различные методы оплаты.'; # Описание
$config['payments']['paysera']['details']			= ''; # Детали (будут высланы на email)
$config['payments']['paysera']['free_delivery']		= true; # Бесплатная доставка для этого метода оплаты?

$config['payments']['paysera']['project_id']		= '';   # ID проекта в Paysera
$config['payments']['paysera']['password']			= '';   # Пароль
$config['payments']['paysera']['description']		= '';   # Описание оплаты
$config['payments']['paysera']['test']				= true; # Тестовый режим?

$config['payments']['paysera']['rate']				= 1; # Курс по отношению к валюте магазина
$config['payments']['paysera']['currency']			= 'руб.'; # Валюта
$config['payments']['paysera']['currencyCode']		= 'RUB'; # Валюта

////////////////////////////////////////////////////////////////////////////////
// НАСТРОЙКА МЕТОДОВ ДОСТАВКИ

$config['deliveries']['1']['enabled']		= ($admin === true) ? true : false; # Использовать?
$config['deliveries']['1']['title']			= 'Доставка почтой'; # Название в выпадающем меню
$config['deliveries']['1']['info']			= 'Отправляется посылкой. Cтоимость зависит из расценок почты.'; # Описание
$config['deliveries']['1']['details']		= ''; # Детали (будут высланы на email)
$config['deliveries']['1']['cost']			= '0'; # Стоимость (будет добавлено к стоимости заказа)
$config['deliveries']['1']['free']			= ''; #Сумма заказа, более которой доставка будет бесплатна. Если не нужна, оставьте поле пустым

$config['deliveries']['2']['enabled']		= ($admin === true) ? true : false;
$config['deliveries']['2']['title']			= 'Доставка курьером';
$config['deliveries']['2']['info']			= 'Доставка курьерской службой. +200 руб. к заказу.';
$config['deliveries']['2']['details']		= '';
$config['deliveries']['2']['cost']			= '200';
$config['deliveries']['2']['free']			= ''; #Сумма заказа, более которой доставка будет бесплатна. Если не нужна, оставьте поле пустым

$config['deliveries']['3']['enabled']		= true; # Использовать?
$config['deliveries']['3']['title']			= 'Скачивание'; # Название в выпадающем меню
$config['deliveries']['3']['info']			= 'Ссылка для скачивания приходит на почту после оплаты.'; # Описание
$config['deliveries']['3']['details']		= ''; # Детали (будут высланы на email)
$config['deliveries']['3']['cost']			= '0'; # Стоимость (будет добавлено к стоимости заказа)
$config['deliveries']['3']['free']			= ''; #Сумма заказа, более которой доставка будет бесплатна. Если не нужна, оставьте поле пустым

////////////////////////////////////////////////////////////////////////////////
// СЛУЖЕБНЫЕ ФУНКЦИИ

# Удаление отключённых методов оплаты и доставки
foreach ($config['payments'] as $key => $type)
	if ($type['enabled'] == false)
		unset($config['payments'][$key]);

foreach ($config['deliveries'] as $key => $type)
	if ($type['enabled'] == false)
		unset($config['deliveries'][$key]);