<?php

namespace Alius\Database\MySQL;

use Alius\Database\Container;
use Alius\Database\Exceptions;
use Alius\Database\Interfaces;

abstract class Server implements Interfaces\ServerInterface
{
    protected static $name;

    private $immutable = false;
    private $writer;
    private $readers = [];
    private $databases = [];

    public function __construct(Interfaces\ConnectionInterface $writer, Interfaces\ConnectionInterface ...$readers)
    {
        $this->writer = $writer;
        $this->readers = $readers;

        static::getName();
        $this->setUpDatabase();
        $this->setImmutable();

        Container::setServer($this);
    }

    final public static function getName(): string
    {
        if (! is_string(static::$name) || static::$name === '') {
            throw Exceptions\SchemaException::invalidServerName(static::class);
        }

        return static::$name;
    }

    abstract protected function setUpDatabase();

    final private function setImmutable(): Interfaces\ServerInterface
    {
        $this->immutable = true;
        return $this;
    }

    final private function isImmutable(): bool
    {
        return $this->immutable;
    }

    final public function getWriter(): Interfaces\ConnectionInterface
    {
        return $this->writer;
    }

    final public function getReader(): Interfaces\ConnectionInterface
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

    final public function execute(string $query, array $data = []): \PDOStatement
    {
        $pdo = $this->getWriter()->getPDO();

        if (stripos($query, 'select') !== false) {
            $pdo = $this->getReader()->getPDO();
        }

        $statement = $pdo->prepare($query);
        $statement->execute($data);
        return $statement;
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

    final public function setDatabase(string $database_class): Interfaces\ServerInterface
    {
        if ($this->isImmutable()) {
            throw Exceptions\LogicException::immutable(static::class);
        }

        if (! isset(class_implements($database_class)[Interfaces\DatabaseInterface::class])) {
            throw Exceptions\SchemaException::invalidDatabase(Interfaces\DatabaseInterface::class, $database_class);
        }

        if ($this->hasDatabase($database_class::getName())) {
            throw Exceptions\SchemaException::databaseAlreadySet(get_class($this), $database_class);
        }

        $this->databases[$database_class::getName()] = $database_class;
        return $this;
    }

    final public function getDatabase(string $database_name): Interfaces\DatabaseInterface
    {
        if (! $this->hasDatabase($database_name)) {
            throw Exceptions\ServerException::databaseNotSet(static::class, $database_name);
        }

        if (is_string($this->databases[$database_name])) {
            $this->databases[$database_name] = new $this->databases[$database_name];
        }

        return $this->databases[$database_name];
    }

    final public function hasDatabase(string $database_name = null): bool
    {
        if ($database_name === null) {
            return $this->databases !== [];
        }

        return isset($this->databases[$database_name]);
    }
}
