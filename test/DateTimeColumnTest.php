<?php

namespace Alius\Database;

class DateTimeColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testDateTimeColumn()
    {
        $column = new DateTimeColumn('foo');
        $this->assertSame('foo', $column->getName());
        $this->assertSame('datetime', $column->getType());
        $this->assertSame(false, $column->isNullable());
        $this->assertSame(false, $column->hasDefault());
        $this->assertSame(false, $column->isDefaultCurrent());
        $this->assertSame(false, $column->isOnUpdateCurrent());
        $this->assertSame(false, $column->hasComment());
        $this->assertSame('', $column->getComment());
        $this->assertSame('`foo` datetime NOT NULL', $column->build());

        // nullable
        $column->nullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame('`foo` datetime', $column->build());

        // nullable + default
        $column->default('2016-01-01');
        $this->assertSame('`foo` datetime DEFAULT "2016-01-01 00:00:00"', $column->build());

        // nullable + default + on update current
        $column->onUpdateCurrent();
        $this->assertSame('`foo` datetime DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP', $column->build());

        // nullable + default + on update current + comment
        $column->comment('foobar');
        $this->assertSame('`foo` datetime DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->build());



        // default
        $column = new DateTimeColumn('foo');
        $column->default(new \DateTime('2016-01-01 11:00:00', new \DateTimeZone('Asia/Tokyo')));
        $this->assertSame('`foo` datetime NOT NULL DEFAULT "2016-01-01 02:00:00"', $column->build());

        $column = new DateTimeColumn('foo');
        $column->default(null);
        $this->assertSame(false, $column->hasDefault());
        $column->default('');
        $this->assertSame(false, $column->hasDefault());
        $this->assertSame('`foo` datetime NOT NULL', $column->build());

        $column = new DateTimeColumn('foo');
        $column->default('2016-01-01');
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame('`foo` datetime NOT NULL DEFAULT "2016-01-01 00:00:00"', $column->build());

        // default + on update current
        $column->onUpdateCurrent();
        $this->assertSame('`foo` datetime NOT NULL DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP', $column->build());

        // default + on update current + comment
        $column->comment('foobar');
        $this->assertSame('`foo` datetime NOT NULL DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->build());



        // on update current
        $column = new DateTimeColumn('foo');
        $column->onUpdateCurrent();
        $this->assertSame(true, $column->isOnUpdateCurrent());
        $this->assertSame('`foo` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP', $column->build());

        // on update current + comment
        $column->comment('foobar');
        $this->assertSame('`foo` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->build());



        // comment
        $column = new DateTimeColumn('foo');
        $column->comment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame('`foo` datetime NOT NULL COMMENT "foobar"', $column->build());



        // default current
        $column = new DateTimeColumn('foo');
        $column->defaultCurrent();
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(true, $column->isDefaultCurrent());
        $this->assertSame('`foo` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP', $column->build());

        $column = new DateTimeColumn('foo');
        $column->default('CURRENT_TIMESTAMP');
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(true, $column->isDefaultCurrent());
        $this->assertSame('`foo` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP', $column->build());
    }
}
