<?php

namespace Pranto\Middleware;

class BasicMiddlewares
{
    public static function startSession($req, $res)
    {
        if (session_name() == PHP_SESSION_NONE) {
            session_start();
        }

        return $res;
    }

    public static function jsonResponse($req, $res)
    {
        $res->run();

        if (!is_string($res->data)) {
            $res->addHeaders(['Content-type: application/json']);
            $res->data = json_encode($res->data);
        }

        return $res;
    }

    private static function hideValues($res)
    {
        if (isset($res::$hidden)) {
            $hiddens = $res::$hidden;
            
            $res = get_object_vars($res);
            
            foreach ($hiddens as $hidden) {
                unset($res[$hidden]);
            }
        }

        return $res;
    }
}