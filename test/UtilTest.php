<?php

namespace Alius\Database;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $this->assertSame(true, Util::instanceOf(Table::class, Table::class));
        $this->assertSame(true, Util::instanceOf(Table1stChild::class, Table::class));
        $this->assertSame(true, Util::instanceOf(Table2ndChild::class, Table::class));
    }
}

class Table1stChild extends Table {}
class Table2ndChild extends Table1stChild {}
