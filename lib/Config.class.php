<?php

$libs = array(
    'model',
    'view',
    'controller',
);

$base = dirname(__FILE__);

foreach ($libs as $lib) {
    $path = $base . DIRECTORY_SEPARATOR . $lib;
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

function __autoload($class)
{
    require $class . '.class.php'; //is substituted as require Customer.php (with capital 'C')
}


/**
 * YARA Configuration provider.
 * User: jsiciarek
 * Date: 27.08.12
 */
class Config
{
    const DBHOST = 'localhost';
    const DBNAME = 'rest_area';
    const DBUSER = 'root';
    const DBPASS = '';
}
