<?php

namespace Alius\Database;

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
        $this->assertSame(sprintf('`foo` %s NOT NULL', $type), $column->build());

        // binary
        $column->binary();
        $this->assertSame(true, $column->isBinary());
        $this->assertSame(sprintf('`foo` %s BINARY NOT NULL', $type), $column->build());

        // binary + charset
        $column->charset('utf8');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 NOT NULL', $type), $column->build());

        // binary + charset + collation
        $column->collation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $column->build());

        // binary + charset + collation + nullable
        $column->nullable();
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin', $type), $column->build());

        // binary + charset + collation + nullable + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT "foobar"', $type), $column->build());



        // charset
        $column = new TextColumn('foo', $type);
        $column->charset('utf8');
        $this->assertSame('utf8', $column->getCharset());
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 NOT NULL', $type), $column->build());

        // charset + collation
        $column->collation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $column->build());

        // charset + collation + nullable
        $column->nullable();
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin', $type), $column->build());

        // charset + collation + nullable + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin COMMENT "foobar"', $type), $column->build());



        // collation
        $column = new TextColumn('foo', $type);
        $column->collation('utf8_bin');
        $this->assertSame('utf8_bin', $column->getCollation());
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin NOT NULL', $type), $column->build());

        // collation + nullable
        $column->nullable();
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin', $type), $column->build());

        // collation + nullable + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin COMMENT "foobar"', $type), $column->build());



        // nullable
        $column = new TextColumn('foo', $type);
        $column->nullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame(sprintf('`foo` %s', $type), $column->build());

        // nullable + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s COMMENT "foobar"', $type), $column->build());



        // comment
        $column = new TextColumn('foo', $type);
        $column->comment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame(sprintf('`foo` %s NOT NULL COMMENT "foobar"', $type), $column->build());
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
        $this->expectException(ColumnException::class);
        $column = new TextColumn('foo', 'bar');
    }

    public function testTooLong()
    {
        $this->expectException(ColumnException::class);
        $column = new TextColumn('foo', 'text');
        $column->check(str_repeat('a', 65536));
    }
}
