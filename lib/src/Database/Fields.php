<?php

namespace Pranto\Database;

class Fields
{
    public static function field(string $type, array $rules, int $max_length = null, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        if (!$null) {
            $rules[] = 'required';
        }

        return [
            'type' => $type,
            'max_length' => $max_length,
            'null' => $null,
            'default' => $default,
            'unique' => $unique,
            'rules' => $rules
        ];
    }

    // String
    public static function string(int $max_length = 255, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field('VARCHAR', ['string', 'max:255'], $max_length, $null, $default, $unique);
    }

    // Email
    public static function email(int $max_length = 255, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field('VARCHAR', ['email', 'max:255'], $max_length, $null, $default, $unique);
    }

    // Slug
    public static function slug(int $max_length = 255, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field('VARCHAR', ['slug', 'max:255'], $max_length, $null, $default, $unique);
    }

    // Text
    public static function text(bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field(type: 'TEXT', rules: ['string', 'max:255'], null: $null, default: $default, unique: $unique);
    }

    // Long Text
    public static function longText(bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field(type: 'LONGTEXT', rules: ['string', 'max:255'], null: $null, default: $default, unique: $unique);
    }

    // Integer
    public static function integer(int $max_length = null, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field('INTEGER', ['integer'], $max_length, $null, $default, $unique);
    }

    // Small Integer
    public static function smallInteger(int $max_length = null, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field('SMALLINT', ['integer'], $max_length, $null, $default, $unique);
    }

    // Big Integer
    public static function bigInteger(int $max_length = null, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field('BIGINT', ['integer'], $max_length, $null, $default, $unique);
    }

    // Float
    public static function float(int $max_length = null, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field('FLOAT', ['numeric'], $max_length, $null, $default, $unique);
    }

    // Decimal
    public static function decimal(int $max_length = null, bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field('DECIMAL', ['numeric'], $max_length, $null, $default, $unique);
    }

    // Boolean
    public static function bool(bool $default = false) : array
    {
        return static::field(type: 'BOOLEAN', rules: ['bool'], default: $default);
    }

    // DateTime
    public static function dateTime(bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field(type: 'DATETIME', rules: ['datetime'], null: $null, default: $default, unique: $unique);
    }

    // Date
    public static function date(bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field(type: 'DATE', rules: ['datetime'], null: $null, default: $default, unique: $unique);
    }

    // Date
    public static function time(bool $null = false, mixed $default = null, bool $unique = false) : array
    {
        return static::field(type: 'TIME', rules: ['time'], null: $null, default: $default, unique: $unique);
    }

    // Foreign Key
    public static function foreignKey(Model|string $foreignTo, string $onDelete = 'CASCADE', bool $null = false) : array
    {
        return [
            'type' => 'foreign_key',
            'foreignTo' => is_string($foreignTo) ? $foreignTo : get_class($foreignTo),
            'onDelete' => $onDelete,
            'null' => $null
        ];
    }

    // Many To Many
    public static function manyToMany(Model|string $relatedTo, string $fieldName = null) : array
    {
        return [
            'type' => 'many_to_many',
            'relatedTo' => is_string($relatedTo) ? $relatedTo : get_class($relatedTo),
            'fieldName' => $fieldName
        ];
    }
}