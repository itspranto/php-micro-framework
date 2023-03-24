<?php

namespace Pranto\Core;

use Closure;

class Response
{
    private static $_instance;

    public string|Closure $view;
    public array $view_args, $headers;
    public mixed $data;

    private function __construct()
    {
        $this->headers = [
            'X-Powered-By: Spectrum/2.0.1'
        ];

        if (app()->config['debug']) {
            $this->headers[] = 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0';
        }

        $this->view = static::class . '::default';
        $this->view_args = [];
    }

    // Static methods
    public static function getInstance()
    {
        if (!static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public static function default()
    {
        return render('default', [], join_path(app()->sys_path, 'templates'));
    }

    public static function serverErrors($code, $message)
    {
        if (file_exists(join_path(app()->config['tpl_dir'], 'server_errors.html'))) {
            return render('server_errors', ['code' => $code, 'message' => $message]);
        } else {
            return render('server_errors', ['code' => $code, 'message' => $message], join_path(app()->sys_path, 'templates'));
        }
    }

    public function notFound()
    {
        $this->addHeaders(['HTTP/1.0 404 Not Found']);
        $this->view = static::class . '::serverErrors';
        $this->view_args = [404, 'Page Not Found!'];
    }

    public function notAllowed()
    {
        $this->addHeaders(['HTTP/1.0 405 Not Allowed']);
        $this->view = static::class . '::serverErrors';
        $this->view_args = [405, 'Method Not Allowed!'];
    }

    public function forbidden()
    {
        $this->addHeaders(['HTTP/1.0 403 Forbidden']);
        $this->view = static::class . '::serverErrors';
        $this->view_args = [403, 'Access Forbidden!'];
    }

    public function unauthorized()
    {
        $this->addHeaders(['HTTP/1.0 401 Unauthorized']);
        $this->view = static::class . '::serverErrors';
        $this->view_args = [401, 'Unauthorized!'];
    }

    public function addHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function run()
    {
        $this->data = call_function($this->view, $this->view_args);
    }
}