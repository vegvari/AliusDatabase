<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;

class UniqueKeyTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $constraint = new UniqueKey('foo', 'column');
        $this->assertSame('foo', $constraint->getName());
        $this->assertSame('UNIQUE KEY `foo` (`column`)', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `foo` UNIQUE (`column`)', $constraint->buildAdd());
        $this->assertSame('DROP INDEX `foo`', $constraint->buildDrop());

        $constraint = new UniqueKey('foo', 'column', 'column2');
        $this->assertSame('foo', $constraint->getName());
        $this->assertSame('UNIQUE KEY `foo` (`column`, `column2`)', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `foo` UNIQUE (`column`, `column2`)', $constraint->buildAdd());
        $this->assertSame('DROP INDEX `foo`', $constraint->buildDrop());
    }

    public function testEmptyName()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::UNIQUE_KEY_INVALID_NAME);

        new UniqueKey('', 'column');
    }

    public function testInvalidName()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::UNIQUE_KEY_INVALID_NAME);

        new UniqueKey('primary', 'column');
    }

    public function testNoColumn()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::UNIQUE_KEY_NO_COLUMN);

        new UniqueKey('foo');
    }

    public function testDuplicated()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::UNIQUE_KEY_DUPLICATED_COLUMN);

        new UniqueKey('foo', 'column', 'column');
    }
}
