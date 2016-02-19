<?php
	if (!empty($_POST['search'])) {
		// HACK Ой-ой.. ну ладно..
		if(!empty($_FILES) && $_FILES['ids']['error'] == UPLOAD_ERR_OK) {
			file_put_contents(__DIR__.'/in/in.txt', file_get_contents($_FILES['ids']['tmp_name']));
		};
		
		system('php '.__DIR__.'/api.php search:audio '.$_POST['search'].' > '.__DIR__.'/result &', $retval);
		echo $retval.' Поиск начался';

	} else {
		echo 'Ключевое слово не найдено';
	};