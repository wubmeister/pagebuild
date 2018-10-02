<?php

namespace DatabaseKit\Query;

use DatabaseKit\Database;

class Condition
{
    protected $key;
    protected $operator = '=';
    protected $value;
    protected $rightHand = '?';
    protected $conditions;

    protected static $operatorMap = [
        '$between' => 'BETWEEN',
        '$in' => 'IN',
        '$gt' => '>',
        '$gte' => '>=',
        '$lt' => '<',
        '$lte' => '<=',
        '$neq' => '<>'
    ];

    public function __construct($key, $value)
    {
        $this->key = $key;

        if ($key == '$and' || $key == '$or') {
            $this->conditions = [];
            foreach ($value as $k => $v) {
                $this->conditions[] = new Condition($k, $v);
            }
        } else {
            if (is_array($value)) {
                $key = current(array_keys($value));
                $this->operator = self::$operatorMap[$key] ? self::$operatorMap[$key] : '=';
                $this->value = $value[$key];

                if ($this->operator == 'BETWEEN') {
                    $this->rightHand = '? AND ?';
                } else if ($this->operator == 'IN') {
                    $this->rightHand = '(?' . (count($this->value) > 0 ? str_repeat(', ?', count($this->value) - 1) : '') . ')';
                }
            } else {
                $this->value = $value;
            }
        }
    }

    public function stringify(Database $db)
    {
        if ($this->key == '$and' || $this->key == '$or') {
            $conditions = [];
            foreach ($this->conditions as $condition) {
                $conditions[] = '(' . $condition->stringify($db) . ')';
            }

            return implode($this->key == '$or' ? ' OR ' : ' AND ', $conditions);
        }

        return $db->quoteIdentifier($this->key) . " {$this->operator} {$this->rightHand}";
    }

    public function getBindValues()
    {
        return is_array($this->value) ? $this->value : [ $this->value ];
    }

    public function getKey()
    {
        return $this->key;
    }
}
