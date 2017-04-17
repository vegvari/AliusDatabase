<?php

namespace Alius\Database\MySQL;

use Alius\Database\Container;
use Alius\Database\Exceptions;
use Alius\Database\Interfaces;
use PHPUnit\Framework\TestCase;

class ServerTestDatabaseFixture extends Database
{
    protected static $name = 'foo';
}

class ServerTestInvalidDatabaseFixture
{
}
class ServerTestServerFixtureInvalidName extends Server
{
}

class ServerTest extends TestCase
{
    use ConnectionTrait;

    public function setUp()
    {
        Container::clearServers();
    }

    public function testWriterAndReader()
    {
        $writer = $this->getConnection();
        $reader = $this->getConnection();
        $reader2 = $this->getConnection();

        $server = new class($writer) extends Server {
            protected static $name = 'foo';
        };

        $this->assertSame('foo', $server::getName());
        $this->assertSame(false, $server->hasReader());
        $this->assertSame($writer, $server->getWriter());
        $this->assertSame($writer, $server->getReader());
        $this->assertSame(false, $server->inTransaction());

        $server = new class($writer, $reader, $reader2) extends Server {
            protected static $name = 'bar';
        };

        $this->assertSame(true, $server->hasReader());
        $this->assertSame($writer, $server->getWriter());
        $this->assertSame($reader, $server->getReader());

        // in transaction getReader returns the writer instance
        $server->startTransaction();
        $this->assertSame(true, $server->inTransaction());
        $this->assertSame($writer, $server->getWriter());
        $this->assertSame($writer, $server->getReader());

        // commit
        $server->commit();
        $this->assertSame(false, $server->inTransaction());
        $this->assertSame($writer, $server->getWriter());
        $this->assertSame($reader, $server->getReader());

        $server->startTransaction();
        $this->assertSame(true, $server->inTransaction());
        $this->assertSame($writer, $server->getWriter());
        $this->assertSame($writer, $server->getReader());

        // rollback
        $server->rollback();
        $this->assertSame(false, $server->inTransaction());
        $this->assertSame($writer, $server->getWriter());
        $this->assertSame($reader, $server->getReader());

        // transaction with callable
        $server->transaction(function (Interfaces\ServerInterface $server) {
            $this->assertSame(true, $server->inTransaction());
        });
        $this->assertSame(false, $server->inTransaction());

        $server->transaction(function (Interfaces\ServerInterface $server) {
            throw new \Exception('foo');
        });
        $this->assertSame(false, $server->inTransaction());
    }

    public function testSetDatabase()
    {
        $server = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';

            protected function setUpDatabase()
            {
                $this->setDatabase(ServerTestDatabaseFixture::class);
            }
        };

        $this->assertSame(true, $server->hasDatabase());
        $this->assertSame(true, $server->hasDatabase('foo'));
        $this->assertSame($server->getDatabase('foo'), $server->getDatabase('foo'));
    }

    public function testExecute()
    {
        $server = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';
        };

        $server->execute('SELECT 1=1');
    }

    public function testSetDatabaseImmutable()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::IMMUTABLE);

        $server = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';
        };

        $server->setDatabase(ServerTestDatabaseFixture::class);
    }

    public function testInvalidNameConstructor()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::SERVER_INVALID_NAME);

        $server = new class($this->getConnection()) extends Server {
        };
    }

    public function testInvalidNameGetName()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::SERVER_INVALID_NAME);

        ServerTestServerFixtureInvalidName::getName();
    }

    public function testInvalidDatabase()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::INVALID_DATABASE);

        $server = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';

            protected function setUpDatabase()
            {
                $this->setDatabase(ServerTestInvalidDatabaseFixture::class);
            }
        };
    }

    public function testDatabaseAlreadySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::SERVER_DATABASE_ALREADY_SET);

        $server = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';

            protected function setUpDatabase()
            {
                $this->setDatabase(ServerTestDatabaseFixture::class);
                $this->setDatabase(ServerTestDatabaseFixture::class);
            }
        };
    }

    public function testTableNotSet()
    {
        $this->expectException(Exceptions\ServerException::class);
        $this->expectExceptionCode(Exceptions\ServerException::SERVER_DATABASE_NOT_SET);

        $server = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';
        };

        $server->getDatabase('foo');
    }
}
