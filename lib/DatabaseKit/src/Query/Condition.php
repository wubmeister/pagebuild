<?php

namespace DatabaseKit\Query;

use DatabaseKit\Database;

class Condition
{
    const RHP_VALUE = 1;
    const RHP_COLUMN = 2;

    protected $key;
    protected $operator = '=';
    protected $bind = [];
    protected $rightHand = '?';
    protected $rightHandPolicy;
    protected $conditions;
    protected $query;

    protected static $operatorMap = [
        '$between' => 'BETWEEN',
        '$in' => 'IN',
        '$gt' => '>',
        '$gte' => '>=',
        '$lt' => '<',
        '$lte' => '<=',
        '$neq' => '<>'
    ];

    public function __construct($query, $key, $value, $rightHandPolicy = self::RHP_VALUE)
    {
        $this->query = $query;
        $this->key = $key;

        if ($key == '$and' || $key == '$or') {
            $this->conditions = [];
            foreach ($value as $k => $v) {
                $this->conditions[] = new Condition($query, $k, $v);
            }
        } else {
            $this->rightHandPolicy = $rightHandPolicy;

            if (is_array($value)) {
                $key = current(array_keys($value));
                $this->operator = self::$operatorMap[$key] ? self::$operatorMap[$key] : '=';

                if (is_array($value[$key])) {
                    $rightHands = [];
                    foreach ($value[$key] as $v) {
                        $result = $this->getRightHand($v);
                        $rightHands[] = $result[0];
                        if (count($result) >= 2) $this->bind[] = $result[1];
                    }

                    if ($this->operator == 'BETWEEN') {
                        $this->rightHand = implode(' AND ', $rightHands);
                    } else if ($this->operator == 'IN') {
                        $this->rightHand = '(' . implode(', ', $rightHands) . ')';
                    }
                } else {
                    $result = $this->getRightHand($value[$key]);
                    $this->rightHand = $result[0];
                    if (count($result) >= 2) $this->bind[] = $result[1];
                }
            } else {
                $result = $this->getRightHand($value);
                $this->rightHand = $result[0];
                if (count($result) >= 2) $this->bind[] = $result[1];
            }
        }
    }

    protected function getRightHand($value)
    {
        if (is_object($value)) {
            if ($value instanceof Value) return [ '?', $value->getValue() ];
            if ($value instanceof Column) return [ $value->stringify($this->query->getDb()) ];
        }

        if ($this->rightHandPolicy == self::RHP_COLUMN) {
            return [ $this->query->getDb()->quoteIdentifier($value) ];
        }

        return [ '?', $value ];
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
        if (is_array($this->conditions)) {
            $bind = [];
            foreach ($this->conditions as $condition) {
                $bind = array_merge($bind, $condition->getBindValues());
            }
            return $bind;
        }

        return $this->bind;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function appendCondition(Condition $condition)
    {
        $this->conditions[] = $condition;
    }

    public function appendConditions(array $conditions)
    {
        foreach ($value as $k => $v) {
            $this->conditions[] = new Condition($this->query, $k, $v);
        }
    }
}
