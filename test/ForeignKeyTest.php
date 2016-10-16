<?php

namespace Alius\Database;

class ForeignKeyTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $constraint = new ForeignKey('name', 'column', 'parent_table');
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column`)', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column`)', $constraint->buildAdd());
        $this->assertSame('DROP FOREIGN KEY `name`', $constraint->buildDrop());

        $constraint = new ForeignKey('name', 'column', 'parent_table', 'column2');
        $this->assertSame(['column2'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column2`)', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column2`)', $constraint->buildAdd());

        $constraint = new ForeignKey('name', ['column', 'column2'], 'parent_table');
        $this->assertSame(['column', 'column2'], $constraint->getColumns());
        $this->assertSame(['column', 'column2'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column`, `column2`)', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column`, `column2`)', $constraint->buildAdd());

        $constraint = new ForeignKey('name', ['column', 'column2'], 'parent_table', ['column3', 'column4']);
        $this->assertSame(['column', 'column2'], $constraint->getColumns());
        $this->assertSame(['column3', 'column4'], $constraint->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column3`, `column4`)', $constraint->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column3`, `column4`)', $constraint->buildAdd());
    }

    public function testDuplicatedChild()
    {
        $this->expectException(ConstraintException::class);
        new ForeignKey('name', ['column', 'column'], 'parent_table');
    }

    public function testDuplicatedParent()
    {
        $this->expectException(ConstraintException::class);
        new ForeignKey('name', ['column', 'column2'], 'parent_table', ['column', 'column']);
    }

    public function testDuplicatedMoreChild()
    {
        $this->expectException(ConstraintException::class);
        new ForeignKey('name', ['column', 'column2'], 'parent_table', 'column');
    }

    public function testDuplicatedMoreParent()
    {
        $this->expectException(ConstraintException::class);
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
        $this->expectException(ConstraintException::class);
        new ForeignKey('name', 'column', 'parent_table', 'column', 'foo');
    }

    public function testInvalidDelete()
    {
        $this->expectException(ConstraintException::class);
        new ForeignKey('name', 'column', 'parent_table', 'column', 'CASCADE', 'foo');
    }
}
