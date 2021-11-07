<?php

use 
    library\Database,
    library\Router,
    library\Curl;

use 
    engine\Registry,
    engine\Loader;

require_once ROOT_PATH . 'app/system/engine/Registry.php';
$registry = new Registry;

$registry->set('db', new Database([DB_SERVER, DB_NAME, DB_USER, DB_PASS]));
$registry->set('load', new Loader($registry));
$registry->set('curl', new Curl($registry));
//$registry->set('request', new Request());
//$registry->set('document', new Document());

// Роутер
$registry->set('router', new Router($registry));
$registry->get('router')->run();