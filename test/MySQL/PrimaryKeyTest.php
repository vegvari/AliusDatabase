<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use PHPUnit\Framework\TestCase;

class PrimaryKeyTest extends TestCase
{
    public function test()
    {
        $constraint = new PrimaryKey('foo');
        $this->assertSame(false, $constraint->isComposite());
        $this->assertSame('PRIMARY KEY (`foo`)', $constraint->buildCreate());
        $this->assertSame('ADD PRIMARY KEY (`foo`)', $constraint->buildAdd());
        $this->assertSame('DROP PRIMARY KEY', $constraint->buildDrop());

        $constraint = new PrimaryKey('foo', 'bar');
        $this->assertSame(true, $constraint->isComposite());
        $this->assertSame('PRIMARY KEY (`foo`, `bar`)', $constraint->buildCreate());
        $this->assertSame('ADD PRIMARY KEY (`foo`, `bar`)', $constraint->buildAdd());
        $this->assertSame('DROP PRIMARY KEY', $constraint->buildDrop());
    }

    public function testNoColumn()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::PRIMARY_KEY_NO_COLUMN);

        new PrimaryKey();
    }

    public function testDuplicated()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::PRIMARY_KEY_DUPLICATED_COLUMN);

        new PrimaryKey('column', 'column');
    }
}
