<?php

namespace DatabaseKit\Query;

/**
 * Represents a value for an SQL query
 */
class Value
{
    protected $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
