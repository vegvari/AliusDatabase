<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;

class PrimaryKeyTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $constraint = new PrimaryKey('column');
        $this->assertSame(false, $constraint->isComposite());
        $this->assertSame('PRIMARY KEY (`column`)', $constraint->buildCreate());
        $this->assertSame('ADD PRIMARY KEY (`column`)', $constraint->buildAdd());
        $this->assertSame('DROP PRIMARY KEY', $constraint->buildDrop());

        $constraint = new PrimaryKey('column', 'column2');
        $this->assertSame(true, $constraint->isComposite());
        $this->assertSame('PRIMARY KEY (`column`, `column2`)', $constraint->buildCreate());
        $this->assertSame('ADD PRIMARY KEY (`column`, `column2`)', $constraint->buildAdd());
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
