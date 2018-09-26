<?php

namespace DatabaseKit;

/**
 * Represents a database connection
 *
 * @author Wubbo Bos <wubbo@wubbobos.nl>
 */
class Database
{
    protected $pdo;

    /**
     * Initializes the database object with a PDO object
     *
     * @param PDO $pdo The PDO object
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Creates a database connection
     *
     * @param string $engine The engine (e.g. Mysql, MSSql, ...)
     * @param array $dsnParams The DSN parameters as an associative array.
     *      Also include the username and password keys if necessary.
     * @return Database The database object
     */
    public static function factory($engine, $dsnParams)
    {
        $engine = strtolower($engine);

        $username = null;
        $password = null;

        if (isset($dsnParams['username'])) {
            $username = $dsnParams['username'];
            unset($dsnParams['username']);
        }
        if (isset($dsnParams['password'])) {
            $password = $dsnParams['password'];
            unset($dsnParams['password']);
        }

        $dsnParts = [];
        foreach ($dsnParams as $key => $value) {
            $dsnParts[] = "{$key}={$value}";
        }
        $dsn = $engine . ':' . implode(';', $dsnParts);

        $pdo = new PDO($dsn, $username, $password);

        $className = "DatabaseKit\\Database\\" . ucfirst($engine);

        return new $className($pdo);
    }

    /**
     * Quotes an identifier for safe use in SQL queries
     *
     * @param string $identifier The unquoted identifier
     * @return string The quoted identifier
     */
    public function quoteIdentifier($identifier)
    {
        return '"' . str_replace('.', '"."', $identifier) . '"';
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function exec($sql)
    {
        return $this->pdo->exec($sql);
    }

    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare();
    }

    public function query($sql)
    {
        return $this->pdo->query();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
}