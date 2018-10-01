<?php

namespace RestKit\Resource;

class Database extends AbstractResource
{
    public function index()
    {
        $table = $this->getTable();
        $select = $table->select();
        $this->trigger('build_index_query', $select);
        $results = $table->fetchAll($select);

        return $results->fetchAll();
    }

    public function detail($id)
    {
        $table = $this->getTable();
        $item = $table->findOne([ 'id' => $id ]);
        if (!$item) {
            throw new NotFoundException('No item found with that ID');
        }

        return $item ? $item->toArray() : null;
    }

    public function add()
    {
        $table = $this->getTable();
        $data = $this->request->getParsedBody();
        $id = $table->insert($data);
        if (!$id) return null;
        return $this->detail($id);
    }

    public function update($id)
    {
        $table = $this->getTable();
        $data = $this->request->getParsedBody();
        $table->update($data, [ 'id' => $id ]);
        return $this->detail($id);
    }

    public function delete($id)
    {
        $table = $this->getTable();
        $data = $this->request->getParsedBody();
        $table->delete([ 'id' => $id ]);
        return [ 'id' => $id ];
    }
}
