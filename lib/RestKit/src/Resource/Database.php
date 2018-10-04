<?php

namespace RestKit\Resource;

class Database extends AbstractResource
{
    public function index()
    {
        $table = $this->getTable();
        $select = $table->select();
        $this->trigger('buildIndexQuery', $select);
        $results = $table->fetchAll($select);
        $this->trigger('parseIndexResult', $results);

        return $results->fetchAll();
    }

    public function detail($id)
    {
        $table = $this->getTable();
        $this->trigger('buildDetailQuery', $select);
        $item = $table->findOne([ 'id' => $id ]);
        if (!$item) {
            throw new NotFoundException('No item found with that ID');
        }
        $this->trigger('parseDetailResult', $item);

        return $item ? $item->toArray() : null;
    }

    public function add()
    {
        $table = $this->getTable();
        $data = $this->request->getParsedBody();
        $this->trigger('beforeAdd', $data);
        $id = $table->insert($data);
        if (!$id) return null;
        $item = $table->findOne([ 'id' => $id ]);
        $this->trigger('afterAdd', $item);

        return $item;
    }

    public function update($id)
    {
        $table = $this->getTable();
        $item = $table->findOne([ 'id' => $id ]);
        if (!$item) {
            throw new Exception('No item found with that ID');
        }

        $data = $this->request->getParsedBody();
        $this->trigger('beforeUpdate', $data, $item);
        foreach ($data as $key => $value) {
            $item->$key = $value;
        }
        $item->save();
        $this->trigger('afterUpdate', $item);

        return $item;
    }

    public function delete($id)
    {
        $table = $this->getTable();
        $item = $table->findOne([ 'id' => $id ]);
        if (!$item) {
            throw new Exception('No item found with that ID');
        }

        $this->trigger('beforeDelete', $item);
        $item->delete();
        $this->trigger('afterDelete', $item);

        return [ 'id' => $id ];
    }
}
