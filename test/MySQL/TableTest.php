<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use PHPUnit\Framework\TestCase;

class TableTestTableFixtureInvalidName extends Table
{
}

class TableTest extends TestCase
{
    public function testDefaults()
    {
        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        // database name
        $this->assertSame('foo', $table->getDatabaseName());

        // table name
        $this->assertSame('bar', $table::getName());

        // engine
        $this->assertSame('InnoDB', $table->getEngine());

        // charset
        $this->assertSame('utf8', $table->getCharset());

        // collation
        $this->assertSame('utf8_general_ci', $table->getCollation());

        // column
        $this->assertSame(false, $table->hasColumn());
        $this->assertSame(false, $table->hasColumn('foobar'));
        $this->assertSame([], $table->getColumns());

        // primary key
        $this->assertSame(false, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());

        // unique key
        $this->assertSame(false, $table->hasUniqueKey());
        $this->assertSame(false, $table->hasUniqueKey('foobar'));
        $this->assertSame([], $table->getUniqueKeys());

        // index
        $this->assertSame(false, $table->hasIndex());
        $this->assertSame(false, $table->hasIndex('foobar'));
        $this->assertSame(false, $table->hasIndexWithColumns('foobar'));
        $this->assertSame([], $table->getIndexes());

        // foreign key
        $this->assertSame(false, $table->hasForeignKey());
        $this->assertSame(false, $table->hasForeignKey('foobar'));
        $this->assertSame([], $table->getForeignKeys());

        $database = new class() extends Database {
            protected static $name = 'foo';

            protected function setUpEngine()
            {
                $this->setEngine('MyISAM');
            }

            protected function setUpCharset()
            {
                $this->setCharset('latin1');
            }

            protected function setUpCollation()
            {
                $this->setCollation('latin1_bin');
            }
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        // database name
        $this->assertSame('foo', $table->getDatabaseName());

        // table name
        $this->assertSame('bar', $table::getName());

        // engine
        $this->assertSame('MyISAM', $table->getEngine());

        // charset
        $this->assertSame('latin1', $table->getCharset());

        // collation
        $this->assertSame('latin1_bin', $table->getCollation());

        // column
        $this->assertSame(false, $table->hasColumn());
        $this->assertSame(false, $table->hasColumn('foobar'));
        $this->assertSame([], $table->getColumns());

        // primary key
        $this->assertSame(false, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());

        // unique key
        $this->assertSame(false, $table->hasUniqueKey());
        $this->assertSame(false, $table->hasUniqueKey('foobar'));
        $this->assertSame([], $table->getUniqueKeys());

        // index
        $this->assertSame(false, $table->hasIndex());
        $this->assertSame(false, $table->hasIndex('foobar'));
        $this->assertSame(false, $table->hasIndexWithColumns('foobar'));
        $this->assertSame([], $table->getIndexes());

        // foreign key
        $this->assertSame(false, $table->hasForeignKey());
        $this->assertSame(false, $table->hasForeignKey('foobar'));
        $this->assertSame([], $table->getForeignKeys());
    }

    public function testSetters()
    {
        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpEngine()
            {
                $this->setEngine('MyISAM');
            }

            protected function setUpCharset()
            {
                $this->setCharset('latin1');
            }

            protected function setUpCollation()
            {
                $this->setCollation('latin1_bin');
            }

            protected function setUpColumn()
            {
                $this->setColumn(Column::int('foobar'));
            }

            protected function setUpPrimaryKey()
            {
                $this->setPrimaryKey('foobar');
            }

            protected function setUpUniqueKey()
            {
                $this->setUniqueKey('foobar');
            }

            protected function setUpIndex()
            {
                $this->setIndex('foobar');
            }

            protected function setUpForeignKey()
            {
                $this->setForeignKey('foobar', 'parent');
            }
        };

        // database name
        $this->assertSame('foo', $table->getDatabaseName());

        // table name
        $this->assertSame('bar', $table::getName());

        // engine
        $this->assertSame('MyISAM', $table->getEngine());

        // charset
        $this->assertSame('latin1', $table->getCharset());

        // collation
        $this->assertSame('latin1_bin', $table->getCollation());

        // column
        $this->assertSame(true, $table->hasColumn());
        $this->assertSame(true, $table->hasColumn('foobar'));
        $this->assertSame(['foobar' => $table->getColumn('foobar')], $table->getColumns());

        // primary key
        $this->assertSame(true, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());
        $this->assertEquals(new PrimaryKey('foobar'), $table->getPrimaryKey());

        // unique key
        $this->assertSame(true, $table->hasUniqueKey());
        $this->assertSame(true, $table->hasUniqueKey('unique-foobar'));
        $this->assertEquals(new UniqueKey('unique-foobar', 'foobar'), $table->getUniqueKey('unique-foobar'));
        $this->assertEquals(['unique-foobar' => new UniqueKey('unique-foobar', 'foobar')], $table->getUniqueKeys());

        // index
        $this->assertSame(true, $table->hasIndex());
        $this->assertSame(true, $table->hasIndex('index-foobar'));
        $this->assertSame(true, $table->hasIndexWithColumns('foobar'));
        $this->assertEquals(new Index('index-foobar', 'foobar'), $table->getIndex('index-foobar'));
        $this->assertEquals(['index-foobar' => new Index('index-foobar', 'foobar')], $table->getIndexes());

        // foreign key
        $this->assertSame(true, $table->hasForeignKey());
        $this->assertSame(true, $table->hasForeignKey('bar_ibfk_1'));
        $this->assertEquals(new ForeignKey('bar_ibfk_1', 'foobar', 'parent'), $table->getForeignKey('bar_ibfk_1'));
        $this->assertEquals(['bar_ibfk_1' => new ForeignKey('bar_ibfk_1', 'foobar', 'parent')], $table->getForeignKeys());
    }

