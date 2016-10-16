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

    public function testTable()
    {
        $db = new Database($this->writer, $this->reader);
        $db->setTable(IntColumnsFixture::class);
        $db->getTable(IntColumnsFixture::class);
    }

    public function testSetNonExistingTableClass()
    {
        $this->expectException(DatabaseException::class);
        $db = new Database($this->writer, $this->reader);
        $db->setTable(PleaseDoNotCreateAClassLikeThisEver::class);
    }

    public function testSetTableClassTwice()
    {
        $this->expectException(DatabaseException::class);
        $db = new Database($this->writer, $this->reader);
        $db->setTable(IntColumnsFixture::class);
        $db->setTable(IntColumnsFixture::class);
    }

    public function testSetTableNotTableInstance()
    {
        $this->expectException(DatabaseException::class);
        $db = new Database($this->writer, $this->reader);
        $db->setTable(FakeTableFixture::class);
    }

    public function testGetUndefinedTable()
    {
        $this->expectException(DatabaseException::class);
        $db = new Database($this->writer, $this->reader);
        $db->getTable(IntColumnsFixture::class);
    }
}

class FakeTableFixture {}

class IntColumnsFixture extends Table
{
    public function setUp()
    {
        $this->setName('int_columns');

        $this->setColumn(Column::int('int_signed'));
        $this->setColumn(Column::int('int_signed_nullable')->nullable());
        $this->setColumn(Column::int('int_signed_with_default')->default(123));
        $this->setColumn(Column::int('int_signed_nullable_with_default')->nullable()->default(123));
        $this->setColumn(Column::int('int_signed_with_comment')->comment('comment'));
        $this->setColumn(Column::int('int_signed_nullable_with_comment')->nullable()->comment('comment'));
        $this->setColumn(Column::int('int_signed_with_default_and_comment')->default(123)->comment('comment'));
        $this->setColumn(Column::int('int_signed_nullable_with_default_and_comment')->nullable()->default(123)->comment('comment'));

        $this->setColumn(Column::int('int_unsigned')->unsigned());
        $this->setColumn(Column::int('int_unsigned_nullable')->unsigned()->nullable());
        $this->setColumn(Column::int('int_unsigned_with_default')->unsigned()->default(123));
        $this->setColumn(Column::int('int_unsigned_nullable_with_default')->unsigned()->nullable()->default(123));
        $this->setColumn(Column::int('int_unsigned_with_comment')->unsigned()->comment('comment'));
        $this->setColumn(Column::int('int_unsigned_nullable_with_comment')->unsigned()->nullable()->comment('comment'));
        $this->setColumn(Column::int('int_unsigned_with_default_and_comment')->unsigned()->default(123)->comment('comment'));
        $this->setColumn(Column::int('int_unsigned_nullable_with_default_and_comment')->unsigned()->nullable()->default(123)->comment('comment'));
    }
}
