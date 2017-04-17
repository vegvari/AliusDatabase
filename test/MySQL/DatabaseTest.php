<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use PHPUnit\Framework\TestCase;

class DatabaseTestTableFixture extends Table
{
    protected static $name = 'foo';
}

class DatabaseTestInvalidTableFixture
{
}

class DatabaseTestDatabaseFixtureInvalidName extends Database
{
}

class DatabaseTest extends TestCase
{
    public function testDefaults()
    {
        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $this->assertSame('foo', $database::getName());
        $this->assertSame('InnoDB', $database->getEngine());
        $this->assertSame('utf8', $database->getCharset());
        $this->assertSame('utf8_general_ci', $database->getCollation());
        $this->assertSame(false, $database->hasTable());
        $this->assertSame([], $database->getTables());
    }

    public function testSetters()
    {
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

            protected function setUpTable()
            {
                $this->setTable(DatabaseTestTableFixture::class);
            }
        };

        $this->assertSame('foo', $database::getName());
        $this->assertSame('MyISAM', $database->getEngine());
        $this->assertSame('latin1', $database->getCharset());
        $this->assertSame('latin1_bin', $database->getCollation());
        $this->assertSame(true, $database->hasTable());
        $this->assertSame(['foo' => $database->getTable('foo')], $database->getTables());
        $this->assertSame(true, $database->hasTable('foo'));
    }

    public function testInvalidNameConstructor()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::DATABASE_INVALID_NAME);

        $database = new class() extends Database {
        };
    }

    public function testInvalidNameGetName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::DATABASE_INVALID_NAME);

        DatabaseTestDatabaseFixtureInvalidName::getName();
    }

    public function testInvalidTable()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::INVALID_TABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';

            protected function setUpTable()
            {
                $this->setTable(DatabaseTestInvalidTableFixture::class);
            }
        };
    }

    public function testTableAlreadySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::DATABASE_TABLE_ALREADY_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';

            protected function setUpTable()
            {
                $this->setTable(DatabaseTestTableFixture::class);
                $this->setTable(DatabaseTestTableFixture::class);
            }
        };
    }

    public function testTableNotSet()
    {
        $this->expectException(Exceptions\DatabaseException::class);
        $this->expectExceptionCode(Exceptions\DatabaseException::DATABASE_TABLE_NOT_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->getTable('foo');
    }

    public function testImmutableSetEngine()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->setEngine('MyISAM');
    }

    public function testImmutableSetCharset()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->setCharset('latin1');
    }

    public function testImmutableSetCollation()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->setCollation('latin1_bin');
    }

    public function testImmutableSetTable()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->setTable(DatabaseTestTableFixture::class);
    }
}
