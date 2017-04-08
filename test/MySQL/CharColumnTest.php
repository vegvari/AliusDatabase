<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;

class CharColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testCharColumn(string $type, int $length)
    {
        $column = new CharColumn('foo', $type, 255);
        $this->assertSame('foo', $column->getName());
        $this->assertSame($type, $column->getType());
        $this->assertSame(255, $column->getLength());
        $this->assertSame(false, $column->isBinary());
        $this->assertSame('', $column->getCharset());
        $this->assertSame('', $column->getCollation());
        $this->assertSame(false, $column->isNullable());
        $this->assertSame(false, $column->hasDefault());
        $this->assertSame(null, $column->getDefault());
        $this->assertSame(false, $column->hasComment());
        $this->assertSame('', $column->getComment());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL', $type), $column->buildCreate());
        $this->assertSame('DROP COLUMN `foo`', $column->buildDrop());
        $this->assertSame(sprintf('ADD COLUMN `foo` %s(255) NOT NULL', $type), $column->buildAdd());
        $this->assertSame(sprintf('ADD COLUMN `foo` %s(255) NOT NULL AFTER `bar`', $type), $column->buildAdd(Column::int('bar')));
        $this->assertSame(sprintf('CHANGE COLUMN `bar` `foo` %s(255) NOT NULL', $type), $column->buildChange(Column::int('bar')));

        // binary
        $column->setBinary();
        $this->assertSame(true, $column->isBinary());
        $this->assertSame(sprintf('`foo` %s(255) BINARY NOT NULL', $type), $column->buildCreate());

        // binary + charset
        $column->setCharset('utf8');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 NOT NULL', $type), $column->buildCreate());

        // binary + charset + collation
        $column->setCollation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $column->buildCreate());

        // binary + charset + collation + nullable
        $column->setNullable();
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin', $type), $column->buildCreate());

        // binary + charset + collation + nullable + default
        $column->setDefault('bar');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar"', $type), $column->buildCreate());

        // binary + charset + collation + nullable + default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $column->buildCreate());



        // charset
        $column = new CharColumn('foo', $type, 255);
        $column->setCharset('utf8');
        $this->assertSame('utf8', $column->getCharset());
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 NOT NULL', $type), $column->buildCreate());

        // charset + collation
        $column->setCollation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $column->buildCreate());

        // charset + collation + nullable
        $column->setNullable();
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin', $type), $column->buildCreate());

        // charset + collation + nullable + default
        $column->setDefault('bar');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar"', $type), $column->buildCreate());

        // charset + collation + nullable + default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $column->buildCreate());



        // collation
        $column = new CharColumn('foo', $type, 255);
        $column->setCollation('utf8_bin');
        $this->assertSame('utf8_bin', $column->getCollation());
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin NOT NULL', $type), $column->buildCreate());

        // collation + nullable
        $column->setNullable();
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin', $type), $column->buildCreate());

        // collation + nullable + default
        $column->setDefault('bar');
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin DEFAULT "bar"', $type), $column->buildCreate());

        // collation + nullable + default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $column->buildCreate());



        // nullable
        $column = new CharColumn('foo', $type, 255);
        $column->setNullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame(sprintf('`foo` %s(255)', $type), $column->buildCreate());

        // nullable + default
        $column->setDefault('bar');
        $this->assertSame(sprintf('`foo` %s(255) DEFAULT "bar"', $type), $column->buildCreate());

        // nullable + default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) DEFAULT "bar" COMMENT "foobar"', $type), $column->buildCreate());



        // default
        $column = new CharColumn('foo', $type, 255);
        $column->setDefault('bar');
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame('bar', $column->getDefault());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL DEFAULT "bar"', $type), $column->buildCreate());

        // default + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL DEFAULT "bar" COMMENT "foobar"', $type), $column->buildCreate());



        // comment
        $column = new CharColumn('foo', $type, 255);
        $column->setComment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL COMMENT "foobar"', $type), $column->buildCreate());
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testCheck(string $type)
    {
        $column = new CharColumn('foo', $type, 255);
        $this->assertSame(null, $column->check(null));
        $this->assertSame(null, $column->check(''));

        $this->assertSame('1', $column->check(true));
        $this->assertSame('1', $column->check(1.0));
        $this->assertSame('1', $column->check(1));
        $this->assertSame('1', $column->check('1'));
    }

    public function dataProviderCharTypes(): array
    {
        return [
            ['char', 255],
            ['varchar', 65535],
        ];
    }

    public function testInvalidType()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::COLUMN_INVALID_TYPE);

        $column = new CharColumn('foo', 'bar', 0);
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testLengthMin(string $type)
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::COLUMN_STRING_INVALID_LENGTH);

        $column = new CharColumn('foo', $type, -1);
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testLengthMax(string $type, int $length)
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::COLUMN_STRING_INVALID_LENGTH);

        $column = new CharColumn('foo', $type, $length + 1);
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testTooLong(string $type, int $length)
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::COLUMN_STRING_INVALID_LENGTH);

        $column = new CharColumn('foo', $type, $length);
        $column->check(str_repeat('a', $length + 1));
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testDefaultTooLong(string $type, int $length)
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::COLUMN_STRING_INVALID_LENGTH);

        $column = new CharColumn('foo', $type, $length);
        $column->setDefault(str_repeat('a', $length + 1));
    }
}
