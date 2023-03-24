<?php

namespace Pranto\Core;

class Validator
{
    private static Validator $_instance;
    private Request $req;
    private array $langs;

    private function __construct(Request $req)
    {
        $this->req = $req;
        $this->langs = include_once(join_path(app()->path, 'assets', 'lang', $req->_lang, 'validation.php'));
    }

    public static function getValidator(Request $req)
    {
        if (!isset(static::$_instance)) {
            static::$_instance = new static($req);
        }

        return static::$_instance;
    }

    // Instance methods
    public function withRequest(Request $req)
    {
        $this->req = $req;
        return $this;
    }

    public function validate(array $rules)
    {
        $errors = [];

        $result = true;

        foreach ($rules as $field => $rule) {
            foreach ($rule as $r) {
                if ($r != 'break') {
                    $r = explode(':', $r);

                    if (isset($r[1])) {
                        $args = explode(',', $r[1]);

                        if (count($args) == 1) {
                            $args = $args[0];
                        }

                        $result = static::{$r[0]}($field, $args);
                    } else {
                        $result = static::{$r[0]}($field);
                    }

                    if (!$result) {
                        $errors[$field] = $this->getLang($r[0], $field);

                        if (in_array('break', $rule)) {
                            break;
                        }
                    }
                }
            }
        }

        if ($errors) {
            if ($this->req->_is_json) {
                header('Content-type: application/json');
                header('HTTP/1.0 400 Bad Request');

                echo json_encode([
                    'status' => 'error',
                    'errors' => $errors
                ]);

                exit;
            } else {
                $_SESSION['errors'] = $errors;
                redirect($this->req->server('HTTP_REFERER') ?? 'javascript:history.back()');
            }
        }
    }

    // Language Convert
    private function getLang($key, $field)
    {
        return str_replace(':field', ucwords(str_replace(['_', '-'], ' ', $field)), $this->langs[$key]) . '.';
    }

    // Rules
    protected function email($field): bool
    {
        return filter_var($this->req->$field, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function required($field): bool
    {
        $field = $this->req->$field;
        return $field && !empty($field);
    }

    protected function accepted($field): bool
    {
        return in_array($this->req->$field, ['yes', 'on', 1, true]);
    }

    protected function alpha($field): bool
    {
        return preg_match('#([a-zA-Z]+)#', $this->req->$field);
    }

    protected function alpha_num($field): bool
    {
        return preg_match('#([a-zA-Z0-9]+)#', $this->req->$field);
    }

    protected function alpha_dash($field): bool
    {
        return preg_match('#([a-zA-Z0-9\-\_]+)#', $this->req->$field);
    }

    protected function array($field)
    {
        return is_array($this->req->$field);
    }

    protected function confirmed($field)
    {
        return $this->req->$field == $this->req->$field . "_confirmation";
    }

    protected function date($field)
    {
        return strtotime($this->req->$field);
    }

    protected function different($field, $field2)
    {
        return $this->req->$field != $this->req->$field2;
    }

    protected function digits($field, $digits)
    {
        return is_numeric($this->req->$field) && strlen($this->req->$field) == $digits;
    }

    protected function digits_between($field, $digits)
    {
        $field = $this->req->$field;
        return is_numeric($field) && (strlen($field) > $digits[0] || strlen($field) < $digits[0]);
    }

    protected function starts_with($field, $needle)
    {
        return str_starts_with($this->req->$field, $needle);
    }

    protected function ends_with($field, $needle)
    {
        return str_ends_with($this->req->$field, $needle);
    }

    protected function exists($field, $opt)
    {
        if (is_array($opt)) {
            return db($opt[0])
                ->where([$opt[1] => $this->req->$field])
                ->count('id') != 0;
        } else {
            return db($opt)
                ->where(['id' => $this->req->$field])
                ->count('id') != 0;
        }
        
    }

    protected function gt($field, $num)
    {
        return $this->req->$field > $num;
    }

    protected function gte($field, $num)
    {
        return $this->req->$field >= $num;
    }

    protected function fn($field, $func)
    {
        if (is_array($func)) {
            $args = [$this->req->$field];
            $args[] = array_slice($func, 1);
            return call_function($func, $args);
        } else {
            return $func($this->req->$field);
        }
    }
}