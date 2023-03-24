<?php

define('APP_ROOT', dirname(__DIR__));

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    if (str_contains($class, 'Pranto' . DIRECTORY_SEPARATOR)) {
        $class = str_replace('Pranto' . DIRECTORY_SEPARATOR, '', $class);
        require_once  APP_ROOT . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . $class . ".php";
    } else {
        require_once APP_ROOT . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . $class . ".php";
    }
});

require_once  APP_ROOT . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "src" .  DIRECTORY_SEPARATOR . "helpers.php";

if (file_exists($helpers = join_path(APP_ROOT, 'src', 'helpers.php'))) {
    require_once $helpers;
}

$app = Pranto\Core\App::initApp(APP_ROOT);
$app->boot();