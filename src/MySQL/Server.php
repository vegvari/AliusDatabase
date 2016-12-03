<?php

namespace Alius\Database\MySQL;

use Alius\Database\SchemaException;
use Alius\Database\ServerException;

abstract class Server implements ServerInterface
{
    private $immutable = false;
    private $writer;
    private $readers = [];
    private $databases = [];

    public function __construct(Connection $writer, Connection ...$readers)
    {
        $this->writer = $writer;
        $this->readers = $readers;
        $this->setUp();
    }

    protected function setUp()
    {
    }

    final public function setImmutable(): ServerInterface
    {
        $this->immutable = true;
        return $this;
    }

    final public function isImmutable(): bool
    {
        return $this->immutable;
    }

    final public function getWriter(): ConnectionInterface
    {
        return $this->writer;
    }

    final public function getReader(): ConnectionInterface
    {
        if (! $this->hasReader() || $this->inTransaction()) {
            return $this->writer;

        }

        return $this->readers[0];
    }

    final public function hasReader(): bool
    {
        return $this->readers !== [];
    }

    final public function startTransaction(): bool
    {
        return $this->getWriter()->getPDO()->beginTransaction();
    }

    final public function commit(): bool
    {
        return $this->getWriter()->getPDO()->commit();
    }

    final public function rollback(): bool
    {
        return $this->getWriter()->getPDO()->rollBack();
    }

    final public function inTransaction(): bool
    {
        return $this->getWriter()->getPDO()->inTransaction();
    }

    final public function transaction(callable $callable)
    {
        try {
            $this->startTransaction();
            $result = $callable($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
        }
    }

    final public function setDatabase(string $database_class): ServerInterface
    {
        if ($this->isImmutable()) {
            throw SchemaException::immutable(static::class);
        }

        if (! isset(class_implements($database_class)[DatabaseInterface::class])) {
            throw SchemaException::invalidDatabase(DatabaseInterface::class, $database_class);
        }

        if ($this->hasDatabase($database_class::getName())) {
            throw SchemaException::databaseAlreadySet(get_class($this), $database_class);
        }

        $this->databases[$database_class::getName()] = $database_class;
        return $this;
    }

    final public function getDatabase(string $database_name): DatabaseInterface
    {
        if (! $this->hasDatabase($database_name)) {
            throw ServerException::databaseNotSet(static::class, $database_name);
        }

        if (is_string($this->databases[$database_name])) {
            $this->databases[$database_name] = new $this->databases[$database_name];
        }

        return $this->databases[$database_name];
    }

    final public function hasDatabase(string $database_name): bool
    {
        return isset($this->databases[$database_name]);
    }
}
