<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;

class ForeignKeyTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $constraint = new ForeignKey('name', 'column', 'parent_table');
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildAdd());
        $this->assertSame('DROP FOREIGN KEY `name`', $constraint->buildDrop());

        $constraint = new ForeignKey('name', 'column', 'parent_table', 'column2');
        $this->assertSame(['column2'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column2`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column2`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildAdd());

        $constraint = new ForeignKey('name', ['column', 'column2'], 'parent_table');
        $this->assertSame(['column', 'column2'], $constraint->getColumns());
        $this->assertSame(['column', 'column2'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column`, `column2`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column`, `column2`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildAdd());

        $constraint = new ForeignKey('name', ['column', 'column2'], 'parent_table', ['column3', 'column4']);
        $this->assertSame(['column', 'column2'], $constraint->getColumns());
        $this->assertSame(['column3', 'column4'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column3`, `column4`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column3`, `column4`) ON UPDATE RESTRICT ON DELETE RESTRICT', $constraint->buildAdd());
    }

    public function testEmptyName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_INVALID_NAME);

        new ForeignKey('', 'column', 'parent');
    }

    public function testInvalidName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_INVALID_NAME);

        new ForeignKey('primary', 'column', 'parent');
    }

    public function testNoColumn()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_NO_COLUMN);

        new ForeignKey('name', [], 'parent_table');
    }

    public function testDuplicatedChild()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_DUPLICATED_CHILD_COLUMN);

        new ForeignKey('name', ['column', 'column'], 'parent_table');
    }

    public function testDuplicatedParent()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_DUPLICATED_PARENT_COLUMN);

        new ForeignKey('name', ['column', 'column2'], 'parent_table', ['column', 'column']);
    }

    public function testDuplicatedMoreChild()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_MORE_CHILD_COLUMN);

        new ForeignKey('name', ['column', 'column2'], 'parent_table', 'column');
    }

    public function testDuplicatedMoreParent()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_MORE_PARENT_COLUMN);

        new ForeignKey('name', 'column', 'parent_table', ['column', 'column2']);
    }

    public function testActions()
    {
        $constraint = new ForeignKey('name', 'column', 'parent_table', 'column', 'CASCADE', 'CASCADE');
        $this->assertSame('CASCADE', $constraint->getUpdateRule());
        $this->assertSame('CASCADE', $constraint->getDeleteRule());

        $constraint = new ForeignKey('name', 'column', 'parent_table', 'column', 'NO ACTION', 'NO ACTION');
        $this->assertSame('RESTRICT', $constraint->getUpdateRule());
        $this->assertSame('RESTRICT', $constraint->getDeleteRule());

        $constraint = new ForeignKey('name', 'column', 'parent_table', 'column', 'SET DEFAULT', 'SET DEFAULT');
        $this->assertSame('SET DEFAULT', $constraint->getUpdateRule());
        $this->assertSame('SET DEFAULT', $constraint->getDeleteRule());

        $constraint = new ForeignKey('name', 'column', 'parent_table', 'column', 'SET NULL', 'SET NULL');
        $this->assertSame('SET NULL', $constraint->getUpdateRule());
        $this->assertSame('SET NULL', $constraint->getDeleteRule());
    }

    public function testInvalidUpdate()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_INVALID_UPDATE_RULE);

        new ForeignKey('name', 'column', 'parent_table', 'column', 'foo');
    }

    public function testInvalidDelete()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::FOREIGN_KEY_INVALID_DELETE_RULE);

        new ForeignKey('name', 'column', 'parent_table', 'column', 'CASCADE', 'foo');
    }
}
