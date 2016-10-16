<?php

namespace Alius\Database;

class TableTest extends \PHPUnit_Framework_TestCase
{
    use ConnectionTrait;

    protected $database;

    public function setUp()
    {
        $this->database = new Database(new Connection(sprintf('host=%s', $this->getHost()), $this->getUser(), $this->getPassword(), $this->getDatabase()));
    }

    public function testName()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame('', $table->getName());
        $table->setName('foo');
        $this->assertSame('foo', $table->getName());
    }

    public function testEngine()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame('InnoDB', $table->getEngine());
        $table->setEngine('MyISAM');
        $this->assertSame('MyISAM', $table->getEngine());
    }

    public function testCharset()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame('utf8', $table->getCharset());
        $table->setCharset('latin1');
        $this->assertSame('latin1', $table->getCharset());
    }

    public function testCollation()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame('utf8_general_ci', $table->getCollation());
        $table->setCollation('latin1_bin');
        $this->assertSame('latin1_bin', $table->getCollation());
    }

    public function testComment()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame(false, $table->hasComment());
        $this->assertSame('', $table->getComment());
        $table->setComment('bar');
        $this->assertSame(true, $table->hasComment());
        $this->assertSame('bar', $table->getComment());
    }

    public function testColumns()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');

        $this->assertSame([], $table->getColumns());
        $this->assertSame(false, $table->hasColumn('id'));

        $column = Column::int('id');
        $table->setColumn($column);
        $this->assertSame(['id' => $column], $table->getColumns());
        $this->assertSame(true, $table->hasColumn('id'));
        $this->assertSame($column, $table->getColumn('id'));
    }

    public function testSetColumnFail()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id'));
    }

    public function testGetColumnFail()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->getColumn('id');
    }

    public function testPrimaryKey()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame(false, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasSimplePrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setPrimaryKey('id');
        $this->assertSame(true, $table->hasPrimaryKey());
        $this->assertSame(true, $table->hasSimplePrimaryKey());
        $this->assertSame(false, $table->hasCompositePrimaryKey());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setPrimaryKey('id', 'id2');
        $this->assertSame(true, $table->hasPrimaryKey());
        $this->assertSame(false, $table->hasSimplePrimaryKey());
        $this->assertSame(true, $table->hasCompositePrimaryKey());
    }

    public function testPrimaryKeySetTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setPrimaryKey('id');
        $table->setPrimaryKey('id');
    }

    public function testPrimaryKeySetNotDefinedColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setPrimaryKey('id');
    }

    public function testPrimaryKeyAutoIncrement()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::serial('id'));
        $this->assertSame(true, $table->hasPrimaryKey());
        $this->assertSame(true, $table->hasSimplePrimaryKey());
    }

    public function testUniqueKey()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame(false, $table->hasUniqueKey());
        $this->assertSame(false, $table->hasUniqueKey('unique-id'));
        $this->assertSame([], $table->getUniqueKeys());

        $table->setColumn(Column::int('id'));
        $table->setUniqueKey('id');
        $table->setColumn(Column::int('id2'));
        $this->assertSame(true, $table->hasUniqueKey());
        $this->assertSame(true, $table->hasUniqueKey('unique-id'));
        $this->assertEquals(new UniqueKey('unique-id', ['id']), $table->getUniqueKey('unique-id'));

        $table->setUniqueKey('id2');
        $this->assertSame(true, $table->hasUniqueKey('unique-id2'));
        $this->assertEquals(new UniqueKey('unique-id2', ['id2']), $table->getUniqueKey('unique-id2'));
        $this->assertSame(['unique-id' => $table->getUniqueKey('unique-id'), 'unique-id2' => $table->getUniqueKey('unique-id2')], $table->getUniqueKeys());

        $table->setUniqueKeyWithName('foo', 'id');
        $this->assertSame(true, $table->hasUniqueKey('foo'));
        $this->assertEquals(new UniqueKey('foo', ['id']), $table->getUniqueKey('foo'));
        $this->assertSame(['unique-id' => $table->getUniqueKey('unique-id'), 'unique-id2' => $table->getUniqueKey('unique-id2'), 'foo' => $table->getUniqueKey('foo')], $table->getUniqueKeys());
    }

    public function testGetNotDefinedUniqueKey()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->getUniqueKey('foo');
    }

    public function testUniqueKeySetTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setUniqueKeyWithName('id', 'id');
        $table->setUniqueKeyWithName('id', 'id');
    }

    public function testUniqueKeySetNotDefinedColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setUniqueKey('id');
    }

    public function testIndex()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $this->assertSame(false, $table->hasIndex());
        $this->assertSame(false, $table->hasIndex('index-id'));
        $this->assertSame([], $table->getIndexes());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setIndex('id');
        $this->assertSame(true, $table->hasIndex());
        $this->assertSame(true, $table->hasIndex('index-id'));
        $this->assertEquals(new Index('index-id', ['id']), $table->getIndex('index-id'));
        $this->assertSame(['index-id' => $table->getIndex('index-id')], $table->getIndexes());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setIndex('id', 'id2');
        $this->assertSame(true, $table->hasIndex());
        $this->assertSame(true, $table->hasIndex('index-id-id2'));
        $this->assertSame(['index-id-id2' => $table->getIndex('index-id-id2')], $table->getIndexes());
    }

    public function testGetNotDefinedIndex()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->getIndex('foo');
    }

    public function testIndexSetTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setIndexWithName('id', 'id');
        $table->setIndexWithName('id', 'id');
    }

    public function testIndexSetNotDefinedColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setIndex('id');
    }

    public function testDefineIndexWithSameColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setIndex('id');
        $table->setIndex('id');
    }

    public function testForeignKey()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $this->assertSame(false, $table->hasForeignKey());
        $this->assertSame(false, $table->hasForeignKey('fk_foo_1'));
        $this->assertSame([], $table->getForeignKeys());

        // simple
        $table->setColumn(Column::int('id'));
        $table->setForeignKey('id', 'foo', 'id');
        $this->assertSame(true, $table->hasForeignKey());
        $this->assertSame(true, $table->hasForeignKey('fk_foo_1'));
        $this->assertSame(true, $table->hasIndex('fk_foo_1'));
        $this->assertEquals(new ForeignKey('fk_foo_1', 'id', 'foo', 'id'), $table->getForeignKey('fk_foo_1'));
        $this->assertSame(['fk_foo_1' => $table->getForeignKey('fk_foo_1')], $table->getForeignKeys());

        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setForeignKey('id', 'foo');
        $this->assertSame(['fk_foo_1' => $table->getForeignKey('fk_foo_1')], $table->getForeignKeys());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setForeignKey(['id', 'id2'], 'foo');
        $this->assertSame(true, $table->hasForeignKey());
        $this->assertSame(true, $table->hasForeignKey('fk_foo_1'));
        $this->assertSame(true, $table->hasIndex('fk_foo_1'));
        $this->assertSame(['fk_foo_1' => $table->getForeignKey('fk_foo_1')], $table->getForeignKeys());
    }

    public function testForeignKeyCatchErrorOnDuplicatedIndex()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setIndex('id');
        $table->setForeignKey('id', 'foo', 'id');
    }

    public function testGetNotDefinedForeignKey()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->getForeignKey('foo');
    }

    public function testForeignKeySetTwice()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setColumn(Column::int('id'));
        $table->setForeignKeyWithName('bar', 'id', 'foo');
        $table->setForeignKeyWithName('bar', 'id', 'foo');
    }

    public function testForeignKeySetNotDefinedColumn()
    {
        $this->expectException(TableException::class);
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setForeignKey('id', 'foo', 'id');
    }

    public function testPrimaryKeyBuildCreate()
    {
        // no primary key
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setComment('no primary key'); // but there is a comment
        $table->setColumn(Column::int('id'));
        $this->assertSame('CREATE TABLE IF NOT EXISTS `foo` (`id` int NOT NULL) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci COMMENT="no primary key";', $table->buildCreate());
        $this->database->execute($table->buildCreate());
        $this->database->execute($table->buildDrop());

        // simple primary key
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::serial('id'));
        $this->assertSame('CREATE TABLE IF NOT EXISTS `foo` (`id` int UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table->buildCreate());
        $this->database->execute($table->buildCreate());
        $this->database->execute($table->buildDrop());

        // composite primary key
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setPrimaryKey('id', 'id2');
        $this->assertSame('CREATE TABLE IF NOT EXISTS `foo` (`id` int NOT NULL, `id2` int NOT NULL, PRIMARY KEY (`id`, `id2`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table->buildCreate());
        $this->database->execute($table->buildCreate());
        $this->database->execute($table->buildDrop());
    }

    public function testUniqueKeyBuildCreate()
    {
        // simple
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setUniqueKey('id');
        $this->assertSame('CREATE TABLE IF NOT EXISTS `foo` (`id` int NOT NULL, UNIQUE KEY `unique-id` (`id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table->buildCreate());
        $this->database->execute($table->buildCreate());
        $this->database->execute($table->buildDrop());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setUniqueKey('id');
        $this->assertSame('CREATE TABLE IF NOT EXISTS `foo` (`id` int NOT NULL, `id2` int NOT NULL, UNIQUE KEY `unique-id` (`id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table->buildCreate());
        $this->database->execute($table->buildCreate());
        $this->database->execute($table->buildDrop());
    }

    public function testIndexBuildCreate()
    {
        // simple
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setIndex('id');
        $this->assertSame('CREATE TABLE IF NOT EXISTS `foo` (`id` int NOT NULL, KEY `index-id` (`id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table->buildCreate());
        $this->database->execute($table->buildCreate());
        $this->database->execute($table->buildDrop());

        // composite
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $table->setColumn(Column::int('id'));
        $table->setColumn(Column::int('id2'));
        $table->setIndex('id');
        $this->assertSame('CREATE TABLE IF NOT EXISTS `foo` (`id` int NOT NULL, `id2` int NOT NULL, KEY `index-id` (`id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table->buildCreate());
        $this->database->execute($table->buildCreate());
        $this->database->execute($table->buildDrop());
    }

    public function testForeignKeyBuildCreate()
    {
        // simple
        $table_a = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table_a->setName('a');
        $table_a->setColumn(Column::serial('a_id'));

        $this->assertSame('CREATE TABLE IF NOT EXISTS `a` (`a_id` int UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`a_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table_a->buildCreate());

        $table_b = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table_b->setName('b');
        $table_b->setColumn(Column::serial('b_id'));
        $table_b->setColumn(Column::int('a_id')->unsigned());
        $table_b->setForeignKey('a_id', 'a');

        $this->assertSame('CREATE TABLE IF NOT EXISTS `b` (`b_id` int UNSIGNED NOT NULL AUTO_INCREMENT, `a_id` int UNSIGNED NOT NULL, PRIMARY KEY (`b_id`), KEY `fk_b_1` (`a_id`), CONSTRAINT `fk_b_1` FOREIGN KEY (`a_id`) REFERENCES `a` (`a_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table_b->buildCreate());

        $this->database->execute($table_a->buildCreate());
        $this->database->execute($table_b->buildCreate());
        $this->database->execute($table_b->buildDrop());
        $this->database->execute($table_a->buildDrop());

        // composite
        $table_a = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table_a->setName('a');
        $table_a->setColumn(Column::serial('a_id'));
        $table_a->setColumn(Column::int('a_id2'));
        $table_a->setIndex('a_id', 'a_id2'); // no composite foreign key without index in parent

        $this->assertSame('CREATE TABLE IF NOT EXISTS `a` (`a_id` int UNSIGNED NOT NULL AUTO_INCREMENT, `a_id2` int NOT NULL, PRIMARY KEY (`a_id`), KEY `index-a_id-a_id2` (`a_id`, `a_id2`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table_a->buildCreate());

        $table_b = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table_b->setName('b');
        $table_b->setColumn(Column::serial('b_id'));
        $table_b->setColumn(Column::int('a_id')->unsigned());
        $table_b->setColumn(Column::int('a_id2'));
        $table_b->setForeignKey(['a_id', 'a_id2'], 'a');

        $this->assertSame('CREATE TABLE IF NOT EXISTS `b` (`b_id` int UNSIGNED NOT NULL AUTO_INCREMENT, `a_id` int UNSIGNED NOT NULL, `a_id2` int NOT NULL, PRIMARY KEY (`b_id`), KEY `fk_b_1` (`a_id`, `a_id2`), CONSTRAINT `fk_b_1` FOREIGN KEY (`a_id`, `a_id2`) REFERENCES `a` (`a_id`, `a_id2`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;', $table_b->buildCreate());

        $this->database->execute($table_a->buildCreate());
        $this->database->execute($table_b->buildCreate());
        $this->database->execute($table_b->buildDrop());
        $this->database->execute($table_a->buildDrop());
    }

    public function testBuildDrop()
    {
        $table = new Table('InnoDB', 'utf8', 'utf8_general_ci');
        $table->setName('foo');
        $this->assertSame('DROP TABLE IF EXISTS `foo`;', $table->buildDrop());
    }
}
