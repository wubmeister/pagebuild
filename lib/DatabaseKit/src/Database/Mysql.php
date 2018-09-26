<?php

namespace DatabaseKit\Database;

/**
 * Represents a MySQL/MariaDB database connection
 *
 * @author Wubbo Bos <wubbo@wubbobos.nl>
 */
class Mysql
{
    /**
     * Quotes an identifier for safe use in SQL queries
     *
     * @param string $identifier The unquoted identifier
     * @return string The quoted identifier
     */
    public function quoteIdentifier($identifier)
    {
        return '`' . str_replace('.', '`.`', $identifier) . '`';
    }
}
