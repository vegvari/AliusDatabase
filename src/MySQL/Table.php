<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

abstract class Table implements Interfaces\TableInterface
{
    protected static $name;

    private $immutable = false;
    private $database_name;
    private $engine;
    private $charset;
    private $collation;

    private $columns = [];
    private $primary_key;
    private $unique_keys = [];
    private $indexes = [];
    private $foreign_keys = [];

    final public function __construct(Interfaces\DatabaseInterface $database)
    {
        $this->getName();

        $this->database_name = $database::getName();
        $this->engine = $database->getEngine();
        $this->charset = $database->getCharset();
        $this->collation = $database->getCollation();

        $this->setUpEngine();
        $this->setUpCharset();
        $this->setUpCollation();
        $this->setUpColumn();
        $this->setUpPrimaryKey();
        $this->setUpUniqueKey();
        $this->setUpIndex();
        $this->setUpForeignKey();

        $this->setImmutable();
    }

    final public function setImmutable(): Interfaces\TableInterface
    {
        $this->immutable = true;
        return $this;
    }

    final public function isImmutable(): bool
    {
        return $this->immutable;
    }

    final public static function getName(): string
    {
        if (! is_string(static::$name) || static::$name === '') {
            throw Exceptions\SchemaException::invalidTableName(static::class);
        }

        return static::$name;
    }

    protected function setUpEngine()
    {
    }

    protected function setUpCharset()
    {
    }

    protected function setUpCollation()
    {
    }

    protected function setUpColumn()
    {
    }

    protected function setUpPrimaryKey()
    {
    }

    protected function setUpUniqueKey()
    {
    }

    protected function setUpIndex()
    {
    }

    protected function setUpForeignKey()
    {
    }

    final public function getDatabaseName(): string
    {
        return $this->database_name;
    }

