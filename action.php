<?php
if (!empty($_POST['search'])) {
	system('php /var/www/html/parser/api.php search:audio '.$_POST['search'].' > /var/www/html/parser/result &', $retval);
	echo $retval.' Поиск начался';
} else {
	echo 'Ключевое слово не найдено';
}