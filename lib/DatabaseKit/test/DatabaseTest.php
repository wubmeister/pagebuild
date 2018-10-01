<?php

use PHPUnit\Framework\TestCase;

use DatabaseKit\Database;

class DatabaseKit_DatabaseTest extends TestCase
{
    public function testQuoteIdentifier()
    {
        $database = new Database(null);

        $identifier = $database->quoteIdentifier('foo');
        $this->assertEquals('"foo"', $identifier);

        $identifier = $database->quoteIdentifier('foo.bar');
        $this->assertEquals('"foo"."bar"', $identifier);
    }
}
