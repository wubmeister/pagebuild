<?php

namespace DatabaseKit\Table;

class Row
{
    protected $table;
    protected $data;

    public function __construct($table, $data)
    {
        $this->table = $table;
        $this->data = $data;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function save()
    {
        if ($this->id) {
            $this->table->update($this->data, [ 'id' => $this->id ]);
        } else {
            $this->id = $this->table->insert($this->data);
        }
    }

    public function delete()
    {
        if ($this->id) {
            $this->table->delete([ 'id' => $this->id ]);
        }
    }
}
