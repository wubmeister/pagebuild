<?php

namespace DatabaseKit\Query;

use DatabaseKit\Database;

class Condition
{
    protected $key;
    protected $operand = '=';
    protected $value;
    protected $rightHand = '?';
    protected $conditions;

    protected static $operandMap = [
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
                $this->operand = self::$operandMap[$key] ? self::$operandMap[$key] : '=';
                $this->value = $value[$key];

                if ($this->operand == 'BETWEEN') {
                    $this->rightHand = '? AND ?';
                } else if ($this->operand == 'IN') {
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

        return $db->quoteIdentifier($this->key) . " {$this->operand} {$this->rightHand}";
    }

    public function getBindValues()
    {
        return is_array($this->value) ? $this->value : [ $this->value ];
    }
}
