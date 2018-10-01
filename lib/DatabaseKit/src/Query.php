<?php

/**
 * Represnts an SQL query
 *
 * @author Wubbo Bos <wubbo@wubbobos.nl>
 */
class Query
{
    const RHP_VALUE = 1;
    const RHP_COLUMN = 2;

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

    protected function buildConditions($array, $rightHandPolicy = self::RHP_VALUE)
    {
        $conditions = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $op = current(array_keys($value));
                $val = '?';
                $insertValue = $value[$op];

                switch ($op) {
                    case '$lt':
                        $operand = '<';
                        break;
                    case '$lte':
                        $operand = '<=';
                        break;
                    case '$gt':
                        $operand = '>';
                        break;
                    case '$gte':
                        $operand = '>=';
                        break;
                    case '$neq':
                        $operand = '<>';
                        break;
                    case '$like':
                        $operand = 'LIKE';
                        break;
                    case '$between':
                        $operand = 'BETWEEN';
                        $val = '? AND ?';
                        break;
                }
            } else {
                $insertValue = $value;
                $operand = '=';
                $val = '?';
            }

            $condition = $this->db->quoteIdentifier($key) . ' ' . $operand . ' ' . $val;
            if ($rightHandPolicy == self::RHP_COLUMN && is_string($insertValue)) {
                $condition = str_replace('?', $this->db->quoteIdentifier($insertValue), $condition);
            }

            $conditions[] = $condition;
        }


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
        $this->parts['join'] = strtoupper($type) . ' ' . $result['sql'];

    }


    public function where($conditions)
    {

    }

    public function having($conditions)
    {

    }


    public function groupBy($columns)
    {

    }

    public function orderBy($columns)
    {

    }

    public function limit($limit)
    {

    }

    public function offset($offset)
    {

    }


    public function values($values)
    {

    }

    public function onDuplicateKey($action = IGNORE | UPDATE, $updates = null)
    {

    }

}
