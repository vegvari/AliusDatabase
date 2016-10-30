<?php

namespace Alius\Database\MySQL;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testFactories()
    {
        // char
        $this->assertEquals(new CharColumn('foo', 'char', 0), Column::char('foo', 0));
        $this->assertEquals(new CharColumn('foo', 'varchar', 0), Column::varchar('foo', 0));

        // datetime
        $this->assertEquals(new DateTimeColumn('foo'), Column::datetime('foo'));

        // decimal
        $this->assertEquals(new DecimalColumn('foo', 10, 2), Column::decimal('foo', 10, 2));

        // float
        $this->assertEquals(new FloatColumn('foo', 10, 2), Column::float('foo', 10, 2));

        // int
        $this->assertEquals(new IntColumn('foo', 'tinyint'), Column::tinyint('foo'));
        $this->assertEquals(new IntColumn('foo', 'smallint'), Column::smallint('foo'));
        $this->assertEquals(new IntColumn('foo', 'int'), Column::int('foo'));
        $this->assertEquals(new IntColumn('foo', 'mediumint'), Column::mediumint('foo'));
        $this->assertEquals(new IntColumn('foo', 'bigint'), Column::bigint('foo'));

        $this->assertEquals((new IntColumn('foo', 'tinyint'))->setUnsigned()->setAutoIncrement(), Column::tinyserial('foo'));
        $this->assertEquals((new IntColumn('foo', 'smallint'))->setUnsigned()->setAutoIncrement(), Column::smallserial('foo'));
        $this->assertEquals((new IntColumn('foo', 'int'))->setUnsigned()->setAutoIncrement(), Column::serial('foo'));
        $this->assertEquals((new IntColumn('foo', 'mediumint'))->setUnsigned()->setAutoIncrement(), Column::mediumserial('foo'));
        $this->assertEquals((new IntColumn('foo', 'bigint'))->setUnsigned()->setAutoIncrement(), Column::bigserial('foo'));

        // text
        $this->assertEquals(new TextColumn('foo', 'tinytext'), Column::tinytext('foo'));
        $this->assertEquals(new TextColumn('foo', 'text'), Column::text('foo'));
        $this->assertEquals(new TextColumn('foo', 'mediumtext'), Column::mediumtext('foo'));
        $this->assertEquals(new TextColumn('foo', 'longtext'), Column::longtext('foo'));

        // timestamp
        $this->assertEquals(new TimestampColumn('foo'), Column::timestamp('foo'));
    }
}
