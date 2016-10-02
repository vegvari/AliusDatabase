<?php

namespace Alius\Database;

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
        $this->assertSame('`foo` decimal(10,2) NOT NULL', $column->build());

        // unsigned
        $column = new DecimalColumn('foo', 10, 2);
        $column->unsigned();
        $this->assertSame(true, $column->isUnsigned());
        $this->assertSame('`foo` decimal(10,2) UNSIGNED NOT NULL', $column->build());

        // unsigned + nullable
        $column->nullable();
        $this->assertSame('`foo` decimal(10,2) UNSIGNED', $column->build());

        // unsigned + nullable + default
        $column->default(1);
        $this->assertSame('`foo` decimal(10,2) UNSIGNED DEFAULT "1.00"', $column->build());

        // unsigned + nullable + default + comment
        $column->comment('foobar');
        $this->assertSame('`foo` decimal(10,2) UNSIGNED DEFAULT "1.00" COMMENT "foobar"', $column->build());

        // unsigned + default
        $column = new DecimalColumn('foo', 10, 2);
        $column->unsigned()->default(1);
        $this->assertSame('`foo` decimal(10,2) UNSIGNED NOT NULL DEFAULT "1.00"', $column->build());

        // unsigned + default + comment
        $column->comment('foobar');
        $this->assertSame('`foo` decimal(10,2) UNSIGNED NOT NULL DEFAULT "1.00" COMMENT "foobar"', $column->build());

        // unsigned + comment
        $column = new DecimalColumn('foo', 10, 2);
        $column->unsigned()->comment('foobar');
        $this->assertSame('`foo` decimal(10,2) UNSIGNED NOT NULL COMMENT "foobar"', $column->build());



        // nullable
        $column = new DecimalColumn('foo', 10, 2);
        $column->nullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame('`foo` decimal(10,2)', $column->build());

        // nullable + default
        $column->default(1);
        $this->assertSame('`foo` decimal(10,2) DEFAULT "1.00"', $column->build());

        // nullable + default + comment
        $column->comment('foobar');
        $this->assertSame('`foo` decimal(10,2) DEFAULT "1.00" COMMENT "foobar"', $column->build());

        // nullable + comment
        $column = new DecimalColumn('foo', 10, 2);
        $column->nullable()->comment('foobar');
        $this->assertSame('`foo` decimal(10,2) COMMENT "foobar"', $column->build());



        // default
        $column = new DecimalColumn('foo', 10, 4);
        $column->default(1.12345);
        $this->assertSame(1.12345, $column->getDefault());
        $this->assertSame('`foo` decimal(10,4) NOT NULL DEFAULT "1.1235"', $column->build());

        $column = new DecimalColumn('foo', 10, 4);
        $column->default(1);
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(1.0, $column->getDefault());
        $this->assertSame('`foo` decimal(10,4) NOT NULL DEFAULT "1.0000"', $column->build());

        // default + comment
        $column->comment('foobar');
        $this->assertSame('`foo` decimal(10,4) NOT NULL DEFAULT "1.0000" COMMENT "foobar"', $column->build());



        // comment
        $column = new DecimalColumn('foo', 10, 2);
        $column->comment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame('`foo` decimal(10,2) NOT NULL COMMENT "foobar"', $column->build());
    }

    public function testCheck()
    {
        $column = new DecimalColumn('foo', 10, 2);
        $this->assertSame(null, $column->check(null));
        $this->assertSame(null, $column->check(''));
    }

    public function testPrecisionAndScale()
    {
        for ($precision = 1; $precision <= 65; $precision++) {
            for ($scale = 0; $scale <= 30; $scale++) {
                if ($precision > $scale) {
                    $column = new DecimalColumn('foo', $precision, $scale);
                    $this->assertSame($precision, $column->getPrecision());
                    $this->assertSame($scale, $column->getScale());
                    $this->assertSame(sprintf('`foo` decimal(%d,%d) NOT NULL', $precision, $scale), $column->build());
                }
            }
        }
    }

    public function testPrecisionMin()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 0, 0);
    }

    public function testPrecisionMax()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 66, 0);
    }

    public function testScaleMin()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 65, -1);
    }

    public function testScaleMax()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 65, 31);
    }

    public function testScaleLargerThanPrecision()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 10, 11);
    }

    public function testCheckSignedMin()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 10, 2);
        $this->assertSame(-100000000.0, $column->getMin());
        $column->check(-100000000);
    }

    public function testCheckSignedMax()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 10, 2);
        $this->assertSame(100000000.0, $column->getMax());
        $column->check(100000000);
    }

    public function testCheckNotFloat()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 10, 2);
        $column->check('bar');
    }

    public function testDefaultSignedMin()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 10, 2);
        $column->default(-100000000);
    }

    public function testDefaultSignedMax()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 10, 2);
        $column->default(100000000);
    }

    public function testDefaultNotFloat()
    {
        $this->expectException(ColumnException::class);
        $column = new DecimalColumn('foo', 10, 2);
        $column->default('bar');
    }
}
