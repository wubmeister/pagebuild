<?php

namespace DatabaseKit;

use DatabaseKit\Query\Condition;

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

    protected function buildConditions($array, $glue = self::GLUE_AND, $rightHandPolicy = self::RHP_VALUE)
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
        $this->parts['join'] = [
            strtoupper($type) . ' ' . $result['sql'],
            $this->buildConditions($condition, self::GLUE_AND, self::RHP_COLUMN)->stringify($this->db)
        ];
    }

    public function where($conditions)
    {
        if ($this->parts['where']) {
            if ($this->parts['where']->getKey == '$and') {
                $this->parts['where']->appendConditions($conditions);
            } else {
                $this->parts['where']->appendCondition($this->buildConditions(conditions));
            }
        } else {
            $this->parts['where'] = $this->buildConditions($conditions);
        }
    }

    public function orWhere($conditions)
    {
        $this->parts['where'] = $this->buildConditions($conditions);
    }

    public function having($conditions)
    {
        $this->parts['having'] = $this->buildConditions($conditions);
    }


    public function groupBy($columns)
    {
        if (!$this->parts['group by']) {
            $this->parts['group by'] = [];
        }
    }

    public function orderBy($columns)
    {
        if (!$this->parts['order by']) {
            $this->parts['order by'] = [];
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
        $sql = $this->parts['what'];
    }

}
