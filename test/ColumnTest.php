<?php

namespace Alius\Database;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testFactories()
    {
        // char
        $this->assertEquals(new CharColumn('foo', 'char', 0), Column::char('foo', 0));
        $this->assertEquals(new CharColumn('foo', 'varchar', 0), Column::varchar('foo', 0));

        // int
        $this->assertEquals(new IntColumn('foo', 'tinyint'), Column::tinyint('foo'));
        $this->assertEquals(new IntColumn('foo', 'smallint'), Column::smallint('foo'));
        $this->assertEquals(new IntColumn('foo', 'int'), Column::int('foo'));
        $this->assertEquals(new IntColumn('foo', 'mediumint'), Column::mediumint('foo'));
        $this->assertEquals(new IntColumn('foo', 'bigint'), Column::bigint('foo'));

        $this->assertEquals((new IntColumn('foo', 'tinyint'))->unsigned()->autoIncrement(), Column::tinyserial('foo'));
        $this->assertEquals((new IntColumn('foo', 'smallint'))->unsigned()->autoIncrement(), Column::smallserial('foo'));
        $this->assertEquals((new IntColumn('foo', 'int'))->unsigned()->autoIncrement(), Column::serial('foo'));
        $this->assertEquals((new IntColumn('foo', 'mediumint'))->unsigned()->autoIncrement(), Column::mediumserial('foo'));
        $this->assertEquals((new IntColumn('foo', 'bigint'))->unsigned()->autoIncrement(), Column::bigserial('foo'));
    }
}
