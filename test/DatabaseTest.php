<?php

namespace Alius\Database;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    use ConnectionTrait;

    protected $writer;
    protected $reader;

    public function setUp()
    {
        $this->writer = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword(), $this->getDatabase());
        $this->reader = new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword(), $this->getDatabase());
    }

    public function testConnections()
    {
        $db = new Database($this->writer);

        $this->assertSame($this->writer->getPDO(), $db->getWriter());
        $this->assertSame($this->writer->getPDO(), $db->getReader());
        $this->assertSame($this->getDatabase(), $db->getName());

        $db = new Database($this->writer, $this->reader);

        $this->assertSame($this->writer->getPDO(), $db->getWriter());
        $this->assertSame($this->reader->getPDO(), $db->getReader());

        $db->startTransaction();
        $this->assertSame($this->writer->getPDO(), $db->getWriter());
        $this->assertSame($this->writer->getPDO(), $db->getReader());
        $db->commit();
        $this->assertSame($this->writer->getPDO(), $db->getWriter());
        $this->assertSame($this->reader->getPDO(), $db->getReader());

        $db->startTransaction();
        $this->assertSame($this->writer->getPDO(), $db->getWriter());
        $this->assertSame($this->writer->getPDO(), $db->getReader());
        $db->rollback();
        $this->assertSame($this->writer->getPDO(), $db->getWriter());
        $this->assertSame($this->reader->getPDO(), $db->getReader());
    }

    public function testEngine()
    {
        $db = new Database($this->writer);
        $this->assertSame('InnoDB', $db->getEngine());
        $db->setEngine('foo');
        $this->assertSame('foo', $db->getEngine());
    }

    public function testTimeZone()
    {
        $db = new Database($this->writer);
        $this->assertSame('UTC', $db->getTimeZone()->getName());
        $db->setTimeZone('Europe/London');
        $this->assertSame('Europe/London', $db->getTimeZone()->getName());
    }

    public function testCharset()
    {
        $db = new Database($this->writer);
        $this->assertSame('utf8', $db->getCharset());
        $db->setCharset('foo');
        $this->assertSame('foo', $db->getCharset());
    }

    public function testCollation()
    {
        $db = new Database($this->writer);
        $this->assertSame('utf8_general_ci', $db->getCollation());
        $db->setCollation('foo');
        $this->assertSame('foo', $db->getCollation());
    }

    public function testExecute()
    {
        $db = new Database($this->writer, $this->reader);
        $db->execute('DROP TABLE IF EXISTS test');
        $db->execute('CREATE TABLE test (test_id INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (test_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $db->execute('INSERT INTO test (test_id) VALUES (NULL)');
        $this->assertSame(1, $db->getLastInsertId());

        $statement = $db->execute('SELECT test_id FROM test');
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $this->assertSame([['test_id' => 1]], $statement->fetchAll());
    }
}
