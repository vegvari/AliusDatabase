<?php

namespace Alius\Database\MySQL;

use Alius\Database\TableException;
use Alius\Database\SchemaException;

class TableTestTableFixtureInvalidName extends Table
{
}

class TableTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $this->assertSame('bar', $table::getName());
        $this->assertSame('foo', $table->getDatabaseName());
        $this->assertSame('InnoDB', $table->getEngine());
        $this->assertSame('utf8', $table->getCharset());
        $this->assertSame('utf8_general_ci', $table->getCollation());
        $this->assertSame(false, $table->hasColumn());
        $this->assertSame(false, $table->hasColumn('foobar'));
        $this->assertSame([], $table->getColumns());
        $this->assertSame(false, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());
        $this->assertSame(false, $table->hasUniqueKey());
        $this->assertSame(false, $table->hasUniqueKey('foobar'));
        $this->assertSame([], $table->getUniqueKeys());
        $this->assertSame(false, $table->hasIndex());
        $this->assertSame(false, $table->hasIndex('foobar'));
        $this->assertSame(false, $table->hasIndexWithColumns('foobar'));
        $this->assertSame([], $table->getIndexes());
        $this->assertSame(false, $table->hasForeignKey());
        $this->assertSame(false, $table->hasForeignKey('foobar'));
        $this->assertSame([], $table->getForeignKeys());

        $database = new class() extends Database {
            const NAME = 'foo';

            protected function setUp()
            {
                $this->setEngine('MyISAM');
                $this->setCharset('latin1');
                $this->setCollation('latin1_bin');
            }
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $this->assertSame('bar', $table::getName());
        $this->assertSame('MyISAM', $table->getEngine());
        $this->assertSame('latin1', $table->getCharset());
        $this->assertSame('latin1_bin', $table->getCollation());
    }

    public function testSetters()
    {
        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setEngine('MyISAM');
        $this->assertSame('MyISAM', $table->getEngine());

        $table->setCharset('latin1');
        $this->assertSame('latin1', $table->getCharset());

        $table->setCollation('latin1_bin');
        $this->assertSame('latin1_bin', $table->getCollation());

        $column = Column::int('foobar');
        $table->setColumn($column);
        $this->assertSame(true, $table->hasColumn());
        $this->assertSame(true, $table->hasColumn('foobar'));
        $this->assertSame($column, $table->getColumn('foobar'));
        $this->assertSame(['foobar' => $column], $table->getColumns());

        $table->setPrimaryKey('foobar');
        $this->assertSame(true, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());
        $this->assertEquals(new PrimaryKey('foobar'), $table->getPrimaryKey());

        $table->setUniqueKey('foobar');
        $this->assertSame(true, $table->hasUniqueKey());
        $this->assertSame(true, $table->hasUniqueKey('unique-foobar'));
        $this->assertEquals(new UniqueKey('unique-foobar', 'foobar'), $table->getUniqueKey('unique-foobar'));
        $this->assertEquals(['unique-foobar' => new UniqueKey('unique-foobar', 'foobar')], $table->getUniqueKeys());

        $table->setIndex('foobar');
        $this->assertSame(true, $table->hasIndex());
        $this->assertSame(true, $table->hasIndex('index-foobar'));
        $this->assertSame(true, $table->hasIndexWithColumns('foobar'));
        $this->assertEquals(new Index('index-foobar', 'foobar'), $table->getIndex('index-foobar'));
        $this->assertEquals(['index-foobar' => new Index('index-foobar', 'foobar')], $table->getIndexes());

        $table->setForeignKey('foobar', 'parent');
        $this->assertSame(true, $table->hasForeignKey());
        $this->assertSame(true, $table->hasForeignKey('bar_ibfk_1'));
        $this->assertEquals(new ForeignKey('bar_ibfk_1', 'foobar', 'parent'), $table->getForeignKey('bar_ibfk_1'));
        $this->assertEquals(['bar_ibfk_1' => new ForeignKey('bar_ibfk_1', 'foobar', 'parent')], $table->getForeignKeys());

        // foreign key adds a new index if there is no index with the same columns
        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setColumn(Column::int('foobar'));
        $table->setForeignKey('foobar', 'parent');
        $this->assertSame(true, $table->hasIndex('bar_ibfk_1'));
    }

    public function testInvalidNameConstructor()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::TABLE_INVALID_NAME);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $test = new class($database) extends Table {
        };
    }

    public function testInvalidNameGetName()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::TABLE_INVALID_NAME);

        TableTestTableFixtureInvalidName::getName();
    }

    public function testSetColumnAlreadySet()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::TABLE_COLUMN_ALREADY_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setColumn(Column::int('foobar'));
        $table->setColumn(Column::int('foobar'));
    }

    public function testGetColumnNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->getColumn('foobar');
    }

    public function testSetPrimaryKeyAlradySet()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::TABLE_PRIMARY_KEY_ALREADY_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setColumn(Column::int('foobar'));
        $table->setPrimaryKey('foobar');
        $table->setPrimaryKey('foobar');
    }

    public function testSetPrimaryKeyColumnNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setPrimaryKey('foobar');
    }

    public function testGetPrimaryKeyNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_PRIMARY_KEY_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->getPrimaryKey();
    }

    public function testSetUniqueKeyAlradySet()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::TABLE_UNIQUE_KEY_ALREADY_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setColumn(Column::int('foobar'));
        $table->setUniqueKey('foobar');
        $table->setUniqueKey('foobar');
    }

    public function testSetUniqueKeyColumnNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setUniqueKey('foobar');
    }

    public function testGetUniqueKeyNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_UNIQUE_KEY_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->getUniqueKey('foobar');
    }

    public function testSetIndexAlradySet()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::TABLE_INDEX_ALREADY_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setColumn(Column::int('foobar'));
        $table->setIndex('foobar');
        $table->setIndex('foobar');
    }

    public function testSetIndexColumnNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setIndex('foobar');
    }

    public function testGetIndexKeyNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_INDEX_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->getIndex('foobar');
    }

    public function testSetForeignKeyAlradySet()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::TABLE_FOREIGN_KEY_ALREADY_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setColumn(Column::int('foobar'));
        $table->setForeignKeyObject(new ForeignKey('fk', 'foobar', 'parent'));
        $table->setForeignKeyObject(new ForeignKey('fk', 'foobar', 'parent'));
    }

    public function testSetForeignKeyColumnNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_COLUMN_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setForeignKey('foobar', 'parent');
    }

    public function testGetForeignKeyNotSet()
    {
        $this->expectException(TableException::class);
        $this->expectExceptionCode(TableException::TABLE_FOREIGN_KEY_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->getForeignKey('foobar');
    }

    public function testImmutableSetEngine()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setImmutable();
        $table->setEngine('MyISAM');
    }

    public function testImmutableSetCharset()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setImmutable();
        $table->setCharset('latin1');
    }

    public function testImmutableSetCollation()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setImmutable();
        $table->setCollation('latin1');
    }

    public function testImmutableSetColumn()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setImmutable();
        $table->setColumn(Column::int('foobar'));
    }

    public function testImmutableSetPrimaryKey()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setImmutable();
        $table->setPrimaryKey('foobar');
    }

    public function testImmutableSetUniqueKey()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setImmutable();
        $table->setUniqueKey('foobar');
    }

    public function testImmutableSetIndex()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setImmutable();
        $table->setIndex('foobar');
    }

    public function testImmutableSetForeignKey()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $table = new class($database) extends Table {
            const NAME = 'bar';
        };

        $table->setImmutable();
        $table->setForeignKey('foobar', 'parent');
    }
}
