<?php

namespace Alius\Database;

class IntColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testIntColumn(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $column = new IntColumn('foo', $type);
        $this->assertSame('foo', $column->getName());
        $this->assertSame($type, $column->getType());
        $this->assertSame(false, $column->isUnsigned());
        $this->assertSame(false, $column->isNullable());
        $this->assertSame(false, $column->isAutoIncrement());
        $this->assertSame(false, $column->hasDefault());
        $this->assertSame(null, $column->getDefault());
        $this->assertSame(false, $column->hasComment());
        $this->assertSame('', $column->getComment());
        $this->assertSame($signed_min, $column->getMin());
        $this->assertSame($signed_max, $column->getMax());
        $this->assertSame(sprintf('`foo` %s NOT NULL', $type), $column->build());
        $this->assertSame(sprintf('`foo` %s NOT NULL', $type), (string) $column);

        // unsigned
        $column = new IntColumn('foo', $type);
        $column->unsigned();
        $this->assertSame(true, $column->isUnsigned());
        $this->assertSame(0, $column->getMin());
        $this->assertSame($unsigned_max, $column->getMax());
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL', $type), $column->build());

        // unsigned + auto_increment
        $column->autoIncrement();
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL AUTO_INCREMENT', $type), $column->build());

        // unsigned + auto_increment + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "foobar"', $type), $column->build());

        // unsigned + nullable
        $column = new IntColumn('foo', $type);
        $column->unsigned()->nullable();
        $this->assertSame(sprintf('`foo` %s UNSIGNED', $type), $column->build());

        // unsigned + nullable + default
        $column->default(1);
        $this->assertSame(sprintf('`foo` %s UNSIGNED DEFAULT "1"', $type), $column->build());

        // unsigned + nullable + default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s UNSIGNED DEFAULT "1" COMMENT "foobar"', $type), $column->build());

        // unsigned + default
        $column = new IntColumn('foo', $type);
        $column->unsigned()->default(1);
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL DEFAULT "1"', $type), $column->build());

        // unsigned + default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL DEFAULT "1" COMMENT "foobar"', $type), $column->build());

        // unsigned + comment
        $column = new IntColumn('foo', $type);
        $column->unsigned()->comment('foobar');
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL COMMENT "foobar"', $type), $column->build());



        // nullable
        $column = new IntColumn('foo', $type);
        $column->nullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame(sprintf('`foo` %s', $type), $column->build());

        // nullable + default
        $column->default(1);
        $this->assertSame(sprintf('`foo` %s DEFAULT "1"', $type), $column->build());

        // nullable + default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s DEFAULT "1" COMMENT "foobar"', $type), $column->build());

        // nullable + comment
        $column = new IntColumn('foo', $type);
        $column->nullable()->comment('foobar');
        $this->assertSame(sprintf('`foo` %s COMMENT "foobar"', $type), $column->build());



        // auto_increment
        $column = new IntColumn('foo', $type);
        $column->autoIncrement();
        $this->assertSame(true, $column->isAutoIncrement());
        $this->assertSame(sprintf('`foo` %s NOT NULL AUTO_INCREMENT', $type), $column->build());

        // auto_increment + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s NOT NULL AUTO_INCREMENT COMMENT "foobar"', $type), $column->build());



        // default
        $column = new IntColumn('foo', $type);
        $column->default(1);
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(1, $column->getDefault());
        $this->assertSame(sprintf('`foo` %s NOT NULL DEFAULT "1"', $type), $column->build());

        // default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s NOT NULL DEFAULT "1" COMMENT "foobar"', $type), $column->build());



        // comment
        $column = new IntColumn('foo', $type);
        $column->comment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame(sprintf('`foo` %s NOT NULL COMMENT "foobar"', $type), $column->build());
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheck(string $type)
    {
        $column = new IntColumn('foo', $type);
        $this->assertSame(null, $column->check(null));
        $this->assertSame(null, $column->check(''));

        $this->assertSame(1, $column->check(true));
        $this->assertSame(1, $column->check(1.0));
        $this->assertSame(1, $column->check(1));
        $this->assertSame(1, $column->check('1'));
    }

    public function dataProviderIntTypes(): array
    {
        return [
            ['tinyint', -128, 127, 255],
            ['smallint', -32768, 32767, 65535],
            ['mediumint', -8388608, 8388607, 16777215],
            ['int', -2147483648, 2147483647, 4294967295],
            ['bigint', -9223372036854775808, 9223372036854775807, 9223372036854775807],
        ];
    }

    public function testInvalidType()
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', 'bar');
    }

    public function testAutoIncrementAndNullable()
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', 'int');
        $column->autoIncrement()->nullable();
    }

    public function testNullableAndAutoIncrement()
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', 'int');
        $column->nullable()->autoIncrement();
    }

    public function testAutoIncrementAndDefault()
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', 'int');
        $column->autoIncrement()->default(1);
    }

    public function testDefaultAndAutoIncrement()
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', 'int');
        $column->default(1)->autoIncrement();
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckSignedMin(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', $type);
        $column->check($signed_min - 1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckSignedMax(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', $type);
        $column->check($signed_max + 1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckUnsignedMin(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', $type);
        $column->unsigned();
        $column->check(-1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckUnsignedMax(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', $type);
        $column->unsigned();
        $column->check($unsigned_max + 1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckDefaultSignedMin(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', $type);
        $column->default($signed_min - 1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckDefaultSignedMax(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', $type);
        $column->default($signed_max + 1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckDefaultUnsignedMin(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', $type);
        $column->unsigned();
        $column->default(-1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckDefaultUnsignedMax(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(ColumnException::class);
        $column = new IntColumn('foo', $type);
        $column->unsigned();
        $column->default($unsigned_max + 1);
    }
}
