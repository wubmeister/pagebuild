<?php

/**
 * Represnts an SQL query
 *
 * @author Wubbo Bos <wubbo@wubbobos.nl>
 */
class Query
{
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

    public function from($table, $columns = '*')
    {
        if (is_array($table)) {
            $alias = current(array_keys($table));
            $table = $table[$alias];
            $this->parts['table'] = $this->db->quoteIdentifier($table) . ' AS ' . $this->db->quoteIdentifier($alias);
        } else {
            $alias = $table;
        }

        if (!isset($this->parts['columns'])) {
            $this->parts['columns'] = [];
        }

        if (is_array($columns)) {
            $qAlias = $this->db->quoteIdentifier($alias);
            foreach ($columns as $key => $column) {
                if (!is_numeric($key)) {
                    $this->parts['columns'][] = $qAlias . '.' . $this->db->quoteIdentifier($column) .
                        ' AS ' . $this->db->quoteIdentifier($key);
                } else {
                    $this->parts['columns'][] = $qAlias . '.' . $this->db->quoteIdentifier($column);
                }
            }
        }

        return $this;
    }

    public function table($table)
    {
        if (is_array($table)) {
            $alias = current(array_keys($table));
            $table = $table[$alias];
            $this->parts['table'] = $this->db->quoteIdentifier($table) . ' AS ' . $this->db->quoteIdentifier($alias);
        } else {
            $alias = $table;
        }

        return $this;
    }

    public function join($type, $table, $condition, $columns)
    {

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
