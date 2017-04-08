<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $constraint = new Index('foo', 'column');
        $this->assertSame('foo', $constraint->getName());
        $this->assertSame('KEY `foo` (`column`)', $constraint->buildCreate());
        $this->assertSame('ADD INDEX `foo` (`column`)', $constraint->buildAdd());
        $this->assertSame('DROP INDEX `foo`', $constraint->buildDrop());

        $constraint = new Index('foo', 'column', 'column2');
        $this->assertSame('foo', $constraint->getName());
        $this->assertSame('KEY `foo` (`column`, `column2`)', $constraint->buildCreate());
        $this->assertSame('ADD INDEX `foo` (`column`, `column2`)', $constraint->buildAdd());
        $this->assertSame('DROP INDEX `foo`', $constraint->buildDrop());
    }

    public function testEmptyName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::INDEX_INVALID_NAME);

        new Index('', 'column');
    }

    public function testInvalidName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::INDEX_INVALID_NAME);

        new Index('primary', 'column');
    }

    public function testNoColumn()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::INDEX_NO_COLUMN);

        new Index('foo');
    }

    public function testDuplicatedColumn()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::INDEX_DUPLICATED_COLUMN);

        new Index('foo', 'column', 'column');
    }
}
