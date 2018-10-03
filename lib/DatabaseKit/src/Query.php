<?php

namespace DatabaseKit;

use DatabaseKit\Query\Condition;
use DatabaseKit\Query\Value;
use DatabaseKit\Query\Column;

/**
 * Represnts an SQL query
 *
 * @author Wubbo Bos <wubbo@wubbobos.nl>
 */
class Query
{
    const GLUE_AND = 1;
    const GLUE_OR = 2;

    const IGNORE = 1;
    const UPDATE = 2;

    /**
     * The database.
     * The query is linked to a database, which can quote identifiers and
     * eventually execute the query.
     * @var Database
     */
    protected $db;

    /**
     * The query parts, like FROM, WHERE and ORDER BY.
     * @var array
     */
    protected $parts = [];

    /**
     * Initializes the query with a linked database.
     * The query is linked to a database, which can quote identifiers and
     * eventually execute the query.
     *
     * @param Database $db The database
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Makes this query a SELECT query
     * @return Query $this
     */
    public function select()
    {
        $this->parts['what'] = 'SELECT';
        return $this;
    }

    /**
     * Makes this query a INSERT query
     * @return Query $this
     */
    public function insert()
    {
        $this->parts['what'] = 'INSERT';
        return $this;
    }

    /**
     * Makes this query a UPDATE query
     * @return Query $this
     */
    public function update()
    {
        $this->parts['what'] = 'UPDATE';
        return $this;
    }

    /**
     * Makes this query a DELETE query
     * @return Query $this
     */
    public function delete()
    {
        $this->parts['what'] = 'DELETE';
        return $this;
    }

    /**
     * Creates a table reference, with an alias if necessary.
     *
     * @param string|array $table The table name: 'table_name' | [ 'alias' => 'table_name' ]
     * @return array [
     *           'ref' => 'The table reference to use throughout the query',
     *           'table' => 'The real table name',
     *           'sql' => 'The table identifier to use in FROM and JOIN (table_name AS alias)',
     *         ]
     */
    protected function tableDef($table)
    {
        $result = [];

        if (is_array($table)) {
            $result['ref'] = current(array_keys($table));
            $result['table'] = $table[$alias];
            $result['sql'] = $this->db->quoteIdentifier($table) . ' AS ' . $this->db->quoteIdentifier($alias);
        } else {
            $result['ref'] = $table;
            $result['table'] = $table;
            $result['sql'] = $this->db->quoteIdentifier($table);
        }

        return $result;
    }

    /**
     * Add columns to the 'columns' part
     *
     * @param string $tableRef The table name or alias
     * @param array $columns Array with columns
     */
    protected function addColumns($tableRef, $columns)
    {
        $qAlias = $this->db->quoteIdentifier($tableRef);

        if (is_array($columns)) {
            foreach ($columns as $key => $column) {
                $column = $this->parts['columns'][] = $qAlias . '.' . $this->db->quoteIdentifier($column);
                if (!is_numeric($key)) {
                    $column .= ' AS ' . $this->db->quoteIdentifier($key);
                }
                $this->parts['columns'][] = $column;
            }
        } else if ($columns == '*') {
            $this->parts['columns'][] = $qAlias . '.*';
        }
    }

    protected function buildConditions($array, $glue = self::GLUE_AND, $rightHandPolicy = Condition::RHP_VALUE)
    {
        $condition = $glue == self::GLUE_OR
            ? new Condition($this, '$or', $array)
            : new Condition($this, '$and', $array);
        return $condition;
    }

    /**
     * Specifies a table to select or delete from.
     *
     * @param string|array $table The table to select or delete from
     * @param array $columns The columns to select.
     */
    public function from($table, $columns = '*')
    {
        $result = $this->tableDef($table);
        $this->parts['from'] = $result['sql'];

        if (!isset($this->parts['columns'])) {
            $this->parts['columns'] = [];
        }

        $this->addColumns($result['ref'], $columns);

        return $this;
    }

    public function table($table)
    {
        $result = $this->tableDef($table);
        $this->parts['table'] = $result['sql'];

        $this->addColumns($result['ref'], $columns);

        return $this;
    }

    /**
     * Alias of {@see table()}
     *
     * @param string $table
     * @return Query $this
     */
    public function into($table)
    {
        return $this->table($table);
    }

    public function join($type, $table, $condition, $columns)
    {
        $table = $this->tableDef($table);

        if (!$this->parts['join']) {
            $this->parts['join'] = [];
            $this->parts['join bind'] = [];
        }

        $condition = $this->buildConditions($condition, self::GLUE_AND, Condition::RHP_COLUMN);

        $this->parts['join'][] =
            strtoupper($type) . ' JOIN ' . $result['sql'] . ' ON ' .
            $condition->stringify($this->db);
        $this->parts['join bind'] = array_merge($this->parts['join bind'], $condition->getBindValues());
    }

    protected function whereHaving($part, $conditions, $operator)
    {
        if ($this->parts[$part]) {
            if ($this->parts[$part]->getKey == $operator) {
                $this->parts[$part]->appendConditions($conditions);
            } else {
                $this->parts[$part]->appendCondition($this->buildConditions(conditions));
            }
        } else {
            $this->parts[$part] = $this->buildConditions($conditions);
        }
    }

    public function where($conditions)
    {
        $this->whereHaving('where', $conditions, '$and');
        return $this;
    }

    public function orWhere($conditions)
    {
        $this->whereHaving('where', $conditions, '$or');
        return $this;
    }

    public function having($conditions)
    {
        $this->whereHaving('having', $conditions, '$and');
        return $this;
    }

    public function orHaving($conditions)
    {
        $this->whereHaving('having', $conditions, '$or');
        return $this;
    }

