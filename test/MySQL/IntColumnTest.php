<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;

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
        $this->assertSame(sprintf('`foo` %s NOT NULL', $type), $column->buildCreate());
        $this->assertSame('DROP COLUMN `foo`', $column->buildDrop());
        $this->assertSame(sprintf('ADD COLUMN `foo` %s NOT NULL', $type), $column->buildAdd());
        $this->assertSame(sprintf('ADD COLUMN `foo` %s NOT NULL AFTER `bar`', $type), $column->buildAdd(Column::int('bar')));
        $this->assertSame(sprintf('CHANGE COLUMN `bar` `foo` %s NOT NULL', $type), $column->buildChange(Column::int('bar')));

        // unsigned
        $column = new IntColumn('foo', $type);
        $column->setUnsigned();
        $this->assertSame(true, $column->isUnsigned());
        $this->assertSame(0, $column->getMin());
        $this->assertSame($unsigned_max, $column->getMax());
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL', $type), $column->buildCreate());

        // unsigned + auto_increment
        $column->setAutoIncrement();
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL AUTO_INCREMENT', $type), $column->buildCreate());

        // unsigned + auto_increment + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "foobar"', $type), $column->buildCreate());

        // unsigned + nullable
        $column = new IntColumn('foo', $type);
        $column->setUnsigned()->setNullable();
        $this->assertSame(sprintf('`foo` %s UNSIGNED', $type), $column->buildCreate());

        // unsigned + nullable + default
        $column->setDefault(1);
        $this->assertSame(sprintf('`foo` %s UNSIGNED DEFAULT "1"', $type), $column->buildCreate());

        // unsigned + nullable + default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s UNSIGNED DEFAULT "1" COMMENT "foobar"', $type), $column->buildCreate());

        // unsigned + default
        $column = new IntColumn('foo', $type);
        $column->setUnsigned()->setDefault(1);
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL DEFAULT "1"', $type), $column->buildCreate());

        // unsigned + default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL DEFAULT "1" COMMENT "foobar"', $type), $column->buildCreate());

        // unsigned + comment
        $column = new IntColumn('foo', $type);
        $column->setUnsigned()->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s UNSIGNED NOT NULL COMMENT "foobar"', $type), $column->buildCreate());



        // nullable
        $column = new IntColumn('foo', $type);
        $column->setNullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame(sprintf('`foo` %s', $type), $column->buildCreate());

        // nullable + default
        $column->setDefault(1);
        $this->assertSame(sprintf('`foo` %s DEFAULT "1"', $type), $column->buildCreate());

        // nullable + default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s DEFAULT "1" COMMENT "foobar"', $type), $column->buildCreate());

        // nullable + comment
        $column = new IntColumn('foo', $type);
        $column->setNullable()->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s COMMENT "foobar"', $type), $column->buildCreate());



        // auto_increment
        $column = new IntColumn('foo', $type);
        $column->setAutoIncrement();
        $this->assertSame(true, $column->isAutoIncrement());
        $this->assertSame(sprintf('`foo` %s NOT NULL AUTO_INCREMENT', $type), $column->buildCreate());

        // auto_increment + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s NOT NULL AUTO_INCREMENT COMMENT "foobar"', $type), $column->buildCreate());



        // default
        $column = new IntColumn('foo', $type);
        $column->setDefault(1);
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame(1, $column->getDefault());
        $this->assertSame(sprintf('`foo` %s NOT NULL DEFAULT "1"', $type), $column->buildCreate());

        // default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s NOT NULL DEFAULT "1" COMMENT "foobar"', $type), $column->buildCreate());



        // comment
        $column = new IntColumn('foo', $type);
        $column->setComment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame(sprintf('`foo` %s NOT NULL COMMENT "foobar"', $type), $column->buildCreate());
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
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INVALID_TYPE);

        $column = new IntColumn('foo', 'bar');
    }

    public function testAutoIncrementAndNullable()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_AUTO_INCREMENT_NULLABLE);

        $column = new IntColumn('foo', 'int');
        $column->setAutoIncrement()->setNullable();
    }

    public function testNullableAndAutoIncrement()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_AUTO_INCREMENT_NULLABLE);

        $column = new IntColumn('foo', 'int');
        $column->setNullable()->setAutoIncrement();
    }

    public function testAutoIncrementAndDefault()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_AUTO_INCREMENT_DEFAULT);

        $column = new IntColumn('foo', 'int');
        $column->setAutoIncrement()->setDefault(1);
    }

    public function testDefaultAndAutoIncrement()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_AUTO_INCREMENT_DEFAULT);

        $column = new IntColumn('foo', 'int');
        $column->setDefault(1)->setAutoIncrement();
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckSignedMin(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_VALUE_MIN);

        $column = new IntColumn('foo', $type);
        $column->check(bcsub($signed_min, 1));
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckSignedMax(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_VALUE_MAX);

        $column = new IntColumn('foo', $type);
        $column->check(bcadd($signed_max, 1));
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckUnsignedMin(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_VALUE_MIN);

        $column = new IntColumn('foo', $type);
        $column->setUnsigned();
        $column->check(-1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckUnsignedMax(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_VALUE_MAX);

        $column = new IntColumn('foo', $type);
        $column->setUnsigned();
        $column->check(bcadd($unsigned_max, 1));
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckDefaultSignedMin(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_VALUE_MIN);

        $column = new IntColumn('foo', $type);
        $column->setDefault(bcsub($signed_min, 1));
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckDefaultSignedMax(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_VALUE_MAX);

        $column = new IntColumn('foo', $type);
        $column->setDefault(bcadd($signed_max, 1));
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckDefaultUnsignedMin(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_VALUE_MIN);

        $column = new IntColumn('foo', $type);
        $column->setUnsigned();
        $column->setDefault(-1);
    }

    /**
     * @dataProvider dataProviderIntTypes
     */
    public function testCheckDefaultUnsignedMax(string $type, int $signed_min, int $signed_max, int $unsigned_max)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INT_INVALID_VALUE_MAX);

        $column = new IntColumn('foo', $type);
        $column->setUnsigned();
        $column->setDefault(bcadd($unsigned_max, 1));
    }
}
