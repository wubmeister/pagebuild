<?php

use PHPUnit\Framework\TestCase;

use DatabaseKit\Query\Condition;
use DatabaseKit\Database;

class DatabaseKit_Query_ConditionTest extends TestCase
{
    protected $database;

    public function __construct()
    {
        parent::__construct();
        $this->database = new Database(null);
    }

    public function testEquals()
    {
        $condition = new Condition('a', 'b');
        $this->assertEquals('"a" = ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals('b', $bindValues[0]);

        $condition = new Condition('foo', 'bar');
        $this->assertEquals('"foo" = ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals('bar', $bindValues[0]);
    }

    public function testOperands()
    {
        $condition = new Condition('value', [ '$lt' => 42 ]);
        $this->assertEquals('"value" < ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);

        $condition = new Condition('value', [ '$lte' => 42 ]);
        $this->assertEquals('"value" <= ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);

        $condition = new Condition('value', [ '$gt' => 42 ]);
        $this->assertEquals('"value" > ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);

        $condition = new Condition('value', [ '$gte' => 42 ]);
        $this->assertEquals('"value" >= ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);

        $condition = new Condition('value', [ '$neq' => 42 ]);
        $this->assertEquals('"value" <> ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(1, $bindValues);
        $this->assertEquals(42, $bindValues[0]);
    }

    public function testBetween()
    {
        $condition = new Condition('value', [ '$between' => [ 42, 84 ] ]);
        $this->assertEquals('"value" BETWEEN ? AND ?', $condition->stringify($this->database));
        $bindValues = $condition->getBindValues();
        $this->assertInternalType('array', $bindValues);
        $this->assertCount(2, $bindValues);
        $this->assertEquals(42, $bindValues[0]);
        $this->assertEquals(84, $bindValues[1]);
    }

    public function testIn()
    {
        $condition = new Condition('value', [ '$in' => [ 42, 84, 126, 168 ] ]);
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
        $condition = new Condition('$and', [ 'a' => 'b', 'foo' => 'bar' ]);
        $this->assertEquals('("a" = ?) AND ("foo" = ?)', $condition->stringify($this->database));
    }

    public function testOr()
    {
        $condition = new Condition('$or', [ 'a' => 'b', 'foo' => 'bar' ]);
        $this->assertEquals('("a" = ?) OR ("foo" = ?)', $condition->stringify($this->database));
    }

    public function testNesting()
    {
        $condition = new Condition('$and', [ 'a' => 'b', 'foo' => 'bar', '$or' => [ 'lorem' => 'ipsum', 'doler' => 'sit amet' ] ]);
        $this->assertEquals('("a" = ?) AND ("foo" = ?) AND (("lorem" = ?) OR ("doler" = ?))', $condition->stringify($this->database));
    }
}