    public function groupBy($columns)
    {
        if (!$this->parts['group by']) {
            $this->parts['group by'] = [];
        }

        if (!is_array($columns)) {
            $columns = [ $columns ];
        }

        foreach ($columns as $column) {
            $this->parts['group by'][] = $this->db->quoteIdentifier($value);
        }

        return $this;
    }

    public function orderBy($columns)
    {
        if (!$this->parts['order by']) {
            $this->parts['order by'] = [];
        }

        if (!is_array($columns)) {
            $columns = [ $columns ];
        }

        foreach ($columns as $key => $value) {
            if (is_numeric($key)) {
                $this->parts['order by'][] = $this->db->quoteIdentifier($value) . ' ASC';
            } else {
                $this->parts['order by'][] = $this->db->quoteIdentifier($key) . (strtoupper($value) == 'DESC' ? ' DESC' : ' ASC');
            }
        }

        return $this;
    }

    public function limit($limit)
    {
        $this->parts['limit'] = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->parts['offset'] = $offset;
        return $this;
    }

    public function values($values)
    {
        $this->parts['values'] = $values;
        return $this;
    }

    public function onDuplicateKey($action, $updates = null)
    {
        if ($action == self::IGNORE) {
            $this->what = "INSERT IGNORE";
        } else {
            $this->what = "INSERT";
            $this->parts['on duplicate key update'] = $updates;
        }

        return $this;
    }

    protected function buildSql($collectBind)
    {
        $what = $sql = $this->parts['what'];
        $bind = [];
        switch ($what) {
            case 'SELECT':
                $sql .= ' ' . ($this->parts['columns'] ? implode(', ', $this->parts['columns']) : '*');
                if ($this->parts['from']) {
                    $sql .= ' FROM ' . $this->parts['from'];
                    if ($this->parts['join']) {
                        $sql .= ' ' . implode(' ', $this->parts['join']);
                        if ($collectBind && count($this->parts['join bind']) > 0) {
                            $bind = array_merge($bind, $this->parts['join bind']);
                        }
                    }
                }
                if ($this->parts['where']) {
                    $sql .= ' WHERE ' . $this->parts['where']->stringify($this->db);
                    if ($collectBind) {
                        $bind = array_merge($bind, $this->parts['where']->getBindValues());
                    }
                }
                if ($this->parts['group by']) {
                    $sql .= ' GROUP BY ' . implode(', ', $this->parts['group by']);
                }
                if ($this->parts['having']) {
                    $sql .= ' HAVING ' . $this->parts['having']->stringify($this->db);
                    if ($collectBind) {
                        $bind = array_merge($bind, $this->parts['having']->getBindValues());
                    }
                }
                if ($this->parts['order by']) {
                    $sql .= ' ORDER BY ' . implode(', ', $this->parts['order by']);
                }
                if ($this->parts['limit']) {
                    $sql .= ' LIMIT ' . $this->parts['limit'];
                    if ($this->parts['offset']) {
                        $sql .= ' OFFSET ' . $this->parts['offset'];
                    }
                }
                break;

            case 'INSERT':
            case 'INSERT IGNORE':
                $keys = [];
                $values = [];
                foreach (array_keys($this->parts['values']) as $index => $key) {
                    $keys[] = $this->db->quoteIdentifier($key);
                    $values[] = '?';
                    if ($collectBind) {
                        $bind[] = $this->parts['values'][$key];
                    }
                }
                $sql .= ' INTO ' . $this->parts['table'] . ' (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $values) . ')';
                break;

            case 'UPDATE':
                $sets = [];
                foreach (array_keys($this->parts['values']) as $index => $key) {
                    $sets[] = $this->db->quoteIdentifier($key) . ' = ?';
                    if ($collectBind) {
                        $bind[] = $this->parts['values'][$key];
                    }
                }
                $sql .= ' ' . $this->parts['table'] . ' SET ' . implode(', ', $sets);
                if ($this->parts['where']) {
                    $sql .= ' WHERE ' . $this->parts['where']->stringify($this->db);
                    if ($collectBind) {
                        $bind = array_merge($bind, $this->parts['where']->getBindValues());
                    }
                }
                break;

            case 'DELETE':
                $sql .= ' FROM ' . ($this->parts['from'] ? $this->parts['from'] : $this->parts['table']);
                if ($this->parts['where']) {
                    $sql .= ' WHERE ' . $this->parts['where']->stringify($this->db);
                    if ($collectBind) {
                        $bind = $this->parts['where']->getBindValues();
                    }
                }
                break;
        }

        return [ $sql, $bind ];
    }

    /**
     * Executes the query.
     *
     * @return PDOStatement The resulting PDO statement
     */
    public function execute()
    {
        // Build SQL and collect bind values
        list($sql, $bind) = $this->buildSql(true);

        // Prepare and execute statement
        $statement = $this->db->prepare($sql);
        if (!$statement) {
            throw new Exception('Filed to prepare statement');
        }
        foreach ($bind as $index => $value) {
            $statement->bindValue($index + 1, $value);
        }
        $statement->execute();

        return $statement;
    }

    /**
     * Returns the string version of the query.
     * WARNING! Do not use this string to pass to your database engine as the
     * filled in values may be unsafe. Use for debugging purposes only! To
     * execute the query on the databse, use {@see execute()} instead.
     *
     * @return string
     */
    public function __toString()
    {
        list($sql, $bind) = $this->buildSql(true);
        foreach ($bind as $value) {
            $pos = strpos($sql, '?');
            if ($pos !== false) {
                $sql = substr_replace($sql, $this->db->quote($value), $pos, 1);
            }
        }

        return $sql;
    }

    public function getDb()
    {
        return $this->db;
    }

    public static function Value($value)
    {
        return new Value($value);
    }

    public static function Column($name)
    {
        return new Column($name);
    }
}
