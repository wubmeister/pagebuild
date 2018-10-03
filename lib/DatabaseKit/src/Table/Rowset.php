<?php

namespace DatabaseKit\Table;

class Rowset implements Iterator
{
    protected $table;
    protected $query;
    protected $rows;
    protected $index;
    protected $rowCount;

    public function __construct($table, $query)
    {
        $this->table = $table;
        $this->query = $query;
    }

    public function paginate($page, $limit)
    {
        $countSql = "SELECT COUNT(*) AS c FROM (" . (string)$this->query() . ") AS t";
        $row = $this->query->getDb()->fetchRow($countSql);
        $itemCount = $row ? (int)$row['c'] : 0;
        $this->pagination = new Pagination($itemCount, $page, $limit);

        $this->query
            ->limit($limit)
            ->offset($this->pagination->firstItemNumber);

        return $this;
    }

    public function groupBy($columns)
    {
        $this->query->groupBy($columns);
        return $this;
    }

    public function orderBy($columns)
    {
        $this->query->orderBy($columns);
        return $this;
    }

    public function limit($limit)
    {
        $this->query->limit($limit);
        return $this;
    }

    public function offset($offset)
    {
        $this->query->offset($offset);
        return $this;
    }

    public function rewind()
    {
        if (!$this->rows) {
            $statement = $this->query->execute();
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $this->rows[] = new Row($this->table, $row);
            }
        }

        $this->index = 0;
        $this->rowCount = count($this->rows);
    }

    public function next()
    {
        $this->index++;
    }

    public function valid()
    {
        return $this->index < $this->rowCount;
    }

    public function current()
    {
        return $this->rows[$this->index];
    }

    public function key()
    {
        return $this->index;
    }
}
