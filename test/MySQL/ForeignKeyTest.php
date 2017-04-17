<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use PHPUnit\Framework\TestCase;

class ForeignKeyTest extends TestCase
{
    public function test()
    {
        $constraint = new ForeignKey('fk', 'foo', 'parent_table');
        $this->assertSame('CONSTRAINT `fk` FOREIGN KEY (`foo`) REFERENCES `parent_table` (`foo`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `fk` FOREIGN KEY (`foo`) REFERENCES `parent_table` (`foo`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildAdd());
        $this->assertSame('DROP FOREIGN KEY `fk`', $constraint->buildDrop());

        $constraint = new ForeignKey('fk', 'foo', 'parent_table', 'bar');
        $this->assertSame(['bar'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `fk` FOREIGN KEY (`foo`) REFERENCES `parent_table` (`bar`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `fk` FOREIGN KEY (`foo`) REFERENCES `parent_table` (`bar`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildAdd());

        $constraint = new ForeignKey('fk', ['foo', 'bar'], 'parent_table');
        $this->assertSame(['foo', 'bar'], $constraint->getColumns());
        $this->assertSame(['foo', 'bar'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `fk` FOREIGN KEY (`foo`, `bar`) REFERENCES `parent_table` (`foo`, `bar`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `fk` FOREIGN KEY (`foo`, `bar`) REFERENCES `parent_table` (`foo`, `bar`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildAdd());

        $constraint = new ForeignKey('fk', ['foo', 'bar'], 'parent_table', ['baz', 'qux']);
        $this->assertSame(['foo', 'bar'], $constraint->getColumns());
        $this->assertSame(['baz', 'qux'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `fk` FOREIGN KEY (`foo`, `bar`) REFERENCES `parent_table` (`baz`, `qux`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `fk` FOREIGN KEY (`foo`, `bar`) REFERENCES `parent_table` (`baz`, `qux`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildAdd());
    }

    public function testEmptyName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_INVALID_NAME);

        new ForeignKey('', 'foo', 'parent');
    }

    public function testInvalidName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_INVALID_NAME);

        new ForeignKey('primary', 'foo', 'parent');
    }

    public function testNoColumn()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_NO_COLUMN);

        new ForeignKey('fk', [], 'parent_table');
    }

    public function testDuplicatedChild()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_DUPLICATED_CHILD_COLUMN);

        new ForeignKey('fk', ['foo', 'foo'], 'parent_table');
    }

    public function testDuplicatedParent()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_DUPLICATED_PARENT_COLUMN);

        new ForeignKey('fk', ['foo', 'bar'], 'parent_table', ['foo', 'foo']);
    }

    public function testDuplicatedMoreChild()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_MORE_CHILD_COLUMN);

        new ForeignKey('fk', ['foo', 'bar'], 'parent_table', 'foo');
    }

    public function testDuplicatedMoreParent()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_MORE_PARENT_COLUMN);

        new ForeignKey('fk', 'foo', 'parent_table', ['foo', 'bar']);
    }

    public function testActions()
    {
        $constraint = new ForeignKey('fk', 'foo', 'parent_table', 'foo', 'CASCADE', 'CASCADE');
        $this->assertSame('CASCADE', $constraint->getUpdateRule());
        $this->assertSame('CASCADE', $constraint->getDeleteRule());

        $constraint = new ForeignKey('fk', 'foo', 'parent_table', 'foo', 'NO ACTION', 'NO ACTION');
        $this->assertSame('RESTRICT', $constraint->getUpdateRule());
        $this->assertSame('RESTRICT', $constraint->getDeleteRule());

        $constraint = new ForeignKey('fk', 'foo', 'parent_table', 'foo', 'SET DEFAULT', 'SET DEFAULT');
        $this->assertSame('SET DEFAULT', $constraint->getUpdateRule());
        $this->assertSame('SET DEFAULT', $constraint->getDeleteRule());

        $constraint = new ForeignKey('fk', 'foo', 'parent_table', 'foo', 'SET NULL', 'SET NULL');
        $this->assertSame('SET NULL', $constraint->getUpdateRule());
        $this->assertSame('SET NULL', $constraint->getDeleteRule());
    }

    public function testInvalidUpdate()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_INVALID_UPDATE_RULE);

        new ForeignKey('fk', 'foo', 'parent_table', 'foo', 'foo');
    }

    public function testInvalidDelete()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_INVALID_DELETE_RULE);

        new ForeignKey('fk', 'foo', 'parent_table', 'foo', 'CASCADE', 'foo');
    }
}
