<?php

namespace Alius\Database;

class TextColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTextTypes
     */
    public function testTextColumn(string $type, int $length)
    {
        $instance = new TextColumn('foo', $type);
        $this->assertSame('foo', $instance->getName());
        $this->assertSame($type, $instance->getType());
        $this->assertSame(false, $instance->isBinary());
        $this->assertSame('', $instance->getCharset());
        $this->assertSame('', $instance->getCollation());
        $this->assertSame(false, $instance->isNullable());
        $this->assertSame(false, $instance->hasComment());
        $this->assertSame('', $instance->getComment());
        $this->assertSame($length, $instance->getLength());
        $this->assertSame(sprintf('`foo` %s NOT NULL', $type), $instance->build());

        // binary
        $instance->binary();
        $this->assertSame(true, $instance->isBinary());
        $this->assertSame(sprintf('`foo` %s BINARY NOT NULL', $type), $instance->build());

        // binary + charset
        $instance->charset('utf8');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 NOT NULL', $type), $instance->build());

        // binary + charset + collation
        $instance->collation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $instance->build());

        // binary + charset + collation + nullable
        $instance->nullable();
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin', $type), $instance->build());

        // binary + charset + collation + nullable + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s BINARY CHARACTER SET utf8 COLLATE utf8_bin COMMENT "foobar"', $type), $instance->build());



        // charset
        $instance = new TextColumn('foo', $type);
        $instance->charset('utf8');
        $this->assertSame('utf8', $instance->getCharset());
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 NOT NULL', $type), $instance->build());

        // charset + collation
        $instance->collation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $instance->build());

        // charset + collation + nullable
        $instance->nullable();
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin', $type), $instance->build());

        // charset + collation + nullable + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s CHARACTER SET utf8 COLLATE utf8_bin COMMENT "foobar"', $type), $instance->build());



        // collation
        $instance = new TextColumn('foo', $type);
        $instance->collation('utf8_bin');
        $this->assertSame('utf8_bin', $instance->getCollation());
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin NOT NULL', $type), $instance->build());

        // collation + nullable
        $instance->nullable();
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin', $type), $instance->build());

        // collation + nullable + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s COLLATE utf8_bin COMMENT "foobar"', $type), $instance->build());



        // nullable
        $instance = new TextColumn('foo', $type);
        $instance->nullable();
        $this->assertSame(true, $instance->isNullable());
        $this->assertSame(sprintf('`foo` %s', $type), $instance->build());

        // nullable + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s COMMENT "foobar"', $type), $instance->build());



        // comment
        $instance = new TextColumn('foo', $type);
        $instance->comment('foobar');
        $this->assertSame(true, $instance->hasComment());
        $this->assertSame('foobar', $instance->getComment());
        $this->assertSame(sprintf('`foo` %s NOT NULL COMMENT "foobar"', $type), $instance->build());
    }

    /**
     * @dataProvider dataProviderTextTypes
     */
    public function testCheck(string $type, int $length)
    {
        $instance = new TextColumn('foo', $type);
        $this->assertSame(null, $instance->check(null));
        $this->assertSame(null, $instance->check(''));
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
        $instance = new TextColumn('foo', 'bar');
    }

    public function testTooLong()
    {
        $this->expectException(ColumnException::class);
        $instance = new TextColumn('foo', 'text');
        $instance->check(str_repeat('a', 65536));
    }
}
