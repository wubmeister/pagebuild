<?php

use PHPUnit\Framework\TestCase;

use DatabaseKit\Database;
use DatabaseKit\Query;

class DatabaseKit_QueryTest extends TestCase
{
    protected $database;

    public function __construct()
    {
        parent::__construct();
        $this->database = new Database(new \PDO("mysql:host=127.0.0.1;dbname=test", "phpunit", "phpunit"));
    }

    public function testSelect()
    {
        $query = new Query($this->database);
        $query->select()->from('tablename');
        $this->assertEquals("SELECT \"tablename\".* FROM \"tablename\"", (string)$query);

        $query = new Query($this->database);
        $query->select()->from('tablename')->where([ 'id' => 42 ]);
        $this->assertEquals("SELECT \"tablename\".* FROM \"tablename\" WHERE (\"id\" = '42')", (string)$query);

        $query = new Query($this->database);
        $query->select()->from('tablename')->where([ 'id' => 42 ])->orderBy('columnname');
        $this->assertEquals("SELECT \"tablename\".* FROM \"tablename\" WHERE (\"id\" = '42') ORDER BY \"columnname\" ASC", (string)$query);
    }

    public function testInsert()
    {
        $query = new Query($this->database);
        $query->insert()->into('tablename')->values([ 'name' => 'Foobar', 'description' => 'This is a description' ]);
        $this->assertEquals("INSERT INTO \"tablename\" (\"name\", \"description\") VALUES ('Foobar', 'This is a description')", (string)$query);
    }

    public function testUpdate()
    {
        $query = new Query($this->database);
        $query->update()->table('tablename')->values([ 'name' => 'Foobar', 'description' => 'This is a description' ])->where([ 'id' => 42 ]);
        $this->assertEquals("UPDATE \"tablename\" SET \"name\" = 'Foobar', \"description\" = 'This is a description' WHERE (\"id\" = '42')", (string)$query);
    }

    public function testDelete()
    {
        $query = new Query($this->database);
        $query->delete()->from('tablename')->where([ 'id' => 42 ]);
        $this->assertEquals("DELETE FROM \"tablename\" WHERE (\"id\" = '42')", (string)$query);
    }
}
