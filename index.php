<?php
use 
    library\Router,
    library\Database;

session_start();

header('Content-Type:text/html; charset=utf-8');

// Конфигурации
if (is_file('config.php')) {

	require_once 'config.php';
	require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/app/system/startup.php';
	
} else die ('Файл конфигурации отсутствует!');

//define('SECURITY', true);

