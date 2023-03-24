<?php

namespace Pranto\Database;

use PDO;

class Database
{
    private string|null $table, $fields = '*', $where = '', $order = '';
    private PDO $pdo;
    private static $instance;
    private array $values = [], $join = [];
    private bool $limit = false;
    private string $model;

    protected function __construct($dsn, $user, $password)
    {
        $this->pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public static function initiate($dsn, $user, $password): void
    {
        if (!static::$instance) {
            static::$instance = new self($dsn, $user, $password);
        }

        //return self::$instance;
    }

    public static function getDB(): Database
    {
        if (!static::$instance) {
            throw new \Exception('Database is not initiated!');
        }

        return self::$instance;
    }

    public function pdo()
    {
        return $this->pdo;
    }

    public function model($model) {
        $this->model = $model;
        return $this;
    }

    public function table(string $table): Database
    {
        $this->table = $table;
        return $this;
    }

    public function select(array|string $fields): Database
    {
        $this->fields = is_array($fields) ? implode(",", $fields) : $fields;
        return $this;
    }

    public function where(array $where, string $method = '=', string $start = null, string $separator = 'AND'): Database
    {
        if ($start) {
            $this->where .= " $start ";
        }

        foreach ($where as $k => $v) {
            $this->where .= "$k $method :$k $separator ";
            $this->values[":$k"] = $v;
        }

        $this->where = rtrim($this->where, " $separator ");

        return $this;
    }

    public function orWhere(array $where, string $method): Database
    {
        return $this->where($where, $method, 'OR', 'OR');
    }

    public function andWhere(array $where, string $method): Database
    {
        return $this->where($where, $method, 'AND');
    }

    public function orderBy(string $order): Database
    {
        $this->order = htmlspecialchars($order);

        return $this;
    }

    public function limit(int $limit, int $offset = 0): Database
    {
        $this->limit = true;
        $this->values[':limit'] = $limit;
        $this->values[':offset'] = $offset;
        return $this;
    }

    public function join(string $type, string $table, array $on): Database
    {
        $join = "$type JOIN $table ON ";

        foreach ($on as $k => $v) {
            $join .= "$k = $v AND ";
        }
        $this->join[] = rtrim($join, ' AND ');


        return $this;
    }

    public function insert(array $data): int|bool
    {
        $sql = "INSERT INTO " . $this->table . " (" . implode(",", array_keys($data)) . ")";
        $sql .= " VALUES (" . implode(',', array_fill(0, count($data), '?')) . ")";

        if ($this->pdo->prepare($sql)->execute(array_values($data))) {
            return $this->pdo->lastInsertId();
        }

        return false;
    }

    public function update(array $data): bool
    {
        if (!$this->where) {
            throw new \Exception('UPDATE without WHERE clause is unadvisable! Please add WHERE');
        }

        $sql = "UPDATE {$this->table} SET ";

        foreach ($data as $k => $v) {
            $sql .= $k . " = :$k, ";
            $this->values[":$k"] = $v;
        }

        $sql = rtrim($sql, ', ');
        $sql .= " WHERE " . $this->where;

        $return = $this->pdo->prepare($sql)->execute($this->values);
        $this->clear();
        return $return;
    }

    public function delete(): bool
    {
        if (!$this->where) {
            throw new \Exception('DELETE without WHERE clause is highly unadvisable! Please add WHERE');
        }

        $return = $this->pdo->prepare("DELETE FROM {$this->table} WHERE " . $this->where);
        $return = $return->execute($this->values);
        $this->clear();

        return $return;
    }

    public function count(string $column): int
    {
        $sql = "SELECT COUNT($column) FROM " . $this->table;

        if ($this->where) {
            $sql .= " WHERE " . $this->where;
        }

        $return = $this->pdo->prepare($sql);

        if ($this->values) {
            $return->execute($this->values);
        } else {
            $return->execute();
        }

        $return = $return->fetchColumn();

        $this->clear();
        return $return;
    }

    private function execute(): bool|\PDOStatement
    {
        $sql = "SELECT " . ($this->fields ?? '*') . " FROM " . $this->table;

        if ($this->join) {
            $sql .= " " . implode(" ", $this->join);
        }

        if ($this->where) {
            $sql .= " WHERE " . $this->where;
        }

        if ($this->order) {
            $sql .= " ORDER BY " . $this->order;
        }

        if ($this->limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
		}
        //dd($this->values);
        $return = $this->pdo->prepare($sql);
        //print($return->queryString);
        $return->execute($this->values);
        $this->clear();
        return $return;
    }

    public function getOne()
    {
        $this->limit(1);

        if (isset($this->model)) {
            return $this->execute()->fetchObject($this->model);
        }
        
        return $this->execute()->fetchObject();
    }

    public function getAll(): array
    {
        if (isset($this->model)) {
            return $this->execute()->fetchAll(PDO::FETCH_CLASS, $this->model);
        }

        return $this->execute()->fetchAll();
    }

    public function getColumn()
    {
        return $this->execute()->fetchColumn();
    }

    private function clear(): void
    {
        $this->where = $this->table = $this->fields = null;
        $this->values = $this->join = [];
        $this->order = $this->limit = false;
    }
}