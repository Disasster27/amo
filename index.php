<?php

declare(strict_types=1);

define("DIR", __DIR__ . '/');

require DIR . 'vendor/autoload.php';

use src\Auth;

spl_autoload_register(function ($class)
{
    $path = str_replace('\\', '/', $class.'.php');
    if (file_exists(DIR . $path))
        require_once DIR . $path;
    else
        echo 'Not found: '.$path;
});

$route = new Auth();
try {
    $route->run();
} catch (Exception $e) {
    printf($e);
}