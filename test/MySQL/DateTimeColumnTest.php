<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use PHPUnit\Framework\TestCase;

class DateTimeColumnTest extends TestCase
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
        $this->assertSame('`foo` datetime NOT NULL', $column->buildCreate());
        $this->assertSame('DROP COLUMN `foo`', $column->buildDrop());
        $this->assertSame('ADD COLUMN `foo` datetime NOT NULL', $column->buildAdd());
        $this->assertSame('ADD COLUMN `foo` datetime NOT NULL AFTER `bar`', $column->buildAdd(Column::int('bar')));
        $this->assertSame('CHANGE COLUMN `bar` `foo` datetime NOT NULL', $column->buildChange(Column::int('bar')));

        // nullable
        $column->setNullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame('`foo` datetime', $column->buildCreate());

        // nullable + default
        $column->setDefault('2016-01-01');
        $this->assertSame('`foo` datetime DEFAULT "2016-01-01 00:00:00"', $column->buildCreate());

        // nullable + default + on update current
        $column->setOnUpdateCurrent();
        $this->assertSame('`foo` datetime DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP', $column->buildCreate());

        // nullable + default + on update current + comment
        $column->setComment('foobar');
        $this->assertSame('`foo` datetime DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->buildCreate());



        // default
        $column = new DateTimeColumn('foo');
        $column->setDefault(new \DateTime('2016-01-01 11:00:00', new \DateTimeZone('Asia/Tokyo')));
        $this->assertSame('`foo` datetime NOT NULL DEFAULT "2016-01-01 02:00:00"', $column->buildCreate());

        $column = new DateTimeColumn('foo');
        $column->setDefault(null);
        $this->assertSame(false, $column->hasDefault());
        $column->setDefault('');
        $this->assertSame(false, $column->hasDefault());
        $this->assertSame('`foo` datetime NOT NULL', $column->buildCreate());

        $column = new DateTimeColumn('foo');
        $column->setDefault('2016-01-01');
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame('`foo` datetime NOT NULL DEFAULT "2016-01-01 00:00:00"', $column->buildCreate());

        // default + on update current
        $column->setOnUpdateCurrent();
        $this->assertSame('`foo` datetime NOT NULL DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP', $column->buildCreate());

        // default + on update current + comment
        $column->setComment('foobar');
        $this->assertSame('`foo` datetime NOT NULL DEFAULT "2016-01-01 00:00:00" ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->buildCreate());



        // on update current
        $column = new DateTimeColumn('foo');
        $column->setOnUpdateCurrent();
        $this->assertSame(true, $column->isOnUpdateCurrent());
        $this->assertSame('`foo` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP', $column->buildCreate());

        // on update current + comment
        $column->setComment('foobar');
        $this->assertSame('`foo` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT "foobar"', $column->buildCreate());



        // comment
        $column = new DateTimeColumn('foo');
        $column->setComment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame('`foo` datetime NOT NULL COMMENT "foobar"', $column->buildCreate());



        // default current
        $column = new DateTimeColumn('foo');
        $column->setDefaultCurrent();
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(true, $column->isDefaultCurrent());
        $this->assertSame('`foo` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP', $column->buildCreate());

        $column = new DateTimeColumn('foo');
        $column->setDefault('CURRENT_TIMESTAMP');
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(true, $column->isDefaultCurrent());
        $this->assertSame('`foo` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP', $column->buildCreate());
    }

    public function testSetNullableImmutable()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $column = new DateTimeColumn('foo');
        $column->setImmutable();
        $column->setNullable();
    }

    public function testSetDefaultImmutable()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $column = new DateTimeColumn('foo');
        $column->setImmutable();
        $column->setDefault('bar');
    }

    public function testSetCommentImmutable()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $column = new DateTimeColumn('foo');
        $column->setImmutable();
        $column->setComment('bar');
    }

    public function testSetOnUpdateCurrentImmutable()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $column = new DateTimeColumn('foo');
        $column->setImmutable();
        $column->setOnUpdateCurrent();
    }
}
