<?php

namespace Pranto\Core;

use Pranto\Middleware\ConsoleResolver;
use Pranto\Middleware\URLResolver;
use Pranto\Database\Database;

class App
{
    private static $_app;
    public array $config;
    public string $path;
    public string $sys_path;

    protected function __construct($app_path)
    {
        $this->path = $app_path;
        $this->sys_path = join_path($app_path, 'lib');
        $this->config = require_once(join_path($app_path, 'config.php'));

        ini_set('display_errors', $this->config['debug']);
        error_reporting($this->config['debug'] ? E_ALL : 0);

        set_exception_handler(function ($e) {
            if ($this->config['debug']) {
                echo render('error', ['e' => $e], join_path($this->sys_path, 'templates'));
            } else {
                echo Response::serverErrors(500, 'Internal Server Error!');
            }
            exit;
        });
    }

    public static function initApp($app_path)
    {
        if (!self::$_app) {
            self::$_app = new self($app_path);
        }

        return self::$_app;
    }

    public static function getApp()
    {
        return self::$_app;
    }

    public function boot()
    {
        if (isset($this->config['database'])) {
            Database::initiate("{$this->config['database']['type']}:host={$this->config['database']['host']};dbname={$this->config['database']['dbname']}", 
                $this->config['database']['user'], $this->config['database']['pass']);
        }

        if (PHP_SAPI == 'cli') {
            ConsoleResolver::resolve(include_once(join_path($this->path, 'routes', 'console.php')));
        } else {
            include_once(join_path($this->path, 'routes', 'web.php'));
            URLResolver::resolve(Request::getInstance(), URLBuilder::urls(), Response::getInstance());
        }
    }
}