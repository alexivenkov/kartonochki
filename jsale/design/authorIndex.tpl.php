<?/*Шаблон индекса админки======================*/?><!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><?# Подключение шапки админкиinclude_once dirname(__FILE__) . '/../design/adminHeader.tpl.php';?>    <div class="hero-unit">		<? if (isset($message)): ?>		<div class="alert alert-error fade in">			<button type="button" class="close" data-dismiss="alert">&times;</button>			<?= $message ?>		</div>		<? endif ?>		<h1>Привет!</h1><br />		<?= $config['author']['info'] ?><br />		<p><a class="btn btn-primary btn-large" href="stat.php" data-loading-text="Загрузка...">Перейти к статистике</a></p>    </div></body></html>