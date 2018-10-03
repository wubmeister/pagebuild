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
    const RHP_VALUE = 1;
    const RHP_COLUMN = 2;

    const GLUE_AND = 1;
    const GLUE_OR = 2;

    const IGNORE = 1;
    const UPDATE = 2;

    protected $db;
    protected $parts = [];

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function select()
    {
        $this->parts['what'] = 'SELECT';
    }

    public function insert()
    {
        $this->parts['what'] = 'INSERT';
    }

    public function update()
    {
        $this->parts['what'] = 'UPDATE';
    }

    public function delete()
    {
        $this->parts['what'] = 'DELETE';
    }

    protected function tableDef($table)
    {
        $result = [];

        if (is_array($table)) {
            $result['alias'] = current(array_keys($table));
            $result['table'] = $table[$alias];
            $result['sql'] = $this->db->quoteIdentifier($table) . ' AS ' . $this->db->quoteIdentifier($alias);
        } else {
            $result['alias'] = $table;
            $result['table'] = $table;
            $result['sql'] = $this->db->quoteIdentifier($table);
        }
    }

    protected function addColumns($alias, $columns)
    {
        $qAlias = $this->db->quoteIdentifier($alias);

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
        $conditions = $glue == self::GLUE_OR ? [ '$or' => $array ] : [ '$and' => $array ];
        return new Condition($conditions);
    }

    public function from($table, $columns = '*')
    {
        $table = $this->tableDef($table);
        $this->parts['from'] = $result['sql'];

        if (!isset($this->parts['columns'])) {
            $this->parts['columns'] = [];
        }

        $this->addColumns($result['alias'], $columns);

        return $this;
    }

    public function table($table)
    {
        $table = $this->tableDef($table);
        $this->parts['table'] = $result['sql'];

        $this->addColumns($result['alias'], $columns);

        return $this;
    }

    public function join($type, $table, $condition, $columns)
    {
        $table = $this->tableDef($table);

        if (!$this->parts['join'])
        $this->parts['join'] =
            strtoupper($type) . ' JOIN ' . $result['sql'] . ' ON ' .
            $this->buildConditions($condition, self::GLUE_AND, Condition::RHP_COLUMN)->stringify($this->db);
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
    }

    public function orWhere($conditions)
    {
        $this->whereHaving('where', $conditions, '$or');
    }

    public function having($conditions)
    {
        $this->whereHaving('having', $conditions, '$and');
    }

    public function orHaving($conditions)
    {
        $this->whereHaving('having', $conditions, '$or');
    }

    protected function groupOrder($part, $columns)
    {
        if (!$this->parts[$part]) {
            $this->parts[$part] = [];
        }

        if (!is_array($columns)) {
            $columns = [ $columns ];
        }

        foreach ($columns as $key => $value) {
            if (is_numeric($key)) {
                $this->parts[$part][] = [ $this->db->quoteIdentifier($value), 'ASC' ];
            } else {
                $this->parts[$part][] = [ $this->db->quoteIdentifier($key), strtoupper($value) == 'DESC' ? 'DESC' : 'ASC' ];
            }
        }
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
    }

    public function limit($limit)
    {
        $this->parts['limit'] = $limit;
    }

    public function offset($offset)
    {
        $this->parts['offset'] = $offset;
    }

    public function values($values)
    {
        $this->parts['values'] = $value;
    }

    public function onDuplicateKey($action, $updates = null)
    {
        if ($action == self::IGNORE) {
            $this->what = "INSERT IGNORE";
        } else {
            $this->what = "INSERT";
            $this->parts['on duplicate key update'] = $updates;
        }
    }

    public function __toString()
    {
        $what = $sql = $this->parts['what'];
        switch ($what) {
            case 'SELECT':
                $sql .= ' ' . ($this->parts['columns'] ? implode(', ', $this->parts['columns']) : '*');
                if ($this->parts['from']) {
                    $sql .= ' FROM ' . $this->parts['from'];
                    if ($this->parts['join']) {
                        $sql .= ' ' . implode(' ', $this->parts['join']);
                    }
                }
                if ($this->parts['where']) {
                    $sql .= ' WHERE ' . $this->parts['where']->stringify($this->db);
                }
                if ($this->parts['group by']) {
                    $sql .= ' GROUP BY ' . implode(', ', $this->parts['group by']);
                }
                if ($this->parts['having']) {
                    $sql .= ' HAVING ' . $this->parts['having']->stringify($this->db);
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
                $sql .= ' INTO ' . $this->parts['table'];
                break;
        }
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
