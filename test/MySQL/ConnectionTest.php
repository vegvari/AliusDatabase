<?php

namespace Alius\Database\MySQL;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    use ConnectionTrait;

    public function testNameAndOptions()
    {
        $connection = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword());
        $this->assertSame(Connection::DEFAULT_OPTIONS, $connection->getOptions());

        $connection = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword(), []);
        $this->assertSame([], $connection->getOptions());
    }

    public function testPDO()
    {
        $connection = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword());
        $this->assertSame(true, $connection->getPDO() instanceof \PDO);
        $this->assertSame($connection->getPDO(), $connection->getPDO());
    }

    public function testSerialize()
    {
        $connection = $this->getConnection();
        $connection_pdo = $connection->getPDO();

        $connection2 = unserialize(serialize($connection));
        $connection2->getPDO();

        $this->assertSame($connection_pdo, $connection->getPDO());
        $this->assertEquals($connection, $connection2);
    }
}
