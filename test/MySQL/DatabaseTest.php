<?php

namespace Alius\Database\MySQL;

use Alius\Database\Container;
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
    use ConnectionTrait;

    protected $server;

    public function getServer()
    {
        if ($this->server === null) {
            Container::clearServers();

            $this->server = new class($this->getConnection()) extends Server {
                protected static $name = 'foo';
            };
        }

        return $this->server;
    }

    public function testDefaults()
    {
        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';
        };

        $this->assertSame($this->getServer()::getName(), $database->getServerName());
        $this->assertSame($this->getServer(), $database->getServer());
        $this->assertSame('foo', $database::getName());
        $this->assertSame('InnoDB', $database->getEngine());
        $this->assertSame('utf8', $database->getCharset());
        $this->assertSame('utf8_general_ci', $database->getCollation());
        $this->assertSame(false, $database->hasTable());
        $this->assertSame([], $database->getTables());
    }

    public function testSetters()
    {
        Container::clearServers();

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

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
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

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
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

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
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

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';
        };

        $database->getTable('foo');
    }

    public function testImmutableSetEngine()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        Container::clearServers();

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';
        };

        $database->setEngine('MyISAM');
    }

    public function testImmutableSetCharset()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';
        };

        $database->setCharset('latin1');
    }

    public function testImmutableSetCollation()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';
        };

        $database->setCollation('latin1_bin');
    }

    public function testImmutableSetTable()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';
        };

        $database->setTable(DatabaseTestTableFixture::class);
    }

    public function testBuildCreate()
    {
        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';
        };

        $this->assertSame('CREATE DATABASE `foo` DEFAULT CHARACTER SET = `utf8` DEFAULT COLLATE = `utf8_general_ci`', $database->buildCreate());

        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';

            protected function setUpCharset()
            {
                $this->setCharset('latin1');
            }

            protected function setUpCollation()
            {
                $this->setCollation('latin1_bin');
            }
        };

        $this->assertSame('CREATE DATABASE `foo` DEFAULT CHARACTER SET = `latin1` DEFAULT COLLATE = `latin1_bin`', $database->buildCreate());
    }

    public function testBuildDrop()
    {
        Container::clearServers();

        $database = new class($this->getServer()) extends Database {
            protected static $name = 'foo';
        };

        $this->assertSame('DROP DATABASE `foo`', $database->buildDrop());
    }
}
