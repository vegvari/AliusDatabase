<?php

namespace Alius\Database;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    use ConnectionTrait;

    public function testNameAndOptions()
    {
        $options = [
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::ATTR_ORACLE_NULLS       => \PDO::NULL_EMPTY_STRING,
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET GLOBAL time_zone="UTC", time_zone="UTC"',
        ];

        $connection = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword(), $this->getDatabase());
        $this->assertSame($this->getDatabase(), $connection->getDatabase());
        $this->assertSame('utf8', $connection->getCharset());
        $this->assertSame($options, $connection->getOptions());

        $connection = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword(), $this->getDatabase(), '', []);
        $this->assertSame('', $connection->getCharset());
        $this->assertSame([], $connection->getOptions());
    }

    public function testPDO()
    {
        $connection = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword(), $this->getDatabase());
        $this->assertSame(true, $connection->getPDO() instanceof \PDO);
        $this->assertSame($connection->getPDO(), $connection->getPDO());
    }
}
