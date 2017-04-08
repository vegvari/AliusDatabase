<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;

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

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testSetters()
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
        $this->assertSame(false, $database->isImmutable());

        $database->setEngine('MyISAM');
        $this->assertSame('MyISAM', $database->getEngine());

        $database->setCharset('latin1');
        $this->assertSame('latin1', $database->getCharset());

        $database->setCollation('latin1_bin');
        $this->assertSame('latin1_bin', $database->getCollation());

        $database->setTable(DatabaseTestTableFixture::class);
        $this->assertSame(true, $database->hasTable());
        $this->assertSame(true, $database->hasTable('foo'));

        // getTable calls setImmutable on the new table class
        $this->assertEquals((new DatabaseTestTableFixture($database))->setImmutable(), $database->getTable('foo'));
        $this->assertSame(['foo' => $database->getTable('foo')], $database->getTables());

        $database->setImmutable();
        $this->assertSame(true, $database->isImmutable());
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
        };

        $database->setTable(DatabaseTestInvalidTableFixture::class);
    }

    public function testTableAlreadySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::DATABASE_TABLE_ALREADY_SET);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->setTable(DatabaseTestTableFixture::class);
        $database->setTable(DatabaseTestTableFixture::class);
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

        $database->setImmutable();
        $database->setEngine('MyISAM');
    }

    public function testImmutableSetCharset()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->setImmutable();
        $database->setCharset('latin1');
    }

    public function testImmutableSetCollation()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->setImmutable();
        $database->setCollation('latin1_bin');
    }

    public function testImmutableSetTable()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            protected static $name = 'foo';
        };

        $database->setImmutable();
        $database->setTable(DatabaseTestTableFixture::class);
    }
}
