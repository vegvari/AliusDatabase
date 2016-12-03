<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;

class DecimalColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testDecimal()
    {
        $column = new DecimalColumn('foo', 10, 2);
        $this->assertSame('foo', $column->getName());
        $this->assertSame('decimal', $column->getType());
        $this->assertSame(10, $column->getPrecision());
        $this->assertSame(2, $column->getScale());
        $this->assertSame(false, $column->isUnsigned());
        $this->assertSame(false, $column->isNullable());
        $this->assertSame(false, $column->hasDefault());
        $this->assertSame(null, $column->getDefault());
        $this->assertSame(false, $column->hasComment());
        $this->assertSame('', $column->getComment());
        $this->assertSame('`foo` decimal(10,2) NOT NULL', $column->buildCreate());
        $this->assertSame('DROP COLUMN `foo`', $column->buildDrop());
        $this->assertSame('ADD COLUMN `foo` decimal(10,2) NOT NULL', $column->buildAdd());
        $this->assertSame('ADD COLUMN `foo` decimal(10,2) NOT NULL AFTER `bar`', $column->buildAdd(Column::int('bar')));
        $this->assertSame('CHANGE COLUMN `bar` `foo` decimal(10,2) NOT NULL', $column->buildChange(Column::int('bar')));

        // unsigned
        $column = new DecimalColumn('foo', 10, 2);
        $column->setUnsigned();
        $this->assertSame(true, $column->isUnsigned());
        $this->assertSame('`foo` decimal(10,2) UNSIGNED NOT NULL', $column->buildCreate());

        // unsigned + nullable
        $column->setNullable();
        $this->assertSame('`foo` decimal(10,2) UNSIGNED', $column->buildCreate());

        // unsigned + nullable + default
        $column->setDefault(1);
        $this->assertSame('`foo` decimal(10,2) UNSIGNED DEFAULT "1.00"', $column->buildCreate());

        // unsigned + nullable + default + comment
        $column->setComment('foobar');
        $this->assertSame('`foo` decimal(10,2) UNSIGNED DEFAULT "1.00" COMMENT "foobar"', $column->buildCreate());

        // unsigned + default
        $column = new DecimalColumn('foo', 10, 2);
        $column->setUnsigned()->setDefault(1);
        $this->assertSame('`foo` decimal(10,2) UNSIGNED NOT NULL DEFAULT "1.00"', $column->buildCreate());

        // unsigned + default + comment
        $column->setComment('foobar');
        $this->assertSame('`foo` decimal(10,2) UNSIGNED NOT NULL DEFAULT "1.00" COMMENT "foobar"', $column->buildCreate());

        // unsigned + comment
        $column = new DecimalColumn('foo', 10, 2);
        $column->setUnsigned()->setComment('foobar');
        $this->assertSame('`foo` decimal(10,2) UNSIGNED NOT NULL COMMENT "foobar"', $column->buildCreate());



        // nullable
        $column = new DecimalColumn('foo', 10, 2);
        $column->setNullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame('`foo` decimal(10,2)', $column->buildCreate());

        // nullable + default
        $column->setDefault(1);
        $this->assertSame('`foo` decimal(10,2) DEFAULT "1.00"', $column->buildCreate());

        // nullable + default + comment
        $column->setComment('foobar');
        $this->assertSame('`foo` decimal(10,2) DEFAULT "1.00" COMMENT "foobar"', $column->buildCreate());

        // nullable + comment
        $column = new DecimalColumn('foo', 10, 2);
        $column->setNullable()->setComment('foobar');
        $this->assertSame('`foo` decimal(10,2) COMMENT "foobar"', $column->buildCreate());



        // default
        $column = new DecimalColumn('foo', 10, 4);
        $column->setDefault(1.12345);
        $this->assertSame(1.12345, $column->getDefault());
        $this->assertSame('`foo` decimal(10,4) NOT NULL DEFAULT "1.1235"', $column->buildCreate());

        $column = new DecimalColumn('foo', 10, 4);
        $column->setDefault(1);
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(1.0, $column->getDefault());
        $this->assertSame('`foo` decimal(10,4) NOT NULL DEFAULT "1.0000"', $column->buildCreate());

        // default + comment
        $column->setComment('foobar');
        $this->assertSame('`foo` decimal(10,4) NOT NULL DEFAULT "1.0000" COMMENT "foobar"', $column->buildCreate());



        // comment
        $column = new DecimalColumn('foo', 10, 2);
        $column->setComment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame('`foo` decimal(10,2) NOT NULL COMMENT "foobar"', $column->buildCreate());
    }

    public function testCheck()
    {
        $column = new DecimalColumn('foo', 10, 2);
        $this->assertSame(null, $column->check(null));
        $this->assertSame(null, $column->check(''));

        $this->assertSame(1.0, $column->check(true));
        $this->assertSame(1.0, $column->check(1.0));
        $this->assertSame(1.0, $column->check(1));
        $this->assertSame(1.0, $column->check('1'));
    }

    public function testPrecisionAndScale()
    {
        for ($precision = 1; $precision <= 65; $precision++) {
            for ($scale = 0; $scale <= 30; $scale++) {
                if ($precision > $scale) {
                    $column = new DecimalColumn('foo', $precision, $scale);
                    $this->assertSame($precision, $column->getPrecision());
                    $this->assertSame($scale, $column->getScale());
                    $this->assertSame(sprintf('`foo` decimal(%d,%d) NOT NULL', $precision, $scale), $column->buildCreate());
                }
            }
        }
    }

    public function testPrecisionMin()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_PRECISION);

        $column = new DecimalColumn('foo', 0, 0);
    }

    public function testPrecisionMax()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_PRECISION);

        $column = new DecimalColumn('foo', 66, 0);
    }

    public function testScaleMin()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_SCALE);

        $column = new DecimalColumn('foo', 65, -1);
    }

    public function testScaleMax()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_SCALE);

        $column = new DecimalColumn('foo', 65, 31);
    }

    public function testScaleLargerThanPrecision()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_SCALE_MAX);

        $column = new DecimalColumn('foo', 10, 11);
    }

    public function testCheckSignedMin()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_VALUE_MIN);

        $column = new DecimalColumn('foo', 10, 2);
        $this->assertSame(-100000000.0, $column->getMin());
        $column->check(-100000000);
    }

    public function testCheckSignedMax()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_VALUE_MAX);

        $column = new DecimalColumn('foo', 10, 2);
        $this->assertSame(100000000.0, $column->getMax());
        $column->check(100000000);
    }

    public function testCheckNotFloat()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_VALUE);

        $column = new DecimalColumn('foo', 10, 2);
        $column->check('bar');
    }

    public function testDefaultSignedMin()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_VALUE_MIN);

        $column = new DecimalColumn('foo', 10, 2);
        $column->setDefault(-100000000);
    }

    public function testDefaultSignedMax()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_VALUE_MAX);

        $column = new DecimalColumn('foo', 10, 2);
        $column->setDefault(100000000);
    }

    public function testDefaultNotFloat()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_FLOAT_INVALID_VALUE);

        $column = new DecimalColumn('foo', 10, 2);
        $column->setDefault('bar');
    }
}
