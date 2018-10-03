<?php

use PHPUnit\Framework\TestCase;

use DatabaseKit\Query\Condition;
use DatabaseKit\Database;
use DatabaseKit\Query;

class DatabaseKit_Query_ConditionTest extends TestCase
{
    protected $database;
    protected $query;

    public function __construct()
    {
        parent::__construct();
        $this->database = new Database(null);
        $this->query = new Query($this->database);
    }

    public function testEquals()
    {
        $condition = new Condition($this->query, 'a', 'b');
        $this->assertEquals('"a" = ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals('b', $bindValues[0]);

        $condition = new Condition($this->query, 'foo', 'bar');
        $this->assertEquals('"foo" = ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals('bar', $bindValues[0]);
    }

    public function testOperands()
    {
        $condition = new Condition($this->query, 'value', [ '$lt' => 42 ]);
        $this->assertEquals('"value" < ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);

        $condition = new Condition($this->query, 'value', [ '$lte' => 42 ]);
        $this->assertEquals('"value" <= ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);

        $condition = new Condition($this->query, 'value', [ '$gt' => 42 ]);
        $this->assertEquals('"value" > ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);

        $condition = new Condition($this->query, 'value', [ '$gte' => 42 ]);
        $this->assertEquals('"value" >= ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);

        $condition = new Condition($this->query, 'value', [ '$neq' => 42 ]);
        $this->assertEquals('"value" <> ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);
    }

    public function testBetween()
    {
        $condition = new Condition($this->query, 'value', [ '$between' => [ 42, 84 ] ]);
        $this->assertEquals('"value" BETWEEN ? AND ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(2, $bindValues);
        $this->assertEquals(42, $bindValues[0]);
        $this->assertEquals(84, $bindValues[1]);
    }

    public function testIn()
    {
        $condition = new Condition($this->query, 'value', [ '$in' => [ 42, 84, 126, 168 ] ]);
        $this->assertEquals('"value" IN (?, ?, ?, ?)', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(4, $bindValues);
        $this->assertEquals(42, $bindValues[0]);
        $this->assertEquals(84, $bindValues[1]);
        $this->assertEquals(126, $bindValues[2]);
        $this->assertEquals(168, $bindValues[3]);
    }

    public function testAnd()
    {
        $condition = new Condition($this->query, '$and', [ 'a' => 'b', 'foo' => 'bar' ]);
        $this->assertEquals('("a" = ?) AND ("foo" = ?)', $condition->stringify($this->database));
    }

    public function testOr()
    {
        $condition = new Condition($this->query, '$or', [ 'a' => 'b', 'foo' => 'bar' ]);
        $this->assertEquals('("a" = ?) OR ("foo" = ?)', $condition->stringify($this->database));
    }

    public function testNesting()
    {
        $condition = new Condition($this->query, '$and', [ 'a' => 'b', 'foo' => 'bar', '$or' => [ 'lorem' => 'ipsum', 'doler' => 'sit amet' ] ]);
        $this->assertEquals('("a" = ?) AND ("foo" = ?) AND (("lorem" = ?) OR ("doler" = ?))', $condition->stringify($this->database));
    }

    public function testAppendCondition()
    {
        $condition = new Condition($this->query, '$and', [ 'a' => 'b', 'foo' => 'bar' ]);
        $condition->appendCondition(new Condition($this->query, 'lorem', 'ipsum'));
        $this->assertEquals('("a" = ?) AND ("foo" = ?) AND ("lorem" = ?)', $condition->stringify($this->database));
    }
}
