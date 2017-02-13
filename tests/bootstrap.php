<?php
//Autoloader for User classes
spl_autoload_register(function($class) {
	$parts = explode('\\', ltrim($class, '\\'));
	if ($parts[0] === 'Events') {
		array_shift($parts);
		require_once 'src/' . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
	}
	else if (file_exists('tests/deps/' . implode(DIRECTORY_SEPARATOR, $parts) . '.php')) {
		include_once 'tests/deps/' . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
	}
});
require_once "MockEventsStorage.php";
