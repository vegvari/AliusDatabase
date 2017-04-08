<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

class ServerTestDatabaseFixture extends Database
{
    protected static $name = 'foo';
}

class ServerTestInvalidDatabaseFixture
{
}

class ServerTest extends \PHPUnit_Framework_TestCase
{
    use ConnectionTrait;

    public function testWriterAndReader()
    {
        $writer = $this->getConnection();
        $reader = $this->getConnection();
        $reader2 = $this->getConnection();

        $server = new class($writer) extends Server {
        };

        $this->assertSame(false, $server->hasReader());
        $this->assertSame($writer, $server->getWriter());
        $this->assertSame($writer, $server->getReader());
        $this->assertSame(false, $server->inTransaction());

        $server = new class($writer, $reader, $reader2) extends Server {
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
        };

        $this->assertSame(false, $server->isImmutable());
        $this->assertSame(false, $server->hasDatabase(ServerTestDatabaseFixture::class));

        $server->setDatabase(ServerTestDatabaseFixture::class);

        $this->assertSame(true, $server->hasDatabase());
        $this->assertSame(true, $server->hasDatabase('foo'));

        // getDatabase calls setImmutable on the new database class
        $this->assertEquals((new ServerTestDatabaseFixture)->setImmutable(), $server->getDatabase('foo'));
        $this->assertSame($server->getDatabase('foo'), $server->getDatabase('foo'));

        $server->setImmutable();
        $this->assertSame(true, $server->isImmutable());
    }

    public function testExecute()
    {
        $server = new class($this->getConnection()) extends Server {
        };

        $server->execute('SELECT 1=1');
    }

    public function testSetDatabaseImmutable()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::IMMUTABLE);

        $server = new class($this->getConnection()) extends Server {
        };

        $server->setImmutable();

        $server->setDatabase(ServerTestDatabaseFixture::class);
    }

    public function testInvalidDatabase()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::INVALID_DATABASE);

        $server = new class($this->getConnection()) extends Server {
        };

        $server->setDatabase(ServerTestInvalidDatabaseFixture::class);
    }

    public function testDatabaseAlreadySet()
    {
        $this->expectException(Exceptions\SchemaException::class);
        $this->expectExceptionCode(Exceptions\SchemaException::SERVER_DATABASE_ALREADY_SET);

        $server = new class($this->getConnection()) extends Server {
        };

        $server->setDatabase(ServerTestDatabaseFixture::class);
        $server->setDatabase(ServerTestDatabaseFixture::class);
    }

    public function testTableNotSet()
    {
        $this->expectException(Exceptions\ServerException::class);
        $this->expectExceptionCode(Exceptions\ServerException::SERVER_DATABASE_NOT_SET);

        $server = new class($this->getConnection()) extends Server {
        };

        $server->getDatabase('foo');
    }
}
