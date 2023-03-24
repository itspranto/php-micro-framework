<?php

namespace Pranto\Network;

class Http
{
    private $ch, $body, $info;

    private $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ];

    public function __construct($url)
    {
        if (!$this->ch) {
            $this->ch = curl_init($url);
        }
    }

    public function changeUrl($url)
    {
        $this->options[CURLOPT_URL] = $url;
        return $this;
    }

    public function reqType($type = "GET")
    {
        $this->options[CURLOPT_CUSTOMREQUEST] = $type;
        return $this;
    }

    public function post($post)
    {
        $this->options[CURLOPT_POST] = true;
        $this->options[CURLOPT_POSTFIELDS] = $post;
    }

    public function headers(array $headers)
    {
        $this->options[CURLOPT_HTTPHEADER] = $headers;
        return $this;
    }

    public function ua($ua)
    {
        $this->options[CURLOPT_USERAGENT] = $ua;
        return $this;
    }

    public function referer($referer) {
        $this->options[CURLOPT_REFERER] = $referer;
        return $this;
    }

    public function proxy($proxy) {
        $this->options[CURLOPT_PROXY] = $proxy;

        return $this;
    }

    public function options(array $options)
    {
        //$this->options = array_merge($this->options, $options);
        $this->options = $this->options + $options;
        return $this;
    }

    public function execute()
    {
        curl_setopt_array($this->ch, $this->options);
        $this->body =  curl_exec($this->ch);
        $this->info = curl_getinfo($this->ch);
        return $this;
    }

    public function getInfo($info = '')
    {
        if (!$info) {
            return $this->info;
        }
        
        return $this->info[$info];
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getJson()
    {
        return json_decode($this->body);
    }
}