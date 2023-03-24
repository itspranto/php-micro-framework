<?php

namespace Pranto\Database;

use JsonSerializable;

class Model implements JsonSerializable
{
    public static string $primaryKey = 'id';
    public static string $table;
    public static array $hidden = [];
    
    private array $_data;

    public function __construct(array $values = [])
    {
        if ($values) {
            $this->_data = $values;
        }
    }

    public static function fields()
    {
        return [];
    }

    public static function getTableName()
    {
        return static::$table ?? static::getColumnName() . 's';
    }

    public static function find(int|array $key)
    {
        return static::query()
            ->select('*')
            ->where(is_array($key) ? $key : [static::$primaryKey => $key])
            ->getOne();
    }

    public static function all()
    {
        return static::query()
            ->select('*')
            ->getAll();
    }

    public static function query()
    {
        return db(static::getTableName())->model(static::class);
    }

    public static function make(array $values)
    {
        return new static($values);
    }

    public static function create(array $data)
    {
        $model = new static($data);
        $model->save();
        return $model;
    }

    public static function getOrCreate(array $data, array $where = [])
    {
        $model = static::query()
                ->select('*')
                ->where($where ? $where : $data)
                ->getOne();
        
        if (!$model) {
            $model = static::create($data);
        }

        return $model;
    }

    public static function getColumnName()
    {
        return strtolower(basename(static::class));
    }

    // Instance methods
    public function __get($name)
    {
        $method = "get" . ucfirst($name);
        
        if (method_exists($this, $method)) {
            return $this->{$method}($this->_data[$name]);
        }

        return $this->_data[$name];
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function jsonSerialize()
    {
        $json = [];

        foreach ($this->_data as $k => $v) {
            if (!in_array($k, static::$hidden)) {
                $json[$k] = $v;
            }
        }

        return $json;
    }

    public function update(array $data)
    {
        if (!isset($this->{static::$primaryKey})) {
            throw new \Exception("Model has no primary key!");
        }

        return static::query()
            ->where([static::$primaryKey => $this->{static::$primaryKey}])
            ->update($data);
    }

    public function save()
    {
        if (isset($this->{static::$primaryKey})) {
            return static::query()
                ->where([static::$primaryKey => $this->{static::$primaryKey}])
                ->update($this->_data);
        } else {
            $id = static::query()
                ->insert($this->_data);

            if ($id) {
                $this->{static::$primaryKey} = $id;
            }

            return $id;
        }
    }

    public function delete(): bool
    {
        if (!isset($this->{static::$primaryKey})) {
            throw new \Exception("Model has no primary key!");
        }

        return static::query()
            ->where([static::$primaryKey => $this->{static::$primaryKey}])
            ->delete();
    }

    public function getMany($relatedTo)
    {
        $model = static::getColumnName();
        $related = $relatedTo::getColumnName();

        if (array_key_exists($related, static::fields())) {
            $table = $model . "_" . $related;
        } else {
            $table = $related . "_" . $model;
        }

        return db($table)
            ->model($relatedTo)
            ->where([$model => $this->{static::$primaryKey}]);
    }

    public function getChild($class)
    {
        $model = static::getColumnName();

        return db($class::getTableName())
            ->model($class)
            ->where([$model => $this->{static::$primaryKey}]);
    }

    public function getParent($class)
    {
        $model = static::getColumnName();

        return db($class::getTableName())
            ->model($class)
            ->where([$class::$primaryKey => $this->{$model}])
            ->getOne();
    }
}