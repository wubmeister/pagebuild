<?php

namespace DatabaseKit\Query;

use DatabaseKit\Database;

/**
 * Represents a columns name in an SQL query
 */
class Column
{
    protected $name;

    function __construct($name)
    {
        $this->name = $name;
    }

    public function stringify(Database $db)
    {
        return $db->quoteIdentifier($this->name);
    }
}
