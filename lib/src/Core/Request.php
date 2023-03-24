<?php

namespace Pranto\Core;

class Request
{
    private static $_instance;
    public string $_host, $_method, $_uri;
    public bool $_is_json = false;
    public string $_lang;
    private array $_data = [];

    protected function __construct() {
        $this->_host = $_SERVER['HTTP_HOST'];
        $this->_uri = rtrim(explode('?', $_SERVER['REQUEST_URI'])[0], "/");

        if (isset($_POST['_method'])) {
            $this->_method = $_POST['_method'];
        } else {
            $this->_method = trim(htmlspecialchars($_SERVER['REQUEST_METHOD']));
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $this->_is_json = $_SERVER['CONTENT_TYPE'] == 'application/json' ? true : false;
        }

        if ($this->_is_json) {
            $data = json_decode(trim(file_get_contents("php://input")), true);

            if (is_array($data)) {
                $this->_data = $this->sanitize($data);
            }
        } else {
            $this->_data = $this->_method == 'GET' ? $this->sanitize($_GET) : $this->sanitize($_POST);
        }

        $this->_lang = 'en';
    }

    public static function getInstance()
    {
        if (!static::$_instance) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    public function __get($name)
    {
        return $this->_data[$name] ?? null;
    }

    public function sanitize($data)
    {
        return array_map(function ($val) {
            if (is_array($val)) {
                return $this->sanitize($val);
            }

            return htmlspecialchars(trim($val));
        }, $data);
    }


    public function session($key)
    {
        return htmlspecialchars(trim($_SESSION[$key]));
    }

    public function server($key)
    {
        return $_SERVER[$key];
    }
}