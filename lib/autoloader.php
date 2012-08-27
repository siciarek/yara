<?php
/**
 * Autoloader
 */

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
    require $class . '.class.php';
}
