<?/*Шаблон индекса админки======================*/?><!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><?# Подключение шапки админкиinclude_once dirname(__FILE__) . '/../design/adminHeader.tpl.php';?>	<br/>    <div class="container-fluid">		<ul class="breadcrumb">			<li>				<a title="Вернуться к началу" href="index.php"><i class="icon-home"></i>&nbsp;Админ.интерфейс</a>				<span class="divider">/</span>				</li>			<li class="active">				<a href="managers.php">Менеджеры</a>			</li>		</ul>			<? if (isset($message)): ?>		<div class="alert alert-error fade in">			<button type="button" class="close" data-dismiss="alert">&times;</button>			<?= $message ?>		</div>		<? endif ?>				<table class="table table-striped table-bordered table-hover table-condensed">		<thead>			<tr>				<th>Email</th>				<th>Имя</th>				<th colspan="3" class="centered">Заказы</th>								<th>Всего заработано, <?= $config['currency'] ?></th>				<th>Выплачено, <?= $config['currency'] ?></th>			</tr>			<tr>				<th></th>				<th></th>				<th>Оформлено</th>				<th>Оплачено</th>				<th>Заработано, <?= $config['currency'] ?></th>				<th></th>				<th></th>			</tr>		</thead>		<tbody>			<? $total_paid = $total_profit = 0; ?>			<? foreach ($managers as $manager): ?>			<tr>				<td><a href="?manager=<?= $manager['id_manager'] ?>"><?= $manager['email'] ?></a></td>				<td><?= $manager['name'] ?></td>				<td><?= $manager['total_count'] ?></td>				<td><?= $manager['paid_count'] ?></td>				<td><?= $manager['paid_sum'] ?></td>								<td><?= $partner_profit = number_format($manager['paid_sum'], 2, '.', '') ?></td>				<td <? if ($partner_profit > $manager['paid']): ?>class="text-error"<? endif; ?>><?= $manager['paid'] ?></td>			</tr>			<? $total_paid += $manager['paid']; $total_profit += $partner_profit;  ?>			<? endforeach; ?>		</tbody>		</table>				<p>Всего заработано: <?= number_format($total_profit, 2, '.', '') ?> <?= $config['currency'] ?></p>		<p>Всего выплачено: <?= number_format($total_paid, 2, '.', '') ?> <?= $config['currency'] ?></p>		<h4 <? if ($total_profit > $total_paid): ?>class="text-error"<? endif ?>>Осталось выплатить: <?= number_format($total_profit - $total_paid, 2, '.', '') ?> <?= $config['currency'] ?></h4>				<div class="form-actions">            <a href="?manager=new" class="btn">Добавить менеджера</a>			<a href="?pay" class="btn">Перейти к выплатам</a>        </div>    </div>		<?= (isset($pagination)) ? $pagination : '' ?>	</body></html>