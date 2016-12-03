<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;

class TextColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTextTypes
     */
    public function testTextColumn(string $type, int $length)
    {
        $column = new TextColumn('foo', $type);
        $this->assertSame('foo', $column->getName());
        $this->assertSame($type, $column->getType());
        $this->assertSame(false, $column->isBinary());
        $this->assertSame('', $column->getCharset());
        $this->assertSame('', $column->getCollation());
        $this->assertSame(false, $column->isNullable());
        $this->assertSame(false, $column->hasComment());
        $this->assertSame('', $column->getComment());
        $this->assertSame($length, $column->getLength());
        $this->assertSame(sprintf('`foo` %s NOT NULL', $type), $column->buildCreate());
        $this->assertSame('DROP COLUMN `foo`', $column->buildDrop());
        $this->assertSame(sprintf('ADD COLUMN `foo` %s NOT NULL', $type), $column->buildAdd());
        $this->assertSame(sprintf('ADD COLUMN `foo` %s NOT NULL AFTER `bar`', $type), $column->buildAdd(Column::int('bar')));
        $this->assertSame(sprintf('CHANGE COLUMN `bar` `foo` %s NOT NULL', $type), $column->buildChange(Column::int('bar')));

        // binary
        $column->setBinary();
        $this->assertSame(true, $column->isBinary());
        $this->assertSame(sprintf('`foo` %s BINARY NOT NULL', $type), $column->buildCreate());

        // binary + charset
        $column->setCharset('utf8');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 NOT NULL', $type), $column->buildCreate());

        // binary + charset + collation
        $column->setCollation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $column->buildCreate());

        // binary + charset + collation + nullable
        $column->setNullable();
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin', $type), $column->buildCreate());

        // binary + charset + collation + nullable + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT "foobar"', $type), $column->buildCreate());



        // charset
        $column = new TextColumn('foo', $type);
        $column->setCharset('utf8');
        $this->assertSame('utf8', $column->getCharset());
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 NOT NULL', $type), $column->buildCreate());

        // charset + collation
        $column->setCollation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $column->buildCreate());

        // charset + collation + nullable
        $column->setNullable();
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin', $type), $column->buildCreate());

        // charset + collation + nullable + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin COMMENT "foobar"', $type), $column->buildCreate());



        // collation
        $column = new TextColumn('foo', $type);
        $column->setCollation('utf8_bin');
        $this->assertSame('utf8_bin', $column->getCollation());
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin NOT NULL', $type), $column->buildCreate());

        // collation + nullable
        $column->setNullable();
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin', $type), $column->buildCreate());

        // collation + nullable + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin COMMENT "foobar"', $type), $column->buildCreate());



        // nullable
        $column = new TextColumn('foo', $type);
        $column->setNullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame(sprintf('`foo` %s', $type), $column->buildCreate());

        // nullable + comment
        $column->setComment('foobar');
        $this->assertSame(sprintf('`foo` %s COMMENT "foobar"', $type), $column->buildCreate());



        // comment
        $column = new TextColumn('foo', $type);
        $column->setComment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame(sprintf('`foo` %s NOT NULL COMMENT "foobar"', $type), $column->buildCreate());
    }

    /**
     * @dataProvider dataProviderTextTypes
     */
    public function testCheck(string $type, int $length)
    {
        $column = new TextColumn('foo', $type);
        $this->assertSame(null, $column->check(null));
        $this->assertSame(null, $column->check(''));

        $this->assertSame('1', $column->check(true));
        $this->assertSame('1', $column->check(1.0));
        $this->assertSame('1', $column->check(1));
        $this->assertSame('1', $column->check('1'));
    }

    public function dataProviderTextTypes(): array
    {
        return [
            ['tinytext', 255],
            ['text', 65535],
            ['mediumtext', 16777215],
            ['longtext', 4294967295],
        ];
    }

    public function testInvalidType()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_INVALID_TYPE);

        $column = new TextColumn('foo', 'bar');
    }

    public function testTooLong()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::COLUMN_STRING_INVALID_LENGTH);

        $column = new TextColumn('foo', 'text');
        $column->check(str_repeat('a', 65536));
    }
}
