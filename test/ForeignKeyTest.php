<?php

namespace Alius\Database;

class ForeignKeyTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $fk = new ForeignKey('name', 'column', 'parent_table');
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column`)', $fk->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column`)', $fk->buildAdd());
        $this->assertSame('DROP FOREIGN KEY `name`', $fk->buildDrop());

        $fk = new ForeignKey('name', 'column', 'parent_table', 'column2');
        $this->assertSame(['column2'], $fk->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column2`)', $fk->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`) REFERENCES `parent_table` (`column2`)', $fk->buildAdd());

        $fk = new ForeignKey('name', ['column', 'column2'], 'parent_table');
        $this->assertSame(['column', 'column2'], $fk->getColumns());
        $this->assertSame(['column', 'column2'], $fk->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column`, `column2`)', $fk->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column`, `column2`)', $fk->buildAdd());

        $fk = new ForeignKey('name', ['column', 'column2'], 'parent_table', ['column3', 'column4']);
        $this->assertSame(['column', 'column2'], $fk->getColumns());
        $this->assertSame(['column3', 'column4'], $fk->getParentColumns());
        $this->assertSame('CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column3`, `column4`)', $fk->buildCreate());
        $this->assertSame('ADD CONSTRAINT `name` FOREIGN KEY (`column`, `column2`) REFERENCES `parent_table` (`column3`, `column4`)', $fk->buildAdd());
    }

    public function testDuplicatedChild()
    {
        $this->expectException(ConstraintException::class);
        $fk = new ForeignKey('name', ['column', 'column'], 'parent_table');
    }

    public function testDuplicatedParent()
    {
        $this->expectException(ConstraintException::class);
        $fk = new ForeignKey('name', ['column', 'column2'], 'parent_table', ['column', 'column']);
    }

    public function testDuplicatedMoreChild()
    {
        $this->expectException(ConstraintException::class);
        $fk = new ForeignKey('name', ['column', 'column2'], 'parent_table', 'column');
    }

    public function testDuplicatedMoreParent()
    {
        $this->expectException(ConstraintException::class);
        $fk = new ForeignKey('name', 'column', 'parent_table', ['column', 'column2']);
    }

    public function testActions()
    {
        $fk = new ForeignKey('name', 'column', 'parent_table', 'column', 'CASCADE', 'CASCADE');
        $this->assertSame('CASCADE', $fk->getOnUpdateAction());
        $this->assertSame('CASCADE', $fk->getOnDeleteAction());

        $fk = new ForeignKey('name', 'column', 'parent_table', 'column', 'NO ACTION', 'NO ACTION');
        $this->assertSame('RESTRICT', $fk->getOnUpdateAction());
        $this->assertSame('RESTRICT', $fk->getOnDeleteAction());

        $fk = new ForeignKey('name', 'column', 'parent_table', 'column', 'SET DEFAULT', 'SET DEFAULT');
        $this->assertSame('SET DEFAULT', $fk->getOnUpdateAction());
        $this->assertSame('SET DEFAULT', $fk->getOnDeleteAction());

        $fk = new ForeignKey('name', 'column', 'parent_table', 'column', 'SET NULL', 'SET NULL');
        $this->assertSame('SET NULL', $fk->getOnUpdateAction());
        $this->assertSame('SET NULL', $fk->getOnDeleteAction());
    }

    public function testInvalidUpdate()
    {
        $this->expectException(ConstraintException::class);
        $fk = new ForeignKey('name', 'column', 'parent_table', 'column', 'foo');
    }

    public function testInvalidDelete()
    {
        $this->expectException(ConstraintException::class);
        $fk = new ForeignKey('name', 'column', 'parent_table', 'column', 'CASCADE', 'foo');
    }
}