    final public function setEngine(string $engine): Interfaces\TableInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        $this->engine = $engine;
        return $this;
    }

    final public function getEngine(): string
    {
        return $this->engine;
    }

    final public function setCharset(string $charset): Interfaces\TableInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        $this->charset = $charset;
        return $this;
    }

    final public function getCharset(): string
    {
        return $this->charset;
    }

    final public function setCollation(string $collation): Interfaces\TableInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        $this->collation = $collation;
        return $this;
    }

    final public function getCollation(): string
    {
        return $this->collation;
    }

    final public function setColumn(Interfaces\ColumnInterface $column): Interfaces\TableInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        if ($this->hasColumn($column->getName())) {
            throw Exceptions\SchemaException::columnAlreadySet(static::class, $column->getName());
        }

        $this->columns[$column->getName()] = $column;
        return $this;
    }

    final public function getColumn(string $column_name): Interfaces\ColumnInterface
    {
        if (! $this->hasColumn($column_name)) {
            throw Exceptions\TableException::columnNotSet(static::class, $column_name);
        }

        return $this->columns[$column_name];
    }

    final public function getColumns(): array
    {
        return $this->columns;
    }

    final public function hasColumn(string $column_name = null): bool
    {
        if ($column_name === null) {
            return $this->columns !== [];
        }

        return isset($this->columns[$column_name]);
    }

    final public function setPrimaryKeyObject(Interfaces\PrimaryKeyInterface $primary_key): Interfaces\TableInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        if ($this->hasPrimaryKey()) {
            throw Exceptions\SchemaException::primaryKeyAlreadySet(static::class);
        }

        $missing = array_diff($primary_key->getColumns(), array_keys($this->getColumns()));
        if ($missing !== []) {
            throw Exceptions\TableException::columnNotSet(static::class, implode(', ', $missing));
        }

        $this->primary_key = $primary_key;
        return $this;
    }

    final public function setPrimaryKey(string ...$columns): Interfaces\TableInterface
    {
        return $this->setPrimaryKeyObject(new PrimaryKey(...$columns));
    }

    final public function getPrimaryKey(): Interfaces\PrimaryKeyInterface
    {
        if (! $this->hasPrimaryKey()) {
            throw Exceptions\TableException::primaryKeyNotSet(static::class);
        }

        return $this->primary_key;
    }

    final public function hasPrimaryKey(): bool
    {
        return $this->primary_key !== null;
    }

    final public function hasCompositePrimaryKey(): bool
    {
        return $this->hasPrimaryKey() && $this->getPrimaryKey()->isComposite();
    }

    final public function setUniqueKeyObject(Interfaces\UniqueKeyInterface $unique_key): Interfaces\TableInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        if ($this->hasUniqueKey($unique_key->getName())) {
            throw Exceptions\SchemaException::uniqueKeyAlreadySet(static::class, $unique_key->getName());
        }

        $missing = array_diff($unique_key->getColumns(), array_keys($this->getColumns()));
        if ($missing !== []) {
            throw Exceptions\TableException::columnNotSet(static::class, implode(', ', $missing));
        }

        $this->unique_keys[$unique_key->getName()] = $unique_key;
        return $this;
    }

    final public function setUniqueKey(string ...$columns): Interfaces\TableInterface
    {
        return $this->setUniqueKeyObject(new UniqueKey(sprintf('unique-%s', implode('-', $columns)), ...$columns));
    }

    final public function getUniqueKey(string $name): Interfaces\UniqueKeyInterface
    {
        if (! $this->hasUniqueKey($name)) {
            throw Exceptions\TableException::uniqueKeyNotSet(static::class, $name);
        }

        return $this->unique_keys[$name];
    }

    final public function getUniqueKeys(): array
    {
        return $this->unique_keys;
    }

    final public function hasUniqueKey(string $name = null): bool
    {
        if ($name !== null) {
            return isset($this->unique_keys[$name]);
        }

        return $this->unique_keys !== [];
    }

    final public function setIndexObject(Interfaces\IndexInterface $index): Interfaces\TableInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        if ($this->hasIndex($index->getName())) {
            throw Exceptions\SchemaException::indexAlreadySet(static::class, $index->getName());
        }

        $missing = array_diff($index->getColumns(), array_keys($this->getColumns()));
        if ($missing !== []) {
            throw Exceptions\TableException::columnNotSet(static::class, implode(', ', $missing));
        }

        $this->indexes[$index->getName()] = $index;
        return $this;
    }

    final public function setIndex(string ...$columns): Interfaces\TableInterface
    {
        return $this->setIndexObject(new Index(sprintf('index-%s', implode('-', $columns)), ...$columns));
    }

    final public function getIndex(string $name): Interfaces\IndexInterface
    {
        if (! $this->hasIndex($name)) {
            throw Exceptions\TableException::indexNotSet(static::class, $name);
        }

        return $this->indexes[$name];
    }

    final public function getIndexes(): array
    {
        return $this->indexes;
    }

    final public function hasIndex(string $name = null): bool
    {
        if ($name !== null) {
            return isset($this->indexes[$name]);
        }

        return $this->indexes !== [];
    }

    final public function hasIndexWithColumns(string ...$columns): bool
    {
        foreach ($this->getIndexes() as $index) {
            if ($index->getColumns() === $columns) {
                return true;
            }
        }

        return false;
    }

    final public function setForeignKeyObject(Interfaces\ForeignKeyInterface $foreign_key): Interfaces\TableInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        if ($this->hasForeignKey($foreign_key->getName())) {
            throw Exceptions\SchemaException::foreignKeyAlreadySet(static::class, $foreign_key->getName());
        }

        $missing = array_diff($foreign_key->getColumns(), array_keys($this->getColumns()));
        if ($missing !== []) {
            throw Exceptions\TableException::columnNotSet(static::class, implode(', ', $missing));
        }

        $this->foreign_keys[$foreign_key->getName()] = $foreign_key;

        if (! $this->hasIndexWithColumns(...$foreign_key->getColumns())) {
            $this->setIndexObject(new Index($foreign_key->getName(), ...$foreign_key->getColumns()));
        }

        return $this;
    }

    final public function setForeignKey($columns, string $parent_table, $parent_columns = null, string $on_update = 'RESTRICT', string $on_delete = 'RESTRICT'): Interfaces\TableInterface
    {
        $name = sprintf('%s_ibfk_%d', static::getName(), count($this->foreign_keys) + 1);
        return $this->setForeignKeyObject(new ForeignKey($name, $columns, $parent_table, $parent_columns, $on_update, $on_delete));
    }

    final public function getForeignKey(string $name): Interfaces\ForeignKeyInterface
    {
        if (! $this->hasForeignKey($name)) {
            throw Exceptions\TableException::foreignKeyNotSet(static::class, $name);
        }

        return $this->foreign_keys[$name];
    }

    final public function getForeignKeys(): array
    {
        return $this->foreign_keys;
    }

    final public function hasForeignKey(string $name = null): bool
    {
        if ($name === null) {
            return $this->foreign_keys !== [];
        }

        return isset($this->foreign_keys[$name]);
    }
}
