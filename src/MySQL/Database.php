<?php

namespace Alius\Database\MySQL;

use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

abstract class Database implements Interfaces\DatabaseInterface
{
    protected static $name;

    private $immutable = false;
    private $engine = 'InnoDB';
    private $charset = 'utf8';
    private $collation = 'utf8_general_ci';
    private $tables = [];

    final public function __construct()
    {
        static::getName();
        $this->setUp();
    }

    final public function setImmutable(): Interfaces\DatabaseInterface
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
            throw Exceptions\SchemaException::invalidDatabaseName(static::class);
        }

        return static::$name;
    }

    protected function setUp()
    {
    }

    final public function setEngine(string $engine): Interfaces\DatabaseInterface
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

    final public function setCharset(string $charset): Interfaces\DatabaseInterface
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

    final public function setCollation(string $collation): Interfaces\DatabaseInterface
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

    final public function setTable(string $table_class): Interfaces\DatabaseInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\SchemaException::immutable(static::class);
        }

        if (! isset(class_implements($table_class)[Interfaces\TableInterface::class])) {
            throw Exceptions\SchemaException::invalidTable(Interfaces\TableInterface::class, $table_class);
        }

        if ($this->hasTable($table_class::getName())) {
            throw Exceptions\SchemaException::tableAlreadySet(static::class, $table_class);
        }

        $this->tables[$table_class::getName()] = $table_class;
        return $this;
    }

    final public function getTable(string $table_name): Interfaces\TableInterface
    {
        if (! $this->hasTable($table_name)) {
            throw Exceptions\DatabaseException::tableNotSet(static::class, $table_name);
        }

        if (is_string($this->tables[$table_name])) {
            $this->tables[$table_name] = (new $this->tables[$table_name]($this))->setImmutable();
        }

        return $this->tables[$table_name];
    }

    final public function hasTable(string $table_name = null): bool
    {
        if ($table_name === null) {
            return $this->tables !== [];
        }

        return isset($this->tables[$table_name]);
    }

    final public function getTables(): array
    {
        foreach ($this->tables as $key => $value) {
            $this->getTable($key);
        }

        return $this->tables;
    }
}
