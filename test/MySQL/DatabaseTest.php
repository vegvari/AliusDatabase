<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;
use Alius\Database\DatabaseException;

class DatabaseTestTableFixture extends Table
{
    const NAME = 'foo';
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
            const NAME = 'foo';
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
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::DATABASE_INVALID_NAME);

        $database = new class() extends Database {
        };
    }

    public function testInvalidNameGetName()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::DATABASE_INVALID_NAME);

        DatabaseTestDatabaseFixtureInvalidName::getName();
    }

    public function testInvalidTable()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::INVALID_TABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $database->setTable(DatabaseTestInvalidTableFixture::class);
    }

    public function testTableAlreadySet()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::DATABASE_TABLE_ALREADY_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $database->setTable(DatabaseTestTableFixture::class);
        $database->setTable(DatabaseTestTableFixture::class);
    }

    public function testTableNotSet()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionCode(DatabaseException::DATABASE_TABLE_NOT_SET);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $database->getTable('foo');
    }

    public function testImmutableSetEngine()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $database->setImmutable();
        $database->setEngine('MyISAM');
    }

    public function testImmutableSetCharset()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $database->setImmutable();
        $database->setCharset('latin1');
    }

    public function testImmutableSetCollation()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $database->setImmutable();
        $database->setCollation('latin1_bin');
    }

    public function testImmutableSetTable()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $database = new class() extends Database {
            const NAME = 'foo';
        };

        $database->setImmutable();
        $database->setTable(DatabaseTestTableFixture::class);
    }
}
