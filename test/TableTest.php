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
        $this->assertSame([], $table->getUniqueKey());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setUniqueKey('id');
        $this->assertSame(true, $table->hasUniqueKey());
        $this->assertSame(true, $table->hasUniqueKey('unique_foo_1'));
        $this->assertSame(['unique_foo_1' => ['id']], $table->getUniqueKey());

        $table->setUniqueKey('id');
        $this->assertSame(true, $table->hasUniqueKey('unique_foo_2'));
        $this->assertSame(['unique_foo_1' => ['id'], 'unique_foo_2' => ['id']], $table->getUniqueKey());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setUniqueKey('id', 'id2');
        $this->assertSame(true, $table->hasUniqueKey());
        $this->assertSame(true, $table->hasUniqueKey('unique_foo_1'));
        $this->assertSame(['unique_foo_1' => ['id', 'id2']], $table->getUniqueKey());
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
        $this->assertSame([], $table->getIndex());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setIndex('id');
        $this->assertSame(true, $table->hasIndex());
        $this->assertSame(true, $table->hasIndex('index_foo_1'));
        $this->assertSame(['index_foo_1' => ['id']], $table->getIndex());

        $table->setIndex('id');
        $this->assertSame(true, $table->hasIndex('index_foo_2'));
        $this->assertSame(['index_foo_1' => ['id'], 'index_foo_2' => ['id']], $table->getIndex());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setIndex('id', 'id2');
        $this->assertSame(true, $table->hasIndex());
        $this->assertSame(true, $table->hasIndex('index_foo_1'));
        $this->assertSame(['index_foo_1' => ['id', 'id2']], $table->getIndex());
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
}
