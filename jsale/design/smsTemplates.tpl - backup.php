<?/*
{{id_order}} - 
{{status}}
{{date}}
{{time}}
{{datetime}}
{{track_id}}
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
Заказ #{{id_order}} на сумму {{sum}} {{currency}} добавлен на сайт {{sitelink}}
<? elseif ($sms_type == 'paid2admin'): ?>
Заказ #{{id_order}} на сумму {{sum}} {{currency}} оплачен
<? elseif ($sms_type == 'order2customer'): ?>
Заказ #{{id_order}} на сумму {{sum}} {{currency}} добавлен на сайт {{sitelink}}
<? elseif ($sms_type == 'paid2customer'): ?>
Ваш заказ #{{id_order}} на сумму {{sum}} {{currency}} оплачен
<? elseif ($sms_type == 'status2customer'): ?>
Ваш заказ #{{id_order}} на сумму {{sum}} {{currency}} переведён в статус {{status}}
<? elseif ($sms_type == 'call2admin'): ?>
Заказ звонка добавлен на сайт {{sitelink}}
<? elseif ($sms_type == 'call2customer'): ?>
Заказ звонка добавлен на сайт {{sitelink}}
<? elseif ($sms_type == 'trackSent2customer'): ?>
Заказ #{{id_order}} отправлен. Номер отправления {{track_id}}
<? elseif ($sms_type == 'trackDelivered2customer'): ?>
Заказ #{{id_order}} доставлен в отделение. Номер отправления {{track_id}}
<? else: ?>
false
<? endif; ?>