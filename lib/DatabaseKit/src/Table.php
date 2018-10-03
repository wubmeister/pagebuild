<?php

namespace DatabaseKit;

use DatabaseKit\Table\Rowset;

class Table
{
    protected $name;
    protected $db;

    public function __construct($name, Database $db)
    {
        $this->name = $name;
        $this->db = $db;
    }

    public function findAll($where = null)
    {
        $query = new Query($this->db);
        $query->select()->from($this->name);
        if ($where) {
            $query->where($where);
        }

        return new Rowset($this, $query);
    }

    public function findOne($where = null)
    {
        $query = new Query($this->db);
        $query->select()->from($this->name);
        if ($where) {
            $query->where($where);
        }

        $statement = $query->execute();
        return new Row($this, $statement->fetch(\PDO::FETCH_ASSOC));
    }

    public function insert($values)
    {
        $query = new Query($this->db);
        $query->insert()->into($this->name)->values($values);
        $query->execute();
        return $this->db->lastInsertId();
    }

    public function update($values, $where = null)
    {
        $query = new Query($this->db);
        $query->update()->table($this->name)->values($values);
        if ($where) {
            $query->where($where);
        }
        $statement = $query->execute();
        return $statement->rowCount();
    }

    public function delete($where = null)
    {
        $query = new Query($this->db);
        $query->delete()->from($this->name);
        if ($where) {
            $query->where($where);
        }
        $statement = $query->execute();
        return $statement->rowCount();
    }
}
