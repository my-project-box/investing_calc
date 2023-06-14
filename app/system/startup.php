<?php

use 
    library\Database,
    library\Router,
    library\Curl,
    library\Cron;

use 
    engine\Registry,
    engine\Loader,
    engine\Request;

require_once ROOT_PATH . 'app/system/engine/Registry.php';
$registry = new Registry;

$registry->set('db', new Database([DB_SERVER, DB_NAME, DB_USER, DB_PASS]));
$registry->set('load', new Loader($registry));
$registry->set('curl', new Curl($registry));
$registry->set('request', new Request());
//$registry->set('document', new Document());

// Крон
/*$registry->set('cron', new Cron($registry));
$cron = $registry->get('request')->get('cron');
if ( isset ($cron) && $cron == 'get-data-exchange' )
    $registry->get('cron')->dataByCurl();*/

// Роутер
$registry->set('router', new Router($registry));
$registry->get('router')->run();

