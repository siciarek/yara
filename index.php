<?php
/**
 * Application Front Controller
 * @package yara
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Config.class.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// AJAX call header: "X-Requested-With", "XMLHttpRequest"

try {
    $src = new SimpleRestController($method, $uri);

    if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)
        and $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest"
    ) {
        $src->setSerializer(new JsonSerializer());
    } else {
        $src->setSerializer(new XmlSerializer());
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit(0);
}

header('Content-Type: ' . $src->getSerializer()->getMimeType());
echo $src->getSerializer()->serialize($src->getOutput());
