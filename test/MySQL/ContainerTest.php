<?php

namespace Alius\Database\MySQL;

use Alius\Database\Container;
use Alius\Database\Exceptions;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    use ConnectionTrait;

    public function setUp()
    {
        Container::clearServers();
    }

    public function testSetServer()
    {
        $this->assertSame(false, Container::hasServer());
        $this->assertSame(false, Container::hasServer('foo'));
        $this->assertSame(false, Container::hasServer('bar'));

        $foo = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';
        };

        $bar = new class($this->getConnection()) extends Server {
            protected static $name = 'bar';
        };

        $this->assertSame(true, Container::hasServer());
        $this->assertSame(true, Container::hasServer('foo'));
        $this->assertSame(true, Container::hasServer('bar'));
    }

    public function testServerAlreadySet()
    {
        $this->expectException(Exceptions\LogicException::class);
        $this->expectExceptionCode(Exceptions\LogicException::CONTAINER_SERVER_ALREADY_SET);

        $foo = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';
        };

        $bar = new class($this->getConnection()) extends Server {
            protected static $name = 'foo';
        };
    }

    public function testServerNotSet()
    {
        $this->expectException(Exceptions\ContainerException::class);
        $this->expectExceptionCode(Exceptions\ContainerException::CONTAINER_SERVER_NOT_SET);

        Container::getServer('bar');
    }
}
