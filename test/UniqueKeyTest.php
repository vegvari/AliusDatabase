<?php

namespace Alius\Database;

class UniqueKeyTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $constraint = new UniqueKey('foo', ['column']);
        $this->assertSame('foo', $constraint->getName());
        $this->assertSame('UNIQUE KEY `foo` (`column`)', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `foo` UNIQUE (`column`)', $constraint->buildAdd());
        $this->assertSame('DROP INDEX `foo`', $constraint->buildDrop());

        $constraint = new UniqueKey('foo', ['column', 'column2']);
        $this->assertSame('foo', $constraint->getName());
        $this->assertSame('UNIQUE KEY `foo` (`column`, `column2`)', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `foo` UNIQUE (`column`, `column2`)', $constraint->buildAdd());
        $this->assertSame('DROP INDEX `foo`', $constraint->buildDrop());
    }

    public function testDuplicated()
    {
        $this->expectException(ConstraintException::class);
        new UniqueKey('foo', ['column', 'column']);
    }
}
