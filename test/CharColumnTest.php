<?php

namespace Alius\Database;

class CharColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderCharTypes
     */
    public function testCharColumn(string $type)
    {
        $instance = new CharColumn('foo', $type, 255);
        $this->assertSame('foo', $instance->getName());
        $this->assertSame($type, $instance->getType());
        $this->assertSame(255, $instance->getLength());
        $this->assertSame(false, $instance->isBinary());
        $this->assertSame('', $instance->getCharset());
        $this->assertSame('', $instance->getCollation());
        $this->assertSame(false, $instance->isNullable());
        $this->assertSame(false, $instance->hasDefault());
        $this->assertSame(null, $instance->getDefault());
        $this->assertSame(false, $instance->hasComment());
        $this->assertSame('', $instance->getComment());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL', $type), $instance->build());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL', $type), (string) $instance);

        // binary
        $instance->binary();
        $this->assertSame(true, $instance->isBinary());
        $this->assertSame(sprintf('`foo` %s(255) BINARY NOT NULL', $type), $instance->build());

        // binary + charset
        $instance->charset('utf8');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 NOT NULL', $type), $instance->build());

        // binary + charset + collation
        $instance->collation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $instance->build());

        // binary + charset + collation + nullable
        $instance->nullable();
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin', $type), $instance->build());

        // binary + charset + collation + nullable + default
        $instance->default('bar');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar"', $type), $instance->build());

        // binary + charset + collation + nullable + default + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) BINARY CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $instance->build());



        // charset
        $instance = new CharColumn('foo', $type, 255);
        $instance->charset('utf8');
        $this->assertSame('utf8', $instance->getCharset());
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 NOT NULL', $type), $instance->build());

        // charset + collation
        $instance->collation('utf8_bin');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL', $type), $instance->build());

        // charset + collation + nullable
        $instance->nullable();
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin', $type), $instance->build());

        // charset + collation + nullable + default
        $instance->default('bar');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar"', $type), $instance->build());

        // charset + collation + nullable + default + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $instance->build());



        // collation
        $instance = new CharColumn('foo', $type, 255);
        $instance->collation('utf8_bin');
        $this->assertSame('utf8_bin', $instance->getCollation());
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin NOT NULL', $type), $instance->build());

        // collation + nullable
        $instance->nullable();
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin', $type), $instance->build());

        // collation + nullable + default
        $instance->default('bar');
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin DEFAULT "bar"', $type), $instance->build());

        // collation + nullable + default + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) COLLATE utf8_bin DEFAULT "bar" COMMENT "foobar"', $type), $instance->build());



        // nullable
        $instance = new CharColumn('foo', $type, 255);
        $instance->nullable();
        $this->assertSame(true, $instance->isNullable());
        $this->assertSame(sprintf('`foo` %s(255)', $type), $instance->build());

        // nullable + default
        $instance->default('bar');
        $this->assertSame(sprintf('`foo` %s(255) DEFAULT "bar"', $type), $instance->build());

        // nullable + default + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) DEFAULT "bar" COMMENT "foobar"', $type), $instance->build());



        // default
        $instance = new CharColumn('foo', $type, 255);
        $instance->default('bar');
        $this->assertSame(true, $instance->hasDefault());
        $this->assertSame('bar', $instance->getDefault());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL DEFAULT "bar"', $type), $instance->build());

        // default + comment
        $instance->comment('foobar');
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL DEFAULT "bar" COMMENT "foobar"', $type), $instance->build());



        // comment
        $instance = new CharColumn('foo', $type, 255);
        $instance->comment('foobar');
        $this->assertSame(true, $instance->hasComment());
        $this->assertSame('foobar', $instance->getComment());
        $this->assertSame(sprintf('`foo` %s(255) NOT NULL COMMENT "foobar"', $type), $instance->build());
    }

    public function dataProviderCharTypes(): array
    {
        foreach (array_keys(CharColumn::TYPES) as $type) {
            $result[] = [$type];
        }

        return $result;
    }

    public function testCharInvalidType()
    {
        $this->expectException(ColumnException::class);
        $instance = new CharColumn('foo', 'bar', 0);
    }

    public function testCharLengthMin()
    {
        $this->expectException(ColumnException::class);
        $instance = new CharColumn('foo', 'char', -1);
    }

    public function testVarCharLengthMin()
    {
        $this->expectException(ColumnException::class);
        $instance = new CharColumn('foo', 'varchar', -1);
    }

    public function testCharLengthMax()
    {
        $this->expectException(ColumnException::class);
        $instance = new CharColumn('foo', 'char', CharColumn::TYPES['char'] + 1);
    }

    public function testVarCharLengthMax()
    {
        $this->expectException(ColumnException::class);
        $instance = new CharColumn('foo', 'varchar', CharColumn::TYPES['varchar'] + 1);
    }

    public function testValueTooLong()
    {
        $this->expectException(ColumnException::class);
        $instance = new CharColumn('foo', 'varchar', 0);
        $instance->check('foo');
    }

    public function testDefaultValueTooLong()
    {
        $this->expectException(ColumnException::class);
        $instance = new CharColumn('foo', 'varchar', 0);
        $instance->default('foo');
    }
}
