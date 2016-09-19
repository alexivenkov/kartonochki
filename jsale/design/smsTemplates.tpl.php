<?/*
{{id_order}} - 
{{status}}
{{date}}
{{time}}
{{datetime}}
{{email}}
{{phone}}
{{sum}}
{{product}}
{{price}}
{{qty}}
{{name}}
{{lastname}}
{{sitelink}}
{{currency}}
{{track_id}}
*/?>
<? if ($sms_type == 'order2admin'): ?>
Новый заказ №{{id_order}} на сумму {{sum}} {{currency}}. {{phone}}
<? elseif ($sms_type == 'paid2admin'): ?>
Заказ #{{id_order}} на сумму {{sum}} {{currency}} оплачен
<? elseif ($sms_type == 'order2customer'): ?>
Здравствуйте! Ваш заказ №{{id_order}} на сумму {{sum}} {{currency}} принят в работу! Ожидайте звонка оператора. 88002001599 <!--107 символов-->
<? elseif ($sms_type == 'paid2customer'): ?>
Ваш заказ #{{id_order}} на сумму {{sum}} {{currency}} оплачен
<? elseif ($sms_type == 'status2customer'): ?>
Заказу №{{id_order}} присвоен статус {{status}}
<? elseif ($sms_type == 'call2admin'): ?>
Заказ звонка добавлен на сайт {{sitelink}}
<? elseif ($sms_type == 'call2customer'): ?>
Заказ звонка добавлен на сайт {{sitelink}}
<? elseif ($sms_type == 'trackSent2customer'): ?>
Здравствуйте! Ваш заказ №{{id_order}} отправлен, трек-номер {{track_id}}. Отследить отправление вы можете здесь: www.pochta.ru/tracking <!--129 символов-->
<? elseif ($sms_type == 'trackDelivered2customer'): ?>
Здравствуйте! Ваш заказ доставлен в почтовое отделение {{zip}}. Номер отправления {{track_id}}. Сумма {{sum}} {{currency}} Не забудьте паспорт <!--~135 символов-->
<? else: ?>
false
<? endif; ?>