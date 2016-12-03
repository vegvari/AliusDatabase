<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;
use Alius\Database\ServerException;

class ServerTestDatabaseFixture extends Database
{
    const NAME = 'test';
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
        $server->transaction(function (ServerInterface $server) {
            $this->assertSame(true, $server->inTransaction());
        });
        $this->assertSame(false, $server->inTransaction());

        $server->transaction(function (ServerInterface $server) {
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
        $server->setImmutable();

        $this->assertSame(true, $server->isImmutable());
        $this->assertSame(true, $server->hasDatabase('test'));
        $this->assertEquals(new ServerTestDatabaseFixture, $server->getDatabase('test'));
        $this->assertSame($server->getDatabase('test'), $server->getDatabase('test'));
    }

    public function testSetDatabaseImmutable()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::IMMUTABLE);

        $server = new class($this->getConnection()) extends Server {
        };

        $server->setImmutable();

        $server->setDatabase(ServerTestDatabaseFixture::class);
    }

    public function testInvalidDatabase()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::INVALID_DATABASE);

        $server = new class($this->getConnection()) extends Server {
        };

        $server->setDatabase(ServerTestInvalidDatabaseFixture::class);
    }

    public function testDatabaseAlreadySet()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionCode(SchemaException::SERVER_DATABASE_ALREADY_SET);

        $server = new class($this->getConnection()) extends Server {
        };

        $server->setDatabase(ServerTestDatabaseFixture::class);
        $server->setDatabase(ServerTestDatabaseFixture::class);
    }

    public function testTableNotSet()
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(ServerException::SERVER_DATABASE_NOT_SET);

        $server = new class($this->getConnection()) extends Server {
        };

        $server->getDatabase('foo');
    }
}
