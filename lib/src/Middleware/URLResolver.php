<?php

namespace Pranto\Middleware;
use Pranto\Core\Request;
use Pranto\Core\Response;

class URLResolver
{
    private static array $patterns = [
        '<int>' => '(\d+)',
        '<str>' => '([^/]+)',
        '<all>' => '(.*)'
    ];

    public static function resolve(Request $req, array $urls, Response $res)
    {
        if ($urls) {
            $middlewares = app()->config['middlewares'];

            foreach ($urls as $url) {
                if (rtrim($url['uri'], '/') == $req->_uri) {
                    if (isset($url[$req->_method])) {
                        $res->view = $url[$req->_method];
                        $res->view_args[] = $req;
                        // Middlewares
                        if (isset($url['middleware'])) {
                            if (is_array($url['middleware'])) {
                                $middlewares = [...$middlewares, ...$url['middleware']];
                            } else {
                                $middlewares[] = $url['middleware'];
                            }
                        }
                        
                        foreach ($middlewares as $middleware) {
                            $res = $middleware($req, $res);
                        }
                    } else {
                        $res->notAllowed();
                    }

                    break;
                } else {
                    if (str_contains($url['uri'], '<')) {
                        $regex_uri = str_replace(array_keys(static::$patterns), array_values(static::$patterns), $url['uri']);

                        if (preg_match("#{$regex_uri}/?$#u", $req->_uri, $m)) {
                            if (isset($url[$req->_method])) {
                                $res->view = $url[$req->_method];
                                $res->view_args[] = $req;
                                $res->view_args = [...$res->view_args, ...array_slice($m, 1)];

                                // Middlewares
                                if (isset($url['middleware'])) {
                                    if (is_array($url['middleware'])) {
                                        $middlewares = [...$middlewares, ...$url['middleware']];
                                    } else {
                                        $middlewares[] = $url['middleware'];
                                    }
                                }
                                
                                foreach ($middlewares as $middleware) {
                                    $res = $middleware($req, $res);
                                }
                            } else {
                                $res->notAllowed();
                            }

                            break;
                        }
                    }
                }
            }

            if (!$res->view_args) {
                $res->notFound();
            }
        }

        foreach ($res->headers as $header) {
            header($header);
        }

        if (!isset($res->data)) {
            $res->run();
        }

        echo $res->data;
    }
}