    public function testInvalidNameConstructor()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_INVALID_NAME);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $test = new class($database) extends Table {
        };
    }

    public function testInvalidNameGetName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_INVALID_NAME);

        TableTestTableFixtureInvalidName::getName();
    }

    public function testSetColumnAlreadySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_COLUMN_ALREADY_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpColumn()
            {
                $this->setColumn(Column::int('foobar'));
                $this->setColumn(Column::int('foobar'));
            }
        };
    }

    public function testGetColumnNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->getColumn('foobar');
    }

    public function testSetPrimaryKeyAlradySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_PRIMARY_KEY_ALREADY_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpColumn()
            {
                $this->setColumn(Column::int('foobar'));
            }

            protected function setUpForeignKey()
            {
                $this->setPrimaryKey('foobar');
                $this->setPrimaryKey('foobar');
            }
        };
    }

    public function testSetPrimaryKeyColumnNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpForeignKey()
            {
                $this->setPrimaryKey('foobar');
            }
        };
    }

    public function testGetPrimaryKeyNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_PRIMARY_KEY_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->getPrimaryKey();
    }

    public function testSetUniqueKeyAlradySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_UNIQUE_KEY_ALREADY_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpColumn()
            {
                $this->setColumn(Column::int('foobar'));
            }

            protected function setUpUniqueKey()
            {
                $this->setUniqueKey('foobar');
                $this->setUniqueKey('foobar');
            }
        };
    }

    public function testSetUniqueKeyColumnNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpUniqueKey()
            {
                $this->setUniqueKey('foobar');
            }
        };
    }

    public function testGetUniqueKeyNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_UNIQUE_KEY_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->getUniqueKey('foobar');
    }

    public function testSetIndexAlradySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_INDEX_ALREADY_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpColumn()
            {
                $this->setColumn(Column::int('foobar'));
            }

            protected function setUpUniqueKey()
            {
                $this->setIndex('foobar');
                $this->setIndex('foobar');
            }
        };
    }

    public function testSetIndexColumnNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpUniqueKey()
            {
                $this->setIndex('foobar');
            }
        };
    }

    public function testGetIndexKeyNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_INDEX_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->getIndex('foobar');
    }

    public function testSetForeignKeyAlradySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_FOREIGN_KEY_ALREADY_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpColumn()
            {
                $this->setColumn(Column::int('foobar'));
            }

            protected function setUpUniqueKey()
            {
                $this->setForeignKeyObject(new ForeignKey('fk', 'foobar', 'parent'));
                $this->setForeignKeyObject(new ForeignKey('fk', 'foobar', 'parent'));
            }
        };
    }

    public function testSetForeignKeyColumnNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';

            protected function setUpUniqueKey()
            {
                $this->setForeignKey('foobar', 'parent');
            }
        };
    }

    public function testGetForeignKeyNotSet()
    {
        $this->expectException(Exceptions\TableException::class);
        $this->expectExceptionCode(Exceptions\TableException::TABLE_FOREIGN_KEY_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->getForeignKey('foobar');
    }

    public function testImmutableSetEngine()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->setEngine('MyISAM');
    }

    public function testImmutableSetCharset()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->setCharset('latin1');
    }

    public function testImmutableSetCollation()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->setCollation('latin1');
    }

    public function testImmutableSetColumn()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->setColumn(Column::int('foobar'));
    }

    public function testImmutableSetPrimaryKey()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->setPrimaryKey('foobar');
    }

    public function testImmutableSetUniqueKey()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->setUniqueKey('foobar');
    }

    public function testImmutableSetIndex()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->setIndex('foobar');
    }

    public function testImmutableSetForeignKey()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $table = new class($database) extends Table {
            protected static $name = 'bar';
        };

        $table->setForeignKey('foobar', 'parent');
    }
}
