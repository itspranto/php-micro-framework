<?php

use Pranto\Core\App;
use Pranto\Core\Template;
use Pranto\Core\URLBuilder;
use Pranto\Database\Database;

function call_function($fn, array $args = [])
{
    if ($args) {
        switch(count($args)) {
            case 1:
                return $fn($args[0]);
            case 2:
                return $fn($args[0], $args[1]);
            case 3:
                return $fn($args[0], $args[1], $args[2]);
            case 4:
                return $fn($args[0], $args[1], $args[2], $args[3]);
            case 5:
                return $fn($args[0], $args[1], $args[2], $args[3], $args[4]);
            case 6:
                return $fn($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
            case 7:
                return $fn($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
            case 8:
                return $fn($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
            case 9:
                return $fn($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]);
            case 10:
                return $fn($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9]);
            case 11:
                return $fn($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9], $args[10]);
            default:
                // Call user func
                return call_user_func_array($fn, $args);
        }
    }

    return $fn();
}

function app()
{
    return App::getApp();
}

function render(string $template, array $vars = [], string $tpl_dir = '')
{
    return (new Template($template, $vars, $tpl_dir))->render();
}

function dd($val)
{
    echo '<pre>';
    var_dump($val);
    echo '</pre>';
    exit;
}

function db($table = null)
{
    if ($table) {
        return Database::getDB()->table($table);
    } else {
        return Database::getDB();
    }
}

function add_command($command, $func, $helpText = '')
{
    return [
        'command' => $command,
        'func' => $func,
        'helpText' => $helpText
    ];
}

function cmd_color($text, $color = 'white', $newLine = PHP_EOL)
{
    $colors = [
        'white' => 0,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36
    ];
    $color = $colors[$color] ?? 0;

    return "\033[{$color}m$text\033[0m$newLine";
}

function join_path(...$paths) : string
{
    return implode(DIRECTORY_SEPARATOR, $paths);
}

function redirect($to)
{
    header('Location: ' . $to);
    exit;
}

function url($name, $values = [])
{
    $url = URLBuilder::urls()[$name]['uri'];
    return preg_replace_callback('/<\w+>/', function($matches) use (&$values) {
        return array_shift($values);
    }, $url);
}
