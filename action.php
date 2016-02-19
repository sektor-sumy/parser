<?php
if (!empty($_POST['search'])) {
	system('php '.__DIR__.'/api.php search:audio '.$_POST['search'].' > '.__DIR__.'/result &', $retval);
	echo $retval.' Поиск начался';
} else {
	echo 'Ключевое слово не найдено';
}