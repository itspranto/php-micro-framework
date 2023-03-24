<?php

namespace Pranto\Core;

class URLBuilder
{
    private static $_instance;
    private static array $_urls = [];
    private array $_url;
    private $_url_key;
    protected $middleware, $prefix;

    private function __construct()
    {
        
    }

    public static function getInstance()
    {
        if (!static::$_instance) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    public static function urls()
    {
        return static::$_urls ?? null;
    }

    private function url($uri, $type, $func)
    {
        if (isset($this->prefix)) {
            $uri = $this->prefix . $uri;
        }

        $this->_url = [
            'uri' => $uri,
            $type => $func
        ];

        if (isset($this->middleware)) {
            $this->_url['middleware'] = $this->middleware;
        }

        static::$_urls[] = $this->_url;
        $this->_url_key = array_key_last(static::$_urls);
        
        return $this;
    }

    public function get($uri, $func)
    {
        return $this->url($uri, 'GET', $func);
    }

    public function post($uri, $func)
    {
        return $this->url($uri, 'POST', $func);
    }

    public function patch($uri, $func)
    {
        return $this->url($uri, 'PATCH', $func);
    }

    public function delete($uri, $func)
    {
        return $this->url($uri, 'DELETE', $func);
    }

    public function name($name)
    {
        static::$_urls[$name] = static::$_urls[$this->_url_key];
        unset(static::$_urls[$this->_url_key]);
        $this->_url_key = $name;
        return $this;
    }

    public function add($type, $func, $middleware = null)
    {
        if ($middleware) {
            static::$_urls[$this->_url_key][$type] = [$func, $middleware];
        } else {
            static::$_urls[$this->_url_key][$type] = $func;
        }

        return $this;
    }

    public function middleware($middleware)
    {
        if ($this->_url_key) {
            static::$_urls[$this->_url_key]['middleware'] = $middleware;
        } else {
            $this->middleware = $middleware;
        }
        
        return $this;
    }

    public function prefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function group($url, $func)
    {
        $func($url);
        
        if ($this->middleware)  unset($this->middleware);
        if ($this->prefix)  unset($this->prefix);
    }
}