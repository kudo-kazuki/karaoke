<?php
set_exception_handler(function ($e) {
    error($e->getMessage(), $e->getCode() ?: 500);
});

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/router.php';

use Core\Router;

$router = new Router();
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
