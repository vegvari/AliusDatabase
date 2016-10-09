<?php

namespace Alius\Database;

class TimestampColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testTimestampColumn()
    {
        $column = new TimestampColumn('foo');
        $this->assertSame('foo', $column->getName());
        $this->assertSame('timestamp', $column->getType());
        $this->assertSame(false, $column->isNullable());
        $this->assertSame(false, $column->hasDefault());
        $this->assertSame(false, $column->isDefaultCurrent());
        $this->assertSame(false, $column->isOnUpdateCurrent());
        $this->assertSame(false, $column->hasComment());
        $this->assertSame('', $column->getComment());
        $this->assertSame('`foo` timestamp NOT NULL', $column->build());

        // nullable
        $column->nullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame('`foo` timestamp', $column->build());

        // nullable + default
        $column->default('2016-01-01');
        $this->assertSame('`foo` timestamp DEFAULT "2016-01-01 00:00:00"', $column->build());

        // nullable + default + on update current
        $column->onUpdateCurrent();
        $this->assertSame('`foo` timestamp DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP', $column->build());

        // nullable + default + on update current + comment
        $column->comment('foobar');
        $this->assertSame('`foo` timestamp DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->build());



        // default
        $column = new TimestampColumn('foo');
        $column->default(new \DateTime('2016-01-01 11:00:00', new \DateTimeZone('Asia/Tokyo')));
        $this->assertSame('`foo` timestamp NOT NULL DEFAULT "2016-01-01 02:00:00"', $column->build());

        $column = new TimestampColumn('foo');
        $column->default(null);
        $this->assertSame(false, $column->hasDefault());
        $column->default('');
        $this->assertSame(false, $column->hasDefault());
        $this->assertSame('`foo` timestamp NOT NULL', $column->build());

        $column = new TimestampColumn('foo');
        $column->default('2016-01-01');
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame('`foo` timestamp NOT NULL DEFAULT "2016-01-01 00:00:00"', $column->build());

        // default + on update current
        $column->onUpdateCurrent();
        $this->assertSame('`foo` timestamp NOT NULL DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP', $column->build());

        // default + on update current + comment
        $column->comment('foobar');
        $this->assertSame('`foo` timestamp NOT NULL DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->build());



        // on update current
        $column = new TimestampColumn('foo');
        $column->onUpdateCurrent();
        $this->assertSame(true, $column->isOnUpdateCurrent());
        $this->assertSame('`foo` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP', $column->build());

        // on update current + comment
        $column->comment('foobar');
        $this->assertSame('`foo` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->build());



        // comment
        $column = new TimestampColumn('foo');
        $column->comment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame('`foo` timestamp NOT NULL COMMENT "foobar"', $column->build());



        // default current
        $column = new TimestampColumn('foo');
        $column->defaultCurrent();
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(true, $column->isDefaultCurrent());
        $this->assertSame('`foo` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP', $column->build());

        $column = new TimestampColumn('foo');
        $column->default('CURRENT_TIMESTAMP');
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(true, $column->isDefaultCurrent());
        $this->assertSame('`foo` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP', $column->build());
    }
}
