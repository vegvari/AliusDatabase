<?php

namespace Alius\Database;

class TableTest extends \PHPUnit_Framework_TestCase
{
    use ConnectionTrait;

    protected $database;

    public function setUp()
    {
        $this->database = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword(), $this->getDatabase());
    }

    public function testName()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame('', $table->getName());
        $table->setName('foo');
        $this->assertSame('foo', $table->getName());
    }

    public function testEngine()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame('InnoDB', $table->getEngine());
        $table->setEngine('MyISAM');
        $this->assertSame('MyISAM', $table->getEngine());
    }

    public function testCharset()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame('utf8', $table->getCharset());
        $table->setCharset('latin1');
        $this->assertSame('latin1', $table->getCharset());
    }

    public function testCollation()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame('utf8_general_ci', $table->getCollation());
        $table->setCollation('latin1_bin');
        $this->assertSame('latin1_bin', $table->getCollation());
    }

    public function testComment()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame(false, $table->hasComment());
        $this->assertSame('', $table->getComment());
        $table->setComment('bar');
        $this->assertSame(true, $table->hasComment());
        $this->assertSame('bar', $table->getComment());
    }

    public function testColumns()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');

        $this->assertSame([], $table->getColumns());
        $this->assertSame(false, $table->hasColumn('id'));

        $column = Column::int('id');
        $table->setColumn($column);
        $this->assertSame(['id' => $column], $table->getColumns());
        $this->assertSame(true, $table->hasColumn('id'));
        $this->assertSame($column, $table->getColumn('id'));
    }

    public function testSetColumnFail()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id'));
    }

    public function testGetColumnFail()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->getColumn('id');
    }

    public function testPrimaryKey()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame(false, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasSimplePrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setPrimaryKey('id');
        $this->assertSame(true, $table->hasPrimaryKey());
        $this->assertSame(true, $table->hasSimplePrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setPrimaryKey('id', 'id2');
        $this->assertSame(true, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasSimplePrimaryKey());
        $this->assertSame(true, $table->hasCompositePrimaryKey());
    }

    public function testPrimaryKeySetTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setPrimaryKey('id');
        $table->setPrimaryKey('id');
    }

    public function testPrimaryKeySetNotDefinedColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setPrimaryKey('id');
    }

    public function testPrimaryKeySetSameTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setPrimaryKey('id', 'id');
    }

    public function testPrimaryKeyAutoIncrement()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::serial('id'));
        $this->assertSame(true, $table->hasPrimaryKey());
        $this->assertSame(true, $table->hasSimplePrimaryKey());
    }

    public function testUniqueKey()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $this->assertSame(false, $table->hasUniqueKey());
        $this->assertSame(false, $table->hasUniqueKey('unique_foo_1'));
        $this->assertSame([], $table->getUniqueKeys());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setUniqueKey('id');
        $this->assertSame(true, $table->hasUniqueKey());
        $this->assertSame(true, $table->hasUniqueKey('unique_foo_1'));
        $this->assertSame(['id'], $table->getUniqueKey('unique_foo_1'));
        $this->assertSame(['unique_foo_1' => ['id']], $table->getUniqueKeys());

        $table->setUniqueKey('id');
        $this->assertSame(true, $table->hasUniqueKey('unique_foo_2'));
        $this->assertSame(['id'], $table->getUniqueKey('unique_foo_2'));
        $this->assertSame(['unique_foo_1' => ['id'], 'unique_foo_2' => ['id']], $table->getUniqueKeys());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setUniqueKey('id', 'id2');
        $this->assertSame(true, $table->hasUniqueKey());
        $this->assertSame(true, $table->hasUniqueKey('unique_foo_1'));
        $this->assertSame(['unique_foo_1' => ['id', 'id2']], $table->getUniqueKeys());
    }

    public function testGetNotDefinedUniqueKey()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->getUniqueKey('foo');
    }

    public function testUniqueKeySetTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setUniqueKeyWithName('id', 'id');
        $table->setUniqueKeyWithName('id', 'id');
    }

    public function testUniqueKeySetNotDefinedColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setUniqueKey('id');
    }

    public function testUniqueKeySetSameTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setUniqueKey('id', 'id');
    }

    public function testIndex()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $this->assertSame(false, $table->hasIndex());
        $this->assertSame(false, $table->hasIndex('index_foo_1'));
        $this->assertSame([], $table->getIndexes());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setIndex('id');
        $this->assertSame(true, $table->hasIndex());
        $this->assertSame(true, $table->hasIndex('index_foo_1'));
        $this->assertSame(['id'], $table->getIndex('index_foo_1'));
        $this->assertSame(['index_foo_1' => ['id']], $table->getIndexes());

        $table->setIndex('id');
        $this->assertSame(true, $table->hasIndex('index_foo_2'));
        $this->assertSame(['id'], $table->getIndex('index_foo_2'));
        $this->assertSame(['index_foo_1' => ['id'], 'index_foo_2' => ['id']], $table->getIndexes());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setIndex('id', 'id2');
        $this->assertSame(true, $table->hasIndex());
        $this->assertSame(true, $table->hasIndex('index_foo_1'));
        $this->assertSame(['index_foo_1' => ['id', 'id2']], $table->getIndexes());
    }

    public function testGetNotDefinedIndex()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->getIndex('foo');
    }

    public function testIndexSetTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setIndexWithName('id', 'id');
        $table->setIndexWithName('id', 'id');
    }

    public function testIndexSetNotDefinedColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setIndex('id');
    }

    public function testIndexSetSameTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setIndex('id', 'id');
    }

    public function testForeignKey()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $this->assertSame(false, $table->hasForeignKey());
        $this->assertSame(false, $table->hasForeignKey('fk_foo_1'));
        $this->assertSame([], $table->getForeignKeys());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setForeignKey('id', 'foo', 'id');
        $this->assertSame(true, $table->hasForeignKey());
        $this->assertSame(true, $table->hasForeignKey('fk_foo_1'));
        $this->assertSame(['columns' => ['id'], 'parent_table' => 'foo', 'parent_columns' => ['id'], 'on_update' => 'RESTRICT', 'on_delete' => 'RESTRICT'], $table->getForeignKey('fk_foo_1'));
        $this->assertSame(['fk_foo_1' => ['columns' => ['id'], 'parent_table' => 'foo', 'parent_columns' => ['id'], 'on_update' => 'RESTRICT', 'on_delete' => 'RESTRICT']], $table->getForeignKeys());

        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setForeignKey('id', 'foo');
        $this->assertSame(['fk_foo_1' => ['columns' => ['id'], 'parent_table' => 'foo', 'parent_columns' => ['id'], 'on_update' => 'RESTRICT', 'on_delete' => 'RESTRICT']], $table->getForeignKeys());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setForeignKey(['id', 'id2'], 'foo');
        $this->assertSame(true, $table->hasForeignKey());
        $this->assertSame(true, $table->hasForeignKey('fk_foo_1'));
        $this->assertSame(['fk_foo_1' => ['columns' => ['id', 'id2'], 'parent_table' => 'foo', 'parent_columns' => ['id', 'id2'], 'on_update' => 'RESTRICT', 'on_delete' => 'RESTRICT']], $table->getForeignKeys());
    }

    public function testGetNotDefinedForeignKey()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->getForeignKey('foo');
    }

    public function testForeignKeyNoAction()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setForeignKey('id', 'foo', 'id', 'NO ACTION', 'NO ACTION');
        $this->assertSame(['fk_foo_1' => ['columns' => ['id'], 'parent_table' => 'foo', 'parent_columns' => ['id'], 'on_update' => 'RESTRICT', 'on_delete' => 'RESTRICT']], $table->getForeignKeys());
    }

    public function testForeignKeySetTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setForeignKeyWithName('bar', 'id', 'foo');
        $table->setForeignKeyWithName('bar', 'id', 'foo');
    }

    public function testForeignKeySetNotDefinedColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setForeignKey('id', 'foo', 'id');
    }

    public function testForeignKeySetSameChildTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setForeignKey(['id', 'id'], 'foo', ['id', 'id2']);
    }

    public function testForeignKeySetSameParentTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setForeignKey(['id', 'id2'], 'foo', ['id', 'id']);
    }

    public function testForeignKeyInvalidOnUpdate()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setForeignKey('id', 'foo', 'id', 'foo');
    }

    public function testForeignKeyInvalidOnDelete()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setForeignKey('id', 'foo', 'id', 'CASCADE', 'foo');
    }
}
