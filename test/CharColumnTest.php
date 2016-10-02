<?php

namespace Alius\Database;

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
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL', $type), $column->build());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL', $type), (string) $column);

        // binary
        $column->binary();
        $this->assertSame(true, $column->isBinary());
        $this->assertSame(sprintf('`foo` %s(255) BINARY NOT NULL', $type), $column->build());

        // binary + charset
        $column->charset('utf8');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 NOT NULL', $type), $column->build());

        // binary + charset + collation
        $column->collation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $column->build());

        // binary + charset + collation + nullable
        $column->nullable();
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin', $type), $column->build());

        // binary + charset + collation + nullable + default
        $column->default('bar');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar"', $type), $column->build());

        // binary + charset + collation + nullable + default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $column->build());



        // charset
        $column = new CharColumn('foo', $type, 255);
        $column->charset('utf8');
        $this->assertSame('utf8', $column->getCharset());
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 NOT NULL', $type), $column->build());

        // charset + collation
        $column->collation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $column->build());

        // charset + collation + nullable
        $column->nullable();
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin', $type), $column->build());

        // charset + collation + nullable + default
        $column->default('bar');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar"', $type), $column->build());

        // charset + collation + nullable + default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $column->build());



        // collation
        $column = new CharColumn('foo', $type, 255);
        $column->collation('utf8_bin');
        $this->assertSame('utf8_bin', $column->getCollation());
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin NOT NULL', $type), $column->build());

        // collation + nullable
        $column->nullable();
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin', $type), $column->build());

        // collation + nullable + default
        $column->default('bar');
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin DEFAULT "bar"', $type), $column->build());

        // collation + nullable + default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $column->build());



        // nullable
        $column = new CharColumn('foo', $type, 255);
        $column->nullable();
        $this->assertSame(true, $column->isNullable());
        $this->assertSame(sprintf('`foo` %s(255)', $type), $column->build());

        // nullable + default
        $column->default('bar');
        $this->assertSame(sprintf('`foo` %s(255) DEFAULT "bar"', $type), $column->build());

        // nullable + default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) DEFAULT "bar" COMMENT "foobar"', $type), $column->build());



        // default
        $column = new CharColumn('foo', $type, 255);
        $column->default('bar');
        $this->assertSame(true, $column->hasDefault());
        $this->assertSame('bar', $column->getDefault());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL DEFAULT "bar"', $type), $column->build());

        // default + comment
        $column->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL DEFAULT "bar" COMMENT "foobar"', $type), $column->build());



        // comment
        $column = new CharColumn('foo', $type, 255);
        $column->comment('foobar');
        $this->assertSame(true, $column->hasComment());
        $this->assertSame('foobar', $column->getComment());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL COMMENT "foobar"', $type), $column->build());
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
        $this->expectException(ColumnException::class);
        $column = new CharColumn('foo', 'bar', 0);
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testLengthMin(string $type)
    {
        $this->expectException(ColumnException::class);
        $column = new CharColumn('foo', $type, -1);
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testLengthMax(string $type, int $length)
    {
        $this->expectException(ColumnException::class);
        $column = new CharColumn('foo', $type, $length + 1);
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testTooLong(string $type, int $length)
    {
        $this->expectException(ColumnException::class);
        $column = new CharColumn('foo', $type, $length);
        $column->check(str_repeat('a', $length + 1));
    }

    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testDefaultTooLong(string $type, int $length)
    {
        $this->expectException(ColumnException::class);
        $column = new CharColumn('foo', $type, $length);
        $column->default(str_repeat('a', $length + 1));
    }
}
