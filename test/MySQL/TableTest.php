<?php

namespace Alius\Database\MySQL;

use Alius\Database\Container;
use Alius\Database\Exceptions;
use PHPUnit\Framework\TestCase;

class TableTestTableFixtureInvalidName extends Table
{
}

class TableTestDatabaseFixture extends Database
{
    protected static $name = 'foo';
}

class TableTest extends TestCase
{
    use ConnectionTrait;

    protected $server;

    public function getServer()
    {
        if ($this->server === null) {
            Container::clearServers();

            $this->server = new class($this->getConnection()) extends Server {
                protected static $name = 'foo';

                protected function setUpDatabase()
                {
                    $this->setDatabase(TableTestDatabaseFixture::class);
                }
            };
        }

        return $this->server;
    }

    public function getDatabase()
    {
        return $this->getServer()->getDatabase('foo');
    }

    public function testDefaults()
    {
        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        // server
        $this->assertSame($this->getServer()::getName(), $table->getServerName());
        $this->assertSame($this->getServer(), $table->getServer());

        // database
        $this->assertSame('foo', $table->getDatabaseName());
        $this->assertSame($this->getDatabase(), $table->getDatabase());

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

        $database = new class($this->getServer()) extends Database {
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
        $table = new class($this->getDatabase()) extends Table {
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

        $test = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->getColumn('foobar');
    }

    public function testSetPrimaryKeyAlradySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_PRIMARY_KEY_ALREADY_SET);

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->getPrimaryKey();
    }

    public function testSetUniqueKeyAlradySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_UNIQUE_KEY_ALREADY_SET);

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->getUniqueKey('foobar');
    }

    public function testSetIndexAlradySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_INDEX_ALREADY_SET);

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->getIndex('foobar');
    }

    public function testSetForeignKeyAlradySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::TABLE_FOREIGN_KEY_ALREADY_SET);

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
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

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->getForeignKey('foobar');
    }

    public function testImmutableSetEngine()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->setEngine('MyISAM');
    }

    public function testImmutableSetCharset()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->setCharset('latin1');
    }

    public function testImmutableSetCollation()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->setCollation('latin1');
    }

    public function testImmutableSetColumn()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->setColumn(Column::int('foobar'));
    }

    public function testImmutableSetPrimaryKey()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->setPrimaryKey('foobar');
    }

    public function testImmutableSetUniqueKey()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->setUniqueKey('foobar');
    }

    public function testImmutableSetIndex()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->setIndex('foobar');
    }

    public function testImmutableSetForeignKey()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'bar';
        };

        $table->setForeignKey('foobar', 'parent');
    }

    public function testBuildCreate()
    {
        // no columns
        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'foo';
        };

        $this->assertSame('CREATE TABLE `foo` () ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci', $table->buildCreate());

        // everything
        $table = new class($this->getDatabase()) extends Table {
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

            protected function setUpColumn()
            {
                $this->setColumn(Column::int('foo_primary_key'));
                $this->setColumn(Column::int('foo_unique_key'));
                $this->setColumn(Column::int('foo_index'));
                $this->setColumn(Column::int('foo_foreign_key'));
            }

            protected function setUpPrimaryKey()
            {
                $this->setPrimaryKey('foo_primary_key');
            }

            protected function setUpUniqueKey()
            {
                $this->setUniqueKey('foo_unique_key');
            }

            protected function setUpIndex()
            {
                $this->setIndex('foo_index');
            }

            protected function setUpForeignKey()
            {
                $this->setForeignKey('foo_foreign_key', 'bar');
            }
        };

        $this->assertSame('CREATE TABLE `foo` (`foo_primary_key` int NOT NULL, `foo_unique_key` int NOT NULL, `foo_index` int NOT NULL, `foo_foreign_key` int NOT NULL, PRIMARY KEY (`foo_primary_key`), UNIQUE KEY `unique-foo_unique_key` (`foo_unique_key`), KEY `index-foo_index` (`foo_index`), KEY `foo_ibfk_1` (`foo_foreign_key`), CONSTRAINT `foo_ibfk_1` FOREIGN KEY (`foo_foreign_key`) REFERENCES `bar` (`foo_foreign_key`) ON UPDATE RESTRICT ON DELETE RESTRICT) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_bin', $table->buildCreate());
    }

    public function testBuildDrop()
    {
        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'foo';
        };

        $this->assertSame('DROP TABLE `foo`', $table->buildDrop());
    }

    public function testBuildTruncate()
    {
        $table = new class($this->getDatabase()) extends Table {
            protected static $name = 'foo';
        };

        $this->assertSame('TRUNCATE TABLE `foo`', $table->buildTruncate());
    }
}
