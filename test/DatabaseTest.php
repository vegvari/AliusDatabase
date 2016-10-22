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
        $db->execute('CREATE TABLE test (test_id INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (test_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $db->execute('INSERT INTO test (test_id) VALUES (NULL)');
        $this->assertSame(1, $db->getLastInsertId());

        $statement = $db->execute('SELECT test_id FROM test');
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $this->assertSame([['test_id' => 1]], $statement->fetchAll());

        $db->execute('DROP TABLE test');
    }

    public function testTable()
    {
        $db = new Database($this->writer, $this->reader);
        $this->assertSame([], $db->getTables());
        $this->assertSame(false, $db->hasTableByName('int_columns'));

        $db->setTable(IntColumnsFixture::class);
        $this->assertEquals(new IntColumnsFixture($db->getName(), $db->getEngine(), $db->getCharset(), $db->getCollation()), $db->getTable(IntColumnsFixture::class));
        $this->assertSame(true, $db->hasTableByName('int_columns'));
        $this->assertSame($db->getTable(IntColumnsFixture::class), $db->getTableByName('int_columns'));
        $this->assertSame(['Alius\Database\IntColumnsFixture' => $db->getTable(IntColumnsFixture::class)], $db->getTables());
        $this->assertSame(['int_columns' => 'Alius\Database\IntColumnsFixture'], $db->getTableNames());
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

    public function testGetUndefinedTableByName()
    {
        $this->expectException(DatabaseException::class);
        $db = new Database($this->writer, $this->reader);
        $db->getTableByName('int_columns');
    }
}

class FakeTableFixture {}

class IntColumnsFixture extends Table
{
    public function setUp()
    {
        $this->setName('int_columns');

        $this->setColumn(Column::int('int_signed'));
        $this->setColumn(Column::int('int_signed_nullable')->setNullable());
        $this->setColumn(Column::int('int_signed_with_default')->setDefault(123));
        $this->setColumn(Column::int('int_signed_nullable_with_default')->setNullable()->setDefault(123));
        $this->setColumn(Column::int('int_signed_with_comment')->setComment('comment'));
        $this->setColumn(Column::int('int_signed_nullable_with_comment')->setNullable()->setComment('comment'));
        $this->setColumn(Column::int('int_signed_with_default_and_comment')->setDefault(123)->setComment('comment'));
        $this->setColumn(Column::int('int_signed_nullable_with_default_and_comment')->setNullable()->setDefault(123)->setComment('comment'));

        $this->setColumn(Column::int('int_unsigned')->setUnsigned());
        $this->setColumn(Column::int('int_unsigned_nullable')->setUnsigned()->setNullable());
        $this->setColumn(Column::int('int_unsigned_with_default')->setUnsigned()->setDefault(123));
        $this->setColumn(Column::int('int_unsigned_nullable_with_default')->setUnsigned()->setNullable()->setDefault(123));
        $this->setColumn(Column::int('int_unsigned_with_comment')->setUnsigned()->setComment('comment'));
        $this->setColumn(Column::int('int_unsigned_nullable_with_comment')->setUnsigned()->setNullable()->setComment('comment'));
        $this->setColumn(Column::int('int_unsigned_with_default_and_comment')->setUnsigned()->setDefault(123)->setComment('comment'));
        $this->setColumn(Column::int('int_unsigned_nullable_with_default_and_comment')->setUnsigned()->setNullable()->setDefault(123)->setComment('comment'));
    }
}
