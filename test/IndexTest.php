<?php

namespace Alius\Database;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $constraint = new Index('foo', ['column']);
        $this->assertSame('foo', $constraint->getName());
        $this->assertSame('KEY `foo` (`column`)', $constraint->buildCreate());
        $this->assertSame('ADD INDEX `foo` (`column`)', $constraint->buildAdd());
        $this->assertSame('DROP INDEX `foo`', $constraint->buildDrop());

        $constraint = new Index('foo', ['column', 'column2']);
        $this->assertSame('foo', $constraint->getName());
        $this->assertSame('KEY `foo` (`column`, `column2`)', $constraint->buildCreate());
        $this->assertSame('ADD INDEX `foo` (`column`, `column2`)', $constraint->buildAdd());
        $this->assertSame('DROP INDEX `foo`', $constraint->buildDrop());
    }

    public function testDuplicated()
    {
        $this->expectException(ConstraintException::class);
        new Index('foo', ['column', 'column']);
    }
}
